<?php 

/*
 * Setion to Multiple Images for Company
*/
add_action( 'add_meta_boxes', 'mip_add_custom_box' );
//add_action( 'save_post', 'mip_update_postdata' );
add_action( 'wp_insert_post', 'mip_insert_postdata' );

add_action( 'admin_init', 'register_mip_post_type' ); //call register settings function

function register_mip_post_type() 
{
	register_setting( 'mip-post-type', 'mip_post_type' );
}

/* Adds a box to the main column on the Post and Page edit screens */
function mip_add_custom_box() {
	
	$post_name = 'company';
    
	add_meta_box('mip_sidebar', 'Company Photos', 'mip_render_images', $post_name, 'normal', 'high');
}


if( !function_exists("update_mip_post_type") )
{
	function update_mip_post_type() {
	  register_setting( 'mip-post-type', 'mip_post_type' );
	}
}


/* Prints the box content */
function mip_render_images() {
global $wpdb;
global $post;
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'mip_noncename' );

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta($post->ID, '_multi_img_array', true);
  $temp = explode(",", $value);
 
  if ($temp) {
    foreach ( $temp as $t_val ) {
		
		$image_attributes = wp_get_attachment_image_src( $t_val , array(63,63) );
		echo '<img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'" data-id="'.$t_val.'">';
	}
}
	else
	{
			//echo wp_get_attachment_image_src( $value , array(63,63) );
		$image_attributes = wp_get_attachment_image_src( $value , array(63,63) );
		echo '<img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'" data-id="'.$value.'">';
	}
 // echo '<input type="text" id="mip_image_upload" name="mip_image_upload" value="'.esc_attr($value).'" size="25" />';
  echo "<style type='text/css'>
  	#mip_sidebar .inside img{margin:5px;}
  </style>";
  echo "<input type='hidden' name='image_upload_val' id='image_upload_val' value='".$value."' />";
  echo "<div class='upload_media button button-primary button-large' id='mip_image_upload'>Upload Images</div>";
  echo "<script type='text/javascript'>

jQuery(document).ready(function(jQuery){
  var _custom_media = true,
      _orig_send_attachment = wp.media.editor.send.attachment;

  jQuery('#mip_image_upload').click(function(e) {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = jQuery(this);
    var id = button.attr('id').replace('_button', '');
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
		  console.log(attachment);
		   
          //jQuery('#'+id).val(attachment.url);
		  
		  if(jQuery('#image_upload_val').val() == '')
			  jQuery('#image_upload_val').val(attachment.id);
		  else
		  {
			  oldVal = jQuery('#image_upload_val').val();
		  	  jQuery('#image_upload_val').val(oldVal+','+attachment.id);
		  }
		  
		  var src_str = attachment.url;
		  jQuery('#image_upload_val').before('<img width=63 height=63 src='+src_str+' data-id='+attachment.id+' class=attachment-63x63 />');
		  //jQuery('#post').submit();
      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      };
    }

    wp.media.editor.open(button);
    return false;
  });

  jQuery('.add_media').on('click', function(){
    _custom_media = false;
  });
  
  jQuery('#mip_sidebar img').live('click',function(){
	  valArr = jQuery('#image_upload_val').val().split(',');
	  console.log(valArr);

	  var index = valArr.indexOf(jQuery(this).attr('data-id'));
	  if (index > -1) 
	  {
          
                var res = confirm('Are you sure you want to remove image');
                if(res==true){
    		valArr.splice(index, 1);
                    jQuery(this).remove();
                }
	  }
	  console.log(valArr);
	  jQuery('#image_upload_val').val(valArr.toString());
  });
});
</script>";  
}

/* When the post is saved, saves our custom data */
function mip_insert_postdata( $post_id ) {
global $wpdb;

$mydata = '';

  // First we need to check if the current user is authorised to do this action. 
  
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
 
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['mip_noncename'] ) || ! wp_verify_nonce( $_POST['mip_noncename'], plugin_basename( __FILE__ ) ) )
      return;

    $post_ID = $_POST['post_ID'];

	$mydata = $_POST['image_upload_val'];

  
    if($mydata)
    {
        $cur_data = get_post_meta($post_ID, '_multi_img_array', true);
        if(!(empty($cur_data)))
        {
            update_post_meta($post_ID, '_multi_img_array', $mydata);
        }
        else
        {
            add_post_meta($post_id, '_multi_img_array', $mydata, true);
        }
    }
   
}
