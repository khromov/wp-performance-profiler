<form method="get" action="<?php echo admin_url( 'admin.php' )?>">
    <input type="hidden" name="page" value="icit-profiler">
    <input type="hidden" name="tab" value="database">
    <table class="icit-profiler-table icit-profiler-table-zebra icit-profiler-table-requests">
        <?php
        $date      = ICIT_Performance_Profiler\Helpers::querystring_value( 'date' );
        $duration  = ICIT_Performance_Profiler\Helpers::querystring_value( 'duration' );
        $plugin    = ICIT_Performance_Profiler\Helpers::querystring_value( 'plugin' );
        $the_query = ICIT_Performance_Profiler\Helpers::querystring_value( 'the_query' );
        $type      = ICIT_Performance_Profiler\Helpers::querystring_value( 'type' );
        ?>
        <tr class="icit-profiler-filters">
            <td>
                <input type="text" name="date" placeholder="Requests since" value="<?php echo $date?>">
            </td>
            <td>
                <input type="text" name="duration" placeholder="Slower than xxx ms" value="<?php echo $duration?>">
            </td>
            <td>
                <select name="plugin">
                    <?php $plugins = \ICIT_Performance_Profiler\Database::get_all_plugins()?>

                    <option value="">All</option>
                    <?php foreach( $plugins as $plugin_name ):?>
                        <option value="<?php echo $plugin_name?>" <?php selected( $plugin, $plugin_name )?>><?php echo $plugin_name?></option>
                    <?php endforeach?>
                </select>
            </td>
            <td>
                <input type="text" name="the_query" placeholder="Part of the SQL query" value="<?php echo $the_query?>">
            </td>
            <td>
                <select name="type">
                    <?php $types = icit_profiler_request_types()?>

                    <option value="">All</option>
                    <?php foreach( $types as $type_name => $type_label ):?>
                        <option value="<?php echo $type_name?>" <?php selected( $type, $type_name )?>><?php echo $type_label?></option>
                    <?php endforeach?>
                </select>
            </td>
            <td>
                <input type="submit" value="Filter" class="button-primary">
            </td>
        </tr>
        <tr>
            <th width="100">Date</th>
            <th width="60">Duration (ms)</th>
            <th width="100">Plugin</th>
            <th width="">Query</th>
            <th width="60">Type</th>
            <th width="60">Request</th>
        </tr>

        <?php foreach( $rows as $row ):?>
            <tr>
                <td><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></td>
                <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                <td><?php echo $row->plugin?></td>
                <td><?php echo htmlentities( $row->the_query )?></td>
                <td><?php echo $row->type?></td>
                <td><a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->request_id?>">Details</a></td>
            </tr>
        <?php endforeach?>
    </table>
</form>

<?php
$pages = ceil( $total_rows / 100 );
ICIT_Performance_Profiler\Pagination::render( $pages );
?>
