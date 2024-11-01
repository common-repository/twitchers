<?php
/*
Plugin Name: twitchers
Plugin URI: http://ypraise.com/2013/wordpress/plugins/wordpress-2/twitchers-wordpress-plugin/
Description: twitcher is an easy to use plugin for geo-tagging visitor wildlife sightings on a Google map. You can display all placemarks on a large map.  It utilizes Google Maps Javascript API V3.
Version: 2.5
Author: Kevin Heath
Author URI: http://ypraise.com/
License: GPLv2 or later
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
 * Plugin constants
 */
define ('twitcher_HANDLE','twitcher');
define ('twitcher_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define ('twitcher_PLUGIN_DIR', WP_PLUGIN_DIR .'/'. dirname( plugin_basename( __FILE__ )));

register_uninstall_hook(__FILE__, 'twitchers_delete_plugin_options');
function twitchers_delete_plugin_options() {
	delete_option('twitchers_options');
}

// enqueue and load scripts
function twitcher_enqueue() {
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui', twitcher_PLUGIN_DIR_URL.'js/jquery-ui-1.8.18.custom.min.js');
      wp_enqueue_script('google_maps_javascript','http://maps.googleapis.com/maps/api/js?sensor=true');
      wp_enqueue_style(twitcher_HANDLE.'_style', twitcher_PLUGIN_DIR_URL.'twitcher.css?'.rand(0,9999));
	    wp_enqueue_script('date', twitcher_PLUGIN_DIR_URL.'js/date.js');
		 wp_enqueue_script('datePicker', twitcher_PLUGIN_DIR_URL.'js/datePicker.js');
		 		 wp_enqueue_style('datePickercss', twitcher_PLUGIN_DIR_URL.'js/datePicker.css');

	  };
add_action('wp_enqueue_scripts', 'twitcher_enqueue');

/*
 * Load twitcher libraries
 */
require_once twitcher_PLUGIN_DIR . '/class-twitcher.php';


/**
 * twitcher plugin settings page
 * @return void
 */

// set up menu and options page.

add_action( 'admin_menu', 'twitchers_menu' );


function twitchers_menu() {
	add_options_page( 'twitchers', 'Twitchers', 'manage_options', 'twitchers', 'twitchers_options' );
}

add_action ('admin_init', 'twitchers_register');

function twitchers_register(){
register_setting('twitchers_options', 'twoptions');
}







function twitchers_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
	<h2>Twitchers Settings</h2>

	<div id="donate_container">
     <p> Help keep this plug-in in development and improved by using our Amazon links to make your purchases. Your commission can help support all my free Wordpress plugins. <a href="http://ypraise.com/2013/wordpress/wordpress-2/suport-my-free-wordpress-plugins/">My Amazon page</a></p>
	 <p>A Pro version of this plug-in is available. The extra feature is that people can plot the location of the sighting by using a location search box as well as geo-location and drag-and-drop. <a href="http://ypraise.com/2013/wordpress/plugins/wordpress-2/twitchers-wordpress-plugin/">Twitchers Pro</a>
    </div>
	
	<p><form method="post" action="options.php">	</p>

	
	<?php
	
	settings_fields( 'twitchers_options' );
	$options = get_option('twoptions');
	
?>

<p></p>
<h3>Set map details:</h3>

<p>Set map display location and zoom:</p>
<p>Latitude:  <input type="text" size="10" name="twoptions[twlat]" value="<?php echo $options['twlat']; ?>" /></p>
<p>Longitude: <input type="text" size="10" name="twoptions[twlong]" value="<?php echo $options['twlong']; ?>" /></p></p>
<p>Zoom level: <input type="text" size="3" name="twoptions[twzoom]" value="<?php echo $options['twzoom']; ?>" /></p></p>


<p>How many markers to display on the maps: <input type="text" size="3" name="twoptions[twitchers_markers]" value="<?php echo $options['twitchers_markers']; ?>" /></p>

<h3>Set public contribution details: </h3>
<p>Set the user to be the publisher of post - MUST have posting rights to allow contributor posts to be published:

                    <select id="users" name="twoptions[twitchers_poster]">
<?php
                        global $wpdb;
    $query = "SELECT ID, user_nicename from $wpdb->users ORDER BY user_nicename";
    $authors = $wpdb->get_results($query);
    foreach($authors as $author) {
        echo '<option value="'.$author->ID.'" '. ($options['twitchers_poster'] == $author->ID ? ' selected' : '') .'>'.$author->user_nicename.'</option>';
    }
    ?>
                    </select>
					<br />
					Should the publisher be notified of new post by email? <input id="notify_user" name="twoptions[notify_user]" type="checkbox" <?php echo isset($options['notify_user']) ? 'checked="checked"' : '' ?>/>


</p>


<p>Set the contibutor post to publish for immediate publication or set to draft for moderation : <select name="twoptions[twitchers_postset]">
							<option value='draft' <?php selected('draft', $options['twitchers_postset']); ?>>Draft</option>
							<option value='publish' <?php selected('publish', $options['twitchers_postset']); ?>>Publish</option>
							</select>

</p>


<p>

        <div class="tables_container">
            <table id="available_categories">
                <tr>
                    <th class="cats_header">
                       Select categories available for public contributions:<br />
					   use +ctrl for multi select
                    </th>
                    <td></td>
                </tr>
                <tr>
                    <td>
                              <select multiple="twitcher_categories" size="10" name="twoptions[tw_cats][]">
                        <?php
                       
                            // Get all categories
                            $categories = get_categories( array( 'type' => 'post','orderby'=> 'name', 'order' => 'ASC' ));
						
                            foreach($categories as $category) {
							
							$label = $category->name;
							$value= $category->term_id;
							
							 if ( in_array( $category->term_id, $options['tw_cats'] ))
							 
							  echo "<option selected='selected' value='" . esc_attr( $value ) . "'>$label</option>";
							  else
                                echo '<option value="'.$category->term_id.'">'.$category->name.'</option>';
                            }
                        ?>
                        </select>
                    </td>
                    <td style="padding: 10px 20px;">
                     <label class="description" for="twoptions[tw_cats]"></label>
                    </td>

                </tr>
            </table>

			
</div>


</p>

 <?php


	
 submit_button();
echo '</form>';

	
	echo '</div>';
}

add_action('admin_init', 'twitchers_admin_init');

function twitchers_admin_init(){

register_setting( 'twitchers_options', 'twitchers_poster');
register_setting( 'twitchers_options', 'twitchers_markers');
register_setting( 'twitchers_options', 'twitchers_postset');

}

//set up shortcodes

/**
 *  Logic and template for the [twitcher-map] shortcode which produces a map containing twitcher from posts
 *
 *  TODO: Support attributes
 *  Optional attributes:
 *  width    -  Set map width in pixels or percent
 *  height   -  Set map height in pixels or percent
 *  zoom     -  Set zoom level
 *  cat_id -  Set category ID for posts to be displayed on map (will inherit sub categories). Will display all if not used.
 *  cat_slug -  Set category slug for posts to be displayed on map (will inherit sub categories). Will display all if not used.
 *  allow_contributors - If contributor link should be visible or not. Visible by default.
 */

function twitcher_map_function($parameters) {
    // Extracting attributes as variables
    $height = '';
    $width = '';
    $zoom = '';
    $cat_id = '';
    $cat_slug = '';
    $draggable = '';
    $scrollwheel = '';
    $allow_contributors = '';
    $allow_contributor_image = '';

    extract(
        shortcode_atts(
            array( // Default attribute values for the twitcher-map
                'width' =>  '100%',
                'height'=>  '400px',
                'zoom'  =>  '4',
                'draggable' => 'true',
                'scrollwheel' => 'false',
                'allow_contributors' => 'true',
                'allow_contributor_image' => 'true',
                'cat_id' => '',
                'cat_slug' => ''
            ), $parameters ) );

    $manager = new twitcher_Manager();

    $twitcher_post_array = array();

    // If contribution form was posted, create new post
    if(isset($_POST['twitcher_title']) && $_POST['twitcher_title'] != '') {
        $twitcher_post_array['title'] = $_POST['twitcher_title'];
    }

    if(isset($_POST['twitcher_date']) && $_POST['twitcher_date'] != '') {
        $twitcher_post_array['date'] = $_POST['twitcher_date'];
    }

    if(isset($_POST['twitcher_body']) && $_POST['twitcher_body'] != '') {
        $twitcher_post_array['body'] = $_POST['twitcher_body'];
    }

    if(isset($_POST['marker_lat']) && $_POST['marker_lat'] != '' && isset($_POST['marker_lng']) && $_POST['marker_lng'] != '') {
        $twitcher_post_array['markers'] = array(array($_POST['marker_lat'], $_POST['marker_lng']));
    }

    if(isset($_POST['map_zoom']) && $_POST['map_zoom'] != '') {
        $twitcher_post_array['zoom'] = $_POST['map_zoom'];
    }

    if(isset($_FILES['twitcher_image_file']) && $_FILES['twitcher_image_file'] != '') {
        $twitcher_post_array['image_file'] = 'twitcher_image_file';
    }

    if(isset($_POST['twitcher_category']) && $_POST['twitcher_category'] != '') {
        $twitcher_post_array['category'] = $_POST['twitcher_category'];
    }

    if(isset($_POST['twitcher_contributor_name']) && $_POST['twitcher_contributor_name'] != '') {
        $twitcher_post_array['name'] = $_POST['twitcher_contributor_name'];
    }

    if(isset($_POST['twitcher_contributor_email']) && $_POST['twitcher_contributor_email'] != '') {
        $twitcher_post_array['email'] = $_POST['twitcher_contributor_email'];
    }

    if(count($_POST) > 0) {
        ?>
    <div id="twitcher_message">
        <?php
        // TODO: Perhaps some more distinct validation here
        if(empty($twitcher_post_array['title']) || empty($twitcher_post_array['body']) || empty($twitcher_post_array['markers']) || empty($twitcher_post_array['zoom'])) {
        echo '<p class="error">';
        _e('The contribution was not submitted! You need to fill out the form completely.',twitcher_HANDLE);
        echo '</p>';
    }
    else {
        $manager->createtwitcherPost($twitcher_post_array);

        echo '<p class="success">';
        _e('Thanks for your contribution!',twitcher_HANDLE);
        echo '</p>';
    }
        ?>
    </div>
        <?php
                    }

    $default_settings = $manager->gettwitcherSettings();

    if((empty($cat_id)) && (empty($cat_slug)))
        $twitcher = $manager->getAlltwitcher();
    else
        $twitcher = $manager->gettwitcherByCategory($cat_slug ? $cat_slug : $cat_id); // cat_slug dominates cat_id

    // Map container
	
	ob_start();
    ?>
<div id="twitcher_map" style="width:<?php echo $width ?>; height:<?php echo $height ?>;">
</div>
    <?php if($allow_contributors) : ?>
        <div class="twitcher_contributor_panel">
            <a href="#">[+] <?php _e('Contribute with a location',twitcher_HANDLE) ?></a>
        </div>
    <?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        <?php
        // Calculate map center based on marker positions
        $lat = '';
        $lng = '';
        $markers_total = 0;
        $markers = array();
        if(count($twitcher) > 0) {
            foreach($twitcher as $sight)
            {
                $sight = get_post_meta($sight->ID, twitcher_HANDLE, ARRAY_A);
                if(isset($sight['markers'])) {
                    $markers = $sight['markers'];
                    $markers_total += count($markers);
                    foreach($markers as $marker_latlng){

                        $lat += $marker_latlng[0];
                        $lng += $marker_latlng[1];
                    }
                }
            }
            $lat = ($lat / $markers_total);
            $lng = ($lng / $markers_total);
        }
        else {
            $lat = 52.6152;
            $lng = -3.7754;
        }
		
		$options = get_option('twoptions');
		$days = round((date('U') - get_the_time(('U'),($sight->ID)))/ (60*60*24));
        ?>
		
		
        var map_latlng =  new google.maps.LatLng(<?php echo $options['twlat'] ?>, <?php echo $options['twlong'] ?>);
        var myOptions = {
            zoom: <?php echo isset($sighting['zoom']) ? $sighting['zoom'] : $options['twzoom'] ?>,
            center: map_latlng,
            draggable: <?php echo $draggable ?>,
            scrollwheel: <?php echo $scrollwheel ?>,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.LARGE
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById('twitcher_map'),
                myOptions);
				
				
			



				image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/red-marker.png';			

				
        var addMarker = function(map, latlng) {
            var marker = new google.maps.Marker({
                map: map,
                draggable: false,
                position: latlng,
				icon: image
            });
            return marker;
        };
        <?php
        // Render sighting markers on map
        if(count($twitcher) > 0) {
            foreach($twitcher as $sight)
            {
                $sight_meta = get_post_meta($sight->ID, twitcher_HANDLE, ARRAY_A);

                if($sight != '' && $sight->post_status == 'publish') {
                    $sight_info = '<div class="sight_info">';
                        has_post_thumbnail($sight->ID) ? $sight_info .= '<p>'.get_the_post_thumbnail($sight->ID,'thumbnail').'</p>' : '';
                    $sight_info .= '<p><strong><a href="'.get_post_permalink($sight->ID).'">'.htmlentities($sight->post_title, ENT_QUOTES).'</a></strong></p>';
                    $sight_info .= $sight_meta['date'] ? '<p>Date: '.$sight_meta['date'].'</p>' : '';
				$days = round((date('U') - get_the_time(('U'),($sight->ID)))/ (60*60*24));
				$days1 = round((date('U') - get_the_time(('U'),($sight->ID)))/ (60*60));
					$sight_info .= '<p>Posted: '.$days.' days ago ( '.$days1.' hours ago)</p>';
				 $sight_info .= $sight_meta['name'] ? '<p>Submitted by: '.$sight_meta['name'].'</p>' : '';
					 $sight_info .= '<p><strong><a href="'.get_post_permalink($sight->ID).'">Read the report</a></strong></p>'; 
          //          $sight_info .= '<p class="excerpt">'.(htmlentities($sight->post_excerpt != '' ? $manager::shorten($sight_meta['body'],100) : $manager::shorten($sight_meta['body'],100), ENT_QUOTES)).'</p>';
                    $sight_categories = wp_get_post_categories($sight->ID);
                    $sight_info .= '<p><strong>'.get_cat_name($sight_categories[0]).'</strong></p>'; 
					// Will only fetch the first category
                    $sight_info .= '</div>';
                    ?>
                    var infoWindow = new google.maps.InfoWindow();
					
                    <?php
                    foreach($sight_meta['markers'] as $marker_latlng) {
                        ?>
						var days ='<?php echo $days ?>';
						var image = '';
					if	(days >= 31){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/grey-marker.png';			
			}
			else	if	(days >= 15){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/dgrey-marker.png';			
			}
						else	if	(days >= 8){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/orange-marker.png';			
			}
			
			
		else	if	(days >= 4){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/lgreen-marker.png';			
			}
			else	if	(days >= 1){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/green-marker.png';			
			}
			
			else {
						 image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/red-marker.png';			
				}
				
                        var latlng = new google.maps.LatLng(<?php echo $marker_latlng[0] ?>,<?php echo $marker_latlng[1] ?>);
                        var marker = addMarker(map, latlng);


						
                        google.maps.event.addListener(marker, 'click', function(){
                            infoWindow.setContent('<?php echo $sight_info ?>');
                            infoWindow.open(map, this);
                        });
                        <?php
                    }
                }
            }
        } ?>
            jQuery('.twitcher_contributor_panel a').one('click', function() {
                var markerImage = new google.maps.MarkerImage('<?php echo twitcher_PLUGIN_DIR_URL ?>/images/blue-marker.png',
                      new google.maps.Size(32,32),
                      new google.maps.Point(0,0),
                      new google.maps.Point(16,32)
                );
                var markerShadow = new google.maps.MarkerImage('<?php echo twitcher_PLUGIN_DIR_URL ?>/images/marker-shadow.png',
                      new google.maps.Size(52,32),
                      new google.maps.Point(0,0),
                      new google.maps.Point(16,32)
                );

                var infoWindow = new google.maps.InfoWindow ();
                <?php // Setup the contributor form
                $sight_form = '<form action="#" method="post" enctype="multipart/form-data"><div class="contributor_form">';
                $sight_form .='<div><label>'.__('Date', twitcher_HANDLE).':<input type="text" id="twitcher_date" name="twitcher_date" style="width:20%" /></label></div>';
                $sight_form .='<div><label for="twitcher_title">'.__('Title',twitcher_HANDLE).':</label><input id="twitcher_title" type="text" name="twitcher_title"/></div>';
                $sight_form .='<div><label for="twitcher_body">'.__('Description',twitcher_HANDLE).':</label><textarea id="twitcher_body" cols rows name="twitcher_body"></textarea></div>';
                if($allow_contributor_image != 'false')
                    $sight_form .='<div><label for="twitcher_image">'.__('Attach an image (optional)',twitcher_HANDLE).':<input type="file" name="twitcher_image_file" id="twitcher_image_file" value="'.__('Browse',twitcher_HANDLE).'" /></label><p id="browsed_image"><em>'.__('No image file selected',twitcher_HANDLE).'</em></p></div>';
                $sight_form .='<div><label for="twitcher_contributor_name">'.__('Your name',twitcher_HANDLE).':</label><input id="twitcher_contributor_name" type="text" name="twitcher_contributor_name"/></div>';
                $sight_form .='<div><label for="twitcher_contributor_email">'.__('Your e-mail',twitcher_HANDLE).':</label><input id="twitcher_contributor_email" type="text" name="twitcher_contributor_email"/></div>';

                // Hidden fields containing marker lat, lng and map zoom level
                $sight_form .='<input type="hidden" id="marker_lat_hidden" name="marker_lat" >';
                $sight_form .='<input type="hidden" id="marker_lng_hidden" name="marker_lng" >';
                $sight_form .='<input type="hidden" id="map_zoom_hidden" name="map_zoom" value="9">';

                // Contributor categories
				$options = get_option('twoptions');
				
                if(isset($options['tw_cats']) && count($options['tw_cats']) > 0) {
                    $sight_form .= '<label for="twitcher_category">'.__('Category',twitcher_HANDLE).':</label><select id="twitcher_category" name="twitcher_category">';
                    foreach($options['tw_cats'] as $cat) {
                        $sight_form .= '<option value="'.$cat.'">'.get_cat_name($cat).'</option>';
                    }
                    $sight_form .= '</select>';
                }

                $sight_form .='<div><input type="submit" value="'.__('Submit',twitcher_HANDLE).'"></div></div></form>';
                ?>

var marker = new google.maps.Marker({
    clickable: false,
    icon: new google.maps.MarkerImage('//maps.gstatic.com/mapfiles/mobile/mobileimgs2.png',
                                                    new google.maps.Size(22,22),
                                                    new google.maps.Point(0,18),
                                                    new google.maps.Point(11,11)),
    shadow: null,
    zIndex: 999,

                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        position: map.getCenter(),
                        icon: markerImage,
                        shadow: markerShadow
                    });

			if (navigator.geolocation) navigator.geolocation.getCurrentPosition(function(pos) {
    var me = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
    marker.setPosition(me);
}, function(error) {
    // ...
});		
					
					
                // Initial marker information
                var greet = function () {
                    var stopAnimation = function() {
                        marker.setAnimation(null);
                    };
                    marker.setAnimation(google.maps.Animation.BOUNCE);
                    infoWindow.setContent('<?php _e('<p>Drag me and click me!</p>',twitcher_HANDLE) ?>');
                    infoWindow.open(map, marker);
                    setTimeout(stopAnimation,500);
                };
                setTimeout(greet,1000);


                google.maps.event.addListener(marker, 'click', function(){
                    infoWindow.setContent('<?php echo $sight_form ?>');
                    infoWindow.open(map, this);
                    var updateSightForm = function () {
                        jQuery('#map_zoom_hidden').val(map.getZoom());
                        jQuery('#marker_lat_hidden').val(Math.round(marker.getPosition().lat()*10000)/10000);
                        jQuery('#marker_lng_hidden').val(Math.round(marker.getPosition().lng()*10000)/10000);

                        jQuery('#twitcher_image_file').change(function() {
                            jQuery('#browsed_image').html(jQuery(this).val().replace('C:\\fakepath\\', ''));
                        });
                        jQuery('#twitcher_date').datePicker();
                    };
                    setTimeout(updateSightForm,500); // Since sight_form does not always exist before this
                });

                // Close infoWindow on dragstart
                google.maps.event.addListener(marker, 'dragstart', function() {
                    infoWindow.close();
                });

                jQuery(this).parent().slideUp().unbind('click');

                return false;
            });
        });
    </script>

    <?php
	  return ob_get_clean();
	
    };

add_shortcode('twitcher-map', 'twitcher_map_function');






function twitcher_map1_function($parameters) {


    // Extracting attributes as variables
    $height = '';
    $width = '';
    $zoom = '';
    $cat_id = '';
    $cat_slug = '';
    $draggable = '';
    $scrollwheel = '';
	
	

    extract(
        shortcode_atts(
            array( // Default attribute values for the twitcher-map
                'width' =>  '100%',
                'height'=>  '400px',
                'zoom'  =>  '4',
                'draggable' => 'true',
                'scrollwheel' => 'false',
                'cat_id' => '',
                'cat_slug' => ''
            ), $parameters ) );

    $manager = new twitcher_Manager();

    $twitcher_post_array = array();



    $default_settings = $manager->gettwitcherSettings();

    if((empty($cat_id)) && (empty($cat_slug)))
        $twitcher = $manager->getAlltwitcher();
    else
        $twitcher = $manager->gettwitcherByCategory($cat_slug ? $cat_slug : $cat_id); // cat_slug dominates cat_id

    // Map container
	
	ob_start();
    ?>

<div id="twitcher_map1" style="width:<?php echo $width ?>; height:<?php echo $height ?>;">
</div>


<script type="text/javascript">
    jQuery(document).ready(function(){
        <?php
        // Calculate map center based on marker positions
        $lat = '';
        $lng = '';
        $markers_total = 0;
        $markers = array();
        if(count($twitcher) > 0) {
            foreach($twitcher as $sight)
            {
                $sight = get_post_meta($sight->ID, twitcher_HANDLE, ARRAY_A);
                if(isset($sight['markers'])) {
                    $markers = $sight['markers'];
                    $markers_total += count($markers);
                    foreach($markers as $marker_latlng){

                        $lat += $marker_latlng[0];
                        $lng += $marker_latlng[1];
                    }
                }
            }
            $lat = ($lat / $markers_total);
            $lng = ($lng / $markers_total);
        }
        else {
            $lat = 52.6152;
            $lng = -3.7754;
        }
   	$options = get_option('twoptions');
        ?>
		
		
        var map_latlng =  new google.maps.LatLng(<?php echo $options['twlat'] ?>, <?php echo $options['twlong'] ?>);
        var myOptions = {
           zoom: <?php echo $zoom ?>,
            center: map_latlng,
            draggable: <?php echo $draggable ?>,
            scrollwheel: <?php echo $scrollwheel ?>,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.LARGE
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
     
        };
        var map = new google.maps.Map(document.getElementById('twitcher_map1'),
                myOptions);
        var addMarker = function(map, latlng) {
            var marker = new google.maps.Marker({
                map: map,
                draggable: false,
                position: latlng,
				icon: image
            });
            return marker;
        };
        <?php
        // Render sighting markers on map
        if(count($twitcher) > 0) {
            foreach($twitcher as $sight)
            {
                $sight_meta = get_post_meta($sight->ID, twitcher_HANDLE, ARRAY_A);

                if($sight != '' && $sight->post_status == 'publish') {
                    $sight_info = '<div class="sight_info">';
                        has_post_thumbnail($sight->ID) ? $sight_info .= '<p>'.get_the_post_thumbnail($sight->ID,'thumbnail').'</p>' : '';
                     $sight_info .= '<p><strong><a href="'.get_post_permalink($sight->ID).'">'.htmlentities($sight->post_title, ENT_QUOTES).'</a></strong></p>';
                    $sight_info .= $sight_meta['date'] ? '<p>Date: '.$sight_meta['date'].'</p>' : '';
				$days = round((date('U') - get_the_time(('U'),($sight->ID)))/ (60*60*24));
				$days1 = round((date('U') - get_the_time(('U'),($sight->ID)))/ (60*60));
					$sight_info .= '<p>Posted: '.$days.' days ago ( '.$days1.' hours ago)</p>';
					 $sight_info .= $sight_meta['name'] ? '<p>Submitted by: '.$sight_meta['name'].'</p>' : '';
					 $sight_info .= '<p><strong><a href="'.get_post_permalink($sight->ID).'">Read the report</a></strong></p>'; 
             //       $sight_info .= '<p class="excerpt">'.(htmlentities($sight->post_excerpt != '' ? $manager::shorten($sight_meta['body'],100) : $manager::shorten($sight_meta['body'],100), ENT_QUOTES)).'</p>';
                    $sight_categories = wp_get_post_categories($sight->ID);
                    $sight_info .= '<p><strong>'.get_cat_name($sight_categories[0]).'</strong></p>'; // Will only fetch the first category
                    $sight_info .= '</div>';
                    ?>
                    var infoWindow = new google.maps.InfoWindow();
                    <?php
                    foreach($sight_meta['markers'] as $marker_latlng) {
                        ?>
						var days ='<?php echo $days ?>';
						var image = '';
					if	(days >= 31){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/grey-marker.png';			
			}
			else	if	(days >= 15){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/dgrey-marker.png';			
			}
						else	if	(days >= 8){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/orange-marker.png';			
			}
			
			
		else	if	(days >= 4){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/lgreen-marker.png';			
			}
			else	if	(days >= 1){
image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/green-marker.png';			
			}
			
			else {
						 image = '<?php echo twitcher_PLUGIN_DIR_URL ?>/images/red-marker.png';			
				}
				
                        var latlng = new google.maps.LatLng(<?php echo $marker_latlng[0] ?>,<?php echo $marker_latlng[1] ?>);
                        var marker = addMarker(map, latlng);

					    google.maps.event.addListener(marker, 'click', function(){
                            infoWindow.setContent('<?php echo $sight_info ?>');
                            infoWindow.open(map, this);
                        });
                        <?php
                    }
                }
            }
        }
 ?>
            
        });
    </script>

    <?php
    return ob_get_clean();
	
	};

add_shortcode('twitcher-map1', 'twitcher_map1_function');


?>