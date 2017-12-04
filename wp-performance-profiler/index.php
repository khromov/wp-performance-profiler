<?php

namespace ICIT_Performance_Profiler;

/**
 * Plugin Name: WordPress Performance Profiler (plugin version)
 * Plugin URI:  http://interconnectit.com
 * Author:      Damian Gostomski
 * Author URI:  http://interconnectit.com
 * Description: Pinpoint slow parts of your site, so that you can make them faster.
 * Version:     0.4
 */

// Setup helper constants
! defined( 'ICIT_PERFORMANCE_PROFILER_DIR' ) && define( 'ICIT_PERFORMANCE_PROFILER_DIR', trailingslashit( dirname( __FILE__ ) ) );
! defined( 'ICIT_PERFORMANCE_PROFILER_URL' ) && define( 'ICIT_PERFORMANCE_PROFILER_URL', plugin_dir_url( __FILE__ ) );
! defined( 'ICIT_PERFORMANCE_PROFILER_PLUGIN_FILE' ) && define( 'ICIT_PERFORMANCE_PROFILER_PLUGIN_FILE', plugin_basename(__FILE__ ) );

require_once ICIT_PERFORMANCE_PROFILER_DIR . 'must-run.php';

// Ensure that we don't run the plugin multiple times
// This can occur when the plugin is installed both an must-use and regular plugin
if( function_exists( 'icit_profiler_is_loaded' ) && icit_profiler_is_loaded() && function_exists( 'icit_profiler_is_active' ) && icit_profiler_is_active() ) return;


// Include required files
require_once ICIT_PERFORMANCE_PROFILER_DIR . 'util.php';
require_once ICIT_PERFORMANCE_PROFILER_DIR . 'upgrade.php';
require_once ICIT_PERFORMANCE_PROFILER_DIR . 'compatibility.php';
require_once ICIT_PERFORMANCE_PROFILER_DIR . 'classes/helpers.php';
require_once ICIT_PERFORMANCE_PROFILER_DIR . 'classes/logger-base.php';

class Core {
    private static $instance;
    private static $logger;

    const DB_VERSION = 2;

    public static function instance() {
        if( self::$instance === null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Setup the profiler
     *
     * Determine the level of logging (if any) and get everything setup
     */
    public function __construct() {
        // Only proceed if the plugin is active
        // This handles all the edge cases between must-use and regular plugins
        // To make sure we only load and execute one of them
        if( ! icit_profiler_is_active() ) return;

        $logging_level = $this->get_logging_level();

        // If we're in the admin, do the core setup
        // This is on top of optionally logging as well
        if( is_admin() ) {
            require_once ICIT_PERFORMANCE_PROFILER_DIR . 'admin/index.php';
            require_once ICIT_PERFORMANCE_PROFILER_DIR . 'classes/pagination.php';
        }

        // Create an instance of the logger, which will take over from here
        switch( $logging_level ) {
            case 'basic':
                require_once ICIT_PERFORMANCE_PROFILER_DIR . 'classes/logger-basic.php';
                self::$logger = new Basic_Logger;
                break;
            case 'advanced':
                require_once ICIT_PERFORMANCE_PROFILER_DIR . 'classes/logger-advanced.php';
                self::$logger = new Advanced_Logger;
                break;
        }

        // Register a callback to save and render the data
        if( self::$logger !== null ) {
            add_action( 'shutdown', array( self::$logger, 'save' ), 9999 );
        }
    }

    public function is_active() {
        $settings = get_option( 'icit_performance_profiler' );

        return isset( $settings['active'] ) ? $settings['active'] : true;
    }

    public function get_logging_level() {
        $settings = get_option( 'icit_performance_profiler' );
        $type     = icit_profiler_current_request_type();

        // Are we forcing logging via the query string?
        // This overrides the other settings below
        if( isset( $_GET['profiler'] ) ) {
            $level = $_GET['profiler'];

            // If we've set the type, it must be part of our whitelist
            // Else, we're set it to the default
            return in_array( $level, array( 'basic', 'advanced' ) ) ? $level : 'advanced';
        }

        // Are we logging this type of request?
        if( ! isset( $settings['request_types'][ $type ] ) ) return 'none';

        // Do we need either basic or advanced logging
        // We'll always check advanced first, as we'd rather that than basic
        // As we want the ability to have a decimal percentage
        // We get a number between 0 and 10000 and divide by 100
        // This will give us 2 decimal places
        $chance = rand( 0, 10000 ) / 100;
        if( $chance <= $settings['advanced_frequency'] ) return 'advanced';
        if( $chance <= $settings['basic_frequency'] ) return 'basic';

        return 'none';
    }
}
Core::instance();
