<fieldset style="margin-top:10px;">
  <p>Should this post be shared?</p>
  <div style="padding:5px 0 12px 10px;">
    <label style="float: left; margin-right: 1em;">
      <input type="radio" name="<?php echo self::META ?>[enabled]" id="sharepress_meta_enabled_on" value="on" <?php if ($enabled) echo 'checked="checked"' ?> /> <strong>Yes</strong>
    </label>
    <label>
      <input type="radio" name="<?php echo self::META ?>[enabled]" id="sharepress_meta_enabled_off" value="off" <?php if (!$enabled) echo 'checked="checked"' ?> /> No
    </label>
    <div style="clear:left;"></div>
  </div>
</fieldset>

<div id="sharepress_meta_controls" style="margin-bottom:15px; <?php if (!$enabled) echo 'display:none;' ?>">
  <?php echo $meta_box ?>
</div>


<script>
(function($) {
  var facebook_enabled_val;
  setInterval(function() {
    var facebook_enabled = $('input[name="sharepress_meta\[enabled\]"]:checked').val() == 'on';
    if (facebook_enabled != facebook_enabled_val) {
      facebook_enabled_val = facebook_enabled;
      if (facebook_enabled_val) {
        $('#sharepress_meta_controls').show();
      } else {
        $('#sharepress_meta_controls').hide();
      }
    }
  }, 250);
})(jQuery);
</script>