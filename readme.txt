=== RT Plugin Statistics ===
Contributors: roytanck
Donate link: http://www.roytanck.com/
Tags: multisite, network, plugins, statistics
Requires at least: 4.2
Tested up to: 4.2.2
Stable tag: 1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays plugin activation statistics accross a multisite network. Helps you determine plugin popularity and track down unused ones.

== Description ==

This plugin adds a network admin options screen that lists all plugins currently activated, on any active blog in the
network. It displays the number of sites on which a plugin is active, and lists those sites.

This helps you:

1. Find unused plugins (will not be listed).
1. Determine the impact in case a plugin needs to be upgraded, removed or reconfigured.

This plugin requires PHP5, and currently does nothing on single-site WordPress installs.

== Installation ==

1. In WordPress, got to "Plugins->Add New".
1. In the search box, type "RT Plugin Statistics".
1. Find the correct plugin, and click "Install Now".
1. When the installation has finished, go to the network admin plugins screen and "Network Activate" the plugin.
1. The statistics can be found in the network admin area, under "Settings".

== Frequently Asked Questions ==

= Why would I use this? =

When you're managing a network of WordPress sites, it can be hard to determine whether a plugin is used by (m)any of
your users. This in turn makes it hard to estimate the impact in case the plugin would (need to) be removed.

Also when a plugin is updated, sometimes it will need to be reconfigured. This plugin can help you find the
sites this applies to, so you don't have to manually go through all of them.

= Is this going to slow down my site? =

On the front-end, no. On large networks, the admin page will likely be slow, especially on larger networks.
Currently the plugin scans a maximum of 9999 sites.

== Screenshots ==

1. The plugin's admin page

== Changelog ==

= 1.0 =
* Initial release
