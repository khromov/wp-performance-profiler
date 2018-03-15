<form method="get" action="<?php echo admin_url( 'admin.php' )?>">
    <input type="hidden" name="page" value="icit-profiler">
    <input type="hidden" name="tab" value="requests">
    <table class="icit-profiler-table icit-profiler-table-zebra icit-profiler-table-requests">
        <?php
        $date     = ICIT_Performance_Profiler\Helpers::querystring_value( 'date' );
        $url      = ICIT_Performance_Profiler\Helpers::querystring_value( 'url' );
        $duration = ICIT_Performance_Profiler\Helpers::querystring_value( 'duration' );
        $memory   = ICIT_Performance_Profiler\Helpers::querystring_value( 'memory' );
        $queries  = ICIT_Performance_Profiler\Helpers::querystring_value( 'queries' );
        $template = ICIT_Performance_Profiler\Helpers::querystring_value( 'template' );
        $type     = ICIT_Performance_Profiler\Helpers::querystring_value( 'type' );
        ?>
        <tr class="icit-profiler-filters">
            <td>
                <input type="text" name="date" placeholder="Requests since" value="<?php echo $date?>">
            </td>
            <td>
                <input type="text" name="url" placeholder="Part of URL string" value="<?php echo $url?>">
            </td>
            <td>
                <input type="text" name="duration" placeholder="Slower than xxx ms" value="<?php echo $duration?>">
            </td>
            <td>
                <input type="text" name="memory" placeholder="More than xxx MB of memory" value="<?php echo $memory?>">
            </td>
            <td>
                <input type="text" name="queries" placeholder="More than xxx database queries" value="<?php echo $queries?>">
            </td>
            <td>
                <input type="text" name="template" placeholder="Full path for template file" value="<?php echo $template?>">
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
            <th>Date</th>
            <th>URL</th>
            <th>Duration (ms)</th>
            <th>Memory</th>
            <th>Number of DB Queries</th>
            <th>Template</th>
            <th>Type</th>
            <th>Level</th>
        </tr>

        <?php foreach( $rows as $row ):?>
            <tr>
                <td><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></td>
                <td><?php echo $row->request?></td>
                <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                <td class="numeric-column"><?php echo ICIT_Performance_Profiler\Helpers::human_filesize( $row->memory )?></td>
                <td class="numeric-column"><?php echo $row->queries?></td>
                <td><?php echo $row->template?></td>
                <td><?php echo $row->type?></td>
                <td>
                    <?php if( ICIT_Performance_Profiler\Helpers::has_details( $row->id ) ):?>
                        <a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>">Advanced</a>
                    <?php else:?>
                        <a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>">Basic</a>
                    <?php endif?>
                </td>
            </tr>
        <?php endforeach?>
    </table>
</form>

<?php
$pages = ceil( $total_rows / 100 );
ICIT_Performance_Profiler\Pagination::render( $pages );
?>
