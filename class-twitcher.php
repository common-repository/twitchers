<?php

class twitcher_Manager {

    function __construct() {

    }

    /**
     * Returns all twitcher from the database
     * @return array
     */
    public function getAlltwitcher() {
	
				$options = get_option('twoptions');

        $posts_array = $posts_array = get_posts(array(
            'post_status'   =>  'publish',
            'post_type' =>  'post',
			'numberposts'     => $options['twitchers_markers'],
            'meta_key'  =>  twitcher_HANDLE
        ));

        return $posts_array ? $posts_array : array();
    }
	
	    public function gettwitcherByCategory($cat) {
        $category = is_numeric($cat) ? get_category($cat) : get_category_by_slug($cat);

        if($category == null)
            trigger_error('No such category! Please check your twitcher-map category parameters.', E_USER_ERROR);

        // Get all posts with this category
			$options = get_option('twoptions');
		
        $posts_array = get_posts(array(
            'category'  =>  $category->term_id,
            'post_status'   =>  'publish',
            'post_type' =>  'post',
			'numberposts'     => $options['twitchers_markers'],
            'meta_key'  =>  twitcher_HANDLE
        ));

        return $posts_array ? $posts_array : array();
    }
	

    public function createtwitcherPost ($twitcher_post_array) {
	


        $default_settings = self::gettwitcherSettings();
		



		$content = 
		'   [twitchermap]
		<div class="twitchersub"><p>Submitted by: '.$twitcher_post_array['name'].'</p></div><div class="twitcherreport"><p>Report: </p></div><div class="twitcherbody"><p>'.$twitcher_post_array['body'].'</p></div><div class="twitcherimage"><p>[gallery size="full" columns="1" type="slideshow"  orderby="rand"] </p></div> ';
			$options = get_option('twoptions');
        $wp_post_array = array(	
            'post_status'=>$options['twitchers_postset'], // Because we don't want contributor posts to be published without moderation
            'post_author'=>$options['twitchers_poster'],
            'post_category'=>array(isset($twitcher_post_array['category']) ? $twitcher_post_array['category'] : 1),
            'post_title'=>$twitcher_post_array['title'],
            'post_content'=>$content
        );
		

		
        $new_post_id = wp_insert_post($wp_post_array);

        if($new_post_id  != 0) {

            // Create image attachment for new Sighting
            if(isset($twitcher_post_array['image_file'])) {

                if($_FILES[$twitcher_post_array['image_file']]['error'] == 0) {

                    $file_id = $twitcher_post_array['image_file'];

                    if(!function_exists('media_handle_upload')){
                        include_once('wp-admin/includes/file.php');
                        include_once('wp-admin/includes/media.php');
                        include_once('wp-admin/includes/image.php');
                    }

                    $new_attachment_id = media_handle_upload($file_id, $new_post_id);

                    if(!$new_attachment_id) {
                        throw new Exception('Could not handle upload for Sighting id:'.$new_post_id);
                    }
                    else {
                        // Set new attachment as new post thumbnail
                        if(!set_post_thumbnail($new_post_id, $new_attachment_id))
                            throw new Exception ('Could not set the new attachment as post thumbnail for post '.$new_post_id);
                    }
                }
            }

            if(self::saveSightingPostMeta($new_post_id, $twitcher_post_array)) {
                if(isset($default_settings['notify_user'])) {
                    self::notifyAuthorAboutNewContribution($default_settings['author'], $twitcher_post_array, $new_post_id);
                }
            }
            else throw new Exception('Could not save twitcher post meta for post ID: '.$new_post_id);
        }
		

		
        else {
            throw new Exception('Could not create new Sighting');
        }

    }
	

	
	
    /**
     * Updates Sighting for a post
     * @param $post_id
     * @param $sighting
     * @return bool true|false was updated
     */
    public function saveSightingPostMeta($post_id, $sighting) {
        return update_post_meta($post_id,twitcher_HANDLE,$sighting);
    }

    /**
     * Deletes Sighting for a post
     * @param $post_id
     * @return bool
     */
    public function deleteSightingPostMeta($post_id) {
        return delete_post_meta($post_id,twitcher_HANDLE);
    }

    /**
     * Deletes all twitcher post-meta for all posts
     */
    public function deleteAlltwitcher() {
        if(!delete_post_meta_by_key(twitcher_HANDLE))
           trigger_error('Could not delete all '.twitcher_HANDLE.' post meta!');
    }

    /**
     * Updates twitcher settings
     * @param $settings array
     * @return bool true|false was updated
     */
    public function savetwitcherSettings($settings) {
        return update_option(twitcher_HANDLE, $settings);
    }

    /**
     * Returns the twitcher settings array from database
     * @return array
     */
    public function gettwitcherSettings() {
        return get_option(twitcher_HANDLE);
    }

    /**
     * Shorten a string if too long
     * @static
     * @param string $str
     * @param int $max_length
     * @param string $ending[optional='...']
     * @param string $append_to[optional=StringUtil::APPEND_TO_END]
     * @return string
     */
    public static function shorten($str, $max_length, $ending = '...', $append_to = 'end') {
        return self::shortener($str, $max_length, $ending, $append_to);
    }

    /**
     * @ignore
     * @return string
     */
    private static function shortener($str, $max_length, $ending, $append_to, $trim_to_last_word = false) {
        if (strlen($str) > $max_length) {
            $str = trim(mb_substr($str, 0, $max_length, 'UTF-8'));
            if ($trim_to_last_word) {
                $last_space_pos = strrpos($str, ' ');
                if ($last_space_pos !== false) {
                    $str = mb_substr($str, 0, $last_space_pos, 'UTF-8');
                }
            }
            if ($append_to == 'end') {
                return $str . $ending;
            }
            else {
                return $ending . $str;
            }
        }
        return strip_tags($str);
    }

    /**
     * Sends an e-mail to the current selected contributor author about new contribution
     * Requires PHP mail to be activated
     * @param $author_id
     * @param $twitcher_post_array
     * @param $new_post_id
     * @throws Exception
     */
    public function notifyAuthorAboutNewContribution($author_id, $twitcher_post_array, $new_post_id) {

        $user = get_userdata($author_id);

        if(!$user) {
            throw new Exception('Could not get userdata for user ID: '.$author_id);
        }

        $to  = $user->user_email;

        // subject
        $subject = __('New twitcher contribution from ',twitcher_HANDLE). (isset($twitcher_post_array['name']) ? $twitcher_post_array : __('Anonymous', twitcher_HANDLE));

        // message
        $message = '<html>
                    <head>
                      <title>'. $twitcher_post_array['title'] .'</title>
                    </head>
                    <body>
                      <p>'.$twitcher_post_array['body'].'</p>
                      <p><strong>lat:'.$twitcher_post_array['lat'].'</strong></p>
                      <p><strong>lng:'.$twitcher_post_array['lng'].'</strong></p>
                      <p><strong>zoom:'.$twitcher_post_array['zoom'].'</strong></p>
                      <br/>';
        if(isset($twitcher_post_array['email']))
            $message .=  '<p>'.__('Contributor e-mail: ',twitcher_HANDLE).'<a href="mailto:'.$twitcher_post_array['email'].'">'.$twitcher_post_array['email'].'</a></p>';
        $message .=  '<p>'.__('Click here to see the post:',twitcher_HANDLE).'<a href="'.get_post_permalink($new_post_id).'">'.get_post_permalink($new_post_id).'</a>
                    </body>
                    </html>
                    ';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        // Additional headers
        $headers .= 'To: '.$user->display_name.' <'.$user->user_email.'>' . "\r\n";
        $headers .= 'From: twitcher contributor <'.$twitcher_post_array['email'].'>' . "\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);
    }
}



 /**
     * Echoes the presentation of a map for a Sighting
     * @param $sighting array
     * @return void
     */

function echoTwitcherPostMap() {




 $custom_fields = get_post_custom_values('twitcher');
  $my_custom_field = $custom_fields;
  foreach ( $my_custom_field as $key =>$value){
    $serialized = maybe_unserialize( $value );
if(is_array($serialized) && isset($serialized['zoom'])) {

 
}
}

 foreach ( $my_custom_field as $key =>$value){
    $serialized = maybe_unserialize( $value );
if(is_array($serialized) && isset($serialized['markers'])) {

 $markers = $serialized['markers'];
foreach($markers as $marker_latlng){

                        $lat = $marker_latlng[0];
                        $lng = $marker_latlng[1];
                    }
  
    echo '<div class="twitcherloc"><p>Sighting location:  latitude: ' . $marker_latlng[0] . '  longitude: ' . $marker_latlng[1] . '</p></div>';

}
}


?>

    <div id="map_canvas" style="width:100%; height:300px; margin-bottom:20px;"></div>
       <script type="text/javascript">
       

        // Load the map
        jQuery(window).load(function(){
            var latlng;
            var lat = <?php echo $marker_latlng[0] ?>;
            var lng =<?php echo $marker_latlng[1] ?>;
        
           

            latlng = new google.maps.LatLng(lat, lng);

            var myOptions = {
                zoom: <?php echo isset($serialized['zoom']) ? $serialized['zoom'] : 6 ?>,
                center: latlng,
                draggable: true,
                zoomControl: true,
                scrollwheel: false,
                streetViewControl: true,
                panControl: false,
                disableDoubleClickZoom: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById('map_canvas'),
                    myOptions);

				        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
          
        });

            return marker;	
					
				
                 
	}	)
    </script>
    <?php
    }
add_shortcode('twitchermap', 'echoTwitcherPostMap');
?>