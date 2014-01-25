<?php if (!defined('ABSPATH')) exit; /* silence is golden... */ ?>
<style>
  .timestamp-wrap {
    line-height: 23px;
  }
  .timestamp-wrap select {
    height: 21px;
    line-height: 14px;
    padding: 0;
    vertical-align: top;
    font-size: 12px;
  }
  #sp_jj, #sp_hh, #sp_mn {
    width: 2em;
  }
  #sp_aa {
    width: 3.4em;
  }
  .timestamp-wrap input {
    border-width: 1px;
    border-style: solid;
    padding: 1px;
    font-size: 12px;
  }
</style>
<div id="sharepress" <?php if (($posted || $scheduled || $last_posted) && @$_GET['sharepress'] != 'schedule') echo 'style="display:none;"' ?>>
  
  <br />
  <fieldset>
    <legend>
      <label for="sharepress_meta_message">
        <b>Facebook Message</b> &nbsp;&nbsp; 
        <label style="display:inline-block;">
          <input type="checkbox" id="sharepress_meta_title_is_message" name="sharepress_meta[title_is_message]" value="1" onclick="click_use_post_title(this);" <?php if (@$meta['title_is_message']) echo 'checked="checked"' ?> /> 
          same as Title
        </label>
      </label>
    </legend>
    <textarea style="width:100%; height:75px;" name="sharepress_meta[message]" id="sharepress_meta_message"><?php echo @$meta['message'] ?></textarea>
    <label for="sharepress_meta_append_link" style="padding:4px;">
      <input type="checkbox" id="sharepress_meta_append_link" name="sharepress_meta[append_link]" value="1" <?php if (!empty($meta['append_link'])) echo 'checked="checked"' ?> />
      Append post link to this message
    </label>
  </fieldset>

  <?php if ( ($posted || $scheduled) || (!$posted && !$scheduled && $post->post_status == 'publish') ) { ?>
    <br />
    <fieldset>
      <legend>
        <label for="sharepress_meta_schedule">
          <b>Schedule</b>
        </label>
      </legend>
      <?php Sharepress::$pro->touch_time($scheduled); ?>
      <div style="padding:10px 0 2px 0;">
        <input type="submit" class="button-primary" value="<?php echo ($scheduled) ? 'Update' : 'Schedule' ?>" style="margin-right:4px;" />
        <input type="submit" class="button" onclick="if(confirm('Are you sure you want to cancel posting to Facebook?')) { sharepress_cancel_publish_again(); } else { return false; }" value="Cancel" />
      </div>
    </fieldset>
  <?php } ?>

  <p class="sharepress_show_advanced" style="margin:5px; text-align:center;">
    <a href="javascript:;" onclick="jQuery(this).parent().hide(); jQuery('.sharepress_advanced').slideDown();">Show Advanced Options</a>
  </p>

  <div class="sharepress_advanced">
  
    <br />
    <fieldset>
      <legend>
        <label for="sharepress_meta_picture">
          <b>Picture to Feature</b>
        </label>
      </legend>

      <p style="color:red; padding-top: 0; margin-top: 0; margin-bottom: 10px; display:none;" id="picture_error">
        Can't use Featured Image if it isn't set. Please set the Featured Image, or select a different option.
      </p>
  
      <label style="display:block; margin-bottom: 8px;">
        <input type="radio" name="sharepress_meta[let_facebook_pick_pic]" value="0" <?php if (!$meta['let_facebook_pick_pic']) echo 'checked="checked"' ?> /> 
        This post's <a href="javascript:;" onclick="jQuery('#set-post-thumbnail').click();">Featured Image</a>
      </label>

      <label style="display:block; margin-bottom: 8px;">
        <input type="radio" name="sharepress_meta[let_facebook_pick_pic]" value="1" <?php if ($meta['let_facebook_pick_pic'] == 1) echo 'checked="checked"' ?> /> 
        The first image in the content
      </label>

      <label style="display:block; margin-bottom: 8px;">
        <input type="radio" name="sharepress_meta[let_facebook_pick_pic]" value="4" <?php if ($meta['let_facebook_pick_pic'] == 4) echo 'checked="checked"' ?> /> 
        The first image in the gallery
      </label>

      <label style="display:block; margin-bottom: 8px;">
        <input type="radio" name="sharepress_meta[let_facebook_pick_pic]" value="2" <?php if ($meta['let_facebook_pick_pic'] == 2) echo 'checked="checked"' ?> /> 
        The <a href="<?php echo admin_url('options-general.php?page=sharepress') ?>#picture" target="_blank">global default</a>
      </label>
    </fieldset>
  
    <?php if (!SharePress::is_business()) { ?>
      <br />
      <fieldset>
        <legend>
          <label for="sharepress_meta_targets">
            <b>Publishing Targets</b> &nbsp;&nbsp; 
          </label>
        </legend>
      
        <div style="max-height:150px; overflow:auto;">
          <p style="color:red; display:none; padding-top: 0; margin-top: 0;" id="publish_target_error">
            Choose at least one.
          </p>
          <?php if (!self::is_excluded_page('wall')) { ?>
            <p>
              <?php $wall_name = ((preg_match('/s$/i', trim($name = Sharepress::me('name')))) ? $name.'&apos;' : $name.'&apos;s') . ' Wall'; ?>
              <label for="sharepress_target_wall" title="<?php echo $wall_name ?>"> 
                <input type="checkbox" class="sharepress_target" id="sharepress_target_wall" name="sharepress_meta[targets][]" value="wall" <?php if (@in_array('wall', $meta['targets'])) echo 'checked="checked"' ?> />
                <?php echo $wall_name ?>
              </label>
            </p>
          <?php } ?>
          <?php 
            $pages = self::pages(); 
            usort($pages, array('Sharepress', 'sort_by_selected')); 
            
            foreach($pages as $page) { 
              if (self::is_excluded_page($page)) {
                continue; 
              }
              ?>
                 <p>
                  <label for="sharepress_target_<?php echo $page['id'] ?>" title="<?php echo $page['name'] ?>">
                    <input class="sharepress_target" type="checkbox" id="sharepress_target_<?php echo $page['id'] ?>" name="sharepress_meta[targets][]" value="<?php echo $page['id'] ?>" <?php if (@in_array($page['id'], $meta['targets'])) echo 'checked="checked"' ?> />
                    <span <?php if ($page['category'] == 'Application') echo 'style="color:#bbbbbb;" title="This is an Application page"' ?>><?php $name = trim(substr($page['name'], 0, 30)); $name .= ($name != $page['name']) ? '...' : ''; echo $name ?></span>
                  </label>
                </p>
              <?php
            }
          ?>
        </div>
      </fieldset>
    <?php } ?>

    <br />
    <fieldset>
      <legend><b>Share Delay</b></legend>
      Delay sharing for:
      <p>
        <input type="number" class="regular-text" style="width:40px;" 
          name="sharepress_meta[delay_length]" value="<?php echo esc_attr($meta['delay_length'] ? $meta['delay_length'] : 0) ?>" />
        <select name="sharepress_meta[delay_unit]">
          <?php foreach(array('minutes', 'hours', 'days') as $unit) { ?>
            <option<?php if ($meta['delay_unit'] == $unit) echo ' selected="selected"' ?>><?php echo $unit ?></option>
          <?php } ?>
        </select>
      </p>
    </fieldset>

    <p style="margin:5px; text-align:center;">
      <a class="sharepress_hide_advanced" href="javascript:;" onclick="jQuery('.sharepress_advanced').slideUp(function() { jQuery('.sharepress_show_advanced').fadeIn(); });">Hide Advanced Options</a>
    </p>
  </div>
  <script>
    jQuery('.sharepress_advanced').hide();
  </script>

  <?php if (SharePress::twitter_ready()) { ?>

    <input type="hidden" name="<?php echo Sharepress::META_TWITTER ?>[__PLACEHOLDER__]" value="" />
    <fieldset id="<?php echo Sharepress::META_TWITTER ?>" style="margin-top:15px;">
      <legend><b>Also Share With...</b></legend>
      
      <div class="tools">
        <p>
          <label>
            <input type="checkbox" value="on" id="<?php echo SharePress::META_TWITTER ?>_enabled" name="<?php echo Sharepress::META_TWITTER ?>[enabled]" <?php if ($twitter_enabled) echo 'checked="checked"' ?> />
            &nbsp; Twitter Followers
          </label>
        </p>
        <p style="margin-left:23px;">
          <label for="<?php echo Sharepress::META_TWITTER ?>_hash_tag" style="display:block; margin-bottom:4px;">Hash Tag &nbsp;<span class="description">(optional)</span></label>
          <input style="width:150px;" type="text" name="<?php echo Sharepress::META_TWITTER ?>[hash_tag]" id="<?php echo Sharepress::META_TWITTER ?>_hash_tag" value="<?php echo trim(esc_attr($twitter_meta['hash_tag'])) ?>" />
        </p>
        <script>
          (function($) {
            var enabled = $('#<?php echo SharePress::META_TWITTER ?>_enabled');
            var hash_tag = $('#<?php echo Sharepress::META_TWITTER ?>_hash_tag');
            if (!enabled[0].checked) {
              hash_tag.parent().hide();
            }
            enabled.change(function() {
              this.checked ? hash_tag.parent().slideDown() : hash_tag.parent().hide();
            });
          })(jQuery);
        </script>
      </div>
    </fieldset>

  <?php } ?>
  
</div><!-- /#sharepress -->

<script>
(function($) {
  if (!$.fn.prop) {
    $.fn.prop = $.fn.attr;
  }
  
  $(function() {
    var editor = null;
    var description_timeout = null;
    var message_timeout = null;
    var excerpt_was = null;
    var content_was = null;

    var msg = $('#sharepress_meta_message');
    var title = $('#title');

    var excerpt = $('#excerpt');
    var description = $('#sharepress_meta_description');
    var description_was = description.val();
    var suspend = false;
    
    msg.keypress(function() {
      $('#sharepress_meta_title_is_message').prop('checked', false);
    })
    
    window.sharepress_publish_again = function() {
      $('#sharepress').show();
      $('#btn_publish_again').hide();
      $('#sharepress_meta_cancelled').val(0);
      $('#sharepress_meta_publish_again').val(1);
    };
    
    window.sharepress_cancel_publish_again = function() {
      $('#sharepress_meta_cancelled').val(1);
      $('#sharepress_meta_publish_again').val(0);      
      $('#sharepress_meta_enabled_on').prop('checked', false);
      $('#sharepress_meta_enabled_off').attr('checked', true);
    };
    
    window.click_use_post_title = function(cb) {
      if (cb.checked) {
        copy_title_to_message(true);
      } else {
        $('#sharepress_meta_message').focus();
      }
    };
    
    window.click_use_excerpt = function(cb) {
      if (cb.checked) {
        excerpt_was = null;
        content_was = null;
        copy_excerpt_to_description(true);
      } else {
        $('#sharepress_meta_description').focus();
      }
    };
    
    window.copy_title_to_message = function(synchronize) {
      if (!$('#sharepress_meta_title_is_message:checked').size()) {
        return false;
      }
      
      clearTimeout(message_timeout);
      setTimeout(function() {
        msg.val(title.val());
        msg_was = msg.val();
      }, synchronize ? 0 : 1000);
    };
    
    title.bind('keypress blur', function() {
      copy_title_to_message();
      return true;
    });

    var check_for_featured_image = true;
    var check_for_targets = <?php echo (SharePress::is_business()) ? 'false' : 'true' ?>;

    $('#save-post, #post-preview, input[value="Submit for Review"]').click(function() {
      check_for_targets = check_for_featured_image = false;
      return true;
    });

    $('#publish').not('input[value="Submit for Review"]').click(function() {
      check_for_targets = check_for_featured_image = true;
      return true;
    });

    $('input[name="sharepress_meta\[let_facebook_pick_pic\]"]').change(function() {
      $('#picture_error').hide();
    });

    $('input.sharepress_target').click(function() {
      if ($('input.sharepress_target:checked').size()) {
        $('label[for="sharepress_meta_targets"]').css('color', 'black');
        $('#publish_target_error').hide();
      }
    });

    $('#post').submit(function() {
      var will_share = ( $('#sharepress_meta_enabled_on:checked').size() || $('#sharepress_meta_publish_again').val() == '1' );
      var let_facebook_pick_pic = $('input[name="sharepress_meta\[let_facebook_pick_pic\]"]:checked');
    
      if (check_for_featured_image && will_share && !$('#postimagediv img').size() && let_facebook_pick_pic.val() == '0') {
        $('#ajax-loading').hide();
        $('#publish').removeClass('button-primary-disabled');
        $('#publishing-action').find('.spinner').hide();
        $('.sharepress_show_advanced').hide(); 
        $('.sharepress_advanced').slideDown();
        $('#picture_error').show();
        $(window).scrollTop($('#sharepress_meta').offset().top);

        return false;
      }

      // are we trying to post with sharepress?
      if (check_for_targets && will_share) {
        // no targets?
        if (!$('input.sharepress_target:checked').size()) {

          // reveal the targets selection, and try to focus the screen on it:
          $('#ajax-loading').hide();
          $('#publish').removeClass('button-primary-disabled');
          $('#publishing-action').find('.spinner').hide();
          $('.sharepress_show_advanced').hide(); 
          $('.sharepress_advanced').slideDown();
          $('label[for="sharepress_meta_targets"]').css('color', 'red');
          $('#publish_target_error').show();
          $(window).scrollTop($('#sharepress_meta').offset().top);

          // don't allow submission:
          return false;

        } else {
          $('.sharepress_show_advanced').show(); 
          $('.sharepress_advanced').hide();
        }
      }
    });
    
  });
})(jQuery);
</script>