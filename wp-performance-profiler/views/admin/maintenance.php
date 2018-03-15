<h3>Cleanup Database</h3>
<p>
    Over time, this plugin captures and stores a large amount of data.
    If you wish to delete that information, click the button below.
</p>
<p>
    <a href="<?php echo admin_url( 'admin.php?page=icit-profiler&tab=maintenance&action=delete' )?>" class="button-primary">Delete all data</a>
    <strong>Warning - This operation cannot be undone.</strong>
</p>

<hr>

<h3>Uninstall</h3>
<p>
    If you want to uninstall the WordPress Performance Profiler, click the button below.
    This will delete all the data from the database and de-activate the plugin.
    As it's a must-use plugin, you will have to delete the files yourself.
</p>
<p>
    <a href="<?php echo admin_url( 'admin.php?page=icit-profiler&tab=maintenance&action=uninstall' )?>" class="button-primary">Uninstall</a>
    <strong>Warning - This operation cannot be undone.</strong>
</p>
