<?php

/**
 * Plugin Name: WordPress Performance Profiler (plugin version)
 * Plugin URI:  http://interconnectit.com
 * Author:      Damian Gostomski
 * Author URI:  http://interconnectit.com
 * Description: Pinpoint slow parts of your site, so that you can make them faster.
 * Version:     0.3
 */

version_compare(PHP_VERSION, '5.3.0', '>=') && require_once(__DIR__ . '/core.php');
