<?php

namespace ICIT_Performance_Profiler;

/**
 * Settings
 */

class Settings {
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

    }

    public function register_settings() {
        register_setting( 'icit-performance-profiler-group', 'icit_performance_profiler' );
        add_settings_section( 'icit-profiler', '', '', 'icit-profiler' );

        add_settings_field( 'basic_frequency', 'Basic Logging Frequency', array( $this, 'render_numeric' ), 'icit-profiler', 'icit-profiler', array(
            'id'        => 'basic_frequency',
            'name'        => 'icit_performance_profiler[basic_frequency]',
            'description' => 'The frequency the basic logger should run as a percentage. For example, to run this on every hundredth request, set this to 1.',
        ) );

        add_settings_field( 'advanced_frequency', 'Advanced Logging Frequency', array( $this, 'render_numeric' ), 'icit-profiler', 'icit-profiler', array(
            'id'        => 'advanced_frequency',
            'name'        => 'icit_performance_profiler[advanced_frequency]',
            'description' => 'The frequency the advanced logger should run as a percentage. For example, to run this on every hundredth request, set this to 1.',
        ) );

        add_settings_field( 'request_types', 'Request Types', array( $this, 'render_checkbox_list' ), 'icit-profiler', 'icit-profiler', array(
            'id'          => 'request_types',
            'name'        => 'icit_performance_profiler[request_types]',
            'description' => 'What request types should logging occur on?',
            'options'     => array(
                array(
                    'id'    => 'front',
                    'label' => 'Front',
                    'name'  => 'icit_performance_profiler[request_types][front]',
                ),
                array(
                    'id'    => 'admin',
                    'label' => 'Admin',
                    'name'  => 'icit_performance_profiler[request_types][admin]',
                ),
                array(
                    'id'    => 'cron',
                    'label' => 'Cron',
                    'name'  => 'icit_performance_profiler[request_types][cron]',
                ),
                array(
                    'id'    => 'ajax',
                    'label' => 'AJAX',
                    'name'  => 'icit_performance_profiler[request_types][ajax]',
                ),
            ),
        ) );
    }

    public function render_numeric( $args ) {
        $settings = get_option( 'icit_performance_profiler' );
        $value    = !empty( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <input type="text" name="<?php echo $args['name']?>" value="<?php echo $value?>">
        <?php if( ! empty( $args['description'] ) ):?>
            <p class="description">
                <?php echo $args['description']?>
            </p>
        <?php endif?>
        <?php
    }

    public function render_checkbox_list( $args ) {
        $settings = get_option( 'icit_performance_profiler' );
        $value    = !empty( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : '';
        ?>
        <?php foreach( $args['options'] as $option ):?>
            <label><input type="checkbox" name="<?php echo $option['name']?>" <?php isset($settings['request_types'][ $option['id'] ]) && checked( $settings['request_types'][ $option['id'] ], 'on' )?>> <?php echo $option['label']?></label><br />
        <?php endforeach?>
        <?php if( ! empty( $args['description'] ) ):?>
            <p class="description">
                <?php echo $args['description']?>
            </p>
        <?php endif?>
        <?php
    }
}
Settings::instance();
