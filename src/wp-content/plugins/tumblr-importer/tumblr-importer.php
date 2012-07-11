<?php
/*
Plugin Name: Tumblr Importer
Plugin URI: http://wordpress.org/extend/plugins/tumblr-importer/
Description: Import posts from a Tumblr blog.
Author: wordpressdotorg
Author URI: http://wordpress.org/
Version: 0.6
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if ( !defined('WP_LOAD_IMPORTERS') && !defined('DOING_CRON') )
	return;

require_once ABSPATH . 'wp-admin/includes/import.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

require_once 'class-wp-importer-cron.php';

/**
 * Tumblr Importer Initialisation routines
 *
 * @package WordPress
 * @subpackage Importer
 */
function tumblr_importer_init() {
	global $tumblr_import;
	load_plugin_textdomain( 'tumblr-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	$tumblr_import = new Tumblr_Import();
	register_importer('tumblr', __('Tumblr', 'tumblr-importer'), __('Import posts from a Tumblr blog.', 'tumblr-importer'), array ($tumblr_import, 'start'));
	if ( !defined('TUMBLR_MAX_IMPORT') )
		define ('TUMBLR_MAX_IMPORT', 20);
}
add_action( 'init', 'tumblr_importer_init' );

/**
 * Tumblr Importer
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'WP_Importer_Cron' ) ) {
class Tumblr_Import extends WP_Importer_Cron {

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'tumblr_importer_metadata', array( $this, 'tumblr_importer_metadata' ) );
		add_filter( 'tumblr_importer_format_post', array( $this, 'filter_format_post' ) );
		add_filter( 'wp_insert_post_empty_content', array( $this, 'filter_allow_empty_content' ), 10, 2 );
		parent::__construct();
	}

	// Figures out what to do, then does it.
	function start() {
		if ( isset($_POST['restart']) )
			$this->restart();
			
		if ( !isset($this->error) ) $this->error = null;
		
		if ( isset( $_POST['email'] ) && isset( $_POST['password'] ) ) {
			$this->check_credentials();
		}	
		if ( isset( $_POST['blogurl'] ) ) {
			$this->start_blog_import();
		}
		if ( isset( $this->blogs ) ) {
			$this->show_blogs($this->error);
		} else {
			$this->greet($this->error);
		}
		
		unset ($this->error);

		if ( !isset($_POST['restart']) ) $saved = $this->save_vars();

		if ( $saved && !isset($_GET['noheader']) ) {
			?>
			<p><?php _e('We have saved some information about your Tumblr account in your WordPress database. Clearing this information will allow you to start over. Restarting will not affect any posts you have already imported. If you attempt to re-import a blog, duplicate posts will be skipped.', 'tumblr-importer'); ?></p>
			<p><?php _e('Note: This will stop any import currently in progress.', 'tumblr-importer'); ?></p>
			<form method='post' action='?import=tumblr&amp;noheader=true'>
			<p class='submit' style='text-align:left;'>
			<input type='submit' class='button' value='<?php esc_attr_e('Clear account information', 'tumblr-importer'); ?>' name='restart' />
			</p>
			</form>
			<?php
		}
	}
	
	function greet($error=null) {
		
		if ( !empty( $error ) )
			echo "<div class='error'><p>{$error}</p></div>";
		?>
		
		<div class='wrap'><?php echo screen_icon(); ?>
		<h2><?php _e('Import Tumblr', 'tumblr-importer'); ?></h2>
		<p><?php _e('Howdy! This importer allows you to import posts from your Tumblr account into your WordPress site.', 'tumblr-importer'); ?></p>
		<p><?php _e('Firstly, you need to provide your email and password for Tumblr, so that WordPress can access your account.', 'tumblr-importer'); ?></p>
		<form action='?import=tumblr' method='post'>
		<?php wp_nonce_field( 'tumblr-import' ) ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for='email'><?php _e('Email:','tumblr-importer'); ?></label></label></th>
					<td><input type='text' class="regular-text" name='email' value='<?php if (isset($this->email)) echo esc_attr($this->email); ?>' /></td>
				</tr>
				<tr>
					<th scope="row"><label for='email'><?php _e('Password:','tumblr-importer'); ?></label></label></th>
					<td><input type='password' class="regular-text" name='password' value='<?php if (isset($this->password)) echo esc_attr($this->password); ?>' /></td>
				</tr>
			</table>
			<p class='submit'>
				<input type='submit' class='button' value="<?php _e('Connect to Tumblr','tumblr-importer'); ?>" />
			</p>
		</form>
		</div>
		<?php
	}
	
	function check_credentials() {
		check_admin_referer( 'tumblr-import' );

		$this->email = $_POST['email'];
		$this->password = $_POST['password'];

		if ( !is_email( $_POST['email'] ) ) {
			$error = __("This doesn't appear to be a valid email address. Please check it and try again.",'tumblr-importer');
			$this->error = $error;
		}

		$blogs = $this->get_blogs($this->email, $this->password);
		if ( is_wp_error ($blogs) ) {
			$this->error = $blogs->get_error_message();
		} else {
			$this->blogs = $blogs;
		}
	}
	
	function show_blogs($error=null) {
	
		if ( !empty( $error ) )
			echo "<div class='error'><p>{$error}</p></div>";

		$authors = get_users( array('who' => 'authors') );
		?>
		<div class='wrap'><?php echo screen_icon(); ?>
		<h2><?php _e('Import Tumblr', 'tumblr-importer'); ?></h2>
		<p><?php _e('Please select the Tumblr blog you would like to import into your WordPress site and then click on the "Import this Blog" button to continue.'); ?></p>
		<p><?php _e('If your import gets stuck for a long time or you would like to import from a different Tumblr account instead then click on the "Clear account information" button below to reset the importer.','tumblr-importer'); ?></p>
		<?php if ( 1 < count( $authors ) ) : ?>
			<p><?php _e('As Tumblr does not expose the "author", even from multi-author blogs you will need to select which WordPress user will be listed as the author of the imported posts.','tumblr-importer'); ?></p>
		<?php endif; ?>
		<table class="widefat" cellspacing="0"><thead>
		<tr>
		<th><?php _e('Tumblr Blog','tumblr-importer'); ?></th>
		<th><?php _e('URL','tumblr-importer'); ?></th>
		<th><?php _e('Posts Imported','tumblr-importer'); ?></th>
		<th><?php _e('Drafts Imported','tumblr-importer'); ?></th>
		<!--<th><?php _e('Queued Imported','tumblr-importer'); ?></th>-->
		<th><?php _e('Pages Imported','tumblr-importer'); ?></th>
		<th><?php _e('Author','tumblr-importer'); ?></th>
		<th><?php _e('Action/Status','tumblr-importer'); ?></th>
		</tr></thead>
		<tbody>
		<?php
		$style = '';
		$custom_domains = false;
		foreach ($this->blogs as $blog) {
			$url = $blog['url'];
			$style = ( 'alternate' == $style ) ? '' : 'alternate';
			if ( !isset( $this->blog[$url] ) ) {
				$this->blog[$url]['posts_complete'] = 0;
				$this->blog[$url]['drafts_complete'] = 0;
				$this->blog[$url]['queued_complete'] = 0;
				$this->blog[$url]['pages_complete'] = 0;
				$this->blog[$url]['total_posts'] = $blog['posts']; 
				$this->blog[$url]['total_drafts'] = $blog['drafts'];
				$this->blog[$url]['total_queued'] = $blog['queued'];
				$this->blog[$url]['name'] = $blog['name'];
			}
			
			if ( empty( $this->blog[$url]['progress'] ) ) {
				$submit = "<input type='submit' value='". __('Import this blog','tumblr-importer') ."' />";
			} else if ( $this->blog[$url]['progress'] == 'finish' ) {
				$submit = '<img src="' . admin_url( 'images/yes.png' ) . '" style="vertical-align: top; padding: 0 4px;" alt="' . __( 'Finished!', 'tumblr-importer' ) . '" title="' . __( 'Finished!', 'tumblr-importer' ) . '" /><span>' . __( 'Finished!', 'tumblr-importer' ) . '</span>';
			} else {
				$submit = '<img src="' . admin_url( 'images/loading.gif' ) . '" style="vertical-align: top; padding: 0 4px;" alt="' . __( 'In Progress', 'tumblr-importer' ) . '" title="' . __( 'In Progress', 'tumblr-importer' ) . '" /><span>' . __( 'In Progress', 'tumblr-importer' ) . '</span>';
				// Just a little js page reload to show progress if we're in the in-progress phase of the import.
				$submit .= "<script type='text/javascript'>setTimeout( 'window.location.href = window.location.href', 15000);</script>";
			}
			
			// Check to see if this url is a custom domain. The API doesn't play nicely with these
			// (intermittently returns 408 status), so make the user disable the custom domain
			// before importing.
			if ( !preg_match( '|tumblr.com/$|', $url ) ) {
				$submit = '<nobr><img src="' . admin_url( 'images/no.png' ) . '" style="vertical-align:top; padding: 0 4px;" alt="' . __( 'Tumblr Blogs with Custom Domains activated cannot be imported, please disable the custom domain first.', 'tumblr-importer' ) . '" title="' . __( 'Tumblr Blogs with Custom Domains activated cannot be imported, please disable the custom domain first.', 'tumblr-importer' ) . '" /><span style="cursor: pointer;" title="' . __( 'Tumblr Blogs with Custom Domains activated cannot be imported, please disable the custom domain first.' ) . '">' . __( 'Custom Domain', 'tumblr-importer' ) . '</nobr></span>';
				$custom_domains = true;
			}
			
			// Build an author selector / static name depending on number
			if ( 1 == count( $authors ) ) {
				$author_selection = "<input type='hidden' value='{$authors[0]->ID}' name='post_author' />{$authors[0]->display_name}";
			} else {
				$args = array('who' => 'authors', 'name' => 'post_author', 'echo' => false );
				if ( isset( $this->blog[$url]['post_author'] ) )
					$args['selected'] = $this->blog[$url]['post_author'];
				$author_selection = wp_dropdown_users( $args );
			}
			?>
			<tr class="<?php echo $style; ?>">
			<form action='?import=tumblr' method='post'>
			<?php wp_nonce_field( 'tumblr-import' ); ?>
			<input type='hidden' name='blogurl' value='<?php echo esc_attr($blog['url']); ?>' />
			
				<td><?php echo esc_html($blog['title']); ?></td>
				<td><?php echo esc_html($blog['url']); ?></td>
				<td><?php echo $this->blog[$url]['posts_complete'] . ' / ' . $this->blog[$url]['total_posts']; ?></td>
				<td><?php echo $this->blog[$url]['drafts_complete'] . ' / ' . $this->blog[$url]['total_drafts']; ?></td>
				<!--<td><?php echo $this->blog[$url]['queued_complete']; ?></td>-->
				<td><?php echo $this->blog[$url]['pages_complete']; ?></td>
				<td><?php echo $author_selection ?></td>
				<td><?php echo $submit; ?></td>
			</form>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
		<?php if ( $custom_domains ) : ?>
		<p><strong>
			<?php _e( 'As one or more of your Tumblr blogs has a Custom Domain mapped to it. If you would like to import one of these sites you will need to temporarily remove the custom domain mapping and clear the account information from the importer to import. Once the import is completed you can re-enable the custom domain for your site.' ); ?>
		</strong></p>
		<?php endif; ?>
		<p><?php _e("Importing your Tumblr blog can take a while so the importing process happens in the background and you may not see immediate results here. Come back to this page later to check on the importer's progress.",'tumblr-importer'); ?></p>
		</div>
		<?php
	}
	
	function start_blog_import() {
		check_admin_referer( 'tumblr-import' );
		$url = $_POST['blogurl'];
		
		if ( !isset( $this->blog[$url] ) ) {
			$this->error = __('The specified blog cannot be found.', 'tumblr-importer');
			return;
		}	

		if ( !empty($this->blog[$url]['progress']) ) {
			$this->error = __('This blog is currently being imported.', 'tumblr-importer');
			return;
		}

		$this->blog[$url]['progress'] = 'start';
		$this->blog[$url]['post_author'] = (int) $_POST['post_author'];
		
		$this->schedule_import_job( 'do_blog_import', array($url) );
	}
	
	function restart() {
		delete_option(get_class($this));
		wp_redirect('?import=tumblr');
	}
	
	function do_blog_import($url) {
		
		// default to the done state
		$done = true;
		
		$this->error=null;
		
		if ( !empty( $this->blog[$url]['progress'] ) ) {
			$done = false;
			do {
				switch ($this->blog[$url]['progress']) {
				case 'start':
				case 'posts':
					$this->do_posts_import($url);
					break;
				case 'drafts':
					$this->do_drafts_import($url);
					break;
				case 'queued':
// TODO Tumblr's API is broken for queued posts
					$this->blog[$url]['progress'] = 'pages';
					//$this->do_queued_import($url);
					break;
				case 'pages':
					$this->do_pages_import($url);
					break;
				case 'finish':
				default:
					$done = true;
					break;
				}				
				$this->save_vars();
			} while ( empty($this->error) && !$done && $this->have_time() );
		} 
	
		return $done;
	}
	
	function do_posts_import($url) {
		$start = $this->blog[$url]['posts_complete'];
		$total = $this->blog[$url]['total_posts'];
		
		// check for posts completion
		if ( $start >= $total ) {
			$this->blog[$url]['progress'] = 'drafts';
			return;
		}
		
		// get the already imported posts to prevent dupes
		$dupes = $this->get_imported_posts( 'tumblr', $this->blog[$url]['name'] );
		
		if ($this->blog[$url]['posts_complete'] + TUMBLR_MAX_IMPORT > $total) $count = $total - $start;
		else $count = TUMBLR_MAX_IMPORT;
		
		$imported_posts = $this->fetch_posts($url, $start, $count, $this->email, $this->password );
		
		if ( false === $imported_posts ) {
			$this->error = __('Problem communicating with Tumblr, retrying later','tumblr-importer');
			return;
		}
		
		if ( is_array($imported_posts) && !empty($imported_posts) ) {
			reset($imported_posts);
			$post = current($imported_posts);
			do {
				// skip dupes
				if ( !empty( $dupes[$post['tumblr_url']] ) ) {
					$this->blog[$url]['posts_complete']++;
					$this->save_vars();
					continue;
				}

				if ( isset( $post['private'] ) ) $post['post_status'] = 'private';
				else $post['post_status']='publish';

				$post['post_author'] = $this->blog[$url]['post_author'];

				do_action( 'tumblr_importing_post', $post );
				$id = wp_insert_post( $post );
				
				if ( !is_wp_error( $id ) ) {
					$post['ID'] = $id; // Allows for the media importing to wp_update_post()
					if ( isset( $post['format'] ) ) set_post_format($id, $post['format']);

					// @todo: Add basename of the permalink as a 404 redirect handler for when a custom domain has been brought accross
					add_post_meta( $id, 'tumblr_'.$this->blog[$url]['name'].'_permalink', $post['tumblr_url'] );
					add_post_meta( $id, 'tumblr_'.$this->blog[$url]['name'].'_id', $post['tumblr_id'] );

					$import_result = $this->handle_sideload($post);
					
					// Handle failed imports.. If empty content and failed to import media..
					if ( is_wp_error($import_result) ) {
						if ( empty($post['post_content']) ) {
							wp_delete_post($id, true);
						}
					}
				}
				
				$this->blog[$url]['posts_complete']++;
				$this->save_vars();

			} while ( false != ($post = next($imported_posts) ) && $this->have_time() );
		}
	}
	
	function do_drafts_import($url) {
		$start = $this->blog[$url]['drafts_complete'];
		$total = $this->blog[$url]['total_drafts'];

		// check for posts completion
		if ( $start >= $total ) {
			$this->blog[$url]['progress'] = 'queued';
			return;
		}

		// get the already imported posts to prevent dupes
		$dupes = $this->get_imported_posts( 'tumblr', $this->blog[$url]['name'] );

		if ($this->blog[$url]['posts_complete'] + TUMBLR_MAX_IMPORT > $total) $count = $total - $start;
		else $count = TUMBLR_MAX_IMPORT;

		$imported_posts = $this->fetch_posts($url, $start, $count, $this->email, $this->password, 'draft' );

		if ( empty($imported_posts) ) {
			$this->error = __('Problem communicating with Tumblr, retrying later','tumblr-importer');
			return;
		}
		
		if ( is_array($imported_posts) && !empty($imported_posts) ) {
			reset($imported_posts);
			$post = current($imported_posts);
			do {
				// skip dupes
				if ( !empty( $dupes[$post['tumblr_url']] ) ) {
					$this->blog[$url]['drafts_complete']++;
					$this->save_vars();
					continue;
				}

				$post['post_status'] = 'draft';
				$post['post_author'] = $this->blog[$url]['post_author'];

				do_action( 'tumblr_importing_post', $post );
				$id = wp_insert_post( $post );
				if ( !is_wp_error( $id ) ) {
					$post['ID'] = $id;
					if ( isset( $post['format'] ) ) set_post_format($id, $post['format']);

					add_post_meta( $id, 'tumblr_'.$this->blog[$url]['name'].'_permalink', $post['tumblr_url'] );
					add_post_meta( $id, 'tumblr_'.$this->blog[$url]['name'].'_id', $post['tumblr_id'] );
					
					$this->handle_sideload($post);
				}

				$this->blog[$url]['drafts_complete']++;
				$this->save_vars();
			} while ( false != ($post = next($imported_posts) ) && $this->have_time() );
		}
	}
	
	function do_pages_import($url) {
		$start = $this->blog[$url]['pages_complete'];

		// get the already imported posts to prevent dupes
		$dupes = $this->get_imported_posts( 'tumblr', $this->blog[$url]['name'] );

		$imported_pages = $this->fetch_pages($url, $this->email, $this->password );

		if ( false === $imported_pages ) {
			$this->error = __('Problem communicating with Tumblr, retrying later','tumblr-importer');
			return;
		}

		if ( is_array($imported_pages) && !empty($imported_pages) ) {
			reset($imported_pages);
			$post = current($imported_pages);
			do {
				// skip dupes
				if ( !empty( $dupes[$post['tumblr_url']] ) ) {
					continue;
				}

				$post['post_type'] = 'page';
				$post['post_status'] = 'publish';
				$post['post_author'] = $this->blog[$url]['post_author'];

				$id = wp_insert_post( $post );
				if ( !is_wp_error( $id ) ) {
					add_post_meta( $id, 'tumblr_'.$this->blog[$url]['name'].'_permalink', $post['tumblr_url'] );
					$post['ID'] = $id;
					$this->handle_sideload($post);
				}

				$this->blog[$url]['pages_complete']++;
				$this->save_vars();
			} while ( false != ($post = next($imported_pages) ) );
		}		
		$this->blog[$url]['progress'] = 'finish';
	}
	
	function handle_sideload_import($post, $source, $description = '', $filename = false) {
		// Make a HEAD request to get the filename:
		if ( empty($filename) ) {
			$head = wp_remote_request( $source, array('method' => 'HEAD') );
			if ( !empty($head['headers']['location']) ) {
				$source = $head['headers']['location'];
				$filename = preg_replace('!\?.*!', '', basename($source) ); // Strip off the Query vars
			}
		}
		
		// still empty? Darned inconsistent tumblr...
		if ( empty($filename) ) {
			$path = parse_url($source,PHP_URL_PATH);
			$filename = basename($path);
		}
		
		// Download file to temp location
		$tmp = download_url( $source );
		if ( is_wp_error($tmp) )
			return $tmp;

		$file_array['name'] = !empty($filename) ? $filename : basename($tmp);
		$file_array['tmp_name'] = $tmp;
		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post['ID'], $description, array( 'post_excerpt' => $description ) );

		if ( $id && ! is_wp_error($id) ) {
			// Update the date/time on the attachment to that of the Tumblr post.
			$attachment = get_post($id, ARRAY_A);
			foreach ( array('post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt') as $field ) {
				if ( isset($post[ $field]) )
					$attachment[ $field ] = $post[ $field ];
			}
			wp_update_post($attachment);
		}

		// If error storing permanently, unlink
		if ( is_wp_error($id) )
			@unlink($file_array['tmp_name']);
		return $id;
	}

	function handle_sideload($post) {

		if ( empty( $post['format'] ) )
			return; // Nothing to import.

		switch ( $post['format'] ) {
		case 'gallery':
			if ( !empty( $post['gallery'] ) ) {
				foreach ( $post['gallery'] as $i => $photo ) {
					$id = $this->handle_sideload_import( $post, (string)$photo['src'], (string)$photo['caption']);
					if ( is_wp_error($id) )
						return $id;
				}
				$post['post_content'] = "[gallery]\n" . $post['post_content'];
				$post = apply_filters( 'tumblr_importer_format_post', $post );
				do_action( 'tumblr_importer_metadata', $post );
				wp_update_post($post);
				break; // If we processed a gallery, break, otherwise let it fall through to the Image handler
			}

		case 'image':
			if ( isset( $post['media']['src'] ) ) {
				$id = $this->handle_sideload_import( $post, (string)$post['media']['src'], (string)$post['post_title']);
				if ( is_wp_error($id) )
					return $id;

				$link = !empty($post['media']['link']) ? $post['media']['link'] : null;
				// image_send_to_editor has a filter to wrap in a shortcode.
				$post_content = $post['post_content'];
				$post['post_content'] = get_image_send_to_editor($id, (string)$post['post_title'], (string)$post['post_title'], 'none', $link, true, 'full' );
				$post['post_content'] .= $post_content;
				$post['meta']['attribution'] = $link;
				$post = apply_filters( 'tumblr_importer_format_post', $post );
				do_action( 'tumblr_importer_metadata', $post );
				//$post['post_content'] .= "\n" . $post['post_content']; // the [caption] shortcode doesn't allow HTML, but this might have some extra markup
				wp_update_post($post);
			}
			
			break;
		
			case 'audio':
			// Handle Tumblr Hosted Audio
			if ( isset( $post['media']['audio'] ) ) {
				$id = $this->handle_sideload_import( $post, (string)$post['media']['audio'], $post['post_title'], (string)$post['media']['filename'] );
				if ( is_wp_error($id) )
					return $id;
				$post['post_content'] = wp_get_attachment_link($id) . "\n" . $post['post_content'];
			} else {
				// Try to work out a "source" link to display Tumblr-style.
				preg_match( '/(http[^ "<>\']+)/', $post['post_content'], $matches );
				if ( isset( $matches[1] ) ) {
					$url_parts = parse_url( $matches[1] );
					$post['meta']['attribution'] = $url_parts['scheme'] . "://" . $url_parts['host'] . "/";
				}
			}
			$post = apply_filters( 'tumblr_importer_format_post', $post );
			do_action( 'tumblr_importer_metadata', $post );
			wp_update_post($post);
			break;
			
		case 'video':
			// Handle Tumblr hosted video
			if ( isset( $post['media']['video'] ) ) {
				$id = $this->handle_sideload_import( $post, (string)$post['media']['video'], $post['post_title'], (string)$post['media']['filename'] );
				if ( is_wp_error($id) )
					return $id;

				// @TODO: Check/change this to embed the imported video.				
				$link = wp_get_attachment_link($id) . "\n" . $post['post_content'];
				$post['post_content'] = $link;
				$post['meta']['attribution'] = $link;
			} else {
				// Try to work out a "source" link to mimic Tumblr's post formatting.
				preg_match( '/(http[^ "<>\']+)/', $post['post_content'], $matches );
				if ( isset( $matches[1] ) ) {
					$url_parts = parse_url( $matches[1] );
					$post['meta']['attribution'] = $url_parts['scheme'] . "://" . $url_parts['host'] . "/";
				}
			}
			$post = apply_filters( 'tumblr_importer_format_post', $post );
			do_action( 'tumblr_importer_metadata', $post );
			wp_update_post($post);

			// Else, Check to see if the url embedded is handled by oEmbed (or not)
			break;
		}

		return true; // all processed
	}
	
	/**
	 * Fetch a list of blogs for a user
	 *
	 * @param $email
	 * @param $password
	 * @returns array of blog info or a WP_Error
	 */
	function get_blogs($email, $password) {
		$url = 'http://www.tumblr.com/api/authenticate';

		$params = array(
			'email'=>$email,
			'password'=>$password,
		);
		$options = array( 'body' => $params );

		// fetch the list
		$out = wp_remote_post($url,$options);
		
		switch ( wp_remote_retrieve_response_code( $out ) ) {
			case 403: // Bad Username / Password
				do_action( 'tumblr_importer_handle_error', 'get_blogs_403' );
				return new WP_Error('tumblr_error', __('Tumblr says that the username and password you provided were not valid. Please check you entered them correctly and try to connect again.', 'tumblr-importer' ) );
				break;
			case 200: // OK
				break;
			default:
				$_error = sprintf( __( 'Tumblr replied with an error: %s', 'tumblr-importer' ), wp_remote_retrieve_body( $out ) );
				do_action( 'tumblr_importer_handle_error', 'response_' . wp_remote_retrieve_response_code( $out ) );
				return new WP_Error('tumblr_error', $_error );
			
		}
		$body = wp_remote_retrieve_body($out);

		// parse the XML into something useful
		$xml = simplexml_load_string($body);

		$blogs = array();

		if (!isset($xml->tumblelog)) {
			new WP_Error('tumblr_error', __('No blog information found for this account. ', 'tumblr-importer' ));
			do_action( 'tumblr_importer_handle_error', 'no_blog_found' );
		}

		$tblogs = $xml->tumblelog;
		foreach ($tblogs as $tblog) {
			$blog = array();

			if ((string) $tblog['is-admin'] != '1') continue; // we'll only allow admins to import their blogs

			$blog['title'] = (string) $tblog['title'];
			$blog['posts'] = (int) $tblog['posts'];
			$blog['drafts'] = (int) $tblog['draft-count'];
			$blog['queued'] = (int) $tblog['queue-count'];
			$blog['avatar'] = (string) $tblog['avatar-url'];
			$blog['url'] = (string) $tblog['url'];
			$blog['name'] = (string) $tblog['name'];

			$blogs[] = $blog;
		}

		return $blogs;
	}

	/**
	 * Fetch a subset of posts from a tumblr blog
	 *
	 * @param $start index to start at
	 * @param $count how many posts to get (max 50)
	 * @param $state can be empty for normal posts, or "draft", "queue", or "submission" to get those posts
	 * @returns false on error, array of posts on success
	 */
	function fetch_posts($url, $start=0, $count = 50, $email = null, $password = null, $state = null) {
		$url = trailingslashit($url).'api/read';
		$params = array(
			'start'=>$start,
			'num'=>$count,
		);
		if ( !empty($email) && !empty($password) ) {
			$params['email'] = $email;
			$params['password'] = $password;
		}

		if ( !empty($state) ) $params['state'] = $state;

		$options = array( 'body' => $params );

		// fetch the posts
		$out = wp_remote_post($url,$options);
		if (wp_remote_retrieve_response_code($out) != 200) return false;
		$body = wp_remote_retrieve_body($out);

		// parse the XML into something useful
		$xml = simplexml_load_string($body);

		if (!isset($xml->posts->post)) return false;

		$tposts = $xml->posts;
		$posts = array();
		foreach($tposts->post as $tpost) {
			$post = array();
			$post['tumblr_id'] = (string) $tpost['id'];
			$post['tumblr_url'] = (string) $tpost['url-with-slug'];
			$post['post_date'] = date( 'Y-m-d H:i:s', strtotime ( (string) $tpost['date'] ) );
			$post['post_date_gmt'] = date( 'Y-m-d H:i:s', strtotime ( (string) $tpost['date-gmt'] ) );
			$post['post_name'] = (string) $tpost['slug'];
			if ( isset($tpost['private']) ) $post['private'] = (string) $tpost['private'];
			if ( isset($tpost->{'tag'}) ) {
				$post['tags_input'] = array();
				foreach ( $tpost->{'tag'} as $tag )
					$post['tags_input'][] = rtrim( (string) $tag, ','); // Strip trailing Commas off it too.
			}

			// set the various post info for each special format tumblr offers
			// TODO reorg this as needed
			switch ((string) $tpost['type']) {
				case 'photo':
					$post['format'] = 'image';
					$post['media']['src'] = (string) $tpost->{'photo-url'}[0];
					$post['media']['link'] =(string) $tpost->{'photo-link-url'};
					$post['media']['width'] = (string) $tpost['width'];
					$post['media']['height'] = (string) $tpost['height'];
					$post['post_content'] = (string) $tpost->{'photo-caption'};
					if ( !empty( $tpost->{'photoset'} ) ) {
						$post['format'] = 'gallery';
						foreach ( $tpost->{'photoset'}->{'photo'} as $photo ) {
							$post['gallery'][] = array (
								'src'=>$photo->{'photo-url'}[0],
								'width'=>$photo['width'],
								'height'=>$photo['height'],
								'caption'=>$photo['caption'],
							);
						}
					}
					break;
				case 'quote':
					$post['format'] = 'quote';
					$post['post_content'] = (string) $tpost->{'quote-text'};
					$post['post_content'] .= "\n\n" . (string) $tpost->{'quote-source'};
					break;
				case 'link':
					$post['format'] = 'link';
					$linkurl = (string) $tpost->{'link-url'};
					$linktext = (string) $tpost->{'link-text'};
					$post['post_content'] = "<a href='{$linkurl}'>{$linktext}</a>";
					$post['post_title'] = (string) $tpost->{'link-description'};
					break;
				case 'conversation':
					$post['format'] = 'chat';
					$post['post_title'] = (string) $tpost->{'conversation-title'};
					$post['post_content'] = (string) $tpost->{'conversation-text'};
					break;
				case 'audio':
					$post['format'] = 'audio';
					$post['media']['filename'] = basename( (string) $tpost->{'authorized-download-url'} ) . '.mp3';
					$post['media']['audio'] = (string) $tpost->{'authorized-download-url'} .'?plead=please-dont-download-this-or-our-lawyers-wont-let-us-host-audio';
					$post['post_content'] = (string) $tpost->{'audio-player'} . "\n" . (string) $tpost->{'audio-caption'};
					if ( !empty($tpost->{'id3-artist'}) )
						$post['post_title'] = $tpost->{'id3-artist'} . ' - ' . $tpost->{'id3-title'};
					break;
				case 'video':
					$post['format'] = 'video';
					$post['post_content'] = '';
					if ( is_serialized( (string) $tpost->{'video-source'} ) ) {
						if ( preg_match('|\'(http://.*video_file.*)\'|U', $tpost->{'video-player'}[0], $matches) ) {
							$post['media']['video'] = $matches[1];
							$val = unserialize( (string) $tpost->{'video-source'} );
							$vidmeta = $val['o1'];
							$post['media']['filename'] = basename($post['media']['video']) . '.' . $vidmeta['extension'];
							$post['media']['width'] = $vidmeta['width'];
							$post['media']['height'] = $vidmeta['height'];
						}
					} else if ( false !== strpos( (string) $tpost->{'video-source'}, 'embed' ) ) {
						if ( preg_match_all('/<embed (.+?)>/', (string) $tpost->{'video-source'}, $matches) ) {
							foreach ($matches[1] as $match) {
								foreach ( wp_kses_hair($match, array('http')) as $attr)
									$embed[$attr['name']] = $attr['value'];
							}
							
							// special case for weird youtube vids
							$embed['src'] = preg_replace('|http://www.youtube.com/v/([a-zA-Z0-9_]+).*|i', 'http://www.youtube.com/watch?v=$1', $embed['src']);
							
							// TODO find other special cases, since tumblr is full of them
							
							$post['post_content'] = $embed['src'];
						}
						// Sometimes, video-source contains iframe markup.
						if ( preg_match( '/<iframe/', $tpost->{'video-source'} ) ) {
							$embed['src'] = preg_replace( '|<iframe.*src="http://www.youtube.com/embed/([a-zA-Z0-9_\-]+)\??.*".*</iframe>|', 'http://www.youtube.com/watch/?v=$1', $tpost->{'video-source'} );
							$post['post_content'] = $embed['src'];
						}
					
					} else {
						// @todo: See if the video-source is going to be oEmbed'able before adding the flash player
						// 1 Seems to be "original" size, with 0 being set otherwise.
						$post['post_content'] .= isset($tpost->{'video-player'}[1]) ? $tpost->{'video-player'}[1] : (string) $tpost->{'video-player'}[0];
					}
					$post['post_content'] .= "\n" . (string) $tpost->{'video-caption'};
					break;
				case 'answer':
					$post['post_title'] = (string) $tpost->{'question'};
					$post['post_content'] = (string) $tpost->{'answer'};
					break;
				case 'regular':
				default:
					$post['post_title'] = (string) $tpost->{'regular-title'};
					$post['post_content'] = (string) $tpost->{'regular-body'};
					break;
			}
			$posts[] = $post;
		}

		return $posts;
	}

	/**
	 * Fetch the Pages from a tumblr blog
	 *
	 * @returns false on error, array of page contents on success
	 */
	function fetch_pages($url, $email = null, $password = null) {
		$tumblrurl = trailingslashit($url).'api/pages';
		$params = array(
			'email'=>$email,
			'password'=>$password,
		);
		$options = array( 'body' => $params );

		// fetch the pages
		$out = wp_remote_post($tumblrurl,$options);
		if (wp_remote_retrieve_response_code($out) != 200) return false;
		$body = wp_remote_retrieve_body($out);

		// parse the XML into something useful
		$xml = simplexml_load_string($body);

		if (!isset($xml->pages)) return false;

		$tpages = $xml->pages;
		$pages = array();
		foreach($tpages->page as $tpage) {
			if ( !empty($tpage['title']) )
				$page['post_title'] = (string) $tpage['title'];
			else if (!empty($tpage['link-title']) )
				$page['post_title'] = (string) $tpage['link-title'];
			else
				$page['post_title'] = '';
			$page['post_name'] = str_replace( $url, '', (string) $tpage['url'] );
			$page['post_content'] = (string) $tpage;
			$page['tumblr_url'] = (string) $tpage['url'];
			$pages[] = $page;
		}

		return $pages;
	}
	
	function filter_format_post( $_post ) {
		if ( isset( $_post['meta']['attribution'] ) ) {
			$attribution = $_post['meta']['attribution'];
			if ( preg_match( '/^http[^ ]+$/', $_post['meta']['attribution'] ) )
				$attribution = sprintf( '<a href="%s">%s</a>', $_post['meta']['attribution'], $_post['meta']['attribution'] );
			$_post['post_content'] .= sprintf( '<div class="attribution">(<span>' . __( 'Source:', 'tumblr-importer' ) . '</span> %s)</div>', $attribution );
		}
		
		return $_post;
	}

	function tumblr_importer_metadata( $_post ) {
		if ( isset( $_post['meta'] ) ) {
			foreach ( $_post['meta'] as $key => $val ) {
				add_post_meta( $_post['ID'], 'tumblr_' . $key, $val );
			}
		}					
	}
	
	/*
	 * When galleries have no caption, the post_content field is empty, which
	 * along with empty title and excerpt causes the post not to insert.
	 * Here we override the default behavior.
	 */
	function filter_allow_empty_content( $maybe_empty, $_post ) {
		if ( 'gallery' == $_post['format'] )
			return false;
		
		return $maybe_empty;
	}
	
}
}
