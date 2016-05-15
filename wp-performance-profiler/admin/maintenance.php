<?php

namespace ICIT_Performance_Profiler;

/**
 *
 */

class Maintenance_Controller {
    public function run() {
        global $wpdb;

        if( isset( $_GET['action'] ) && $_GET['action'] === 'uninstall' ) {
            // Delete all the tables
            $wpdb->query( "DROP TABLE {$wpdb->prefix}profiler_functions" );
            $wpdb->query( "DROP TABLE {$wpdb->prefix}profiler_plugins" );
            $wpdb->query( "DROP TABLE {$wpdb->prefix}profiler_queries" );
            $wpdb->query( "DROP TABLE {$wpdb->prefix}profiler_requests" );

            // Update the settings to say the plugin is disabled
            // This is necessary because we can't conventionally disable the plugin
            $settings = get_option( 'icit_performance_profiler', array() );
            $settings['active'] = false;
            update_option( 'icit_performance_profiler', $settings );

            wp_redirect( admin_url( '/' ) );
            exit;
        }
    }
}
