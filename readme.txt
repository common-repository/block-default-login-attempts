=== Block Default Login Attempts ===
Contributors: Beej
Donate link: http://bayates.host-ed.me/wordpress/
Tags: protect, login, hack, hacker, bot
Requires at least: 3.0
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Completely block default admin user login attempts in WordPress.

== Description ==

The greatest hack focus on a WordPress site seems to be trying to log in with
the default username "admin". This plugin detects all login attempts with that
username and exits with a 403 Forbidden header. This should eventually
discourage login bots from continuing to pound your site.

All attempts are logged inside the /wp-content/plugin-data folder, just in case
you need the info. Logs are kept for up to 30 days.

== Installation ==

1. Create a unique administrator account, if necessary.
2. Assign all admin posts to this alternate administrator account.
3. Delete the default admin account.
4. Alternatively, use a plugin or database access to change the default username.
5. When there's no longer an "admin" user, just upload, install and activate.

== Screenshots ==

1. A screenshot of a typical log entry
2. Block attempts counter
