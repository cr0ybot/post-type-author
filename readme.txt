=== Post Type Author ===
Contributors: cr0ybot
Tags: post type, author
Requires at least: 4.7
Tested up to: 6.1
Requires PHP: 5.3
Stable tag: 0.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Force selected post types to always have the same chosen author.

== Description ==

This plugin allows you to select a default author for any public post type.

When a post of the selected type is created, the author will be automatically set to the chosen author, regardless of who is logged in.

After installing and activating the plugin, go to Settings > Writing and scroll to the Post Type Author Settings section to select an author for any of the listed post types.

== Frequently Asked Questions ==

= Can I use this plugin with custom post types? =

Yes, you can. Just make sure the post type has `public` set to true.

= Can I retroactively change the author of existing posts? =

Not at this time. This plugin only affects new posts. This feature is on the roadmap.

= Why are some users not showing up in the dropdown? =

Only users who have the `edit_posts` capability can be selected. If you don't see the user you're looking for, make sure they have this capability.

= What happens if the selected user is deleted? =

First, when you delete a user, WordPress will prompt you to reassign their posts to another user. This does not change the settings of this plugin.

However, if the selected user ID is not found in the database when a post is saved, the plugin will fall back to the current user.

= Why would I want to set default authors for post types? =

You may have a certain post type that should always be associated with a specific user. For example, you may have a post type for "Press Releases" that should always be created by a generic company/brand user.

You might also be using the ActivityPub plugin, and want a specific feed of posts to be attributed to a specific user. For example, you may have a post type for "Podcast Episodes" that should always be created by a generic brand user that people can subscribe to on Mastodon, such as @intheloop@blackbird.digital.

== Changelog ==

= 0.1.0 =
* Prerelease version
