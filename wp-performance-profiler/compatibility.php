<?php

namespace ICIT_Performance_Profiler;

/**
 * Improve compatibility
 *
 * This includes:
 * - Fixing core bugs
 * - Changes for specific WordPress versions
 * - Backward compatibility with older versions of the plugin
 * - Conflicts with known plugins
 */

add_filter( 'theme_root', 'wp_normalize_path' );
