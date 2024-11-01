<?php

$manager = new twitcher_Manager();

if(count($_POST) > 0)
{
    if(isset($_POST['delete_all_twitcher'])) {
        $manager->deleteAlltwitcher();
        echo '<div id="status_success">'. __('All twitcher removed!', twitcher_HANDLE) .'</div>';
    }
    $settings['lat'] = $_POST['map_lat'];
    $settings['lng'] = $_POST['map_lng'];
    $settings['zoom'] = $_POST['map_zoom'];
    $settings['display'] = isset($_POST['display']) ? 1 : 0;
    if(isset($_POST['contributor_categories']))
        $settings['contributor_categories'] = $_POST['contributor_categories'];
    if(isset($_POST['author']))
        $settings['author'] = $_POST['author'];
    if(isset($_POST['notify_user']))
        $settings['notify_user'] = $_POST['notify_user'];
		  
    if(count($settings) > 0) {
        $manager->savetwitcherSettings($settings);
    }
	

    ?>
<div id="status_success"><?php _e('Settings saved successfully', twitcher_HANDLE) ?></div>
<?php
}
else {
    $settings = $manager->gettwitcherSettings();
}
?>

<div id="status_fail" style="display:none;"><?php _e('Settings are invalid! Only numeric values and dots are allowed. (no commas)', twitcher_HANDLE) ?></div>

<div id="twitcher_map_settings wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
	    <div id="donate_container" style="display:none">
        <span class="howto">Help keep this plugin in development and improved by using our Amazon links to make your purchases. Your commission can help support all my free Wordpress plugins. <a href="http://ypraise.com/2012/08/plugin-donate-page/">My Amazon page</a></span>
    </div>

    <h2 style="padding-top: 15px"><?php _e('twitcher settings',twitcher_HANDLE) ?></h2>
    <br />
    <hr />
    <form name="twitcher_settings" action="" onsubmit="return validateSettings();" method="post">
        <h3><?php _e('Map',twitcher_HANDLE) ?></h3>
        <h4><?php _e('Default map settings',twitcher_HANDLE) ?>:</h4>
        <span class="howto"><?php _e('The default position and zoom level for the maps on new posts.',twitcher_HANDLE) ?></span>
        <table id="settings_table" style="margin-bottom: 20px">
            <tr><td><label for="map_lat"><?php _e('Latitude:',twitcher_HANDLE) ?></label></td>
                <td><input name="map_lat" id="map_lat" type="text" size="10" value="<?php echo $settings ? $settings['lat'] : '' ?>"/></td></tr>
            <tr><td><label for="map_lng"><?php _e('Longitude:',twitcher_HANDLE) ?></label></td>
                <td><input name="map_lng" id="map_lng" type="text" size="10" value="<?php echo $settings ? $settings['lng'] : '' ?>"/></td></tr>
            <tr><td><label for="map_zoom"><?php _e('Zoom level:',twitcher_HANDLE) ?></label></td>
                <td><input name="map_zoom" id="map_zoom" type="text" size="2" value="<?php echo $settings ? $settings['zoom'] : '' ?>"/></td></tr>
				            <tr><td><label for="markernumber"><?php _e('Number of markers on map:',twitcher_HANDLE) ?></label></td>
                <td><input name="markernumber" id="markernumber" type="text" size="2" value="<?php echo $settings ? $settings['twitchernumber'] : '' ?>"/></td></tr>
        </table>

      

        <hr/>
        <h3><?php _e('Contributors',twitcher_HANDLE) ?></h3>
        <h4><?php _e('Author',twitcher_HANDLE); ?>:</h4>
        <span class="howto"><?php _e('This user that will be set as author on contributor posts.',twitcher_HANDLE) ?></span>
        <table id="contributions_table" style="margin-bottom: 20px">
            <tr>
                <td>
                    <label for="users"><?php _e('Post as user:',twitcher_HANDLE) ?></label>
                </td>
                <td>
                    <select id="users" name="author">
<?php
                        global $wpdb;
    $query = "SELECT ID, user_nicename from $wpdb->users ORDER BY user_nicename";
    $authors = $wpdb->get_results($query);
    foreach($authors as $author) {
        echo '<option value="'.$author->ID.'" '. ($settings['author'] == $author->ID ? ' selected' : '') .'>'.$author->user_nicename.'</option>';
    }
    ?>
                    </select>
                </td>
                <td style="padding-left:20px;"><input id="notify_user" name="notify_user" type="checkbox" <?php echo isset($settings['notify_user']) ? 'checked="checked"' : '' ?>/>&nbsp;<label for="notify_user"><?php _e('Send notification e-mail to user when someone submits a new contribution', twitcher_HANDLE) ?></label></td>
            </tr>
        </table>
        <h4><?php _e('Contributor categories',twitcher_HANDLE); ?>:</h4>
        <span class="howto"><?php _e('Select the categories you want to be available for contributors.',twitcher_HANDLE) ?></span>
        <div class="tables_container">
            <table id="available_categories">
                <tr>
                    <th class="cats_header">
                        <?php _e('Available categories',twitcher_HANDLE) ?>
                    </th>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <select id="twitcher_categories">
                        <?php
                            global $wpdb;
                            // Get all categories
                            $categories = get_categories();
                            foreach($categories as $category) {
                                echo '<option value="'.$category->term_id.'">'.$category->name.'</option>';
                            }
                        ?>
                        </select>
                    </td>
                    <td style="padding: 10px 20px;">
                        <input type="button" onclick="addCategory(jQuery('#twitcher_categories option:selected'))" value="<?php _e('Add',twitcher_HANDLE) ?> &raquo;"/>
                    </td>

                </tr>
            </table>
            <table id="contributor_categories">
                <tr>
                    <th class="cats_header">
                        <?php _e('Contributor categories',twitcher_HANDLE) ?>
                    </th>
                </tr>
                <?php
                if(isset($settings['contributor_categories'])) {
                    foreach($settings['contributor_categories'] as $cat) {
                        echo '<tr><td>'.get_cat_name($cat).'</td><td><a href="#" onclick="removeCategory(jQuery(this)); return false;"> [- '. __('Remove', twitcher_HANDLE) .']</a><input type="hidden" name="contributor_categories[]" value="'.$cat.'" /></td></tr>';
                    }
                }
                ?>
            </table>
        </div>

        <hr/>

        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>">
        <input type="button" onclick="deleteAlltwitcher();" style="margin-left:20px; background: #fbb;" class="button-secondary" value="<?php _e('Delete all twitcher') ?>">

    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#status_success').delay(3000).slideUp();
        jQuery('#donate_container').delay(5000).slideDown();
    });

    function validateSettings() {
        if(/^[-+]?[0-9]*\.?[0-9]+$/.test(jQuery('#map_lat').val())
                &&
                /^[-+]?[0-9]*\.?[0-9]+$/.test(jQuery('#map_lng').val())
                &&
                /^[-+]?[0-9]*\.?[0-9]+$/.test(jQuery('#map_zoom').val())) {
            return true;
        }
        else
        {
            jQuery('#status_fail').slideDown().delay(6000).slideUp();
            jQuery('#settings_table').css('border','1px solid #f00');
            return false;
        }
    }

    function removeCategory(node) {
        node.parent().parent().fadeOut(function() {
            jQuery(this).remove();
        });
    }

    function addCategory(jQObj) {

        var cat_list = jQuery('#contributor_categories input');
        var is_duplicate = false;
        jQuery.each(cat_list, function() {
            if(jQuery(this).val() == jQObj.val()) {
                is_duplicate = true;
                jQuery(this).parent('td').parent('tr').animate({backgroundColor:'red'}, 'fast', 'linear', function() {
                jQuery(this).animate({
                    backgroundColor: 'white'
                }, 'normal', 'linear', function() {
                    jQuery(this).css({'background':'none', backgroundColor : ''});
                });
                });
            }
        });
        if(! is_duplicate) {
            jQuery('#contributor_categories').append('<tr><td>'+jQObj.text()+'</td><td><a href="#" onclick="removeCategory(jQuery(this)); return false;"> [- <?php _e('Remove', twitcher_HANDLE) ?>]</a><input type="hidden" name="contributor_categories[]" value="'+jQObj.val()+'" /></td></tr>')
        }
    }

    function deleteAlltwitcher() {
        var confirm_delete =  confirm('<?php _e('Are you sure? This will delete all recorded twitcher!') ?>');
        if(confirm_delete) {
            var $hidden_input = jQuery('<input type="hidden" name="delete_all_twitcher" value="1" />');
            console.log(jQuery('form[name="twitcher_settings"]'));
            jQuery('form[name="twitcher_settings"]').append($hidden_input).submit();
        }
    }

</script>

