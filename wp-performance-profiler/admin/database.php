<?php

namespace ICIT_Performance_Profiler;

class Database {
    public static function get_all_plugins() {
        global $wpdb;

        return $wpdb->get_col( "
            SELECT DISTINCT plugin
            FROM {$wpdb->prefix}profiler_queries
            WHERE plugin != ''
            ORDER BY plugin ASC
        " );
    }
}
