<?php
if (!class_exists('PostImage')):

class PostImage {
  
  static $instance;
  static function load() {
    if (!self::$instance) {
      self::$instance = new PostImage();
    }
    return self::$instance;
  }
  
  private function __construct() {
    if (is_admin()) {
      add_action('admin_init', array($this, 'admin_init'));
    }
  }
  
  function admin_init() {
    if (@$_REQUEST['fx'] == 'postimage' && ($store_as = @$_REQUEST['callback'])) {
      // the name of the javascript callback function
      $callback = "callback_{$store_as}";
      // the ref post, if applicable
      $ref_post = @$_REQUEST['post_id'];
      // size data
      $width = @$_REQUEST['width'];
      $height = @$_REQUEST['height'];
      
      if (!current_user_can('upload_files')) {
        ?>
          <script>
            window.parent.<?php echo $callback ?>(null, 'You are not allowed to change this picture.');
          </script>
        <?php
      } else {
        $result = wp_handle_upload($_FILES['file']);
        if (is_wp_error($result)) {
          ?>
            <script>
              window.parent.<?php echo $callback ?>(null, '<?php echo $result->get_error_message() ?>');
            </script>
          <?php
        } else {
          
          // resize the image?
          if ($width || $height) {
            // resize to the requested dimensions
            if ($resized = image_make_intermediate_size($result['file'], $width, $height, ($width || $height))) {
              // update result file and url spec with resized name
              $file = explode('/', $result['file']);
              array_pop($file);
              $file[] = $resized['file'];
              $result['file'] = implode('/', $file);
          
              $url = explode('/', $result['url']);
              array_pop($url);
              $url[] = $resized['file'];
              $result['url'] = implode('/', $url);
            }
          }
          
          // stash the result
          if ($ref_post) {
            update_post_meta($ref_post, $store_as, $result);
          } else {
            update_option($store_as, $result);
          }
          
          // callback the new URL
          ?>
            <script>
              window.parent.<?php echo $callback ?>('<?php echo $result['url'] ?>');
            </script>
          <?php
        }
      }
    
      exit;
    }
  }
  
  static function ui($page, $store_as, $ref_post = null, $max_width = null, $max_height = null, $default = null, $allow_none = true) {
    $current = $default;
    $key = $store_as;
    if ($ref_post) {
      if (!is_object($ref_post)) {
        $ref_post = get_post($ref_post);
        // TODO: make sure this isn't a revision post
      }
      
      if ($stored = get_post_meta($ref_post->ID, $key, true)) {
        $current = $stored['url'];
      }
    } else {
      if ($stored = get_option($key, null)) {
        $current = $stored['url'];
      }
    }
    
    ?>
      <?php if ($max_width || $max_height): ?>
        <?php if ($max_width && $max_height): ?>
          <div id="postimage_<?php echo $store_as ?>" style="background:url('<?php echo $current ?>') no-repeat; border: 1px solid #ddd; float:left; margin-right: 1em; width: <?php echo $max_width ?>px; height: <?php echo $max_height ?>px;"></div>
        <?php else: ?>
          
        <?php endif; ?>
      <?php endif; ?>
      <div style="position:relative; float: left; width:300px;">
        <div style="position:absolute; top:0; left:0;">
          &nbsp; <a class="button" id="postimage_change_<?php echo $store_as ?>" href="javascript:;">Change...</a>
          <?php if ($allow_none): ?>
            &nbsp; <a class="button" id="postimage_none_<?php echo $store_as ?>" href="javascript:;" style="display:none;">None</a>
          <?php endif; ?>
          <?php echo $max_width ? $max_width : 'whatever' ?>&nbsp;x&nbsp;<?php echo $max_height ? $max_height : 'whatever' ?>
          <div id="error_<?php echo $store_as ?>" style="padding:10px; color:red;"></div>
        </div>
        <div style="position:absolute; top:0; left:0;">
          <input type="hidden" name="width" value="<?php echo $max_width ?>" />
          <input type="hidden" name="height" value="<?php echo $max_height ?>" />
          <?php if ($ref_post) { ?>
            <input type="hidden" name="post_id" value="<?php echo $ref_post->ID ?>" />
          <?php } ?>
          <input id="file_<?php echo $store_as ?>" name="file" type="file" style="opacity: 0; filter: alpha(opacity=0);" />
        </div>
      </div>
      <script>
        (function($) {
          var instance = 0;
          var file = $('#file_<?php echo $store_as ?>');
          var btn_change = $('#postimage_change_<?php echo $store_as ?>');
          var btn_none = $('#postimage_none_<?php echo $store_as ?>');
          // monitor file element for value change
          var old_val = file.val();
          var form = file.closest('form');

          if (!form.find('input[name="action"]').size()) {
            form.append('<input type="hidden" name="action" value="" />');
          }

          var action = form.find('input[name="action"]');
          var interval = null;
          var monit = function() {
            var new_val = file.val();
            if (new_val != old_val) {
              clearInterval(interval);
              if (new_val) {
                // disable the button and implement uploading status
                btn_change.addClass('disabled').text('Uploading');
                
                // increment the iframe instance count
                instance++;
                
                // hide any error messages
                $('#error_<?php echo $store_as ?>').hide();
                
                // embed the fresh iframe
                $('body').append('<iframe width="1" height="1" style="position:absolute; top: 0; left: 0; visibility:hidden;" name="iframe_<?php echo $store_as ?>'+instance+'"></iframe>');
                
                // stash the current action and target
                form.data('action', form.attr('action'));
                form.data('target', form.attr('target'));
                form.data('method', form.attr('method'));
                form.data('encType', form.attr('encType'));
                form.data('encoding', form.attr('encoding'));
                action.data('value', action.val());
                
                // set the new action and target
                form.attr('action', 'admin.php?page=<?php echo $page ?>&fx=postimage&callback=<?php echo $store_as ?>');
                form.attr('target', 'iframe_<?php echo $store_as ?>'+instance);
                form.data('method', 'post');
                form.attr('encType', 'multipart/form-data');
                form.attr('encoding', 'multipart/form-data');
                action.val('wp_handle_upload');

                // submit the file input's form with target set to the iframe
                form.submit();
              }
              old_val = new_val;
            }
          };

          interval = setInterval(monit, 100);
          
          window['callback_<?php echo $store_as ?>'] = function(url, error) {
            // reset the form's attributes
            form.attr('action', form.data('action') == undefined ? '' : form.data('action'));
            form.attr('target', form.data('target') == undefined ? '' : form.data('target'));
            form.attr('encType', form.data('encType') == undefined ? '' : form.data('encType'));
            form.attr('encoding', form.data('encoding') == undefined ? '' : form.data('encoding'));
            form.attr('method', form.data('method') == undefined ? '' : form.data('method'));
            action.val(action.data('value'));
                    
            if (error) {
              $('#error_<?php echo $store_as ?>').text(error).show();
            } else {
              if ($('div#postimage_<?php echo $store_as ?>').size()) {
                $('div#postimage_<?php echo $store_as ?>').css('background-image', 'url('+url+')');
              } else {
                
              }
            }

            btn_change.removeClass('disabled').text('Change...');

            interval = setInterval(monit, 100);
          }
        })(jQuery);
      </script>
    <?php
  }
  
}

PostImage::load();

endif;