<div class="icit-profiler-results">
    <table class="icit-profiler-results-advanced">
        <?php foreach( $stats as $plugin ):?>
            <tr>
                <th>Function</th>
                <th>Duration</th>
                <th>Count</th>
            </tr>
            <tr>
                <th><?php echo $plugin['plugin']?></th>
                <th><?php echo $plugin['duration']?></th>
                <th><?php echo $plugin['count']?></th>
            </tr>

            <?php foreach( $plugin['functions'] as $function ):?>
                <tr>
                    <td><?php echo $function['function']?></td>
                    <td><?php echo $function['duration']?></td>
                    <td><?php echo $function['count']?></td>
                </tr>
            <?php endforeach?>

            <tr><td colspan="3">&nbsp;</td></tr>
        <?php endforeach?>
    </table>
</div>
