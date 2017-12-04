=== Plugin Name ===
Contributors:      gostomski, interconnectit
Tags:              performance, optimisation, profiler, plugins, speed
Requires at least: 4.0.1
Tested up to:      4.9
Stable tag:        0.4
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Log the overall performance of your site and drill down to identify bottlenecks.

== Description ==

The WordPress Performance Profiler will monitor the performance of your site and help you identify the slow parts:

* Slow requests on the front and back end to track how well certain pages and templates perform
* Drill down into specific requests to see how time is distributed among plugins
* View the overall plugin execution time across all requests, broken down by type of request
* Log slow database queries
* Track the best and worst performing requests for a given template or URL

== Installation ==

To install the WordPress Performance Profiler as a must-use plugin (recommended):

1. If you don't already have have a folder called `mu-plugins` inside your wp-content directory, create it
2. Copy the contents of the zip file into `wp-content/mu-plugins`
3. Login into the admin panel of your site, this will create the required database tables
4. Go to Profiler > Settings in the admin area and configure what level of logging you want
5. For multisite networks, repeat steps 3 and 4 for every site in the network

To install the WordPress Performance Profiler as a regular plugin, repeat the steps above, but copy the files into `wp-content/plugins` instead of `wp-content/mu-plugins`.

To uninstall the plugin, assuming it's been installed as a must-use plugin:

1. Go to Profiler > Maintenance and click uninstall - This will delete all custom tables and set a flag in the options table to not run the plugin any more

OR

1. Delete `load-wp-performance-profiler.php` and `wp-performance-profiler` from the `mu-plugins` directory
2. Delete the database tables beginning `wp_profiler_` (prefix depending on setup)

== Frequently Asked Questions ==

= What benefit does this have over server level profiling? =

Tools like the xdebug profiler are great, but they add a lot of overhead and are focused on individual requests, and also require setup at the server level, which you may not have access to. The WordPress Performance Profiler is optimised to work with WordPress with minimal setup. It's also focused on being lightweight and can help to spot trends in aggregate data.

= Why does this plugin need to be installed as a must-use plugin instead of a regular one? =

Although it's possible to run this plugin as a normal one instead of a must-use one, it would capture less data, and the data it does capture would be less accurate. This is because must-use plugins are loaded much earlier than normal ones, allowing the profiler to monitor the load time of all plugins, and not just ones loaded after it (which could be none).

= Why does it create its own database tables instead of using the core WordPress tables? =

As the plugin captures and stores a lot of information, it creates its own tables for best performance. This also has the benefit of not cluttering up the core WordPress tables, and makes it much easier for the plugin to clean up after itself.

= Why is this not logging exactly 10% of requests? =

The logging is based on probability, not absolute metrics. Although it would be possible to make the number of requests logged more accorate, this would add overhead to every single request, not just the ones being logged. Therefore, in the interest of performance, it will only log the request if the probably of a random number if less than or equal to your target. This will mean that sometimes you get slightly more or less than the target number of requests.

= What's the difference between basic and advanced logging? =

Basic logging is very light weight and will only capture the URL, total duration, amount of memory used, number of database queries, template used and the type of request. Advanced logging will also capture the execution time of each plugin down to the function level, as well as all the database queries.

Basic logging is great to leave running in the background to get an idea of the overall performance of your site and spot trends, such as slow performing requests, templates or types of requests. Advanced logging is then great for drilling down in more detail to find out how the time is spent on those slow requests. In the requests table, advanced requests will have the link to view details in the actions column.

= Can this be used on a production website? =

Yes, although this plugin is primarily meant as a developer tool - so is most beneficial running on your local development environment and staging servers - it can be used in production. We've tried to minimise the overhead as much as possible, but any tool like this will innevitably carry an overhead in terms of performance and also database size.

If you are going to run it in a production environment, it's recommended to set the advanced logging level to a very low number, and the basic level to a low-medium number. With a large amount of traffic, this will still capture a lot of data with minimal overhead. If you run it with higher values (especially for the advanced logging), you'll need to periodically purge the database in the maintenance tab.

= Can I manually log requests =

If you don't want to run the profiler all the time, or only want to log specific requests, you can manually log them by adding `?profiler` to the query string. This will enable the advanced profiler, but if you want just basic logging, add `?profiler=basic`.
By using the query string, you will override any default settings.

== Changelog ==
= 0.4.0 =
* First new version since fork
* Fix for memory usage not displaying correctly in certain cases
* Minor hook tweaks

= 0.3.0 =
* Added ability to view plugins ordered by average duration, minimum duration, maximum duration and standard deviation
* Ability to order plugin overview screen by overall, front-end back-end, admin or cron performance
* Added more visual representation of details and time spent on detailed request view
* Added all database queries to the detailed request page
* Added best and worst performing requests for the same URL and template to the detailed request view
* Security fix to prevent a potential XSS exploit

= 0.2.3 =
* Updated table columns to make information clearer
* Updated request view to show that plugin information can be expanded
* Ability to manually log requests by adding ?profiler to the query string
* Added function level profiling information for the plugins section
* Redesigned summary for detailed requests to make them clearer
* Added ability to sort plugin details and show a variety of information
* All logged requests can now click through for additional information
* Detailed request view also shows best/worst response times for that URL and template

= 0.2.2 =
* Resolved numerous file path issues when running the plugin on Windows

= 0.2 =
* Initial public release

= 0.1 =
* Initial internal release
