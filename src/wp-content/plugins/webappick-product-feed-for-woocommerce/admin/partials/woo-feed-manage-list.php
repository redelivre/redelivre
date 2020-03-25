<?php
/**
 * Feed List View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */
$myListTable = new Woo_Feed_Manage_list();
$myListTable->prepare_items();
$limit       = get_option( 'woo_feed_per_batch', 200 );
$fileName    = '';
$message     = array();
global $regenerating, $regeneratingName, $plugin_page;
$regenerating = false;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ( isset( $_GET['feed_created'] ) || isset( $_GET['feed_updated'] ) ) && isset( $_GET['feed_regenerate'] ) && 1 == $_GET['feed_regenerate'] ) {
	// filename must be wf_config+XXX format
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$fileName         = isset( $_GET['feed_name'] ) && ! empty( $_GET['feed_name']) ? sanitize_text_field( $_GET['feed_name'] ) : ''; // trigger feed regenerate...
	$fileName         = str_replace( array( 'wf_feed_', 'wf_config' ), '', $fileName );
	$regeneratingName = $fileName;
	$fileName         = 'wf_config' . $fileName; // to be safe...
	$regenerating     = true;
}

// Checking woo version to run different version of feed processing
$woo32 = 'no';
if ( woo_feed_wc_version_check( 3.2 ) ) {
	$woo32 = 'yes';
}
?>
<div class="wrap wapk-admin">
	<div class="wapk-section">
		<h1 class="wp-heading-inline"><?php _e( 'Manage Feed', 'woo-feed' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=webappick-new-feed' ) ); ?>" class="page-title-action"><?php _e( 'New Feed', 'woo-feed' ); ?></a>
		<hr class="wp-header-end">
		<?php WPFFWMessage()->displayMessages(); ?>
		<div id="feed_progress_table" style="display: none;">
			<table class="table widefat fixed">
				<thead>
				<tr>
					<th><b><?php esc_html_e( 'Generating Product Feed', 'woo-feed' ); ?></b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
						<div class="feed-progress-container">
							<div class="feed-progress-bar" >
								<span class="feed-progress-bar-fill"></span>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="float: left;"><b style='color: darkblue;'><i class='dashicons dashicons-sos wpf_spin'></i></b>&nbsp;&nbsp;&nbsp;</div>
						<div class="feed-progress-status"></div>
						<div class="feed-progress-percentage"></div>
					</td>
				</tr>
				</tbody>
			</table>
			<br>
		</div>
		<table class=" widefat fixed">
			<thead>
			<tr>
				<th><b><?php esc_html_e( 'Auto Update Feed Interval', 'woo-feed' ); ?></b></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>
					<form action="" method="post">
						<?php wp_nonce_field( 'wf_schedule', 'wf_schedule_nonce' ); ?>
						<label for="wf_schedule"><b><?php _e( 'Interval', 'woo-feed' ); ?></b></label>
						<select name="wf_schedule" id="wf_schedule">
							<?php
							$interval = get_option( 'wf_schedule' );
							foreach ( woo_feed_get_schedule_interval_options() as $k => $v ) {
								printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $interval, $k, false ), esc_html( $v ) );
							}
							?>
						</select>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Interval', 'woo-feed' ); ?></button>
					</form>
				</td>
			</tr>
			</tbody>
		</table>
		<form id="contact-filter" method="post">
			<!-- For plugins, we also need to ensure that the form posts back to our current page -->
			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>"/>
			<?php // $myListTable->search_box('search', 'search_id'); ?>
			<!-- Now we can render the completed list table -->
			<?php $myListTable->display(); ?>
		</form>
	</div>
	<!--suppress JSUnresolvedVariable, ES6ConvertVarToLetConst -->
	<script type="text/javascript">
        (function( $, window, document, opts ) {
            'use strict';
            /**
             * All of the code for your admin-facing JavaScript source
             * should reside in this file.
             *
             * Note: It has been assumed you will write jQuery code here, so the
             * $ function reference has been prepared for usage within the scope
             * of this function.
             *
             * This enables you to define handlers, for when the DOM is ready:
             *
             * $(function() {
             * });
             *
             */
            /**
             * On Window Load
             * @TODO move this to js file so we can minify this.
             */
            $( window ).load(function() {
                // noinspection ES6ConvertVarToLetConst
                var feedProgress = {
                        table: $( '#feed_progress_table' ),
                        status: $( '.feed-progress-status' ),
                        percentage: $( '.feed-progress-percentage' ),
                        bar: $( '.feed-progress-bar-fill' ),
                        barProgress: 10, // Variable responsible to hold progress bar width
                    },
                    isRegenerating = false,
                    regenerateBtn = $( '.wpf_regenerate' ),
                    fileName = "<?php echo isset( $fileName ) ? esc_attr( $fileName ) : ''; ?>", // wf_config+xxxx
                    limit = <?php echo ( $limit ) ? absint( $limit ) : 200; ?>;
                var woo32 = "<?php echo esc_attr( $woo32 ); ?>";
                // feed delete alert
                $( '.single-feed-delete' ).click( function ( event ) {
                    //@TODO move to js file with proper i18n entries.
                    event.preventDefault();
                    if ( confirm( '<?php _e( 'Are You Sure to Delete?', 'woo-feed' ); ?>' ) ) {
                        window.location.href = jQuery(this).attr('val');
                    }
                });

                // bulk delete alert
                $('#doaction, #doaction2').click(function () {
                    //@TODO move to js file with proper i18n entries.
                    return confirm('<?php _e( 'Are You Sure to Delete?', 'woo-feed' ); ?>');
                });
                // generate feed
                if( fileName !== '' ) {
                    feedProgress.table.show();
                    generate_feed();
                }
                //==================Manage Feed==============================
                // Feed Regenerate
                regenerateBtn.on( "click", function ( e ) {
                    e.preventDefault();
                    // noinspection ES6ConvertVarToLetConst
                    var el = $( this );
                    if( el.hasClass('disabled') || isRegenerating === true ) return;
                    isRegenerating = true;
                    regenerateBtn.addClass('disabled');
                    fileName = el.attr( 'id' ).replace( "wf_feed_", "wf_config" );
                    // noinspection JSUnresolvedVariable
                    el.attr( 'aria-label', opts.regenerate ).attr( 'title', opts.regenerate );
                    el.find('span').addClass('wpf_spin_reverse');
                    if( fileName ) {
                        feedProgress.table.show();
                        generate_feed();
                    }
                });

                /*#######################################################
				#######-------------------------------------------#######
				#######    Ajax Feed Making Functions Start       #######
				#######-------------------------------------------#######
				#########################################################
				*/

                function showFeedProgress( color ){
                    feedProgress.bar.css( {
                        width: feedProgress.barProgress + '%',
                        background: color || "#3DC264",
                    } );
                    feedProgress.percentage.text( Math.round( feedProgress.barProgress ) + '%' );
                }

                function generate_feed() {
                    console.log( "Counting Total Products" );
                    feedProgress.status.text( "Fetching products." );
                    showFeedProgress();
                    $.ajax({
                        url : opts.wpf_ajax_url,
                        type : 'post',
                        data : {
                            _ajax_nonce: opts.nonce,
                            action: "get_product_information",
                            feed: fileName,
                            limit: limit,
                        },
                        success : function( response ) {
                            console.log( response );
                            if(response.success) {
                                if (woo32 === 'yes') {
                                    processFeed_v3(response.data.product);
                                    var total = response.data.total;
                                    console.log("Total " + total + " Products found.");
                                    feedProgress.status.text( "Processing Products..." );
                                } else {
                                    feedProgress.status.text( "Delivering Feed Configuration." );
                                    processFeed( parseInt( response.data.product ) );
                                    //feedProgress.status.text("Total "+products+" products found.");
                                    feedProgress.status.text( "Processing Products..." );
                                }

                            }else{
                                feedProgress.status.text(response.data.message);
                                showFeedProgress( 'red' );
                            }
                        }
                    });
                }
                function processFeed_v3( batches, n ) {
                    var totalBatch = batches.length;
                    var progressBatch = 90 / totalBatch;
                    if ( typeof(n) === 'undefined' ) n = 0;

                    var batch = batches[n];
                    var productBatch = n + 1;
                    feedProgress.status.text( "Processing Batch " + productBatch + " of " + totalBatch);
                    showFeedProgress();
                    console.log( "Processing Batch " + productBatch + " of " + totalBatch );

                    $.ajax({
                        url: opts.wpf_ajax_url,
                        type: 'post',
                        data: {
                            _ajax_nonce: opts.nonce,
                            action: "make_batch_feed",
                            feed: fileName,
                            products: batch,
                            loop: n,
                        },
                        success: function (response) {
                            console.log( "Batch "+ productBatch +" Completed.");
                            console.log(response);

                            if (productBatch < totalBatch) {
                                n = n + 1;
                                processFeed_v3(batches, n);

                                feedProgress.barProgress = feedProgress.barProgress + progressBatch;
                                showFeedProgress();
                            }

                            if (productBatch === totalBatch) {
                                console.log( "Saving feed file.");
                                feedProgress.status.text("Saving feed file.");
                                save_feed_file();
                            }
                        },
                        error: function (response) {
                            console.log(response);
                        }
                    });
                }
                function processFeed( n, offset, batch ) {
                    if ( typeof( offset ) === 'undefined' ) offset = 0;
                    if ( typeof( batch ) === 'undefined' ) batch = 0;
                    var batches = Math.ceil( n/limit ), progressBatch = 90 / batches;
                    console.log( ( limit*batch ) + " out of " + n + " products processed." );
                    feedProgress.status.text( "Processing products..." + Math.round( feedProgress.barProgress ) + "%" );
                    if( batch < batches ) {
                        console.log( "Processing Batch " + batch + " of " + batches );
                        $.ajax({
                            url : opts.wpf_ajax_url,
                            type : 'post',
                            data : {
                                _ajax_nonce: opts.nonce,
                                action: "make_batch_feed",
                                limit: limit,
                                offset: offset,
                                feed: fileName
                            },
                            success : function(response) {
                                console.log( response );
                                if( response.success ) {
                                    if( response.data.products === "yes" ) {
                                        offset = offset+limit;
                                        batch++;
                                        setTimeout( function(){
                                            processFeed( n, offset, batch );
                                        }, 2000 );
                                        feedProgress.barProgress = feedProgress.barProgress + progressBatch;
                                        showFeedProgress();
                                    } else if( n > offset ) {
                                        offset = offset+limit;
                                        batch++;
                                        processFeed( n, offset, batch );
                                        feedProgress.barProgress = feedProgress.barProgress + progressBatch;
                                        showFeedProgress();
                                    }else{
                                        feedProgress.status.text( "Saving feed file." );
                                        save_feed_file();
                                    }
                                }
                            },
                            error:function (response) {
                                if( response.status !== "200" ) {
                                    offset = (offset-limit)+10;
                                    batch++;
                                    processFeed( n, offset, batch );
                                    feedProgress.barProgress = feedProgress.barProgress + progressBatch;
                                    showFeedProgress();
                                }
                                console.log(response);
                            }
                        });
                    }else{
                        feedProgress.status.text("Saving feed file.");
                        save_feed_file();
                    }
                }

                /**
                 * Save feed file into WordPress upload directory
                 * after successfully processing the feed
                 */
                function save_feed_file() {
                    $.ajax({
                        url : opts.wpf_ajax_url,
                        type : 'post',
                        data : {
                            _ajax_nonce: opts.nonce,
                            action: "save_feed_file",
                            feed: fileName
                        },
                        success : function( response ) {
                            console.log( response );
                            if( response.success ) {
                                feedProgress.barProgress = 100;
                                showFeedProgress();
                                feedProgress.status.text( response.data.message );
                                regenerateBtn.val( 'Regenerate' );
                                regenerateBtn.disabled( false );
                                window.location.href = "<?php echo esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ); ?>&link=" + response.data.url + "&cat=" + response.data.cat;
                            }else{
                                showFeedProgress( "red" );
                                feedProgress.status.text( response.data.message );
                            }
                        },
                        error:function ( response ) {
                            console.log( response );
                            feedProgress.status.text( "Failed to save feed file." );
                        }
                    });
                }

                /*########################################################
				#######-------------------------------------------#######
				#######    Ajax Feed Making Functions End         #######
				#######-------------------------------------------#######
				#########################################################
				*/
            });
        })( jQuery, window, document, wpf_ajax_obj );
	</script>
</div>
