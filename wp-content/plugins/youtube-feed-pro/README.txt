=== Feeds for YouTube Pro ===
Author: Smash Balloon
Contributors: smashballoon, craig-at-smash-balloon
Support Website: http://smashballoon/youtube-feed/
Tags: YouTube, YouTube feed, YouTube widget, YouTube player, YouTube gallery
Requires at least: 3.4
Tested up to: 5.5
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Feeds for YouTube Pro allows you to display completely customizable YouTube feeds from any channel.

== Description ==
Display **completely customizable**, **responsive** and **search engine crawlable** versions of your YouTube feed on your website. Completely match the look and feel of your site with tons of customization options!

= Features =
* **Completely Customizable** - by default inherits your theme's styles
* YouTube feed content is **crawlable by search engines** adding SEO value to your site
* **Completely responsive and mobile optimized** - works on any screen size
* Display videos from any channel in a list, gallery, carousel slider or grid
* Display videos from your **favorites list**
* Show a feed of live streaming videos on your site.
* Allow **filtering** of videos using keywords in the description or title
* Fully functional **search endpoint** for display videos from a search result
* **Combine multiple feeds** into one
* Customizable **actions when video completes** like displaying a link to a product page for example.
* Download video data into a **custom post type** to allow visitors to browse and view videos on your website.
* Display **multiple feeds** from different YouTube channels on multiple pages or widgets
* Post caching means that your YouTube feed loads **lightning fast** and minimizes YouTube API requests
* **Infinitely load more** of your YouTube videos with the 'Load More' button
* Built-in easy to use "YouTube Feed" Widget
* Fully internationalized and translatable into any language
* Display a beautiful header at the top of your YouTube gallery
* Enter your own custom CSS or JavaScript for even deeper customization

For simple step-by-step directions on how to set up the Feeds for YouTube Pro plugin please refer to our [setup guide](http://smashballoon.com/youtube-feed/docs/setup/ 'Feeds for YouTube Pro setup guide').

= Benefits =
* **Increase social engagement** between you and your users, customers, or fans
* **Save time** by using the Feeds for YouTube Pro plugin to generate dynamic, search engine crawlable content on your website
* **Get more follows** by displaying your YouTube videos directly on your site
* Display your YouTube gallery **your way** to perfectly match your website's style
* The plugin is **updated regularly** with new features, bug-fixes and YouTube API changes
* Support is quick and effective
* We're dedicated to providing the **most customizable**, **robust** and **well supported** YouTube feed plugin in the world!

== Installation ==
1. Install the Feeds for YouTube Pro plugin either via the WordPress plugin directory, or by uploading the files to your web server (in the /wp-content/plugins/ directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Feeds for YouTube Pro' settings page to configure your YouTube gallery.
4. Use the shortcode [youtube-feed] in your page, post or widget to display your feed.
5. You can display multiple YouTube channels with different configurations by specifying the necessary parameters directly in the shortcode: [youtube-feed channel=smashballoon].

For simple step-by-step directions on how to set up the Feeds for YouTube Pro plugin please refer to our [setup guide](http://smashballoon.com/youtube-feed/docs/setup/ 'Feeds for YouTube Pro setup guide').

= Setting up the Free Feeds for YouTube Pro WordPress Plugin =

The Feeds for YouTube Pro plugin is brand new and so we're currently working on improving our documentation for it. If you have an issue or question please submit a support ticket and we'll get back to you as soon as we can.

1) Once you've installed the Feeds for YouTube Pro plugin click on the "Feeds for YouTube Pro" item in your WordPress menu

2) Click on the large blue YouTube login button to get your YouTube Access Token and YouTube Refresh Token. Note; if you have your own YouTube Developer Project set up then you can enter your YouTube API key by enabling the checkbox below the YouTube login button.

3) Authorize the Feeds for YouTube Pro plugin to read data about your YouTube videos and YouTube channel.

4) YouTube sends back your YouTube Access Token and YouTube Refresh Token which are then automatically saved by the Feeds for YouTube Pro plugin. This information is required in order to connect to the YouTube API.

5) Enter a YouTube channel name to display videos from.

6) Navigate to the Customize and Style pages to customize your YouTube feed.

7) Once you've customized your YouTube feed, click on the "Display Your Feed" tab for directions on how to display your YouTube feed (or multiple YouTube feeds).

8) Copy the [youtube-feed] shortcode and paste it into any page, post or widget where you want the YouTube feed to appear.

9) You can paste the [youtube-feed] shortcode directly into your page editor.

10) You can use the default WordPress 'Text' widget to display your Feeds for YouTube Pro in a sidebar or other widget area.

11) View your website to see your YouTube feed(s) in all their glory!

== Frequently Asked Questions ==

= Can I display multiple YouTube feeds on my site or on the same page? =

Yep. You can display multiple YouTube feeds by using our built-in shortcode options, for example: `[youtube-feed channel="smashballoon" num=3]`.

= How do I embed a YouTube gallery directly into a WordPress page template? =

You can embed your YouTube gallery directly into a template file by using the WordPress [do_shortcode](http://codex.wordpress.org/Function_Reference/do_shortcode) function: `<?php echo do_shortcode('[youtube-feed]'); ?>`.

= Will Feeds for YouTube Pro work with W3 Total Cache or other caching plugins? =

The Feeds for YouTube Pro plugin should work in compatibility with most, if not all, caching plugins, but you may need to tweak the settings in order to allow the YouTube feed to update successfully and display your latest posts.  If you are experiencing problems with your YouTube feed not updating then try disabling either 'Page Caching' or 'Object Caching' in W3 Total Cache (or any other similar caching plugin) to see whether that fixes the problem and the YouTube feed displays and updates successfully.

== Screenshots ==

1. By default the plugin inherits styles from your theme
2. Display multiple YouTube feeds and customize each one
3. When using the "grid" layout, videos open and play in a lightbox
4. Feeds for YouTube Pro Settings pages
5. Built in customization settings make customizing your feed easy
6. Use handy shortcode options to customize individual feeds
7. To display a feed just copy and paste the shortcode into a widget or page

== Changelog ==
= 1.2 =
* New: Added compatibility with our new [Social Wall](http://smashballoon.com/social-wall/?utm_source=plugin-pro&utm_campaign=cff&utm_medium=readme) plugin, which allows you to combine feeds from Instagram, Facebook, Twitter, and YouTube into one social media "wall". If you are using our Smash Balloon All-Access Bundle then the Social Wall plugin is included at no additional cost. Just log into your account to download and install the plugin.

= 1.1.4 =
* Tweak: Added noticed explaining the account connection and what it can and can't be used for when connecting an account.
* Tweak: Removed refresh token from being required for a manual connection.
* Tweak: Cache name for custom search feeds changed to avoid cache conflicts.
* Fix: Caches of multiple feeds with filters would cause conflicts resulting in the wrong videos displaying in feeds.
* Fix: Improved performance of API requests for creating feeds.

= 1.1.3 =
* New: To help us improve the plugin we have added usage tracking so that we can understand what features and settings are being used, and which features matter to you the most. The plugin will send a report in the background once per week with your plugin settings and basic information about your website environment. No personal or sensitive data is collected (such as email addresses, YouTube account information, license keys, etc). You can opt-out by simply disabling the setting at: Feeds for YouTube > Customize > Advanced > Enable Usage Tracking. See [here](https://smashballoon.com/youtube-feed/docs/usage-tracking/) for more information.
* Tweak: Play icon changed to YouTube logo to comply with YouTube's terms of service.
* Tweak: More details are provided for API errors such as an API key not working after being created.
* Tweak: If an API key has not been entered, an account reconnection is required if trying to display videos from a previously unused channel and more than an hour has passed since the last account connection.
* Fix: Prevented a Fatal PHP error caused by retrieving videos without using an API key and either entering a non-existing channel ID or having allow_url_fopen disabled on the server.
* Fix: Non-latin characters were displaying incorrectly in titles and descriptions when a feed was using backup data.

= 1.1.2 =
* Tweak: Video thumbnails are cropped to a 9:16 aspect ratio to remove black bars at the top and bottom of the images.
* Tweak: Improved workarounds for video player issues caused by having YouTube iframes from other sources on the same page.
* Fix: YouTube video post type would sometimes cause issues with WordPress search feature.

= 1.1.1 =
* Tweak: Add setting to load iframes with the rest of the page to prevent conflict caused by other plugin's or theme's YouTube players being on the same page.
* Tweak: Upcoming and currently playing live streams are loaded into the feed differently allowing for up to 15 videos to display in a feed.
* Fix: Emoji in video description would cause the wrong thumbnail to be used for list layout.
* Fix: Line breaks in combination with double quotes in video descriptions would cause feed display issues.

= 1.1 =
* Tested with WordPress 5.4 update.
* New: Display video players in 9:16 ratio. To use 3:4 ratio, go to the "Customize" tab, "Video Experience" area.
* New: "Single" feed type added. Use this type to display individual YouTube videos using the video ID.
* New: Added a “Feeds for YouTube” Gutenberg block to use in the block editor, allowing you to easily add a feed to posts and pages.
* Tweak: Removed extra space between posts if there was no info for the video included in the feed.
* Tweak: Changed how live streaming video feed type works to be more reliable.
* Tweak: Non Feeds for YouTube admin notices are removed when viewing settings pages for Feeds for YouTube.
* Tweak: Slight styling changes for how end of video actions are displayed.
* Fix: Pagination for certain feed types would not work properly.
* Fix: PHP error related to using PHP version 5.3 or less.
* Fix: Background caching was not updating feed caches.

= 1.0 =
* Launched the Feeds for YouTube Pro plugin!