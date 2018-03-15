<div class="profiler-table-controls profiler-table-controls-5">
    <label class="profiler-table-control">Showing:</label>
    <label class="profiler-table-control"><input type="radio" name="profiler_plugin_showing" value="average" <?php checked( $showing, 'average' )?>>Average duration</label>
    <!-- <label class="profiler-table-control"><input type="radio" name="profiler_plugin_showing" value="median" <?php checked( $showing, 'median' )?>>Median duration</label> -->
    <label class="profiler-table-control"><input type="radio" name="profiler_plugin_showing" value="deviation" <?php checked( $showing, 'deviation' )?>>Standard Deviation</label>
    <label class="profiler-table-control"><input type="radio" name="profiler_plugin_showing" value="minimum" <?php checked( $showing, 'minimum' )?>>Minimum duration</label>
    <label class="profiler-table-control"><input type="radio" name="profiler_plugin_showing" value="maximum" <?php checked( $showing, 'maximum' )?>>Maximum duration</label>
</div>

<table class="icit-profiler-table icit-profiler-table-zebra icit-profiler-table-functions">
    <tr>
        <th colspan="2">Plugin</th>
        <th><a href="<?php echo icit_profiler_order_url( 'average' )?>">Average (ms) <?php icit_profiler_order_icon( 'average' )?></a></th>
        <th><a href="<?php echo icit_profiler_order_url( 'front' )?>">Front-end (ms)<?php icit_profiler_order_icon( 'front' )?></a></th>
        <th><a href="<?php echo icit_profiler_order_url( 'admin' )?>">Admin (ms)<?php icit_profiler_order_icon( 'admin' )?></a></th>
        <th><a href="<?php echo icit_profiler_order_url( 'ajax' )?>">AJAX (ms)<?php icit_profiler_order_icon( 'ajax' )?></a></th>
        <th><a href="<?php echo icit_profiler_order_url( 'cron' )?>">Cron (ms)<?php icit_profiler_order_icon( 'cron' )?></a></th>
    </tr>
    <?php foreach( $data as $plugin ):?>
        <tr class="summary" data-plugin="<?php echo $plugin['plugin']?>">
            <th colspan="2"><i class="toggle-icon"></i><?php echo $plugin['plugin']?></th>
            <th><?php echo ! empty( $plugin['duration']['average'] ) ? number_format( $plugin['duration']['average'], 2 ) : '-'?></th>
            <th><?php echo ! empty( $plugin['duration']['front'] ) ? number_format( $plugin['duration']['front'], 2 ) : '-'?></th>
            <th><?php echo ! empty( $plugin['duration']['admin'] ) ? number_format( $plugin['duration']['admin'], 2 ) : '-'?></th>
            <th><?php echo ! empty( $plugin['duration']['ajax'] ) ? number_format( $plugin['duration']['ajax'], 2 ) : '-'?></th>
            <th><?php echo ! empty( $plugin['duration']['cron'] ) ? number_format( $plugin['duration']['cron'], 2 ) : '-'?></th>
        </tr>

        <?php foreach( $plugin['functions'] as $function ):?>
            <tr class="detail plugin-<?php echo $plugin['plugin']?>">
                <td></td>
                <td><?php echo $function['function']?></td>
                <td><?php echo ! empty( $function['average'] ) ? number_format( $function['average'], 2 ) : '-'?></td>
                <td><?php echo ! empty( $function['front'] ) ? number_format( $function['front'], 2 ) : '-'?></td>
                <td><?php echo ! empty( $function['admin'] ) ? number_format( $function['admin'], 2 ) : '-'?></td>
                <td><?php echo ! empty( $function['ajax'] ) ? number_format( $function['ajax'], 2 ) : '-'?></td>
                <td><?php echo ! empty( $function['cron'] ) ? number_format( $function['cron'], 2 ) : '-'?></td>
            </tr>
        <?php endforeach?>
    <?php endforeach?>
</table>
