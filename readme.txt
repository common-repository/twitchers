=== Twitchers ===
Author: Kevin Heath (ypraise.com)
Plugin URI: http://ypraise.com/2013/wordpress/plugins/wordpress-2/twitchers-wordpress-plugin/
Donate link: http://ypraise.com/2013/wordpress/wordpress-2/suport-my-free-wordpress-plugins/
Tags: geotagging, geolocation, geolocate, geotag, place, location, gps, placemark, sightings, map, crowd sourcing, crowdsourcing, maps, google maps, birdwatching, wildlife
Requires at least: 3.0
Tested up to: 3.6.0
Stable tag: 2.5
Version: 2.5
License: GPLv2 or later


== Description ==

Twitcher allows people who visit your web site to post wildlife sightings and display them on a Google map. The plug-in only offers a front end google maps geotagging option. The plug-in was inspired by the Sightings plug-in but with lots of fixes to the code. You can display the maps using one of two shortcodes [twitcher-map] which shows a contributor form and [twitcher-map1] which does not. They use different DOM option so you can display one on the sidebar and still call the main map without a DOM conflict.

The plug-in is ideal for wildlife and nature website where they want visitors to contribute to the site. But it can be used for plotting anything you want where you need a front end map function.

While a lot of fixes have been made to get this working there is quite a number of things to do. 


== Installation ==

Manual install: Unzip the file into your WordPress plug-in-directory. Activate the plug-in in wp-admin.

Install through WordPress admin: Go to Plugins > Add New. Search for "Twitcher". Locate the plug-in in the search results. Click "Install now". Click "Activate".

== Frequently Asked Questions ==

= How do I use this plug-in? =

Add a new post and add the shortcode [twitcher-map] and then save it. The map will be displayed and people will be able to add a contribution.
To display the map in the sidebar ensure your theme allows text widget boxes to run shortcodes and add [twitcher-map1] to display a non-interactive version of the map.
You can make your theme shortcode friendly in widget text boxes by adding the following to your theme functions file:
 add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode');

= Can I filter out markers by category on the larger map? =

Yes, just enter the [twitcher-map] attribute cat_slug or cat_id for the category you want to display on the map.
This will only fetch and display markers for the provided category on the [twitcher-map].
For example: [twitcher-map cat_id="3"] ,will display only records that have the category with id 3.

= What does each colour market mean? =

The different colour markers represent how long ago the sighting was made. The colours are:

Red - within the last 24 hours

Dark green - between 1 day and 3 days ago

Light green - between 4 days and 7 days ago

Orange - between 8 days and 14 days ago

Dark grey - between 15 and 30 days ago

Light grey - over 30 days ago.

I plan at some stage to give you the option to change these time periods through the plug-in admin screen but this is a to do at some later stage.

The timing is based on the number of hours since the posting was made so there may be some slight discrepancies in actual day number if the post was made in the afternoon and you are viewing the map in the morning. This is something I plan on cleaning up in the future at some time.

= Can I change the markers to my own design? =

Yes you can. Since Twitchers 2.0 the markers are custom icons and do not use ones called from the Google api.

However at the moment you have to replace the icons manually, you will find them in the images folder of the plug-in. Just swap the relevant markers with your own design - but remember to keep the png file the same dimensions and keep the file name the same otherwise the plug-in will not be able to find it.
To enable your own custom icons to be used I have not mapped the click area to bring up the infobox. As it stands the entire png image is a click area.

I do plan on some time in the future to add image upload options to the admin page so you can upload your own personal icons  without having to change it in the images folder. This is a to do feature so is not currently available.


== Screenshots ==

1. The map displaying on a page.

2. The contributor form that is presented to visitors when clicking on add contribution.



== Changelog ==

= 2.5 =

* cleaned up some open short php opening tags.

= 2.4 = 

* updated datepicker for WP3.6 jquery

= 2.3 = 

*  added a read the report link to the infowindow to encourage people to click through to the post.

*  changed the image on the post page from thumbnail to full size.

= 2.2 = 

* fixed issue of not being able to set zoom using shortcode attributes in the twitcher-map1 shortcode.

* commented out the content in the map info window. For some reason if people added information into the body that contained a line break then the map display broke.  There must be some form of filter in operation that stops <br /> from being passed into the map markers that I can not find. I will fix and re-establish this at some stage in the future. The post that is formed by the contribution works OK it's just when it's imported into the info-window on the map that it causes a break. The info window now contains the date, thumbnail, post title and link to the actual contributors post.
 

= 2.1 = 

* added auto-locate feature for the add contribution feature. This makes it quicker and easier to post via mobile when out in the field.

= 2.0 = 

* added a time ago since posting in days and hours to the info window which is helpful to give people an idea if the sighting is new or old.

* added visual representation of how long ago the sighting was made through different colour markers

* markers are now custom icons so you can use your own icons by changing the png files in the images folder.

= 1.4 =

* moved the script calls to the main php file.

* moved the shortcodes code to the main php file.

* changed the script enqueue hook from init to the wordpress recommended wp_enqueue_scripts hooks.

* This completes the rewrite of the plug-in to bring it inline with wordpress standards. Future updates will concentrate on additional functions and customisation options.

= 1.3 =

* Moved the settings page mark up and registration to the main twitcher php file.

* simplified settings coding to reduce jquery conflict risk and converted to standard Wordpress Settings API to make it easier to add options in upcoming changes.

* This is a major update and there are changes to the options table settings. Once updated you will need to reset your default settings for map position, zoom  etc. You will not lose any posts or contributions but back up to be on the safe side.



= 1.2 =

* added map display to contributor posts.

* added some div tags for styling of contributor posts.

= 1.1 =

* Added imagees to contributor blog post via the Wordpress default gallery shortcode. This will not add images to sightings already posted, only new reports will contain the image.

* Seet the default contributor post to draft so you can pre-moderate. Eventually the choice will go into the setting page for you to slect moderation on or off. If you want the post to go live without moderation open up class-twitchers.php and change post_status'=>'draft' to post_status'=>'publish' on line 58

= 1.0 =
* Say hello to twitcher - the birdwatchers blogging friend.

== Upgrade Notice ==

= 2.5 =

* cleaned up some short opening php tags.

= 2.4 = 

* updated datepicker for WP3.6 jquery

= 2.3 = 

*  added a read the report link to the infowindow to encourage people to click through to the post.

*  changed the gallery image on the post page from thumbnail to full size.

= 2.2 = 

* fixed issue of not being able to set zoom using shortcode attributes in the twitcher-map1 shortcode.

* commented out the content in the map info window. For some reason if people added information into the body that contained a line break then the map display broke.  There must be some form of filter in operation that stops <br /> from being passed into the map markers that I can not find. I will fix and re-establish this at some stage in the future. The post that is formed by the contribution works OK it's just when it's imported into the info-window on the map that it causes a break. The info window now contains the date, thumbnail, post title and link to the actual contributors post.

= 2.0 =
A number of changes to show how long ago the posting was made and the plug-in now uses custom icons with a time conditional so different colour markers are shown according to age of sighting.

= 1.4 =
bought code and script calls all within the main php file allowing the removal of 2 folders and 3 files. Changed the script enqueue hook from the init to the wordpress recommended wp_enqueue_scripts.

= 1.3 =
Major update which changes the options tables for default data and plug-in settings. Converted the settings page to make use of standard Wordpress settings api to make it easier for future upgrades such as allowing custom marker uploads.


= 1.2 =
Added the map on the contributor post page and also added div tags for styling of contributor post page.

= 1.1 =
Added the images to contributor blog posts and set the default post status to default to allow moderation. See change log for how to set it to publish immediately. This option to choose will eventually make it to the settings page.

= 1.0 =
Initial release.
