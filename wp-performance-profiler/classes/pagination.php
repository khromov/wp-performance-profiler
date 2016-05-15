<?php

namespace ICIT_Performance_Profiler;

/**
 * Render pagination links
 */
class Pagination {
    public static function render( $max = 1, $range = 5) {
        // If there's only one page, then we don't need pagination
        if( $max <= 1 ) return;

        // Work out various variables
        $current        = ! empty( $_GET['p'] ) ? $_GET['p'] : 1;
        $previous_index = $current <= $range ? 1 : $current - $range;
        $next_index     = $current + $range < $max ? $current + $range : $max;

        // Render the output
        echo '<div class="icit-profiler-pagination">';

        // Do we need links before the current page - First, Previous and numeric?
        if( $current > 1 ) {
            echo '<a href="' . self::pagination_link( 1 ) . '" class="page-number">&laquo;</a>';
            echo '<a href="' . self::pagination_link( $current - 1 ) . '" class="page-number">&lsaquo;</a>';

            for( $i = $previous_index; $i < $current; $i++ ) {
                echo '<a href="' . self::pagination_link( $i ) . '" class="page-number">' . $i . '</a>';
            }
        }

        // The current page link
        echo '<a href="' . self::pagination_link( $current ) . '" class="page-number page-number-active">' . $current . '</a>';

        // Do we need links after the current page - Numeric links, Next and Last?
        if( $current < $max ) {
            for( $i = $current + 1; $i <= $next_index; $i++ ) {
                echo '<a href="' . self::pagination_link( $i ) . '" class="page-number">' . $i . '</a>';
            }

            echo '<a href="' . self::pagination_link( $current + 1 ) . '" class="page-number">&rsaquo;</a>';
            echo '<a href="' . self::pagination_link( $max ) . '" class="page-number">&raquo;</a>';
        }

        echo '</div>';
    }

    public static function pagination_link( $page ) {
        $url         = $_SERVER['SCRIPT_NAME'];
        $args        = $_GET;
        $args['p']   = $page;
        $querystring = http_build_query( $args );

        return $url . '?' . $querystring;
    }
}
