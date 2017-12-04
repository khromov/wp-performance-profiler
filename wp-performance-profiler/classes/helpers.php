<?php

namespace ICIT_Performance_Profiler;

class Helpers {
    public static function order( $a, $b ) {
        if( is_object( $a ) ) {
            return $a->duration < $b->duration;
        } else {
            return $a['duration'] < $b['duration'];
        }
    }

    public static function human_filesize( $bytes, $decimals = 2 ) {
        $size   = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];
    }

    public static function pagination_link( $page ) {
        $url         = $_SERVER['SCRIPT_NAME'];
        $args        = $_GET;
        $args['p']   = $page;
        $querystring = http_build_query( $args );

        return $url . '?' . $querystring;
    }

    public static function querystring_value( $key, $default = '' ) {
        return isset( $_GET[ $key ] ) ? $_GET[ $key ] : $default;
    }

    public static function has_details( $request_id ) {
        global $wpdb;

        static $requests;

        if( $requests === null ) {
            $requests = $wpdb->get_col( "
                SELECT DISTINCT request_id
                FROM {$wpdb->prefix}profiler_functions"
            );

            $requests = array_flip( $requests );
        }

        return isset( $requests[ $request_id ] );
    }

    public static function get_memory_usage() {
      if ( function_exists( 'memory_get_peak_usage' ) ) {
        $memory_usage = memory_get_peak_usage();
      } elseif ( function_exists( 'memory_get_usage' ) ) {
        $memory_usage = memory_get_usage();
      } else {
        $memory_usage = 0;
      }

      return $memory_usage;
    }
}
