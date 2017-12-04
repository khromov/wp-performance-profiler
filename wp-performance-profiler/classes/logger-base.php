<?php

namespace ICIT_Performance_Profiler;

/**
 * Base Logger
 *
 * This handles all common functionality for all loggers
 */
class Base_Logger {
    private $start;
    private $template;

    public function __construct() {
        // We only need to set the start time
        // As the other things we're tracking are measured at the end
        // And not the difference between now and then
        $this->start = microtime( true ) * 1000;

        // We'll need to get the template file for later
        add_filter( 'template_include', array( $this, 'get_template' ), 999 );
    }

    /**
     * Save the basic request and return the request ID
     *
     * This will save the duration, memory usage, database queries, URL request, template and type
     */
    public function save_request() {
        global $wpdb;

        $table    = $wpdb->prefix . 'profiler_requests';
        $now      = microtime( true ) * 1000;
        $payload  = ! empty( $_POST ) ? serialize( $_POST ) : '';
        $data     = array(
            'timestamp' => time(),
            'duration'  => $now - $this->start,
            'queries'   => get_num_queries(),
            'request'   => $_SERVER['REQUEST_URI'],
            'memory'    => Helpers::get_memory_usage(),
            'template'  => $this->template,
            'type'      => icit_profiler_current_request_type(),
            'payload'   => $payload,
        );

        $wpdb->insert( $table, $data );

        return $wpdb->insert_id;
    }

    /**
     * Allow for the bulk insertion for records into a single table in one query
     *
     * @param $table String - The name of the table to insert to (with prefix)
     * @param $schema Array - Array of columns to insert, with the key as the column name as the value as the placeholder string (%s or %d)
     * @param $data Array   - Array of data arrays, with the same column keys as $schema
     */
    public function bulk_insert( $table, $schema, $data ) {
        global $wpdb;

        // Create the basic query structure
        // We'll then create the other variables later
        $query = "INSERT INTO %s (%s) VALUES %s;";

        // Create the schema string
        $columns = implode( ', ', array_keys( $schema ) );

        // Loop through the data and setup the placeholders and values
        // This is because we're going to use prepare() to replace them
        $placeholders = array();
        $values       = array();

        foreach( $data as $row ) {
            $placeholders[] = '(' . implode( ', ', $schema ) . ')';

            foreach( $row as $value ) {
                $values[] = $value;
            }
        }

        // Finish building the query string
        $placeholders = implode( ', ', $placeholders );
        $query = sprintf( $query, $table, $columns, $placeholders );
        $query = $wpdb->prepare( $query, $values );

        return $wpdb->query( $query );
    }

    /**
     * Save the loaded template file for use later
     *
     * This will strip out the full path
     */
    public function get_template( $template ) {
        $this->template = str_replace( get_template_directory(), '', $template );

        return $template;
    }
}
