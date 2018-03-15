<h1>About</h1>
<p>The WordPress Performance Profiler monitors the performance of your site and helps you pinpoint sluggish behavious. The admin is split into the following sections:</p>

<h3>Requests</h3>
<p>This shows a list of all your logged requests. You can filter them using the form at the top of the table, and for requests logged with the advanced logger, there is a details link to get more in depth information.</p>
<p>For each request, you have the following information:</p>
<ul>
    <li><strong>Date</strong> &mdash; The date and time the request occured. These are ordered newest first and filtering by date/time will show all requests that occured since that time.</li>
    <li><strong>URL</strong> &mdash; The URL of the request. Filtering on this field will search anywhere within the field</li>
    <li><strong>Duration</strong> &mdash; The duration of the request in milliseconds. This is the time from when the request hit the server and the plugin is instantiated. Filtering on this field will return requests taking longer than X milliseconds</li>
    <li><strong>Memory</strong> &mdash; The amount of memory consumed serviceing the request. This is based on process memory at the end of the request, and actual memory usage could fluctuate during the request. Filtering on this field will return requests using more than X MB of memory</li>
    <li><strong>Database Queries</strong> &mdash; The total number of database queries used to render the page. Filtering on this field will return all requests which made more than X queries</li>
    <li><strong>Template</strong> &mdash; The template file used for the request. This is only applicable for front end requests, and when filtering on this field, you need to full file path from the theme root including the leading slash, such as <code>/single.php</code></li>
    <li><strong>Type</strong> &mdash; The type of request, such as front end, admin, AJAX or cron</li>
</ul>

<h3>Plugins</h3>
<p>This tab shows you an aggregate view of plugin performance. It uses all the available data from the advanced logging data to give a summary of which plugins perform worst overall, as well as breaking down the performance for each request type, as some plugins might perform very well in the front end but terribly in the admin and vice versa.</p>

<h3>In Depth</h3>
<p>The in depth tab gives a detailed breakdown about the execution time for an individual request. This information is not available for all requests, only ones captured with the advanced logger. Going directly to this tab will give an error message - instead, you need to click on the details link from the requests tab.</p>
<p>On this page, you can see an overview of basic information at the top, followed by a breakdown of eecution time by plugin. Clicking on a plugin will expand the table to show all the functions within that plugin and their execution time and call count.</p>

<h3>Database</h3>
<p>This tab logs all database queries ordered by duration, slowest first. As with requests, you can filter the contents of this table based on date, duration or search within the SQL query. Once you've found a query you're interested in, you can view the request it originated from by clicking on the details link (if applicable).</p>

<h3>Settings</h3>
<p>Here you can control how frequently the profiler captures data. The basic and advanced logging frequency is set as a percentage, so if you want it to log all requests, you'd set it to 100, to log half of requests you'd set it to 50 etc. You can also have decimal numbers, so to only log 1 in a thousand requests, you'd set it to 0.1. The frequency the profiler will run may vary from the settings here, as it's the probability that the profiler will run.</p>
<p>You can also control which types of requests are logged. For example, if you're attempting to diagnose a performance problem on the front end, you can disable the other request types to minimise overhead.</p>
<p>During development, you'll most likely wan tto have advanced logging set to 100, so that it logs every request, but if you're running it in a production environment, then set the logging levels to be much lower, especially the advanced one.</p>

<h3>Maintenance</h3>
<p>Over time, the WordPress Performance Profiler will accumulate a lot of data, making the database increase in size significantly. You can clear out the database from the maintenance tab, either deleting all the stored data, or deleting all the data and the database tables.</p>
<hr>

<h1>FAQs</h1>

<h3>What benefit does this have over server level profiling?</h3>
<p>Tools like the xdebug profiler are great, but they add a lot of overhead and are focused on individual requests, and also require setup at the server level, which you may not have access to. The WordPress Performance Profiler is optimised to work with WordPress with minimal setup. It's also focused on being lightweight and can help to spot trends in aggregate data.</p>

<h3>Why does this plugin need to be installed as a must-use plugin instead of a regular one?</h3>
<p>Although it's possible to run this plugin as a normal one instead of a must-use one, it would capture less data, and the data it does capture would be less accurate. This is because must-use plugins are loaded much earlier than normal ones, allowing the profiler to monitor the load time of all plugins, and not just ones loaded after it (which could be none).</p>
<p>To install the this as a must-use plugin, de-activate the plugin and then move <code>load-wp-performance-profiler.php</code> and <code>wp-performance-profiler</code> from the plugins directory of your site to <code>wp-content/mu-plugins</code>. If this directory doesn't already exist, you can create it.</p>

<h3>Why does it create its own database tables instead of using the core WordPress tables?</h3>
<p>As the plugin captures and stores a lot of information, it creates its own tables for best performance. This also has the benefit of not cluttering up the core WordPress tables, and makes it much easier for the plugin to clean up after itself.</p>

<h3>Why is this not logging exactly 10% of requests?</h3>
<p>The logging is based on probability, not absolute metrics. Although it would be possible to make the number of requests logged more accorate, this would add overhead to every single request, not just the ones being logged. Therefore, in the interest of performance, it will only log the request if the probably of a random number if less than or equal to your target. This will mean that sometimes you get slightly more or less than the target number of requests.</p>

<h3>What's the difference between basic and advanced logging?</h3>
<p>Basic logging is very light weight and will only capture the URL, total duration, amount of memory used, number of database queries, template used and the type of request. Advanced logging will also capture the execution time of each plugin down to the function level, as well as all the database queries.</p>
<p>Basic logging is great to leave running in the background to get an idea of the overall performance of your site and spot trends, such as slow performing requests, templates or types of requests. Advanced logging is then great for drilling down in more detail to find out how the time is spent on those slow requests. In the requests table, advanced requests will have the link to view details in the actions column.</p>

<h3>Can this be used on a production website?</h3>
<p>Yes, although this plugin is primarily meant as a developer tool - so is most beneficial running on your local development environment and staging servers - it can be used in production. We've tried to minimise the overhead as much as possible, but any tool like this will innevitably carry an overhead in terms of performance and also database size.</p>
<p>If you are going to run it in a production environment, it's recommended to set the advanced logging level to a very low number, and the basic level to a low-medium number. With a large amount of traffic, this will still capture a lot of data with minimal overhead. If you run it with higher values (especially for the advanced logging), you'll need to periodically purge the database in the maintenance tab.</p>

<h3>Can I manually log requests</h3>
<p>If you don't want to run the profiler all the time, or only want to log specific requests, you can manually log them by adding <code>?profiler</code> to the query string. This will enable the advanced profiler, but if you want just basic logging, add <code>?profiler=basic</code>.</p>
<p>By using the query string, you will override any default settings.</p>

<h3>Any other questions?</h3>
<p>If you have a question that's not answered here, or have encountered an issue, please email <a href="mailto:cases@interconnectit.fogbugz.com">cases@interconnectit.fogbugz.com</a>.</p>
