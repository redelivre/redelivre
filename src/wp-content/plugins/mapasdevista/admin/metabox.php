<?php


add_action( 'add_meta_boxes', 'mapasdevista_add_custom_box' );


add_action( 'save_post', 'mapasdevista_save_postdata' );


function mapasdevista_add_custom_box() {

		$post_types = get_option('mapasdevista');
		$post_types = $post_types['post_types'];
		foreach ($post_types as $post_type )
		{
        	add_meta_box( 'mapasdevista_metabox', __( 'Place it on the map', 'mapasdevista' ), 'mapasdevista_metabox_map', $post_type );
		}

}

/**
 * Renderiza o Google Maps na pagina de posts
 */
function mapasdevista_metabox_map() {
    global $post, $post_type;
    if( !$location=get_post_meta($post->ID, '_mpv_location', true) ) {
        $location = array('lat'=>'', 'lon'=>'');
    }
    $post_pin = get_post_meta($post->ID, '_mpv_pin', true);

    $args = array(
        'post_type' => 'attachment',
        'meta_key' => '_pin_anchor',
        'posts_per_page' => '-1'
    );
    $pins = get_posts($args);

    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'mapasdevista_noncename' );
    ?>
    <fieldset>
        <label for="mpv_lat"><?php _e('Latitude', 'mpv');?>:</label>
        <input type="text" class="medium-field" name="mpv_lat" id="mpv_lat" value="<?php echo $location['lat'];?>"/>

        <label for="mpv_lon"><?php _e('Longitude', 'mpv');?>:</label>
        <input type="text" class="medium-field" name="mpv_lon" id="mpv_lon" value="<?php echo $location['lon'];?>"/>

        <input type="button" id="mpv_load_coords" value="Exibir"/>
    </fieldset>
    <div id="mpv_canvas" class="mpv_canvas"></div>
    <fieldset>
        <label for="mpv_search_address"><?php _e('Search address', 'mpv');?>:</label>
        <input type="text" id="mpv_search_address" class="large-field"/>
    </fieldset>


    <h4><?php _e("Available pins", "mapasdevista");?> (<?php echo $post_pin; ?>)</h4>
    <p>Se preferir, você pode <a href="<?php echo add_query_arg( array('post' => null, 'page' => 'mapasdevista_pins_page', 'post_type' => 'mapa'), admin_url('edit.php') ); ?>">adicionar seu próprio marcador</a></p>
    <div class="iconlist">
        <script type="text/javascript">var pinsanchor = { };</script>
        <?php foreach($pins as $pin): $pinanchor = json_encode(get_post_meta($pin->ID, '_pin_anchor', true)); ?>
            <div class="icon">
                <script type="text/javascript">pinsanchor.pin_<?php echo $pin->ID;?>=<?php echo $pinanchor;?>;</script>
                <div class="icon-image"><label for="pin_<?php echo $pin->ID;?>">
                    <?php echo wp_get_attachment_image($pin->ID, 'full', false, array('style'=>'max-width:64px;max-height:64px;'));?>
                </label></div>
                <div class="icon-info">
                <input type="radio" name="mpv_pin" id="pin_<?php echo $pin->ID;?>" value="<?php echo $pin->ID;?>"<?php if($post_pin==$pin->ID) echo ' checked';?>/>
                    <!-- <span class="icon-name"><?php echo $pin->post_name;?></span> -->
                </div>
            </div>
        <?php endforeach;?>
    </div>
    
    <input type="hidden" name="mpv_inmap[]" value="1" />
    
    
    <div class="clear"></div>

    <?php
}

/**
 * Save from metabox
 */
function mapasdevista_save_postdata($post_id) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

    if ( !isset($_POST['mapasdevista_noncename']) || !wp_verify_nonce( $_POST['mapasdevista_noncename'], plugin_basename( __FILE__ ) ) )
            return;

    global $wp_post_types;
    $cap = $wp_post_types[$_POST['post_type']]->cap->edit_post;

    if ( !current_user_can( $cap, $post_id ) )
        return;

    // save
    
    delete_post_meta($post_id, '_mpv_inmap');
    delete_post_meta($post_id, '_mpv_in_img_map');
    if(isset($_POST['mpv_inmap']) && is_array($_POST['mpv_inmap'])) {
        foreach($_POST['mpv_inmap'] as $page_id ) {
            if(is_numeric($page_id)) {
                $page_id = intval($page_id);
                add_post_meta($post_id, "_mpv_inmap", $page_id); 
            }
        }
    }
    
    if(isset($_POST['mpv_lat']) && isset($_POST['mpv_lon'])) {
        $location = array();
        $location['lat'] = floatval(sprintf("%f", $_POST['mpv_lat']));
        $location['lon'] = floatval(sprintf("%f", $_POST['mpv_lon']));

        if($location['lat'] !== floatval(0) && $location['lon'] !== floatval(0)) {
            update_post_meta($post_id, '_mpv_location', $location);
        } else {
            delete_post_meta($post_id, '_mpv_location');
        }
    }

    if(isset($_POST['mpv_pin']) && is_numeric($_POST['mpv_pin'])) {
        $pin_id = intval(sprintf("%d", $_POST['mpv_pin']));
        if($pin_id > 0) {
            update_post_meta($post_id, '_mpv_pin', $pin_id);
        }
    }

    if(isset($_POST['mpv_img_pin']) && is_array($_POST['mpv_img_pin'])) {
        foreach($_POST['mpv_img_pin'] as $page_id => $pin_id) {
            if(is_numeric($page_id) && is_numeric($pin_id)) {
                $page_id = intval($page_id);
                $pin_id = intval($pin_id);
                update_post_meta($post_id, "_mpv_img_pin_{$page_id}", $pin_id);
            }
        }
    }

    if(isset($_POST['mpv_img_coord']) && is_array($_POST['mpv_img_coord'])) {
        foreach($_POST['mpv_img_coord'] as $page_id => $coord) {
            if(is_numeric($page_id) && preg_match('/^(-?[0-9]+),(-?[0-9]+)$/', $coord, $coord)) {
                $page_id = intval($page_id);
                $coord = "{$coord[1]},{$coord[2]}";
                update_post_meta($post_id, "_mpv_img_coord_{$page_id}", $coord);
                add_post_meta($post_id, "_mpv_in_img_map", $page_id);
            }
        }
    } 
}

