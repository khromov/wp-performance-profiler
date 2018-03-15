<div class="wrap">
    <form action="options.php" method="POST">
        <?php settings_fields( 'icit-performance-profiler-group' )?>
        <?php do_settings_sections( 'icit-profiler' )?>
        <?php submit_button()?>
    </form>
</div>
