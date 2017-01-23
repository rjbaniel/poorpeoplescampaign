=== Vimeo Short Code ===
Contributors: Matroschka, Gabriele Maidecchi
Tags: video, embed, movie, shortcode, plugin, clip, vimeo, flv, quicktag, html5
Requires at least: 2.5
Tested up to: 3.8.1-alpha
Stable tag: trunk

Allows the user to embed Vimeo movie clips by entering a shortcode ([vimeo]) into the post area.

== Description ==

Allows the user to embed Vimeo movie clips by entering a shortcode (`[vimeo]`) into the post area. Vimeo's options regarding the display of meta properties like by-line, title, or the video author's portrait are supported as short code attributes. We built this plugin as a solution for embedding videos on [our site](http://www.partnervermittlung-ukraine.net/info/lux-vimeo-wordpress-plugin).

= Credits =

HTML5 embed code contributed by Gabriele Maidecchi. German translation by [@talkpress](http://talkpress.de/).

= Usage =
1. Enter the `[vimeo clip_id="XXXXXXX"]` short code into any post. `clip_id` is the number from the clip's URL (e.g. `vimeo.com/12345678`)
1. Optionally modify the clip's appearance by specifying width or height, like so: `[vimeo clip_id="XXXXXXX" width="400" height="225"]`
1. Toggle the display of byline, portrait and title like so: `[vimeo clip_id="XXXXXXX" byline="0" portrait="1" title="1"]`
1. Using empty values for either the `width` or `height` attributes will cause Lux Vimeo to calculate the proper dimension based on a 16:9 aspect ration. Example: `[vimeo clip_id="12345678" height="300" width=""]` or `[vimeo clip_id="12345678" height="" width="640"]`

== Installation ==

1. Unzip `lux_vimeo.zip` and upload the contained files to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Change log ==

= Version 1.3 =

1. Bug fixes
1. Remove deprecated old embedding method with OBJECTs
1. Tested for WP 3.8.1-alpha compatibility

= Version 1.2 =

1. Tested for WP 3.7-alpha compatibility

= Version 1.1 =

1. Added attributes: byline, title, portrait, html5 (props Gabriele Maidecchi), color
1. Fixed bug in dimension calculation.

= Version 1.0 =

Initial release.