<?php
/*
Copyright (C)2011 Fat Panda, LLC

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

// support for setting individual images in options
require('postimage.php');

if (!defined('ABSPATH')) exit;

/**
 * This PHP class is a namespace for the pro version of your plugin. 
 */
class SharepressPro {
  
  // holds the singleton instance of your plugin's core
  static $instance;
  
  /**
   * Get the singleton instance of this plugin's core, creating it if it does
   * not already exist.
   */
  static function load() {
    return (self::$instance) ? self::$instance : ( self::$instance = new SharepressPro() );
  }
  
  private function __construct() {
    add_action('init', array($this, 'init'), 10, 1);
    
    #
    # Discover this file's path
    #
    $parts = explode(DIRECTORY_SEPARATOR, __FILE__);
    $fn = array_pop($parts);
    $fd = (($fd = array_pop($parts)) != 'plugins' ? $fd : '');
    
    add_action('plugin_action_links_sharepress/pro.php', array($this, 'plugin_action_links'), 10, 4);

    // enhancement #1: post thumbnails are used in messages posted to facebook
    add_action('after_setup_theme', array($this, 'after_setup_theme'));
  }
  
  /**
   * Add "Settings" link to the Plugins screen.
   */
  function plugin_action_links($actions, $plugin_file, $plugin_data, $context) {
    $actions['settings'] = '<a href="options-general.php?page=sharepress">Settings</a>';
    return $actions;
  }
  
  function init() {
    // attach a reference to the pro version onto the lite version
    Sharepress::$pro = $this;
    
    // enhancement #2: ability to publish to pages
    add_filter('sharepress_pages', array($this, 'pages'));
    add_action('sharepress_post', array($this, 'post'), 10, 2);
    
    // enhancement #3: configure the content of each post individually
    add_filter('sharepress_meta_box', array($this, 'meta_box'), 10, 2);
    add_action('wp_ajax_sharepress_get_excerpt', array($this, 'ajax_get_excerpt'));
    
    // enhancement #4: enhancements to the posts browser
    if (is_admin()) {
      $post_type = !empty($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
      if (in_array($post_type, Sharepress::supported_post_types())) {
        add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
        add_filter('posts_where', array($this, 'posts_where'));
        add_filter('posts_orderby', array($this, 'posts_orderby'));
      }
    }
    foreach(Sharepress::supported_post_types() as $type) {
      add_action("manage_edit-{$type}_columns", array($this, 'manage_posts_columns'));
    }
    add_action('manage_posts_custom_column', array($this, 'manage_posts_custom_column'), 10, 2);
    
    // enhancement #5: scheduling 
    add_action('sharepress_oneminute_cron', array($this, 'oneminute_cron'));
    
    // enhancement #6: twitter support
    add_action('wp_ajax_sharepress_test_twitter_settings', array($this, 'ajax_test_twitter_settings'));

  }
  
  function after_setup_theme() {
    add_theme_support('post-thumbnails');
  }  
  
  function ajax_test_twitter_settings() {
    if ( current_user_can('administrator') ) {
      
      extract($settings = array_map('trim', $_POST[SharePress::OPTION_SETTINGS]));

      if (!$twitter_consumer_key) {
        echo 'Consumer key is required.';
        exit;
      }

      if (!$twitter_consumer_secret) {
        echo 'Consumer secret is required.';
        exit;
      }

      if (!$twitter_access_token) {
        echo 'Access token is required.';
        exit;
      }

      if (!$twitter_access_token_secret) {
        echo 'Access token secret is required.';
        exit;
      }

      $client = new SharePress_TwitterClient($settings);
      echo $client->test();
     
    }

    exit;
  }

  function manage_posts_columns($cols) {
    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $current_url = remove_query_arg( 'sharepress_sort', $current_url );
    
    if ( isset( $_GET['sharepress_sort'] ) && 'desc' == $_GET['sharepress_sort'] ) {
      $current_order = 'desc';
    } else {
      $current_order = 'asc';
    }
      
    $url = $current_url . '&sharepress_sort='. ( $current_order == 'asc' ? 'desc' : 'asc' );
    
    
    $cols['sharepress'] = '<a href="'.$url.'">'.__('SharePress').'</a>';
    
    return $cols;
  }
  
  function manage_posts_custom_column($column_name, $post_id) {
    if ($column_name == 'sharepress') {
      $post = get_post($post_id);
      $posted = get_post_meta($post_id, Sharepress::META_POSTED, true);
      $last_posted = Sharepress::get_last_posted($post);
      $scheduled = get_post_meta($post_id, Sharepress::META_SCHEDULED, true);
      $edit = get_admin_url()."post.php?post={$post->ID}&action=edit&sharepress=schedule";
      $meta = (array) get_post_meta($post_id, Sharepress::META, true);
      $delayed = (($length = $meta['delay_length']) && ($unit = $meta['delay_unit'])) ? strtotime($delay = "{$length} {$unit}", strtotime($post->post_date_gmt)) : false;
      $error = get_post_meta($post_id, Sharepress::META_ERROR, true);
      
      if ($error) {
        echo '<span style="color:red;">'.__('Last Post Failed').': '.__($error).'</span><br /><a href="'.$edit.'">Try Again</a>';
      } else if ($posted) {
        echo __('Posted').': '.date('Y/m/d g:ia', strtotime($posted) + ( get_option( 'gmt_offset' ) * 3600 )).'<br /><a href="'.$edit.'">Schedule Future Repost</a>';
      } else if ($delayed) {
        echo '<span title="'.date('Y/m/d g:ia', $delayed).'">'.__('Delay for').' '.$delay.'</span><br /><a href="'.$edit.'">Edit</a>';
      } else if ($scheduled) {
        echo __('Scheduled').': '.date('Y/m/d g:ia', $scheduled).'<br /><a href="'.$edit.'">Edit Schedule</a>';
      } else if ($last_posted) {
        echo __('Posted').': '.date('Y/m/d g:ia', $last_posted + ( get_option( 'gmt_offset' ) * 3600 )).'<br /><a href="'.$edit.'">Schedule Future Repost</a>';
      } else if ($post->post_status == 'future') {
        if ($meta['enabled'] == 'on') {
          echo __('Scheduled').': '.date('Y/m/d g:ia', strtotime( $post->post_date )).'<br /><a href="'.$edit.'">Edit Schedule</a>';
        } else {
          'Not scheduled<br /><a href="'.$edit.'">Schedule Now</a>';
        }
      } else if ($post->post_status != 'publish') {
        echo 'Post in draft';
      } else {
        echo 'Not yet posted<br /><a href="'.$edit.'">Schedule Now</a>';
      }
    }
  }
  
  function restrict_manage_posts() {
    $current = @$_GET['sharepress'];
    ?>
      <select name="sharepress">
        <option value="">SharePress Filter (Off)</option>
        <option value="all" <?php if ($current == 'all') echo 'selected="selected"' ?>>Show only SharePressed</option>
        <option value="posted" <?php if ($current == 'posted') echo 'selected="selected"' ?>>&mdash; Already posted</option>
        <option value="scheduled" <?php if ($current == 'scheduled') echo 'selected="selected"' ?>>&mdash; Scheduled to be posted</option>
        <option value="error" <?php if ($current == 'error') echo 'selected="selected"' ?>>&mdash; Errors</option>
        <option value="not" <?php if ($current == 'not') echo 'selected="selected"' ?>>Show never SharePressed</option>
      </select>
    <?php
  }
  
  function posts_where($where) {
    global $wpdb;

    if (@$_GET['sharepress'] == 'all') {
      $where .= sprintf(" 
        AND EXISTS ( 
          SELECT * FROM {$wpdb->postmeta} 
          WHERE 
            post_id = {$wpdb->posts}.ID 
            AND meta_key IN ('%s', '%s') 
            AND meta_value IS NOT NULL
        )
      ",
        Sharepress::META_RESULT, Sharepress::META_SCHEDULED
     );
    } else if (@$_GET['sharepress'] == 'posted') {
      $where .= sprintf(" 
        AND EXISTS ( 
          SELECT * FROM {$wpdb->postmeta} 
          WHERE 
            post_id = {$wpdb->posts}.ID 
            AND meta_key = '%s' 
            AND meta_value IS NOT NULL
        )
      ",
        Sharepress::META_RESULT
     );
     
    } else if (@$_GET['sharepress'] == 'scheduled') {
      $where .= sprintf(" 
        AND EXISTS ( 
          SELECT * FROM {$wpdb->postmeta} 
          WHERE 
            post_id = {$wpdb->posts}.ID 
            AND meta_key IN ('%s') 
            AND meta_value IS NOT NULL
        )
      ",
        Sharepress::META_SCHEDULED
     );
     
    } else if (@$_GET['sharepress'] == 'not') {
      $where .= sprintf(" 
        AND NOT EXISTS ( 
          SELECT * FROM {$wpdb->postmeta} 
          WHERE 
            post_id = {$wpdb->posts}.ID 
            AND meta_key IN ('%s', '%s') 
            AND meta_value IS NOT NULL
        )
      ",
        Sharepress::META_RESULT, Sharepress::META_SCHEDULED
     );
    
    } else if (@$_GET['sharepress'] == 'error') {
      $where .= sprintf(" 
        AND EXISTS ( 
          SELECT * FROM {$wpdb->postmeta} 
          WHERE 
            post_id = {$wpdb->posts}.ID 
            AND meta_key IN ('%s') 
            AND meta_value IS NOT NULL
        )
      ",
        Sharepress::META_ERROR
     );
      
    }  
    
    return $where;
  }
  
  function posts_orderby($orderby) {
    global $wpdb;
    
    if (@$_GET['sharepress_sort']) {
      $dir = $_GET['sharepress_sort'] == 'asc' ? 'asc' : 'desc';
      
      $cols = array();
      
      // these first two are arranged in ascending order -- posted stuff is older than scheduled stuff
      
      $cols[] = sprintf("
        (
          EXISTS (
            SELECT * 
            FROM {$wpdb->postmeta}
            WHERE 
              post_id = {$wpdb->posts}.ID
              AND meta_key = '%s'
              AND meta_value IS NOT NULL
          )
        )
      ", 
        Sharepress::META_POSTED
      );
      
      $cols[] = sprintf("
        (
          EXISTS (
            SELECT * 
            FROM {$wpdb->postmeta}
            WHERE 
              post_id = {$wpdb->posts}.ID
              AND meta_key = '%s'
              AND meta_value IS NOT NULL
          )
        )
      ", 
        Sharepress::META_SCHEDULED
      );
      
      // so if descending order is requested, we flip those two
      if ($dir == 'desc') {
        rsort($cols);
      }
      
      $cols[] = sprintf("
        (
          SELECT CONVERT(meta_value, signed) 
          FROM {$wpdb->postmeta}
          WHERE 
            post_id = {$wpdb->posts}.ID
            AND meta_key = '%s'
        ) {$dir}
      ",
        Sharepress::META_SCHEDULED
      );
      
      $cols[] = sprintf("
        (
          SELECT STR_TO_DATE(meta_value, '%%Y/%%m/%%d %%H:%%i:%%s')
          FROM {$wpdb->postmeta}
          WHERE 
            post_id = {$wpdb->posts}.ID
            AND meta_key = '%s'
        ) {$dir}
      ",
        Sharepress::META_POSTED
      );
        
      $orderby = implode(', ', $cols);
    }
    
    return $orderby;
  }
  
  function oneminute_cron() {
    Sharepress::log('SharepressPro::oneminute_cron');

    // make sure we don't allow more than one instance of this per minute
    $fh = fopen(__FILE__, 'r');
    if (!$fh) {
      Sharepress::log('SharepressPro::oneminute_cron - failed to open file handle for process locking');
    } else if (!flock($fh, LOCK_EX)) {
      Sharepress::log('SharepressPro::oneminute_cron - is already running, or failed to lock process');
      return false;
    }

    foreach($this->get_scheduled_posts() as $post) {
      Sharepress::load()->share($post->ID);
    }
  }
  
  function get_scheduled_posts() {
    // load list of posts that are scheduled and ready to post
    global $wpdb;
    return $wpdb->get_results(sprintf("
      SELECT P.ID
      FROM $wpdb->posts P 
      INNER JOIN $wpdb->postmeta M ON (M.post_id = P.ID)
      WHERE 
        P.post_status = 'publish'
        AND M.meta_key = '%s' 
        AND M.meta_value <= %s
        AND NOT EXISTS (
          SELECT * FROM $wpdb->postmeta E
          WHERE 
            E.post_id = P.ID
            AND E.meta_key = '%s'
            AND E.meta_value IS NOT NULL
        )
    ",
      Sharepress::META_SCHEDULED,
      current_time('timestamp'),
      Sharepress::META_POSTED
    ));
  }
  
  function ajax_get_excerpt() {
    global $wpdb;
    $post_id = @$_POST['post_id'];
    $content = @$_POST['content'];

    if (!current_user_can('edit_post', $post_id)) {
      exit;
    }
    
    echo str_replace( array('&nbsp;'), array(' '), Sharepress::load()->get_excerpt( null, stripslashes($content) ) );

    exit;
  }
  
  private static $ok_page_names = null;

  static function is_excluded_page($page) {
    if (is_null(self::$ok_page_names)) {
      self::$ok_page_names = array_map(array(__CLASS__, '_map_page_names'), self::pages());
      self::$ok_page_names[] = 'wall';
      self::$ok_page_names = apply_filters('sharepress_ok_page_names', self::$ok_page_names, function_exists('get_current_site') ? get_current_site() : null);
    }

    $page_name = is_array($page) ? $page['name'] : $page;

    return self::$ok_page_names ? !in_array($page_name, self::$ok_page_names) : false;    
  }
  
  private static function _map_page_names($page) {
    return $page['name'];
  }

  function post($meta, $post) {
    if (Sharepress::debug()) {
      Sharepress::log(sprintf('SharepressPro::post(%s, %s)', $meta['message'], is_object($post) ? $post->post_title : $post));
      Sharepress::log(sprintf('SharepressPro::post => count(SharepressPro::pages()) = %s', count(self::pages())));
      Sharepress::log(sprintf('SharperessPro::post => $meta["targets"] = %s', serialize($meta['targets'])));
    }

    // loop over authorized pages
    foreach(self::pages() as $page) {
      if (in_array($page['id'], $meta['targets'])) {
        if (self::is_excluded_page($page)) {
          continue;
        }

        $result = Sharepress::api($page['id'].'/links', 'POST', array(
          'access_token' => $page['access_token'],
          'message' => $meta['message'],
          'link' => Sharepress::load()->get_permalink($post->ID)
        ));
        
        Sharepress::log(sprintf("posted to the page(%s): %s", $page['name'], serialize($result)));
        
        // store the ID for queuing 
        $result['posted'] = time();
        add_post_meta($post->ID, Sharepress::META_RESULT, $result);        
      }
    }
  }
  
  function meta_box($meta_box, $args) {
    extract($args);

    ob_start();
    require('pro-meta-box.php');
    return ob_get_clean();
  }
  
  static function sort_by_name($a, $b) {
    return strcasecmp($a['name'], $b['name']);
  }
  
  function pages($default = array()) {
    try {
      if (SharePress::is_business()) {
        return array();
      }
      $result = Sharepress::api(Sharepress::me('id').'/accounts', 'GET', array(), '30 days');
    } catch (Exception $e) {
      Sharepress::handleFacebookException($e);
      return array();
    }

    if ($result) {
      $data = $result['data'];
      
      // we only care about pages...
      $pages = array();
      if ($data) {
        foreach($data as $d) {
          if (isset($d['name'])) {
            $pages[] = $d;
          }
        }
      }
      
      // sort by page name, for sanity's sake
      usort($pages, array('SharepressPro', 'sort_by_name'));
      
      $result = $default + $pages;
      return !$result || !is_array($result) ? array() : $result;

    } else {
      return array();
    }
  }
  
  function get_publish_time() {
    $meta = @$_POST[Sharepress::META];
    if (!$meta) {
      return false;
    }
    
    if ($mm = @$meta['mm']) {
      $date = sprintf('%s/%s/%s %s:%s', (int) $meta['aa'], (int) $meta['mm'], (int) $meta['jj'], (int) $meta['hh'], (int) $meta['mn']);
      return strtotime($date);
    } else {
      return false;
    }
  }
  
  function touch_time($scheduled = null) {
    global $wp_locale, $post, $comment;

    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
      $tab_index_attribute = " tabindex=\"$tab_index\"";

    // echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';

    $time_adj = current_time('timestamp');
    
    $jj = ($scheduled) ? date( 'd', $scheduled ) : gmdate( 'd', $time_adj );
    $mm = ($scheduled) ? date( 'm', $scheduled ) : gmdate( 'm', $time_adj );
    $aa = ($scheduled) ? date( 'Y', $scheduled ) : gmdate( 'Y', $time_adj );
    $hh = ($scheduled) ? date( 'H', $scheduled ) : gmdate( 'H', $time_adj );
    $mn = ($scheduled) ? date( 'i', $scheduled ) : gmdate( 'i', $time_adj );
    $ss = ($scheduled) ? date( 's', $scheduled ) : gmdate( 's', $time_adj );

    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );

    $field = Sharepress::META;

    $month = "<select " . ( $multi ? '' : 'id="sp_mm" ' ) . "name=\"{$field}[mm]\"$tab_index_attribute>\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
      $month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
      if ( $i == $mm )
        $month .= ' selected="selected"';
      $month .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
    }
    $month .= '</select>';

    $day = '<input id="sp_jj" type="text" name="'.$field.'[jj]" onblur="if(!jQuery.trim(jQuery(this).val())) jQuery(this).val(\''.$jj.'\');" value="' . $jj . '"  maxlength="2" autocomplete="off" />';
    $year = '<input id="sp_aa" type="text" name="'.$field.'[aa]" onblur="if(!jQuery.trim(jQuery(this).val())) jQuery(this).val(\''.$aa.'\');" value="' . $aa . '"  maxlength="4" autocomplete="off" />';
    $hour = '<input id="sp_hh" type="text" name="'.$field.'[hh]" onblur="if(!jQuery.trim(jQuery(this).val())) jQuery(this).val(\''.$hh.'\');" value="' . $hh . '" maxlength="2" autocomplete="off" />';
    $minute = '<input id="sp_mn" type="text" name="'.$field.'[mn]" onblur="if(!jQuery.trim(jQuery(this).val())) jQuery(this).val(\''.$mn.'\');" value="' . $mn . '" maxlength="2" autocomplete="off" />';

    echo '<div class="timestamp-wrap">';
    /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
    printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);

    echo '</div>';

  }
  
}

if (!function_exists('like')): 

function like(/* dynamic args */) {
  $args = func_get_args();
  
  if (count($args) == 1) {
    if (is_array($args[0])) {
      $args = $args[0];
    } else if (strpos($args[0], '=') !== false) {
      parse_str($args[0], $arguments);
      $args = $arguments;
    } else {
      $args = array('href' => $args[0]);
    }
  } else {
    $args = array('href' => get_permalink());
  }
  
  $args = array_merge(array(
    'href' => get_permalink(),
    'send' => true,
    'width' => 450,
    'show_faces' => true,
    'layout' => 'standard'
  ), $args);
  
  extract($args);
  
  static $script_out;
  if (!$script_out) {
    ?>
      <div id="fb-root"></div>
      <script src="http://connect.facebook.net/en_US/all.js#appId=<?php echo get_option(Sharepress::OPTION_API_KEY) ?>&amp;xfbml=1"></script>
    <?php
    $script_out = true;
  }
  
  ?>
    <span class="like">
      <fb:like 
        href="<?php echo $href ?>"
        send="<?php echo $send && $send != 'false' ? 'true' : 'false' ?>"
        width="<?php echo $width ?>"
        show_faces="<?php echo $show_faces && $show_faces != 'false' ? 'true' : 'false' ?>"
        layout="<?php echo $layout ?>"
      ></fb:like>
    </span>
  <?php
}

endif; // !function_exists('like')

SharepressPro::load();