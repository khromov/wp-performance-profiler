<?php

namespace ICIT_Performance_Profiler;

/**
 * Basic Logger
 *
 * This will track the duration, number of queries and memory usage
 * And log them to the database with information about the request
 */
class Basic_Logger extends Base_Logger {
    public function __construct() {
        parent::__construct();
    }

    public function save() {
        global $wpdb;

        // Save the base request
        $request_id = $this->save_request();

        // We need to save the duration of DB queries as well
        $database_duration = 0;
        foreach( $wpdb->queries as $query ) {
            $database_duration += $query[1] * 1000;
        }

        $table = $wpdb->prefix . 'profiler_details';
        $wpdb->insert( $table, array(
            'request_id'        => $request_id,
            'duration_database' => $database_duration,
        ) );
    }

    public function render() {

    }
}
