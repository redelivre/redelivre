<?php
/**
* version 3.0
* work with bootstrap
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(!class_exists('pisol_class_form_ewcl')):
class pisol_class_form_ewcl{

    private $setting;
    private $saved_value; 
    private $pro;
    function __construct($setting){

        $this->setting = $setting;

        if(isset( $this->setting['default'] )){
            $this->saved_value = get_option($this->setting['field'], $this->setting['default']);
        }else{
            $this->saved_value = get_option($this->setting['field']);
        }

        if(isset( $this->setting['pro'] )){
            if($this->setting['pro']){
                $this->pro = ' free-version ';
                //$this->setting['desc'] = '<span style="color:#f00; font-weight:bold;">Workes in Pro version only / Without PRO version this setting will have no effect</span>';
            }else{
                $this->pro = ' paid-version ';
            }
        }else{
            $this->pro = "";
        }
        
        
        $this->check_field_type();
    }

    

    
    function check_field_type(){
        if(isset($this->setting['type'])):
            switch ($this->setting['type']){
                case 'select':
                    $this->select_box();
                break;

                case 'number':
                    $this->number_box();
                break;

                case 'text':
                    $this->text_box();
                break;
                    
                case 'textarea':
                    $this->textarea_box();
                break;

                case 'multiselect':
                    $this->multiselect_box();
                break;

                case 'color':
                    $this->color_box();
                break;

                case 'hidden':
                    $this->hidden_box();
                break;

                case 'switch':
                    $this->switch_display();
                break;

                case 'setting_category':
                    $this->setting_category();
                break;

                case 'image':
                    $this->image();
                break;
            }
        endif;
    }

    function bootstrap($label, $field, $desc = ""){
        if($this->setting['type'] != 'hidden'){
        ?>
        <div id="row_<?php echo $this->setting['field']; ?>"  class="row py-4 border-bottom align-items-center <?php echo $this->pro; ?>">
            <div class="col-12 col-md-5">
            <?php echo $label; ?>
            <?php echo $desc != "" ? $desc: ""; ?>
            </div>
            <div class="col-12 col-md-7">
            <?php echo $field; ?>
            </div>
        </div>
        <?php
        }else{
            ?>
            <div id="row_<?php echo $this->setting['field']; ?>" class="row align-items-center <?php echo $this->pro; ?>">
            <div class="col-12 col-md-12">
            <?php echo $field; ?>
            </div>
            </div>
            <?php
        }
    }

    /*
        Field type: select box
    */
    function select_box(){

        $label = '<label class="h6 mb-0" class="mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc = (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        
        $field = '<select class="form-control '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'"'
         .(isset($this->setting['multiple']) ? ' multiple="'.$this->setting['multiple'].'"': '')
        .'>';
            foreach($this->setting['value'] as $key => $val){
               $field .= '<option value="'.$key.'" '.( ( $this->saved_value == $key) ? " selected=\"selected\" " : "" ).'>'.$val.'</option>';
            }
        $field .= '</select>';

        $this->bootstrap($label, $field, $desc);

    }

    /*
        Field type: select box
    */
    function multiselect_box(){
        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc = ((isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "");
        $field = '<select style="min-height:100px;" class="form-control multiselect '.$this->pro.'" name="'.$this->setting['field'].'[]" id="'.$this->setting['field'].'" multiple'. '>';
            foreach($this->setting['value'] as $key => $val){
                if(isset($this->saved_value) && $this->saved_value != false){
                    $field .='<option value="'.$key.'" '.( ( in_array($key, $this->saved_value) ) ? " selected=\"selected\" " : "" ).'>'.$val.'</option>';
                }else{
                    $field .= '<option value="'.$key.'">'.$val.'</option>';
                }
            }
            $field .= '</select>';

            $this->bootstrap($label, $field, $desc);

    }

    /*
        Field type: Number box
    */
    function number_box(){

        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc =  (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field = '<input type="number" class="form-control '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'" value="'.$this->saved_value.'"'
        .(isset($this->setting['min']) ? ' min="'.$this->setting['min'].'"': '')
        .(isset($this->setting['max']) ? ' max="'.$this->setting['max'].'"': '')
        .(isset($this->setting['step']) ? ' step="'.$this->setting['step'].'"': '')
        .(isset($this->setting['required']) ? ' required="'.$this->setting['required'].'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.$this->setting['readonly'].'"': '')
        .'>';
        $this->bootstrap($label, $field, $desc);
    }

    /*
        Field type: Number box
    */
    function text_box(){

        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc =  (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field = '<input type="text" class="form-control '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'" value="'.$this->saved_value.'"'
        .(isset($this->setting['required']) ? ' required="'.$this->setting['required'].'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.$this->setting['readonly'].'"': '')
        .'>';
        $this->bootstrap($label, $field, $desc);
    }
    
    /*
    Textarea field
    */
    function textarea_box(){
        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc =  (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field = '<textarea style="height:auto !important; min-height:200px;" type="text" class="form-control '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'"'
        .(isset($this->setting['required']) ? ' required="'.$this->setting['required'].'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.$this->setting['readonly'].'"': '')
        .'>';
        $field .= $this->saved_value; 
        $field .= '</textarea>';
        $this->bootstrap($label, $field, $desc);
    }

     /*
        Field type: color
    */
    function color_box(){
        wp_enqueue_style( 'wp-color-picker');
        wp_enqueue_script( 'wp-color-picker');
        wp_add_inline_script('wp-color-picker','
        jQuery(document).ready(function($) {
            $(".color-picker").wpColorPicker();
          });
        ');
        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc =  (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field = '<input type="text" class="color-picker pisol_select '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'" value="'.$this->saved_value.'"'
        .(isset($this->setting['required']) ? ' required="'.$this->setting['required'].'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.$this->setting['readonly'].'"': '')
        .'>';
        $this->bootstrap($label, $field, $desc);
    }

    function hidden_box(){
        $label =  '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc =   (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field ='<input type="hidden" class="pisol_select '.$this->pro.'" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'" value="'.$this->saved_value.'"'
        .(isset($this->setting['required']) ? ' required="'.$this->setting['required'].'"': '')
        .(isset($this->setting['readonly']) ? ' readonly="'.$this->setting['readonly'].'"': '')
        .'>';
        $this->bootstrap($label, $field, $desc);
    }

    /*
        Field type: switch
    */
    function switch_display(){

        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc = (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        
        $field = '<div class="custom-control custom-switch">
        <input type="checkbox" value="1" class="custom-control-input" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'"'.(($this->saved_value == true) ? "checked='checked'": "").' >
        <label class="custom-control-label" for="'.$this->setting['field'].'"></label>
        </div>';

        $this->bootstrap($label, $field, $desc);
    }

    /**
     * Category: is to devide setting in different part 
     */
    function setting_category(){
        if(isset($this->setting['label']) && $this->setting['label'] != ""):
        ?>
        <div id="row_<?php echo $this->setting['field']; ?>" class="row py-4 border-bottom align-items-center <?php echo ( isset($this->setting['class']) ? $this->setting['class'] : "" ); ?>">
            <div class="col-12">
            <h2 class="mt-0 mb-0 <?php echo ( isset($this->setting['class_title']) ? $this->setting['class_title'] : "" ); ?>"><?php echo $this->setting['label']; ?></h2>
            </div>
        </div>
        <?php
        endif;
    }

    function image(){
        wp_enqueue_media();
        add_action( 'admin_footer', array($this,'media_selector_scripts') );
        $label = '<label class="h6 mb-0" for="'.$this->setting['field'].'">'.$this->setting['label'].'</label>';
        $desc = (isset($this->setting['desc'])) ? '<br><small>'.$this->setting['desc'].'</small>' : "";
        $field = '
        <div class="row align-items-center">
        <div class="col-6">
        <input id="'.$this->setting['field'].'_button" type="button" class="button" value="'.__('Upload image').'" />
        <input type="hidden" name="'.$this->setting['field'].'" id="'.$this->setting['field'].'" value="'.$this->saved_value.'">
        </div>
        <div class="col-6">
        <div class="image-preview-wrapper">
		<img id="'.$this->setting['field'].'_preview"'.($this->saved_value > 0 ? 'src="'.wp_get_attachment_url( get_option( $this->setting['field'] ) ).'"': '').' width="100" height="100" style="max-height: 100px; width: 100px;">
        </div>
        </div>
        </div>
        ';
        $this->bootstrap($label, $field, $desc);
    }

    function media_selector_scripts(){
        $my_saved_attachment_post_id = get_option($this->setting['field'], 0 );
	    ?><script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
			jQuery('#<?php echo $this->setting['field']; ?>_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#<?php echo $this->setting['field']; ?>_preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#<?php echo $this->setting['field']; ?>' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		});
	</script>
    <?php
    }
}
endif;
