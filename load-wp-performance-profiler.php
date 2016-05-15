<?php

/**
 * Plugin Name: WordPress Performance Profiler (must-use version)
 * Plugin URI:  http://interconnectit.com
 * Author:      Damian Gostomski
 * Author URI:  http://interconnectit.com
 * Description: Pinpoint slow parts of your site, so that you can make them faster.
 * Version:     0.3
 *
 * This file exists to make installing this as a mu-plugin as easy as possible, and also works for regular plugins.
 */

! defined( 'ICIT_PERFORMANCE_PROFILER_LOADER_FILE' ) && define( 'ICIT_PERFORMANCE_PROFILER_LOADER_FILE', plugin_basename( __FILE__ ) );

require_once 'wp-performance-profiler/index.php';
