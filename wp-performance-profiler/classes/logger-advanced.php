<?php

namespace ICIT_Performance_Profiler;

/**
 * Advanced Logger
 *
 * This will track all function calls and database queries along with their duration
 * As well as total page duration, queries and memory usage
 * They will be logged to the database and optionally rendered on the front end
 */
class Advanced_Logger extends Base_Logger {
    private $time;
    private $stats         = array();
    private $queries       = array();
    private $last_file     = '';
    private $last_function = '';

    private $time_core     = 0;
    private $time_theme    = 0;
    private $time_plugin   = 0;
    private $time_muplugin = 0;
    private $time_database = 0;

    private $content_dir   = '';
    private $theme_dir     = '';
    private $plugin_dir    = '';
    private $mu_plugin_dir = '';

    public function __construct() {
        parent::__construct();

        $this->time          = microtime( true ) * 1000;
        $this->content_dir   = wp_normalize_path( WP_CONTENT_DIR );
        $this->theme_dir     = get_theme_root();
        $this->plugin_dir    = wp_normalize_path( WP_PLUGIN_DIR );
        $this->mu_plugin_dir = wp_normalize_path( WPMU_PLUGIN_DIR );

        declare( ticks = 1 );
        register_tick_function( array( $this, 'do_tick' ) );

        if( ! defined( 'SAVEQUERIES') ) {
            define( 'SAVEQUERIES', true );
        }

        add_filter( 'query', array( $this, 'log_query' ) );
    }

    /**
     * Master function which records all the stats
     *
     * This is called once for every tick (not including core PHP)
     * And we log metrics between ticks to get an idea of stats
     */
    function do_tick() {
        // Get the current trace
        $trace = debug_backtrace();

        // Ensure we have some data to work with
        // Check the second slot as this is the first one in the trace
        if( ! isset( $trace[1]['file'] ) ) return;

        // Get all the information we need
        $now      = microtime( true ) * 1000;
        $file     = wp_normalize_path( $trace[1]['file'] );
        $function = $trace[1]['function'];
        $line     = $trace[1]['line'];
        $duration = $now - $this->time;

        // If the file and function are the same as the last ones
        // Ignore this tick, as it's (most likely) the same function call
        if( $file === $this->last_file && $function === $this->last_function ) return;

        // We can safely update the class variables now
        $this->last_file     = $file;
        $this->last_function = $function;
        $this->time          = $now;

        // Determine if this tick is core, themes, plugins or mu-plugins
        $is_theme    = strpos( $file, $this->theme_dir ) !== false;
        $is_plugin   = strpos( $file, $this->plugin_dir ) !== false;
        $is_muplugin = strpos( $file, $this->mu_plugin_dir ) !== false;
        $is_core     = ! $is_theme && ! $is_plugin && ! $is_muplugin;

        // Update the running tally for how long this type of request took
        if( $is_theme ) $this->time_theme       += $duration;
        if( $is_plugin ) $this->time_plugin     += $duration;
        if( $is_muplugin ) $this->time_muplugin += $duration;
        if( $is_core ) $this->time_core         += $duration;

        // At this point, we're no longer interested in measuring core
        // Crucially, we've updated the time, so future time logs will be accurate
        if( $is_core ) return;

        // What plugin is this part of?
        // $1 - One of themes, plugins, mu-plugins etc
        // $2 - The folder name of the plugin
        // $3 - The filename within the plugin
        $regex  = '#' . $this->content_dir . '/([^\/]+)/([^\/]+)/(.+)#';
        $plugin = preg_replace( $regex, '$2', $file );

        // If this plugin / function doesn't exist, initialise it
        if( ! isset( $this->stats[ $plugin ] ) ) {
            $this->stats[ $plugin ] = array( 'plugin' => $plugin, 'duration' => 0, 'count' => 0, 'functions' => array() );
        }
        if( ! isset( $this->stats[ $plugin ]['functions'][ $function ] ) ) {
           $this->stats[ $plugin ]['functions'][ $function ] = array( 'function' => $function, 'duration' => 0, 'count' => 0 );
        }

        // Add the new stats
        $this->stats[ $plugin ]['duration'] += $duration;
        $this->stats[ $plugin ]['count']    += 1;

        $this->stats[ $plugin ]['functions'][ $function ]['duration'] += $duration;
        $this->stats[ $plugin ]['functions'][ $function ]['count']    += 1;

        $this->stack[] = array(
            'file'     => $file,
            'line'     => $line,
            'function' => $function,
            'plugin'   => $plugin,
            'duration' => $duration,
        );
    }

    public function save() {
        global $wpdb;

        // First, we need to save the basic request, so we can link everything together
        $request_id = $this->save_request();

        // Next, we save top level plugin data
        // This will be the plugin, runtime and call count
        // Although this information could be computed from the function calls when doign reporting
        // It would suffer a huge performance hit due to the large number of records there
        // And this also allows for an intermediate level of logging in the future, where we just store plugin level stats
        $table  = $wpdb->prefix . 'profiler_plugins';
        $schema = array(
            'request_id' => '%d',
            'plugin'     => '%s',
            'count'      => '%d',
            'duration'   => '%f',
        );
        $data   = array();

        foreach( $this->stats as $plugin ) {
            $data[] = array(
                'request_id' => $request_id,
                'plugin'     => $plugin['plugin'],
                'count'      => $plugin['count'],
                'duration'   => $plugin['duration'],
            );
        }

        $this->bulk_insert( $table, $schema, $data );

        // We'll also store all the database queries and link them to this request
        $table  = $wpdb->prefix . 'profiler_queries';
        $schema = array(
            'request_id'     => '%d',
            'duration'       => '%f',
            'plugin'         => '%s',
            'the_query'      => '%s',
            'stack'          => '%s',
        );
        $data   = array();
        foreach( $wpdb->queries as $query ) {
            $data[] = array(
                'request_id' => $request_id,
                'duration'   => $query[1] * 1000,
                'plugin'     => $this->get_plugin_from_query( $query[0] ),
                'the_query'  => $query[0],
                'stack'      => $query[2],
            );

            $this->time_database += $query[1] * 1000;
        }

        $this->bulk_insert( $table, $schema, $data );

        // Then, we'll store the detailed logs for each function
        $table  = $wpdb->prefix . 'profiler_functions';
        $schema = array(
            'request_id' => '%d',
            'plugin'     => '%s',
            'function'   => '%s',
            'count'      => '%d',
            'duration'   => '%f',
        );
        $data   = array();

        foreach( $this->stats as $plugin ) {
            foreach( $plugin['functions'] as $function ) {
                $data[] = array(
                    'request_id' => $request_id,
                    'plugin'     => $plugin['plugin'],
                    'function'   => $function['function'],
                    'count'      => $function['count'],
                    'duration'   => $function['duration'],
                );
            }
        }

        $this->bulk_insert( $table, $schema, $data );

        // Insert the detailed information for this request
        $table = $wpdb->prefix . 'profiler_details';
        $wpdb->insert( $table, array(
            'request_id'          => $request_id,
            'duration_core'       => $this->time_core,
            'duration_themes'     => $this->time_theme,
            'duration_plugins'    => $this->time_plugin,
            'duration_mu_plugins' => $this->time_muplugin,
            'duration_database'   => $this->time_database,
        ) );
    }

    public function render() {
        $stats = $this->stats;

        // Order the stats
        // First, we order the function time for each plugin
        // And then the plugins as a whole
        foreach( $stats as $name => $plugin ) {
            usort( $plugin['functions'], function( $a, $b ) {
                return $a['duration'] < $b['duration'];
            } );

            $stats[ $name ] = $plugin;
        }

        usort( $stats, function( $a, $b ) {
                return $a['duration'] < $b['duration'];
        } );

        include ICIT_PERFORMANCE_PROFILER_DIR . 'views/results-advanced.php';
    }

    public function log_query( $query ) {
        // debug_backtrace() and $exception->getTrace() give different traces
        // The exception route seems to give the actual call trace
        $exception = new \Exception;
        $trace     = $exception->getTrace();
        $type      = 'core';
        $source    = '';

        // Loop through all the included files until we find one that is either a theme or plugin
        foreach( $trace as $point ) {
            if( ! isset( $point['file'] ) ) continue;

            if( strpos( $point['file'], $this->plugin_dir ) !== false ) {
                $type   = 'plugin';
                $source = preg_replace( '#' . $this->plugin_dir . '/([^\/]+)/.+#', '$1', $point['file'] );
                continue;
            } else if( strpos( $point['file'], $this->theme_dir ) !== false ) {
                $type   = 'theme';
                $source = preg_replace( '#' . $this->theme_dir . '/([^\/]+)/.+#', '$1', $point['file'] );
                continue;
            }
        }

        $this->queries[] = array(
            'query'  => $query,
            'type'   => $type,
            'source' => $source,
        );

        return $query;
    }

    private function get_plugin_from_query( $sql ) {
        foreach( $this->queries as $query ) {
            if( $query['query'] === $sql ) {
                return $query['source'];
            }
        }

        return '';
    }
}
