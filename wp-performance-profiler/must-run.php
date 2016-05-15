<?php

namespace ICIT_Performance_Profiler;

/**
 * This file is executed even if the (must-use) plugin is uninstalled
 * If you only install it as a regular plugin and uninstall it, obviously there is no way for this file to get loaded
 *
 * Keep this file extra lean as it'll execute on every request
 */

if( ! class_exists( 'Must_Run' ) ) {
    class Must_Run {
        private static $instance;

        public static function instance() {
            if( self::$instance === null ) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function __construct() {
            add_filter( 'plugin_row_meta', array( $this, 'add_activate_link' ), 10, 4 );
            add_action( 'admin_init', array( $this, 'admin_actions' ) );
        }

        public function add_activate_link( $meta, $file, $data, $status ) {
            $is_bootstrapped = defined( 'ICIT_PERFORMANCE_PROFILER_LOADER_FILE' ) && $file === ICIT_PERFORMANCE_PROFILER_LOADER_FILE;
            $is_active       = icit_profiler_is_active();
            $is_mustuse      = $status === 'mustuse';

            if( $is_bootstrapped && ! $is_active && $is_mustuse ) {
                $meta[] = '<a href="' . admin_url( 'admin.php?action=activate_icit_performance_profiler' ) . '">Activate</a>';
            } else if( $is_bootstrapped && $is_active && $is_mustuse ) {
                $meta[] = '<a href="' . admin_url( 'admin.php?action=deactivate_icit_performance_profiler' ) . '">Deactivate</a>';
            }

            return $meta;
        }

        public function admin_actions() {
            if ( empty( $_GET['action'] ) ) return;

            switch( $_GET['action'] ) {
                case 'activate_icit_performance_profiler':
                    $settings           = get_option( 'icit_performance_profiler', array() );
                    $settings['active'] = true;

                    update_option( 'icit_performance_profiler', $settings );
                    wp_redirect( admin_url( 'plugins.php?plugin_status=mustuse' ) );
                    exit;
                case 'deactivate_icit_performance_profiler':
                    $settings           = get_option( 'icit_performance_profiler', array() );
                    $settings['active'] = false;

                    update_option( 'icit_performance_profiler', $settings );
                    wp_redirect( admin_url( 'plugins.php?plugin_status=mustuse' ) );
                    exit;
            }
        }
    }

    Must_Run::instance();
}
