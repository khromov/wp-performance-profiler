<?php

namespace ICIT_Performance_Profiler;

/**
 * Admin bootstrap functionality
 */

class Admin {
    private static $instance;

    public static function instance() {
        if( self::$instance === null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     *
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'setup_menu' ) );
        add_filter( 'parent_file', array( $this, 'set_correct_submenu_item' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Setup our settings if we're on the settings tab of our plugin
        require_once ICIT_PERFORMANCE_PROFILER_DIR . 'admin/settings.php';
        $settings = Settings::instance();
        add_action( 'admin_init', array( $settings, 'register_settings' ) );

        $action = isset( $_GET['tab'] ) ? $_GET['tab'] : 'requests';

        if( $action === 'maintenance' ) {
            require_once ICIT_PERFORMANCE_PROFILER_DIR . 'admin/maintenance.php';
            $controller = new Maintenance_Controller;

            add_action( 'admin_init', array( $controller, 'run' ) );
        }
    }

    public function setup_menu() {
        add_menu_page( 'Performance Profiler', 'Profiler', 'manage_options', 'icit-profiler', array( $this, 'render_page' ), 'dashicons-chart-line' );
        add_submenu_page( 'icit-profiler', 'Requests', 'Requests', 'manage_options', 'icit-profiler' );
        add_submenu_page( 'icit-profiler', 'Plugins', 'Plugins', 'manage_options', 'admin.php?page=icit-profiler&tab=plugins' );
        add_submenu_page( 'icit-profiler', 'Database', 'Database', 'manage_options', 'admin.php?page=icit-profiler&tab=database' );
        add_submenu_page( 'icit-profiler', 'Settings', 'Settings', 'manage_options', 'admin.php?page=icit-profiler&tab=settings' );
        add_submenu_page( 'icit-profiler', 'Maintenance', 'Maintenance', 'manage_options', 'admin.php?page=icit-profiler&tab=maintenance' );
        add_submenu_page( 'icit-profiler', 'Help', 'Help', 'manage_options', 'admin.php?page=icit-profiler&tab=help' );
    }

    public function set_correct_submenu_item( $parent ) {
        global $submenu_file;

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'icit-profiler' && isset( $_GET['tab'] ) ) {
            $submenu_file = 'admin.php?page=icit-profiler&tab=' . $_GET['tab'];
        }

        return $parent;
    }

    public function admin_assets() {
        // Only proceed if we're on our admin pages
        if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'icit-profiler' ) return;

        wp_enqueue_style( 'icit-profiler-admin', ICIT_PERFORMANCE_PROFILER_URL . 'assets/css/admin.css' );
        wp_enqueue_script( 'icit-profiler-admin', ICIT_PERFORMANCE_PROFILER_URL . 'assets/js/admin.js', array( 'jquery' ) );
    }

    public function render_page() {
        global $wpdb;
        ?>
        <div class="wrap">
            <h2>Performance Profiler</h2>

            <h2 class="nav-tab-wrapper">
                <a href="?page=icit-profiler&tab=requests" class="nav-tab <?php $this->is_tab_active('requests')?>">Requests</a>
                <a href="?page=icit-profiler&tab=plugins" class="nav-tab <?php $this->is_tab_active('plugins')?>">Plugins</a>
                <a href="?page=icit-profiler&tab=detail" class="nav-tab <?php $this->is_tab_active('detail')?>">In Depth</a>
                <a href="?page=icit-profiler&tab=database" class="nav-tab <?php $this->is_tab_active('database')?>">Database</a>
                <a href="?page=icit-profiler&tab=settings" class="nav-tab <?php $this->is_tab_active('settings')?>">Settings</a>
                <a href="?page=icit-profiler&tab=maintenance" class="nav-tab <?php $this->is_tab_active('maintenance')?>">Maintenance</a>
                <a href="?page=icit-profiler&tab=help" class="nav-tab <?php $this->is_tab_active('help')?>">Help</a>
            </h2>
        </div>

        <div class="wrap">
            <?php
            $tab  = $this->get_active_tab();

            switch( $tab ) {
                case 'requests':
                    // Setup the database query
                    $results_query = "SELECT * FROM {$wpdb->prefix}profiler_requests WHERE 1 = 1";
                    $count_query   = "SELECT COUNT(id) FROM {$wpdb->prefix}profiler_requests WHERE 1 = 1";
                    $values        = array();

                    // Are we filtering by date?
                    if( ! empty( $_GET['date'] ) ) {
                        $results_query .= " AND timestamp > %d";
                        $count_query   .= " AND timestamp > %d";
                        $values[]       = strtotime( $_GET['date'] );
                    }

                    // Do we want to filter on the URL?
                    if( ! empty( $_GET['url'] ) ) {
                        $results_query .= " AND request LIKE %s";
                        $count_query   .= " AND request LIKE %s";
                        $values[]       = '%' . $_GET['url'] . '%';
                    }

                    // Do we want to filter just slow loading request?
                    if( ! empty( $_GET['duration'] ) ) {
                        $results_query .= " AND duration > %d";
                        $count_query   .= " AND duration > %d";
                        $values[]       = absint( $_GET['duration'] );
                    }

                    // Do we want to filter by memory consumption
                    if( ! empty( $_GET['memory'] ) ) {
                        $results_query .= " AND memory > %d";
                        $count_query   .= " AND memory > %d";
                        $values[]       = absint( $_GET['memory'] ) * 1024 * 1024;
                    }

                    // Are we filtering by number of database queries
                    if( ! empty( $_GET['queries'] ) ) {
                        $results_query .= " AND queries > %d";
                        $count_query   .= " AND queries > %d";
                        $values[]       = absint( $_GET['queries'] );
                    }

                    // Are we filtering by template?
                    if( ! empty( $_GET['template'] ) ) {
                        $results_query .= " AND template = %s";
                        $count_query   .= " AND template = %s";
                        $values[]       = $_GET['template'];
                    }

                    // Are we filtering by type?
                    if( ! empty( $_GET['type'] ) ) {
                        $results_query .= " AND type = %s";
                        $count_query   .= " AND type = %s";
                        $values[]       = $_GET['type'];
                    }

                    // Set the order
                    $results_query .= " ORDER BY id DESC";

                    // Add pagination
                    $results_query .= " LIMIT %d, 100";
                    $values[]       = isset( $_GET['p'] ) ? ( $_GET['p'] - 1 ) * 100 : 0;

                    // Execute the query
                    $results_query = $wpdb->prepare( $results_query, $values );
                    $rows          = $wpdb->get_results( $results_query );

                    // Get the total number of rows
                    if( count( $values) > 0 ) {
                        array_pop($values);
                    }
                    if( count( $values ) ) {
                        $count_query = $wpdb->prepare( $count_query, $values );
                    }

                    $total_rows      = $wpdb->get_var( $count_query );

                    break;
                case 'plugins':
                    $data    = array();
                    $types   = array( 'front', 'admin', 'ajax', 'cron' );
                    $showing = ! empty( $_GET['profiler_plugin_showing'] ) ? $_GET['profiler_plugin_showing'] : 'average';

                    // Set our queries depending on what we want to show
                    if( $showing === 'median' ) {
                        $query_average = "
                            SELECT functions.plugin, functions.function, STD(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            WHERE 1=1
                            GROUP BY plugin, function;
                        ";
                        $query_type    = "
                            SELECT functions.plugin, functions.function, STD(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            JOIN {$wpdb->prefix}profiler_requests requests
                            WHERE 1=1
                            AND functions.request_id = requests.id
                            AND requests.type = '%s'
                            GROUP BY plugin, function;
                        ";
                    }  else if( $showing === 'deviation' ) {
                        $query_average = "
                            SELECT functions.plugin, functions.function, STD(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            WHERE 1=1
                            GROUP BY plugin, function;
                        ";
                        $query_type    = "
                            SELECT functions.plugin, functions.function, STD(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            JOIN {$wpdb->prefix}profiler_requests requests
                            WHERE 1=1
                            AND functions.request_id = requests.id
                            AND requests.type = '%s'
                            GROUP BY plugin, function;
                        ";
                    } else if( $showing === 'minimum' ) {
                        $query_average = "
                            SELECT functions.plugin, functions.function, MIN(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            WHERE 1=1
                            GROUP BY plugin, function;
                        ";
                        $query_type    = "
                            SELECT functions.plugin, functions.function, MIN(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            JOIN {$wpdb->prefix}profiler_requests requests
                            WHERE 1=1
                            AND functions.request_id = requests.id
                            AND requests.type = '%s'
                            GROUP BY plugin, function;
                        ";
                    } else if( $showing === 'maximum' ) {
                        $query_average = "
                            SELECT functions.plugin, functions.function, MAX(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            WHERE 1=1
                            GROUP BY plugin, function;
                        ";
                        $query_type    = "
                            SELECT functions.plugin, functions.function, MAX(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            JOIN {$wpdb->prefix}profiler_requests requests
                            WHERE 1=1
                            AND functions.request_id = requests.id
                            AND requests.type = '%s'
                            GROUP BY plugin, function;
                        ";
                    } else {
                        $query_average = "
                            SELECT functions.plugin, functions.function, SUM(functions.duration)/COUNT(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            WHERE 1=1
                            GROUP BY plugin, function;
                        ";
                        $query_type    = "
                            SELECT functions.plugin, functions.function, SUM(functions.duration)/COUNT(functions.duration) AS duration
                            FROM {$wpdb->prefix}profiler_functions functions
                            JOIN {$wpdb->prefix}profiler_requests requests
                            WHERE 1=1
                            AND functions.request_id = requests.id
                            AND requests.type = '%s'
                            GROUP BY plugin, function;
                        ";
                    }

                    // Get and process the durations across all request types
                    $results = $wpdb->get_results( $query_average );

                    foreach( $results as $row ) {
                        // Check the plugin is set
                        if( ! isset( $data[ $row->plugin ] ) ) {
                            $data[ $row->plugin ] = array(
                                'plugin'    => $row->plugin,
                                'duration'  => array( 'average' => 0, 'front' => 0, 'admin' => 0, 'ajax' => 0, 'cron' => 0 ),
                                'functions' => array(),
                            );
                        }

                        // Check the function is set
                        if( ! isset( $data[ $row->plugin ]['functions'][ $row->function ] ) ) {
                            $data[ $row->plugin ]['functions'][ $row->function ] = array( 'function' => $row->function );
                        }

                        // Set the average duration and increment the plugin timer
                        $data[ $row->plugin ]['functions'][ $row->function ]['average'] = $row->duration;
                        $data[ $row->plugin ]['duration']['average']                   += $row->duration;
                    }

                    // Get and process the durations for eachrequest type
                    foreach( $types as $type ) {
                        $results = $wpdb->get_results( $wpdb->prepare( $query_type, $type ) );

                        foreach( $results as $row ) {
                            // Set the type specific duration and increment the plugin timer
                            $data[ $row->plugin ]['functions'][ $row->function ][ $type] = $row->duration;
                            $data[ $row->plugin ]['duration'][ $type]                   += $row->duration;
                        }
                    }

                    // What column are we sorting on
                    $sort_column = ! empty( $_GET['profiler_sort_by'] ) ? $_GET['profiler_sort_by'] : 'average';
                    $sort_order  = ! empty( $_GET['profiler_sort_order'] ) ? $_GET['profiler_sort_order'] : 'desc';

                    // Sort the top level plugins
                    usort( $data, function( $a, $b ) use ( $sort_column, $sort_order ) {
                        if( $sort_order === 'asc' ) {
                            return $a['duration'][ $sort_column ] > $b['duration'][ $sort_column ];
                        } else {
                            return $a['duration'][ $sort_column ] < $b['duration'][ $sort_column ];
                        }
                    } );

                    // Order the nested functions
                    // If we don't have any data for a specific function, revert to the average
                    foreach( $data as $index => $plugin ) {
                        usort( $plugin['functions'], function( $a, $b ) use ( $sort_column, $sort_order ) {
                            if( $sort_order === 'asc' ) {
                                if( ! isset( $a[ $sort_column ] ) && ! isset( $b[ $sort_column ] ) ) {
                                    return $a['average'] > $b['average'];
                                } else {
                                    return $a[ $sort_column ] > $b[ $sort_column ];
                                }
                            } else {
                                if( ! isset( $a[ $sort_column ] ) && ! isset( $b[ $sort_column ] ) ) {
                                    return $a['average'] < $b['average'];
                                } else {
                                    return $a[ $sort_column ] < $b[ $sort_column ];
                                }
                            }
                        } );

                        $data[ $index ] = $plugin;
                    }

                    break;
                case 'detail':
                    if( ! isset( $_GET['request_id'] ) ) break;

                    $request_id = absint( $_GET['request_id'] );

                    // Get basic request details
                    $query   = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}profiler_requests WHERE id = %d", $request_id );
                    $request = $wpdb->get_row( $query );

                    // Get the more advanced details
                    $query   = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}profiler_details WHERE request_id = %d", $request_id );
                    $details = $wpdb->get_row( $query );

                    $details->duration = $details->duration_core + $details->duration_themes + $details->duration_plugins + $details->duration_mu_plugins + $details->duration_database;

                    // Get all the function level stats from the database
                    $query      = "SELECT * FROM {$wpdb->prefix}profiler_functions WHERE request_id = %d";
                    $query      = $wpdb->prepare( $query, $request_id );
                    $rows       = $wpdb->get_results( $query );

                    // Pre-process them so it's all in a nice, structured array
                    $plugins = array();

                    foreach( $rows as $row ) {
                        // Is this the first stat for this plugin?
                        // If so, we need to initialise the data structures
                        if( ! isset( $plugins[ $row->plugin ] ) ) {
                            $plugins[ $row->plugin ] = array(
                                'plugin'    => $row->plugin,
                                'count'     => 0,
                                'duration'  => 0,
                                'functions' => array(),
                            );
                        }

                        $plugins[ $row->plugin ]['count']      += $row->count;
                        $plugins[ $row->plugin ]['duration']   += $row->duration;
                        $plugins[ $row->plugin ]['functions'][] = $row;
                    }

                    // And now sort everything so we have the slowest at the top
                    // Do this at both the plugin and function level
                    usort( $plugins, array( __NAMESPACE__ . '\Helpers', 'order' ) );

                    foreach( $plugins as $index => $row ) {
                        usort( $row['functions'], array( __NAMESPACE__ . '\Helpers', 'order' ) );

                        $plugins[ $index ] = $row;
                    }

                    // Get the payload data
                    $payload = maybe_unserialize( $request->payload );

                    // Get database queries
                    $results_query = "SELECT queries.request_id, queries.duration, queries.plugin, queries.the_query, requests.timestamp, requests.type
                                      FROM {$wpdb->prefix}profiler_queries queries
                                      JOIN {$wpdb->prefix}profiler_requests requests
                                      WHERE queries.request_id = requests.id
                                      AND request_id = %d
                                      ORDER BY duration DESC";

                    // Execute the query
                    $results_query = $wpdb->prepare( $results_query, array( $request_id ) );
                    $database      = $wpdb->get_results( $results_query );

                    // Get similar requests
                    // We need to get the top and bottom performing requests
                    // If it's a front end request (and has a template) we'll do the same for templates
                    // In both cases, we don't want duplicate rows, which could occur with small data sets
                    $similar_requests_bottom = $wpdb->get_results( $wpdb->prepare( "
                        SELECT id, timestamp, duration, memory, queries
                        FROM {$wpdb->prefix}profiler_requests
                        WHERE 1=1
                        AND request = %s
                        AND id != %d
                        ORDER BY duration DESC
                        LIMIT 5
                    ", $request->request, $request_id ) );

                    $bottom_ids = wp_list_pluck( $similar_requests_bottom, 'id' );
                    $bottom_ids_str = implode( ',', $bottom_ids );

                    $similar_requests_top = $wpdb->get_results( $wpdb->prepare( "
                        SELECT id, timestamp, duration, memory, queries
                        FROM {$wpdb->prefix}profiler_requests
                        WHERE 1=1
                        AND request = %s
                        AND id != %d " .
                        ( !empty( $bottom_ids_str ) ? "AND id NOT IN ({$bottom_ids_str}) " : "" ) .
                        "ORDER BY duration ASC
                        LIMIT 5
                    ", $request->request, $request_id ) );

                    if( ! empty( $request->template ) ) {
                        $similar_templates_bottom = $wpdb->get_results( $wpdb->prepare( "
                            SELECT id, timestamp, request, duration, memory, queries
                            FROM {$wpdb->prefix}profiler_requests
                            WHERE 1=1
                            AND template = %s
                            AND id != %d
                            ORDER BY duration DESC
                            LIMIT 5
                        ", $request->template, $request_id ) );

                        $bottom_ids = wp_list_pluck( $similar_requests_bottom, 'id' );
                        $bottom_ids_str = implode( ',', $bottom_ids );

                        $similar_templates_top = $wpdb->get_results( $wpdb->prepare( "
                            SELECT id, timestamp, request, duration, memory, queries
                            FROM {$wpdb->prefix}profiler_requests
                            WHERE 1=1
                            AND template = %s
                            AND id != %d " .
                            ( !empty( $bottom_ids_str ) ? "AND id NOT IN ({$bottom_ids_str}) " : "" ) .
                            "ORDER BY duration ASC
                            LIMIT 5
                        ", $request->template, $request_id ) );
                    }

                    break;
                case 'database':
                    require_once ICIT_PERFORMANCE_PROFILER_DIR . 'admin/database.php';

                    // Setup the database queries
                    // We'll have one for the results and one for the count
                    $results_query = "SELECT queries.request_id, queries.duration, queries.plugin, queries.the_query, requests.timestamp, requests.type
                                       FROM {$wpdb->prefix}profiler_queries queries
                                       JOIN {$wpdb->prefix}profiler_requests requests
                                       WHERE queries.request_id = requests.id";
                    $count_query   = "SELECT COUNT(queries.request_id)
                                       FROM {$wpdb->prefix}profiler_queries queries
                                       JOIN {$wpdb->prefix}profiler_requests requests
                                       WHERE queries.request_id = requests.id";
                    $values        = array();

                    // Are we filtering by request?
                    if( ! empty( $_GET['request_id'] ) ) {
                        $results_query .= " AND request_id = %d";
                        $count_query   .= " AND request_id = %d";
                        $values[]       = absint( $_GET['request_id'] );
                    }

                    // Are we filtering by date?
                    if( ! empty( $_GET['date'] ) ) {
                        $results_query .= " AND timestamp > %d";
                        $count_query   .= " AND timestamp > %d";
                        $values[]       = strtotime( $_GET['date'] );
                    }

                    // Are we filtering by plugin?
                    if( ! empty( $_GET['plugin'] ) ) {
                        $results_query .= " AND plugin = %s";
                        $count_query   .= " AND plugin = %s";
                        $values[]       = $_GET['plugin'];
                    }

                    // Do we want to filter on the SQL query
                    if( ! empty( $_GET['the_query'] ) ) {
                        $results_query .= " AND the_query LIKE %s";
                        $count_query   .= " AND the_query LIKE %s";
                        $values[]       = '%' . $_GET['the_query'] . '%';
                    }

                    // Do we want to filter just slow loading request?
                    if( ! empty( $_GET['duration'] ) ) {
                        $results_query .= " AND queries.duration > %d";
                        $count_query   .= " AND queries.duration > %d";
                        $values[]       = absint( $_GET['duration'] );
                    }

                    // Are we filtering by type?
                    if( ! empty( $_GET['type'] ) ) {
                        $results_query .= " AND type = %s";
                        $count_query   .= " AND type = %s";
                        $values[]       = $_GET['type'];
                    }

                    // Set the order
                    // We want the slowest ones first
                    $results_query .= " ORDER BY duration DESC";

                    // Add pagination
                    $results_query   .= " LIMIT %d, 100";
                    $values[] = isset( $_GET['p'] ) ? ( $_GET['p'] - 1 ) * 100 : 0;

                    // Execute the query
                    $results_query = $wpdb->prepare( $results_query, $values );
                    $rows          = $wpdb->get_results( $results_query );

                    // Get the total number of rows
                    if( count( $values) > 0 ) {
                        array_pop($values);
                    }
                    if( count( $values ) ) {
                        $count_query = $wpdb->prepare( $count_query, $values );
                    }

                    $total_rows      = $wpdb->get_var( $count_query );

                    break;
                case 'maintenance':
                    if( isset( $_GET['action'] ) ) {
                        $action = $_GET['action'];

                        if( $action === 'uninstall' ) {

                        } else if( $action === 'delete' ) {
                            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}profiler_functions" );
                            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}profiler_plugins" );
                            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}profiler_queries" );
                            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}profiler_requests" );
                        }
                    }

                    break;
            }

            include ICIT_PERFORMANCE_PROFILER_DIR . 'views/admin/' . $tab . '.php';
            ?>
        </div>
        <?php
    }

    private function get_active_tab() {
        $valid   = array( 'requests', 'plugins', 'detail', 'database', 'settings', 'maintenance', 'help' );
        $default = 'requests';

        if( ! isset( $_GET['tab'] ) ) return $default;
        if( ! in_array( $_GET['tab'], $valid ) ) return $default;

        return $_GET['tab'];
    }

    public function admin_notices() {
        if( ! icit_profiler_is_mu_plugin() ):
            ?>
            <div class="updated">
                <p>
                    The WordPress Performance Profiler works best as a must-use plugin but is currently installed as a regular plugin.
                    <a href="<?php echo admin_url( 'admin.php?page=icit-profiler&tab=help' )?>">Install the plugin as a must-use plugin now.</a>
                </p>
            </div>
            <?php
        endif;
    }

    private function is_tab_active( $tab ) {
        echo $tab == $this->get_active_tab() ? 'nav-tab-active' : '';
    }

    private function get_plugin_stats_by_request_type( $type ) {
        global $wpdb;

        if( $type === 'all' ) {
            return $wpdb->get_results( "
                SELECT plugin.plugin, ( SUM(plugin.duration) / COUNT(plugin.duration) ) AS duration
                FROM {$wpdb->prefix}profiler_plugins plugin
                WHERE 1 = 1
                GROUP BY plugin.plugin
                ORDER BY duration DESC
            " );
        } else {
            return $wpdb->get_results( $wpdb->prepare( "
                SELECT plugin.plugin, ( SUM(plugin.duration) / COUNT(plugin.duration) ) AS duration
                FROM {$wpdb->prefix}profiler_plugins plugin
                JOIN {$wpdb->prefix}profiler_requests request
                WHERE 1 = 1
                AND plugin.request_id = request.id
                AND request.type = %s
                GROUP BY plugin.plugin
                ORDER BY duration DESC
            ", $type ) );
        }
    }
}
Admin::instance();
