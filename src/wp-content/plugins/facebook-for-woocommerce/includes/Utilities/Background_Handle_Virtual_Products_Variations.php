<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Utilities;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_4 as Framework;


/**
 * Background job handler to change all sync enabled and visible virtual products and virtual product variations to Sync and hide.
 *
 * @since 2.0.0
 */
class Background_Handle_Virtual_Products_Variations extends Framework\SV_WP_Background_Job_Handler {


	/**
	 * Background job constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->prefix = 'wc_facebook';
		$this->action = 'background_handle_virtual_products_variations';

		parent::__construct();
	}


	/**
	 * Processes job.
	 *
	 * This job continues to update products and product variations meta data until we run out of memory
	 * or exceed the time limit. There is no list of items to loop over.
	 *
	 * @since 2.0.0
	 *
	 * @param object $job
	 * @param int $items_per_batch number of items to process in a single request. Defaults to unlimited.
	 * @return object
	 */
	public function process_job( $job, $items_per_batch = null ) {

		if ( ! isset( $job->total ) ) {
			$job->total = $this->count_remaining_products();

			if ( empty( $job->total ) ) {

				// no products or variations need to be set to Sync and hide, do not display admin notice
				update_option( 'wc_facebook_background_handle_virtual_products_variations_skipped', 'yes' );
			}
		}

		if ( ! isset( $job->progress ) ) {
			$job->progress = 0;
		}

		$remaining_products = $job->total;
		$processed_products = 0;

		// set to Sync and hide until memory or time limit is exceeded
		while ( $processed_products < $remaining_products ) {

			$rows_updated = $this->sync_and_hide();

			$processed_products += $rows_updated;
			$job->progress      += $rows_updated;

			// update job progress
			$job = $this->update_job( $job );

			// memory or time limit reached
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				break;
			}
		}

		// job complete! :)
		if ( $this->count_remaining_products() === 0 ) {

			update_option( 'wc_facebook_background_handle_virtual_products_variations_complete', 'yes' );

			$this->complete_job( $job );
		}

		return $job;
	}


	/**
	 * Counts the number of virtual products or product variations with sync enabled and visible.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function count_remaining_products() {
		global $wpdb;

		$sql = "
			SELECT COUNT( posts.ID )
			FROM {$wpdb->posts} AS posts
			INNER JOIN {$wpdb->postmeta} AS virtual_meta ON ( posts.ID = virtual_meta.post_id AND virtual_meta.meta_key = '_virtual' AND virtual_meta.meta_value = 'yes' )
			LEFT JOIN {$wpdb->postmeta} AS sync_meta ON ( posts.ID = sync_meta.post_id AND sync_meta.meta_key = '_wc_facebook_sync_enabled' )
			LEFT JOIN {$wpdb->postmeta} AS visibility_meta ON ( posts.ID = visibility_meta.post_id AND visibility_meta.meta_key = 'fb_visibility' )
			WHERE posts.post_type IN ( 'product', 'product_variation' )
			AND ( sync_meta.meta_value IS NULL OR sync_meta.meta_value = 'yes' )
			AND ( visibility_meta.meta_value IS NULL OR visibility_meta.meta_value = 'yes' )
		";

		return (int) $wpdb->get_var( $sql );
	}


	/**
	 * Update rows in the postmeta table to hide in Catalog.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	private function sync_and_hide() {
		global $wpdb;

		$rows_inserted = 0;

		// get post IDs to update
		$sql = "
			SELECT DISTINCT( posts.ID )
			FROM {$wpdb->posts} AS posts
			INNER JOIN {$wpdb->postmeta} AS virtual_meta ON ( posts.ID = virtual_meta.post_id AND virtual_meta.meta_key = '_virtual' AND virtual_meta.meta_value = 'yes' )
			LEFT JOIN {$wpdb->postmeta} AS sync_meta ON ( posts.ID = sync_meta.post_id AND sync_meta.meta_key = '_wc_facebook_sync_enabled' )
			LEFT JOIN {$wpdb->postmeta} AS visibility_meta ON ( posts.ID = visibility_meta.post_id AND visibility_meta.meta_key = 'fb_visibility' )
			WHERE posts.post_type IN ( 'product', 'product_variation' )
			AND ( sync_meta.meta_value IS NULL OR sync_meta.meta_value = 'yes' )
			AND ( visibility_meta.meta_value IS NULL OR visibility_meta.meta_value = 'yes' )
			LIMIT 1000
		";

		$post_ids = $wpdb->get_col( $sql );

		if ( empty( $post_ids ) ) {

			facebook_for_woocommerce()->log( 'There are no products or products variations to update.' );

		} else {

			$values = [];

			foreach ( $post_ids as $post_id ) {

				$values[] = "('$post_id', 'fb_visibility', 'no')";
			}

			$values_str = implode( ',', $values );

			// we need to explicitly insert the metadata and set it to no, because not having it means it is visible
			$sql = "
				INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value )
					VALUES {$values_str}
			";

			$rows_inserted = $wpdb->query( $sql );

			if ( false === $rows_inserted ) {

				$message = sprintf( 'There was an error trying to set products and variations meta data. %s', $wpdb->last_error );

				facebook_for_woocommerce()->log( $message );
			}
		}

		return (int) $rows_inserted;
	}


	/**
	 * No-op
	 *
	 * @since 2.0.0
	 */
	protected function process_item( $item, $job ) {
		// void
	}


}
