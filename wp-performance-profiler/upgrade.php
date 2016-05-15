<?php

namespace ICIT_Performance_Profiler;

/**
 * Handle all installation and upgrade functionality
 * This will mostly consist of creating / alterign the DB schema
 */

if( ! class_exists( 'Upgrade' ) ) {
    class Upgrade {
        private static $instance;

        public static function instance() {
            if( self::$instance === null ) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function __construct() {
            global $wpdb;

            $settings   = get_option( 'icit_performance_profiler' );
            $db_version = absint( isset( $settings['database_version'] ) ? $settings['database_version'] : 0 );

            // Are we making any updates?
            if( $db_version !== Core::DB_VERSION ) {
                // Load the required helper functions
                if ( ( ! function_exists( 'maybe_create_table' ) || ! function_exists( 'check_column' ) ) && file_exists( ABSPATH . '/wp-admin/install-helper.php' ) ) {
                    require_once( ABSPATH . '/wp-admin/install-helper.php' );
                }

                // Run the migrations
                if( $db_version < 1 ) {
                    $this->create_request_table();
                    $this->create_plugins_table();
                    $this->create_functions_table();
                    $this->create_queries_table();
                }

                if( $db_version < 2 ) {
                    $this->create_details_table();
                }

                // Update the database version, so we don't do this again
                $settings['database_version'] = Core::DB_VERSION;
                update_option( 'icit_performance_profiler', $settings );
            }
        }

        private function create_request_table() {
            $table   = $this->get_table( 'requests' );
            $charset = $this->get_charset();

            maybe_create_table( $table, "CREATE TABLE $table (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `timestamp` int(11) DEFAULT NULL,
                `request` varchar(1024) DEFAULT NULL,
                `type` varchar(8) DEFAULT NULL,
                `template` varchar(1024) DEFAULT NULL,
                `duration` double DEFAULT NULL,
                `memory` int(11) DEFAULT NULL,
                `queries` int(11) DEFAULT NULL,
                `payload` text,
                PRIMARY KEY (`id`),
                KEY `timestamp` (`timestamp`),
                KEY `type` (`type`),
                KEY `template` (`template`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset;" );
        }

        private function create_plugins_table() {
            $table   = $this->get_table( 'plugins' );
            $charset = $this->get_charset();

            maybe_create_table( $table, "CREATE TABLE $table (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `request_id` int(11) DEFAULT NULL,
                `plugin` varchar(64) DEFAULT NULL,
                `count` int(11) DEFAULT NULL,
                `duration` double DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `request_id` (`request_id`),
                KEY `count` (`count`),
                KEY `duration` (`duration`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset;" );
        }

        private function create_functions_table() {
            $table   = $this->get_table( 'functions' );
            $charset = $this->get_charset();

            maybe_create_table( $table, "CREATE TABLE $table (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `request_id` int(11) DEFAULT NULL,
                `plugin` varchar(64) DEFAULT NULL,
                `function` varchar(256) DEFAULT NULL,
                `count` int(11) DEFAULT NULL,
                `duration` float DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `request_id` (`request_id`),
                KEY `plugin` (`plugin`),
                KEY `function` (`function`),
                KEY `count` (`count`),
                KEY `duration` (`duration`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset;" );
        }

        private function create_queries_table() {
            $table   = $this->get_table( 'queries' );
            $charset = $this->get_charset();

            maybe_create_table( $table, "CREATE TABLE $table (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `request_id` int(11) DEFAULT NULL,
                `duration` double DEFAULT NULL,
                `plugin` varchar(64) DEFAULT NULL,
                `the_query` varchar(2048) DEFAULT NULL,
                `stack` varchar(2048) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `request_id` (`request_id`),
                KEY `duration` (`duration`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset;" );
        }

        private function create_details_table() {
            $table   = $this->get_table( 'details' );
            $charset = $this->get_charset();

            maybe_create_table( $table, "CREATE TABLE $table (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `request_id` int(11) DEFAULT NULL,
                `duration_core` double DEFAULT NULL,
                `duration_themes` double DEFAULT NULL,
                `duration_plugins` double DEFAULT NULL,
                `duration_mu_plugins` double DEFAULT NULL,
                `duration_database` double DEFAULT NULL,
                `duration_http` double DEFAULT NULL,
                `duration_misc` double DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset;" );
        }

        private function get_table( $name ) {
            global $wpdb;

            return $wpdb->prefix . 'profiler_' . $name;
        }

        private function get_charset() {
            global $wpdb;

            $charset = '';

            if ( ! empty( $wpdb->charset ) )
                $charset .= "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty( $wpdb->collate ) )
                $charset .= " COLLATE $wpdb->collate";

            return $charset;
        }
    }

    add_action( 'admin_init', array( '\ICIT_Performance_Profiler\Upgrade', 'instance' ) );
}
