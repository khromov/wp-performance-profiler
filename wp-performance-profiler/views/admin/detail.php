<?php if( empty( $_GET['request_id'] ) ) {
    printf( '<p>To view in-depth information on a specific request, select it from the <a href="%s">requests</a> tab.</p>', admin_url( 'admin.php?page=icit-profiler&tab=requests' ) );
    echo '<p>Not all requests have detailed information available due to the level of logging.</p>';
    return;
}
?>
<p>
    Requesting <code><a href="<?php echo home_url( $request->request )?>" target="_blank"><?php echo home_url( $request->request )?></a></code>.
    <?php if( ! empty( $request->template ) ):?>
        Rendering output with the <code><?php echo $request->template?></code> template.
    <?php endif?>
</p>
<div class="profiler-summary-boxes">
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo number_format( $request->duration, 2 )?></span>
        <span class="profiler-summary-heading">ms duration</span>
    </div>
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo date( 'H:i:s', $request->timestamp )?></span>
        <span class="profiler-summary-heading"><?php echo date( 'd-m-Y', $request->timestamp )?></span>
    </div>
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo number_format( $request->memory / 1024 / 1024, 2 )?></span>
        <span class="profiler-summary-heading">MB Memory</span>
    </div>
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo $request->queries?></span>
        <span class="profiler-summary-heading"><a href="<?php echo admin_url( 'admin.php?page=icit-profiler&tab=database&request_id=' . $request->id )?>"># of queries</a></span>
    </div>
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo number_format( $details->duration_database, 2 )?></span>
        <span class="profiler-summary-heading">ms of DB queries</span>
    </div>
    <div class="profiler-summary-box">
        <span class="profiler-summary-value"><?php echo strtoupper( $request->type )?></span>
        <span class="profiler-summary-heading">Request type</span>
    </div>
</div>

<?php if( isset( $details->duration_core ) ):?>
    <div class="duration-bar">
        <span class="duration duration-core" title="Core (<?php echo number_format( $details->duration_core, 2 )?> ms)" style="width: <?php echo ( $details->duration_core / $details->duration ) * 100?>%;"></span>
        <span class="duration duration-themes" title="Themes (<?php echo number_format( $details->duration_themes, 2 )?> ms)" style="width: <?php echo ( $details->duration_themes / $details->duration ) * 100?>%;"></span>
        <span class="duration duration-plugins" title="Plugins (<?php echo number_format( $details->duration_plugins, 2 )?> ms)" style="width: <?php echo ( $details->duration_plugins / $details->duration ) * 100?>%;"></span>
        <span class="duration duration-mu-plugins" title="MU Plugins (<?php echo number_format( $details->duration_mu_plugins, 2 )?> ms)" style="width: <?php echo ( $details->duration_mu_plugins / $details->duration ) * 100?>%;"></span>
        <span class="duration duration-database" title="Database (<?php echo number_format( $details->duration_database, 2 )?> ms)" style="width: <?php echo ( $details->duration_database / $details->duration ) * 100?>%;"></span>
    </div>

    <div class="duration-key">
        <div class="key duration-core">
            <span class="key-color"></span>
            <span class="key-label">Core (<?php echo number_format( $details->duration_core, 2 )?> ms)</span>
        </div>
        <div class="key duration-themes">
            <span class="key-color"></span>
            <span class="key-label">Themes (<?php echo number_format( $details->duration_themes, 2 )?> ms)</span>
        </div>
        <div class="key duration-plugins">
            <span class="key-color"></span>
            <span class="key-label">Plugins (<?php echo number_format( $details->duration_plugins, 2 )?> ms)</span>
        </div>
        <div class="key duration-mu-plugins">
            <span class="key-color"></span>
            <span class="key-label">MU Plugins (<?php echo number_format( $details->duration_mu_plugins, 2 )?> ms)</span>
        </div>
        <div class="key duration-database">
            <span class="key-color"></span>
            <span class="key-label">Database (<?php echo number_format( $details->duration_database, 2 )?> ms)</span>
        </div>
    </div>
<?php endif?>

<?php if( ! empty( $plugins ) ):?>
    <h2>Plugins</h2>
    <table class="icit-profiler-table icit-profiler-table-zebra icit-profiler-table-functions">
        <tr>
            <th colspan="2">Plugin</th>
            <th>Duration (ms)</th>
            <th>Call count</th>
        </tr>
        <?php foreach( $plugins as $plugin ):?>
            <tr class="summary" data-plugin="<?php echo $plugin['plugin']?>">
                <th colspan="2"><i class="toggle-icon"></i><?php echo $plugin['plugin']?></th>
                <th><?php echo $plugin['duration']?></th>
                <th><?php echo $plugin['count']?></th>
            </tr>

            <?php foreach( $plugin['functions'] as $function ):?>
                <tr class="detail plugin-<?php echo $plugin['plugin']?>">
                    <td></td>
                    <td><?php echo $function->function?></td>
                    <td><?php echo $function->duration?></td>
                    <td><?php echo $function->count?></td>
                </tr>
            <?php endforeach?>
        <?php endforeach?>
    </table>
<?php endif?>

<?php if( ! empty( $database ) ):?>
    <h2>Database</h2>
    <table class="icit-profiler-table icit-profiler-table-zebra icit-profiler-table-database">
        <tr>
            <th width="100">Duration (ms)</th>
            <th width="150">Plugin</th>
            <th width="">Query</th>
        </tr>

        <?php foreach( $database as $query ):?>
            <tr>
                <td><?php echo $query->duration?></td>
                <td><?php echo $query->plugin?></td>
                <td><?php echo htmlentities( $query->the_query )?></td>
            </tr>
        <?php endforeach?>
    </table>
<?php endif?>

<h2>Other requests for this URL</h2>
<?php if( ! empty( $similar_requests_bottom ) ):?>
    <div class="profiler-2-col-wrapper">
        <div class="profiler-col">
            <?php if( ! empty( $similar_requests_bottom ) ):?>
                <p class="description"><strong>Worst performers</strong></p>
                <table class="icit-profiler-table icit-profiler-table-zebra">
                    <tr>
                        <th>Date</th>
                        <th>Duration (ms)</th>
                        <th>Memory (mb)</th>
                        <th>DB Queries (#)</th>
                    </tr>
                    <?php foreach( $similar_requests_bottom as $row ):?>
                        <tr>
                            <td><a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>"><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></a></td>
                            <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                            <td class="numeric-column"><?php echo ICIT_Performance_Profiler\Helpers::human_filesize( $row->memory )?></td>
                            <td class="numeric-column"><?php echo $row->queries?></td>
                        </tr>
                    <?php endforeach?>
                </table>
            <?php endif?>
        </div>

        <div class="profiler-col">
            <p class="description"><strong>Best performers</strong></p>
            <?php if( ! empty( $similar_requests_top ) ):?>
                <table class="icit-profiler-table icit-profiler-table-zebra">
                    <tr>
                        <th>Date</th>
                        <th>Duration (ms)</th>
                        <th>Memory (mb)</th>
                        <th>DB Queries (#)</th>
                    </tr>
                    <?php foreach( $similar_requests_top as $row ):?>
                        <tr>
                            <td><a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>"><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></a></td>
                            <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                            <td class="numeric-column"><?php echo ICIT_Performance_Profiler\Helpers::human_filesize( $row->memory )?></td>
                            <td class="numeric-column"><?php echo $row->queries?></td>
                        </tr>
                    <?php endforeach?>
                </table>
            <?php else:?>
                <p class="description">There aren't enough matching requests to show the best performing requests without duplicating the worst performing requests.</p>
            <?php endif?>
        </div>
    </div>
<?php else:?>
    <p class="description">There are currently no other requests that match this URL.</p>
    <p class="description">Note: If a URL has a unique query string, it won't appear here.</p>
<?php endif?>

<?php if( ! empty( $similar_templates_bottom ) ):?>
    <h2>Other requests with this template</h2>
    <div class="profiler-2-col-wrapper">
        <div class="profiler-col">
            <p class="description"><strong>Worst performers</strong></p>
            <?php if( ! empty( $similar_templates_bottom ) ):?>
                <table class="icit-profiler-table icit-profiler-table-zebra">
                    <tr>
                        <th>Date</th>
                        <th>URL</th>
                        <th>Duration (ms)</th>
                        <th>Memory (mb)</th>
                        <th>DB Queries (#)</th>
                    </tr>
                    <?php foreach( $similar_templates_bottom as $row ):?>
                        <tr>
                            <td><a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>"><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></a></td>
                            <td><?php echo $row->request?></td>
                            <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                            <td class="numeric-column"><?php echo ICIT_Performance_Profiler\Helpers::human_filesize( $row->memory )?></td>
                            <td class="numeric-column"><?php echo $row->queries?></td>
                        </tr>
                    <?php endforeach?>
                </table>
            <?php endif?>
        </div>

        <div class="profiler-col">
            <p class="description"><strong>Best performers</strong></p>
            <?php if( ! empty( $similar_templates_top ) ):?>
                <table class="icit-profiler-table icit-profiler-table-zebra">
                    <tr>
                        <th>Date</th>
                        <th>URL</th>
                        <th>Duration (ms)</th>
                        <th>Memory (mb)</th>
                        <th>DB Queries (#)</th>
                    </tr>
                    <?php foreach( $similar_templates_top as $row ):?>
                        <tr>
                            <td><a href="admin.php?page=icit-profiler&tab=detail&request_id=<?php echo $row->id?>"><?php echo date( 'd-m-Y H:i:s', $row->timestamp )?></a></td>
                            <td><?php echo $row->request?></td>
                            <td class="numeric-column"><?php echo number_format( $row->duration, 2 )?></td>
                            <td class="numeric-column"><?php echo ICIT_Performance_Profiler\Helpers::human_filesize( $row->memory )?></td>
                            <td class="numeric-column"><?php echo $row->queries?></td>
                        </tr>
                    <?php endforeach?>
                </table>
            <?php else:?>
                <p class="description">There aren't enough matching requests to show the best performing requests without duplicating the worst performing requests.</p>
            <?php endif?>
        </div>
    </div>
<?php endif?>

<?php if( ! empty( $payload ) ):?>
    <h2>Payload</h2>
    <pre><?php print_r( $payload )?></pre>
<?php endif?>
