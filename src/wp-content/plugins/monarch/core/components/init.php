<?php

if ( ! function_exists( 'et_core_init' ) ):
/**
 * {@see 'plugins_loaded' (9999999) Must run after cache plugins have been loaded.}
 */
function et_core_init() {
	ET_Core_PageResource::startup();

	if ( defined( 'ET_CORE_UPDATED' ) ) {
		global $wp_rewrite;
		add_action( 'shutdown', array( $wp_rewrite, 'flush_rules' ) );

		update_option( 'et_core_page_resource_remove_all', true );
	}

	$cache_dir = ET_Core_PageResource::get_cache_directory();

	if ( file_exists( $cache_dir . '/DONOTCACHEPAGE' ) ) {
		! defined( 'DONOTCACHEPAGE' ) ? define( 'DONOTCACHEPAGE', true ) : '';
		@unlink( $cache_dir . '/DONOTCACHEPAGE' );
	}

	if ( get_option( 'et_core_page_resource_remove_all' ) ) {
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );
	}

	if ( ! wp_next_scheduled( 'et_core_page_resource_auto_clear' ) ) {
		wp_schedule_event( time() + MONTH_IN_SECONDS, 'monthly', 'et_core_page_resource_auto_clear' );
	}
}
endif;


if ( ! function_exists( 'et_core_clear_wp_cache' ) ):
function et_core_clear_wp_cache( $post_id = '' ) {
	if ( ! wp_doing_cron() && ! et_core_security_check_passed( 'edit_posts' ) ) {
		return;
	}

	try {
		// Cache Plugins
		// Comet Cache
		if ( is_callable( 'comet_cache::clear' ) ) {
			comet_cache::clear();
		}

		// WP Rocket
		if ( function_exists( 'rocket_clean_post' ) ) {
			if ( '' !== $post_id ) {
				rocket_clean_post( $post_id );
			} else if ( function_exists( 'rocket_clean_domain' ) ) {
				rocket_clean_domain();
			}
		}

		// W3 Total Cache
		if ( has_action( 'w3tc_flush_post' ) ) {
			'' !== $post_id ? do_action( 'w3tc_flush_post', $post_id ) : do_action( 'w3tc_flush_posts' );
		}

		// WP Super Cache
		if ( function_exists( 'wp_cache_debug' ) && defined( 'WPCACHEHOME' ) ) {
			include_once WPCACHEHOME . 'wp-cache-phase1.php';
			include_once WPCACHEHOME . 'wp-cache-phase2.php';

			if ( '' !== $post_id && function_exists( 'clear_post_supercache' ) ) {
				clear_post_supercache( $post_id );
			} else if ( '' === $post_id && function_exists( 'wp_cache_clear_cache_on_menu' ) ) {
				wp_cache_clear_cache_on_menu();
			}
		}

		// WP Fastest Cache
		if ( isset( $GLOBALS['wp_fastest_cache'] ) ) {
			if ( '' !== $post_id && method_exists( $GLOBALS['wp_fastest_cache'], 'singleDeleteCache' ) ) {
				$GLOBALS['wp_fastest_cache']->singleDeleteCache( $post_id );
			} else if ( '' === $post_id && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
				$GLOBALS['wp_fastest_cache']->deleteCache();
			}
		}

		// WordPress Cache Enabler
		if ( has_action( 'ce_clear_cache' ) ) {
			'' !== $post_id ? do_action( 'ce_clear_post_cache', $post_id ) : do_action( 'ce_clear_cache' );
		}

		// LiteSpeed Cache
		if ( is_callable( 'LiteSpeed_Cache::get_instance' ) ) {
			$litespeed = LiteSpeed_Cache::get_instance();

			if ( '' !== $post_id && method_exists( $litespeed, 'purge_post' ) ) {
				$litespeed->purge_post( $post_id );
			} else if ( '' === $post_id && method_exists( $litespeed, 'purge_all' ) ) {
				$litespeed->purge_all();
			}
		}

		// LiteSpeed Cache v1.1.3+
		if ( '' !== $post_id && function_exists( 'litespeed_purge_single_post' ) ) {
			litespeed_purge_single_post( $post_id );
		} else if ( '' === $post_id && is_callable( 'LiteSpeed_Cache_API::purge_all' ) ) {
			LiteSpeed_Cache_API::purge_all();
		}

		// Hyper Cache
		if ( class_exists( 'HyperCache' ) && isset( HyperCache::$instance ) ) {
			if ( '' !== $post_id && method_exists( HyperCache::$instance, 'clean_post' ) ) {
				HyperCache::$instance->clean_post( $post_id );
			} else if ( '' === $post_id && method_exists( HyperCache::$instance, 'clean' ) ) {
				HyperCache::$instance->clean_post( $post_id );
			}
		}

		// Hosting Provider Caching
		// Pantheon Advanced Page Cache
		$pantheon_clear     = 'pantheon_wp_clear_edge_keys';
		$pantheon_clear_all = 'pantheon_wp_clear_edge_all';
		if ( function_exists( $pantheon_clear ) || function_exists( $pantheon_clear_all ) ) {
			if ( '' !== $post_id && function_exists( $pantheon_clear ) ) {
				pantheon_wp_clear_edge_keys( array( "post-{$post_id}" ) );
			} else if ( '' === $post_id && function_exists( $pantheon_clear_all ) ) {
				pantheon_wp_clear_edge_all();
			}
		}

		// Siteground
		if ( isset( $GLOBALS['sg_cachepress_supercacher'] ) ) {
			global $sg_cachepress_supercacher;

			if ( is_object( $sg_cachepress_supercacher ) && method_exists( $sg_cachepress_supercacher, 'purge_cache' ) ) {
				$sg_cachepress_supercacher->purge_cache( true );
			}
		}

		// WP Engine
		if ( class_exists( 'WpeCommon' ) ) {
			is_callable( 'WpeCommon::purge_memcached' ) ? WpeCommon::purge_memcached() : '';
			is_callable( 'WpeCommon::clear_maxcdn_cache' ) ? WpeCommon::clear_maxcdn_cache() : '';
			is_callable( 'WpeCommon::purge_varnish_cache' ) ? WpeCommon::purge_varnish_cache() : '';

			if ( is_callable( 'WpeCommon::instance' ) && $instance = WpeCommon::instance() ) {
				method_exists( $instance, 'purge_object_cache' ) ? $instance->purge_object_cache() : '';
			}
		}

		// Bluehost
		if ( class_exists( 'Endurance_Page_Cache' ) ) {
			wp_doing_ajax() ? ET_Core_LIB_BluehostCache::get_instance()->clear( $post_id ) : do_action( 'epc_purge' );
		}

		// Complimentary Performance Plugins
		// Autoptimize
		if ( is_callable( 'autoptimizeCache::clearall' ) ) {
			autoptimizeCache::clearall();
		}

	} catch( Exception $err ) {
		ET_Core_Logger::error( 'An exception occurred while attempting to clear site cache.' );
	}
}
endif;


if ( ! function_exists( 'et_core_get_nonces' ) ):
/**
 * Returns the nonces for this component group.
 *
 * @return string[]
 */
function et_core_get_nonces() {
	static $nonces = null;

	return $nonces ? $nonces : $nonces = array(
		'clear_page_resources_nonce' => wp_create_nonce( 'clear_page_resources' ),
	);
}
endif;


if ( ! function_exists( 'et_core_page_resource_auto_clear' ) ):
function et_core_page_resource_auto_clear() {
	ET_Core_PageResource::remove_static_resources( 'all', 'all' );
}
add_action( 'switch_theme', 'et_core_page_resource_auto_clear' );
add_action( 'activated_plugin', 'et_core_page_resource_auto_clear', 10, 0 );
add_action( 'deactivated_plugin', 'et_core_page_resource_auto_clear', 10, 0 );
add_action( 'et_core_page_resource_auto_clear', 'et_core_page_resource_auto_clear' );
endif;


if ( ! function_exists( 'et_core_page_resource_clear' ) ):
/**
 * Ajax handler for clearing cached page resources.
 */
function et_core_page_resource_clear() {
	et_core_security_check( 'manage_options', 'clear_page_resources' );

	if ( empty( $_POST['et_post_id'] ) ) {
		et_core_die();
	}

	$post_id = sanitize_key( $_POST['et_post_id'] );
	$owner   = sanitize_key( $_POST['et_owner'] );

	ET_Core_PageResource::remove_static_resources( $post_id, $owner );
}
add_action( 'wp_ajax_et_core_page_resource_clear', 'et_core_page_resource_clear' );
endif;


if ( ! function_exists( 'et_core_page_resource_fallback' ) ):
/**
 * Handles page resource fallback requests.
 */
function et_core_page_resource_fallback() {
	if ( ! isset( $_GET['et_core_page_resource'] ) ) {
		return;
	}

	if ( is_admin() && ! is_customize_preview() ) {
		return;
	}

	$resource_id = sanitize_text_field( $_GET['et_core_page_resource'] );
	$pattern     = '/et-(\w+)-([\w-]+)-cached-inline-(?>styles|scripts)(global|\d+)/';
	$has_matches = preg_match( $pattern, $resource_id, $matches );

	if ( $has_matches ) {
		$resource = et_core_page_resource_get( $matches[1], $matches[2], $matches[3] );

		if ( $resource->has_file() ) {
			wp_redirect( $resource->URL );
			die();
		}
	}

	status_header( 404 );
	nocache_headers();
	die();
}
add_action( 'init', 'et_core_page_resource_fallback', 0 );
endif;


if ( ! function_exists( 'et_core_page_resource_get' ) ):
/**
 * Get a page resource instance.
 *
 * @param string     $owner    The owner of the instance (core|divi|builder|bloom|monarch|custom).
 * @param string     $slug     A string that uniquely identifies the resource.
 * @param string|int $post_id  The post id that the resource is associated with or `global`.
 *                             If `null`, the return value of {@link get_the_ID()} will be used.
 * @param string     $type     The resource type (style|script). Default: `style`.
 * @param string     $location Where the resource should be output (head|footer). Default: `head-late`.
 *
 * @return ET_Core_PageResource
 */
function et_core_page_resource_get( $owner, $slug, $post_id = null, $priority = 10, $location = 'head-late', $type = 'style' ) {
	$post_id = $post_id ? $post_id : et_core_page_resource_get_the_ID();
	$global  = 'global' === $post_id ? '-global' : '';
	$_slug   = "et-{$owner}-{$slug}{$global}-cached-inline-{$type}s";

	$all_resources = ET_Core_PageResource::get_resources();

	return isset( $all_resources[ $_slug ] )
		? $all_resources[ $_slug ]
		: new ET_Core_PageResource( $owner, $slug, $post_id, $priority, $location, $type );
}
endif;


if ( ! function_exists( 'et_core_page_resource_maybe_output_fallback_script' ) ):
function et_core_page_resource_maybe_output_fallback_script() {
	if ( is_admin() && ! is_customize_preview() ) {
		return;
	}

	if ( function_exists( 'et_get_option' ) && 'off' === et_get_option( 'et_pb_static_css_file', 'on' ) ) {
		return;
	}

	$IS_SINGULAR = et_core_page_resource_is_singular();
	$POST_ID     = $IS_SINGULAR ? et_core_page_resource_get_the_ID() : 'global';

	if ( $IS_SINGULAR && 'off' === get_post_meta( $POST_ID, '_et_pb_static_css_file', true ) ) {
		return;
	}

	$SITE_URL = get_site_url();
	$SCRIPT   = file_get_contents( ET_CORE_PATH . 'admin/js/page-resource-fallback.min.js' );

	print( "<script>var et_site_url='{$SITE_URL}';var et_post_id='{$POST_ID}';{$SCRIPT}</script>" );
}
add_action( 'wp_head', 'et_core_page_resource_maybe_output_fallback_script', 0 );
endif;


if ( ! function_exists( 'et_core_page_resource_get_the_ID' ) ):
function et_core_page_resource_get_the_ID() {
	static $post_id = null;

	if ( is_int( $post_id ) ) {
		return $post_id;
	}

	return $post_id = apply_filters( 'et_core_page_resource_current_post_id', get_the_ID() );
}
endif;


if ( ! function_exists( 'et_core_page_resource_is_singular' ) ):
function et_core_page_resource_is_singular() {
	return apply_filters( 'et_core_page_resource_is_singular', is_singular() );
}
endif;


if ( ! function_exists( 'et_core_page_resource_register_fallback_query' ) ):
function et_core_page_resource_register_fallback_query() {
	add_rewrite_tag( '%et_core_page_resource%', '([\w\d-]+)' );
}
add_action( 'init', 'et_core_page_resource_register_fallback_query', 11 );
endif;


if ( ! function_exists( 'et_debug' ) ):
function et_debug( $msg, $bt_index = 4, $log_ajax = true ) {
	ET_Core_Logger::debug( $msg, $bt_index, $log_ajax );
}
endif;


if ( ! function_exists( 'et_error' ) ):
function et_error( $msg, $bt_index = 4 ) {
	ET_Core_Logger::error( $msg, $bt_index );
}
endif;
