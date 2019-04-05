(function($){

	maybe_set_location_hash_after_oauth_redirect();

	$( document ).ready( function() {
		var follow_delete_counter = 0,
			share_delete_counter = 0,
			url = window.location.href,
			tab_link = url.split( '#tab_' )[1],
			$et_modal_window;

		//Check whether tab_id specified in the URL, if not - set the first tab from the navigation as a current tab.
		if ( undefined != tab_link ) {
			var section = ( -1 != tab_link.indexOf( 'header' ) ) ? 'header' : 'side';

			set_current_tab( tab_link, section );
		} else {
			set_current_tab ( $( 'div#et_social_navigation > ul > li > ul > li > a' ).first().attr( 'id' ), 'side' );
		}

		// show/hide appropriate api settings depending on selected networks
		reset_api_visibility();

		// Execute the stats graphs generating function via Ajax, to speed up the dashboard loading
		$.ajax({
				type: "POST",
				url: monarchSettings.ajaxurl,
				data: {
						action : 'get_share_stats_graphs',
						get_stats_nonce : monarchSettings.get_stats
					},
				beforeSend: function ( xhr ){
					$( '#et_social_stats_container .spinner' ).addClass( 'spinner_visible' );
				},
				success: function( data ){
					$( '#et_social_stats_container .spinner' ).removeClass( 'spinner_visible' );
					$( '#et_social_stats_container' ).append( data );

					calculate_bars_size();
				}
		});

		$( '.et_social_notice_link' ).click( function() {
			var $this = $( this ),
				tab_open = $this.attr( 'href' ).split( '#tab_' )[1];

			set_current_tab( tab_open, 'side' );
		} );

		$( '.et_social_api_trigger' ).click( function() {
			reset_api_visibility();
		} );

		// checks which social networks selected and adds et_social_visible_api class to required api settings.
		function reset_api_visibility() {
			var visible_options_count = 0,
				$api_setting_containers = $( '.et_social_api_setting' ),
				$all_settings_container = $api_setting_containers.closest( '.et_social_row' ),
				$social_network_row = $( '.follow_networks_networks_sorting.et_social_row' ).find( '.et_social_network' );

			$api_setting_containers.removeClass( 'et_social_visible_api' );

			if ( $social_network_row.length ) {
				$social_network_row.each( function() {
					var network_name = $( this ).data( 'name' );

					switch( network_name ) {
						case 'vimeo' :
							$( '.et_social_vimeo_api').addClass( 'et_social_visible_api' );
							visible_options_count++;
							break;
						case 'instagram' :
							$( '.et_social_instagram_api').addClass( 'et_social_visible_api' );
							visible_options_count++;
							break;
						case 'linkedin' :
							$( '.et_social_linkedin_api').addClass( 'et_social_visible_api' );
							visible_options_count++;
							break;
						case 'twitter' :
							$( '.et_social_twitter_api').addClass( 'et_social_visible_api' );
							visible_options_count++;
							break;
						case 'youtube' :
							$( '.et_social_youtube_api').addClass( 'et_social_visible_api' );
							visible_options_count++;
							break;
					}
				});
			}

			if ( 0 === visible_options_count ) {
				$all_settings_container.addClass( 'et_social_hidden_api' );
			} else {
				$all_settings_container.removeClass( 'et_social_hidden_api' );
			}
		}

		function calculate_bars_size() {
			// Calculate and change the height for the graph bars in each row
			$( '.et_social_tab_content_header_stats ul.et_social_graph' ).each( function() {
				var this_el = $( this );
				if ( this_el.hasClass( 'et_social_graph_alltime' ) ) {
					resize ( this_el, false );
				} else {
					resize ( this_el, true );
				}
			});

			$( '.et_social_tab_content_header_stats ul li > div > div' ).each( resize_network );
		}

		/* Create checkbox/toggle UI based off form data */

		$("div.et_social_multi_selectable").click(function() {
			var checkbox = $(this).children("input");
			checkbox.prop('checked') == false ? checkbox.prop('checked', true) : checkbox.prop('checked', false);
			$(this).toggleClass( "et_social_selected et_social_just_selected" );
			$(this).mouseleave(function() {
			 	$(this).removeClass( "et_social_just_selected" );
			});
		});

		$("div.et_social_single_selectable").click(function() {
			var tabs = $(this).parents(".et_social_row").find("div.et_social_single_selectable");
			var inputs = $(this).parents(".et_social_row").find("input");
			tabs.removeClass( "et_social_selected" );
			inputs.prop('checked', false);
			$(this).toggleClass( "et_social_selected" );
			$(this).children("input").prop('checked',true);
		});

		function toggle() {
			$(this).parent().addClass("et_social_selected");
		}

		$("input.et_social_toggle[checked='checked']").each(toggle);

		/* Tabs System */

		//Function which sets the current tab in navigation menu
		function set_current_tab( $tab_id, $section ) {
			var tab = $( 'div.' + $tab_id );
			var current = $( 'a.current' );

			$( current ).removeClass( 'current' );
			$( 'a#' + $tab_id ).addClass( 'current' );

			$( 'div.et_social_tab_content' ).removeClass( 'et_tab_selected' );
			$( tab ).addClass( 'et_tab_selected' );

			//If the tab is in opened section, then we don't need to toggle current_section class
			if ( '' != $section ) {
				var current_section = $( 'ul.current_section' );

				current_section.removeClass( 'current_section' );
			}

			//Hide save button from the header section since there is nothing to save
			if ( 'header' == $section ) {
				$( '.et_social_save_changes' ).css( { 'display' : 'none' } );
			}

			if ( 'side' == $section ) {
				$( 'a#' + $tab_id ).parent().parent().toggleClass( 'current_section' );
				$( '.et_social_save_changes' ).css( { 'display' : 'block' } );
			}
		}

		// Adding href to tabs of each parent element to store the link of current tab in URL properly
		$( 'div#et_social_navigation > ul > li > a' ).each( function() {
			var $this_el = $( this );
			$this_el.attr( 'href', '#tab_' + $this_el.parent().find( 'ul > li > a' ).first().attr( 'id' ) );
		});

		$( 'div#et_social_navigation > ul > li > a' ).click( function() {
			set_current_tab ( $( this ).parent().find( 'ul > li > a' ).first().attr( 'id' ), 'side');
		});

		$( '#et_social_navigation ul li ul li > a' ).click( function() {
			set_current_tab ( $( this ).attr( 'id' ), '' );
		});

		$( 'div#et_social_header > ul > li > a' ).click( function() {
			set_current_tab ( $( this ).attr( 'id' ), 'header' );
		});

		/* Build a uniform statistics graph based on data input*/

		function resize( $current_ul, $is_height ) {
			var bar_array = '';

			var bar_array = $( $current_ul ).find( 'li > div' ).map( function() {
				return $( this ).attr( 'value' );
			}).get();
			var bar_size = Math.max.apply( Math, bar_array );

			$( $current_ul ).find( 'li > div' ).each( function() {
				set_bar_size( $( this ), bar_size, $is_height );
			});
		}

		function set_bar_size( $element, $bar_size, $is_height ) {
			var value = $( $element ).attr( 'value' );
			var li_size = ( value / $bar_size * 100 ) + '%';
			if ( true == $is_height ) {
				$( $element ).animate( { height: li_size }, 700 );
			} else {
				$( $element ).animate( { width: li_size }, 700 );
			}
		}

		function resize_network() {
			var value = $(this).attr("value");
			var parent_value = $(this).parent().attr("value");
			var new_height = value / parent_value * 100;
			var percentage = new_height + "%";
			var type = $(this).attr("type");
			$(this).css("height", percentage);
			$(this).addClass("et_social_" + type)
		}

		$( '.et_social_tab_content_header_stats' ).on( 'mouseenter', '.et_social_hover_item', function(){
			var $this_el = $( this );
			var value = $this_el.attr( 'value' );
			var type = $this_el.attr( 'type' );
			var action = $this_el.data( 'action' );
			var display_message = 'like' == action ? monarchSettings.like_text : monarchSettings.share_text;

			$( '<div class="et_social_tooltip"><strong>' + type + '</strong><br>' + display_message + value + '</div>' ).appendTo( $this_el );

		}).on( 'mouseleave', '.et_social_hover_item', function(){
			$( this ).find( 'div.et_social_tooltip' ).remove();
		});

		/* jQuery Sortable */

		function recalculate_networks_order( $section_id ) {
			var order = [];
			$item =  $( '#' + $section_id ).find('.et_social_network');
			$current_id = 0;
			$item.each(function(){
				$network = $(this);
				$network.find('.input_label').attr('name', 'et_social[' + $network.data('area') + '][label][' + $current_id + ']');
				$network.find('.input_name').attr('name', 'et_social[' + $network.data('area') + '][username][' + $current_id + ']');
				$network.find('.input_class').attr('name', 'et_social[' + $network.data('area') + '][class][' + $current_id + ']');
				$network.find('.input_count').attr('name', 'et_social[' + $network.data('area') + '][count][' + $current_id + ']');
				$network.find('.input_cid').attr('name', 'et_social[' + $network.data('area') + '][client_id][' + $current_id + ']');
				$network.find('.input_client_name').attr('name', 'et_social[' + $network.data('area') + '][client_name][' + $current_id + ']');
				$current_id ++;
				order.push($(this).find('.input_label').attr('name'));
			});
		}

		$( function() {
			$( ".et_social_sortable" ).sortable({
				placeholder: "et_social_sortable_placeholder",
				update: function ( event, ui ) {
								$items = $( this );
								recalculate_networks_order( $items.attr( 'id' ) );
						}
			});
		});

		function generate_modal_window( $area, $network_to_deselect, $disply_modal ) {
			var modal_class = '.et_social_networks_modal.' + $area;

			if ( ! $( modal_class ).length ) {
				$.ajax({
						type: 'POST',
						url: monarchSettings.ajaxurl,
						data: {
								action : 'generate_modal_ajax',
								area : $area,
								network_modal_nonce : monarchSettings.network_modal
							},
						success: function( data ){
									$( '#wpwrap' ).append( data );

									if ( '' !== $network_to_deselect ){
										$( modal_class ).find( $network_to_deselect ).removeClass( 'et_social_selectednetwork' ).addClass( 'et_social_nonselectednetwork' );
									}

									if( true == $disply_modal ){
										$( modal_class ).css( { 'z-index' : '99999', 'display' : 'block' } );
									}

									$et_modal_window = $( modal_class );

									et_change_modal_window_height();
								}
					});
			}

			if( true == $disply_modal ){
				$( modal_class ).css( { 'z-index' : '99999', 'display' : 'block' } );
			}
		}

		function et_change_modal_window_height() {
			if ( 1 <= $( '.et_social_networks_modal' ).length ) {
				var $parent_div			= $et_modal_window.closest( '.et_social_networks_modal' ),
					$modal_header		= $parent_div.find( '.et_social_modal_header' ),
					$modal_footer		= $parent_div.find( '.et_social_modal_footer' ),
					$networks_container	= $et_modal_window.find( '.social_icons_container' ),
					paddings			= parseInt( $parent_div.css( 'paddingTop' ) ) + $modal_header.innerHeight() + $modal_footer.innerHeight(),
					height_percent		= 0.7;

				$networks_container.css( 'height', ( $(window).height() - paddings ) * height_percent );
			}
		}

		$( window ).resize( et_change_modal_window_height );

		$( '.et_social_addnetwork' ).click( function(){
			var area = $( this ).data( 'area' );

				generate_modal_window( area, '', true );

			return false;
		});

		$( '#et_social_shortcode_button' ).click( function(){
			var options_shortcode = $( '#et_monarch_options' ).serialize();
			$spinner_shortcode = $( this ).parent().find( '.spinner' );
			$.ajax({
					type: 'POST',
					url: monarchSettings.ajaxurl,
					data: {
							action : 'generate_shortcode_ajax',
							options_shortcode : options_shortcode,
							shortcode_nonce : monarchSettings.shortcode_nonce
						},
					beforeSend: function ( xhr ){
						$spinner_shortcode.addClass( 'spinner_visible' );
					},
					success: function( data ){
						$spinner_shortcode.removeClass( 'spinner_visible' );
						$( '#et_social_shortcode_field' ).empty().val( data );
					}
				});
			return false;
		});

		$( 'body' ).on( 'click', '.et_social_close', function(){
			var modal_container = $( this ).parent().parent().parent();

			//Remove the modal container of warning or hide the modal of networks picker
			if ( modal_container.hasClass( 'et_social_warning' ) ) {
				modal_container.remove();
			} else {
				modal_container.css( { 'z-index' : '-1' , 'display' : 'none' } );
			}
		});

		$( 'body' ).on( 'click', '.et_social_nonselectednetwork', function(){
			$( this ).removeClass( 'et_social_nonselectednetwork' ).addClass( 'et_social_selectednetwork' );
			return false;
		});

		$( 'body' ).on( 'click', '.et_social_selectednetwork', function(){
			$( this ).removeClass( 'et_social_selectednetwork' ).addClass( 'et_social_nonselectednetwork et_social_deselected' );
			return false;
		});

		$( 'body' ).on( 'click', '.et_social_apply', function(){
			$this_el = $( this );
			$network_container = $( this ).parent().parent();
			$networks = $network_container.find( '.et_social_selectednetwork' ).parent();
			$section = $( '.et_social_sortable.' + $this_el.data( 'area' ) );
			$need_recalculation = false;

			$network_container.find( '.et_social_deselected' ).parent().each( function(){
				$network_deselected = $( this );
				$network_in_list = $section.find( '.et_social_' + $network_deselected.data( 'name' ) );
				if ( $network_in_list.length ) {
					$network_in_list.parent().remove();
					$need_recalculation = true;
				}
			});

			$current_id = $section.find( '.et_social_network' ).length;
			$network_container.find( '.et_social_selectednetwork' ).parent().each( function(){
				$network = $( this );
				$add_class = '';
				$input_cid = '';
				$additional_name = '';
				if ( ! $section.find( '.et_social_' + $network.data( 'name' ) ).length ) {
					$is_follow_window = $network_container.find( '.social_icons_container' ).hasClass( 'follow_networks_networks_sorting' ) ? true : false;
					$input_counts = ( false != $network.data( 'counts' ) && $is_follow_window ) ? '<input class="input_count" type="text" placeholder="0" name="et_social[' + $this_el.data( 'area' ) + '][count][' + $current_id + ']">' : '';
					$username = ( false != $network.data( 'username' ) ) ? '<input class="input_name" type="text" placeholder="' + $network.data( 'placeholder' ) + '" name="et_social[' + $this_el.data( 'area' ) + '][username][' + $current_id + ']">' : '';
					$check_mark_holder = ( undefined != $network.data( 'api_support' ) && true == $network.data( 'api_support' ) && $is_follow_window ) ? '<p class="et_social_checkmark_holder"></p>' : '';

					if ( undefined != $network.data( 'client_id_placeholder' ) && '' != $network.data( 'client_id_placeholder' ) && $is_follow_window  ) {
						$add_class = ( undefined != $network.data( 'client_name_placeholder' ) && '' != $network.data( 'client_name_placeholder' ) ) ? 'et_social_5_fields' : 'et_social_4_fields';
						$input_cid = '<input type="text" class="input_cid" placeholder="' + $network.data( 'client_id_placeholder' ) + '" name="et_social[' + $this_el.data( 'area' ) + '][client_id][' + $current_id + ']">';
						$additional_name = ( undefined != $network.data( 'client_name_placeholder' ) && '' != $network.data( 'client_name_placeholder' ) ) ? '<input type="text" class="input_client_name" placeholder="' + $network.data( 'client_name_placeholder' ) + '" name="et_social[' + $this_el.data( 'area' ) + '][client_name][' + $current_id + ']">' : '';
					}

					$network_to_add = '<div class="et_social_network et_social_icon ui-sortable-handle ' + $add_class + '" data-name="' + $network.data( 'name' ) +'" data-area="' + $this_el.data( 'area' ) +'" ><span class="et_social_' + $network.data( 'name' ) + '" ><a href="#" class="et_social_deletenetwork"></a></span><input class="input_label" type="text" placeholder="' + $network.data( 'label' ) +'" value="' + $network.data( 'label' ) +'" name="et_social[' + $this_el.data( 'area' ) + '][label][' + $current_id + ']">' + $username + $additional_name + $input_cid + $check_mark_holder + $input_counts + '<input type="hidden" class="input_class" name="et_social[' + $this_el.data( 'area' ) + '][class][' + $current_id + ']" value="' + $network.data( 'name' ) + '"/></div>';
					$('.et_social_sortable.' + $this_el.data( 'area' ) ).append( $network_to_add );
					$current_id++;
				}
			});

			if( true == $need_recalculation ) {
				recalculate_networks_order( $section.attr( 'id' ) );
			}

			//Hide the Network Picker window
			$network_container.parent().css( { 'z-index' : '-1' , 'display' : 'none' } );

			// show/hide appropriate api settings depending on selected networks
			reset_api_visibility();

			return false;
		});

		//Handle click on the OK button in warning window
		$( 'body' ).on( 'click', '.et_social_ok', function(){
			var this_el = $( this ),
				link = this_el.attr( 'href' ),
				main_container = this_el.parent().parent().parent();

			main_container.remove();

			//If OK button is a tab link, then open the tab
			if ( -1 != link.indexOf( '#tab' ) ) {
				var tab_link = link.split( '#tab_' )[1],
					section = ( -1 != tab_link.indexOf( 'header' ) ) ? 'header' : 'side';

				set_current_tab( tab_link, section );

				return false;
			}

			//Do nothing if there is no link in the OK button
			if ( '#' == link ) {
				return false;
			}

		});

		$( '.et_social_sortable' ).on( 'click', '.et_social_deletenetwork', function(){
			$this_el = $( this );
			$sortable_items = $this_el.closest( '.et_social_sortable' );
			$id = $this_el.closest( '.et_social_network' ).data( 'name' );
			$network = $this_el.parent().parent();
			$network_to_deselect = '.et_social_' + $network.data( 'name' );

			if ( 'follow_networks_networks_sorting' == $network.data( 'area' ) ) {
				$clicks_counter = follow_delete_counter++;
			} else {
				$clicks_counter = share_delete_counter++;
			}

			//Need to generate the modal only if the button clicked first time.
			if ( 0 == $clicks_counter ) {
				generate_modal_window( $this_el.closest( '.et_social_network' ).data( 'area' ).replace('_networks_networks_sorting', ''), $network_to_deselect, false );
			}

			$( '.social_icons_container.' + $network.data( 'area' ) ).find( $network_to_deselect ).removeClass( 'et_social_selectednetwork' ).addClass( 'et_social_nonselectednetwork' );
			$this_el.closest( '.et_social_network' ).remove();

			recalculate_networks_order( $sortable_items.attr( 'id' ) );

			// show/hide appropriate api settings depending on selected networks
			reset_api_visibility();

			return false;

		});

		$( '.et_social_save_changes button' ).click( function() {
			var options_fromform = $( '#et_monarch_options' ).serialize();
			$spinner = $( this ).parent().find( '.spinner' );
			$.ajax({
				type: 'POST',
				url: monarchSettings.ajaxurl,
				data: {
						action : 'ajax_save_settings',
						options : options_fromform,
						save_settings_nonce : monarchSettings.save_settings
					},
				beforeSend: function ( xhr ){
					$spinner.addClass( 'spinner_visible' );
				},
				success: function( data ){
					$spinner.removeClass( 'spinner_visible' );
					display_warning( data );
				}
			});
			return false;
		});

		function display_warning( $warn_window ) {
			if ( '' == $warn_window ){
				return;
			}

			$( '#wpwrap' ).append( $warn_window );
		}

		function generate_warning( $message, $link ){
			var link = '' == $link ? '#' : $link;
			$.ajax({
				type: 'POST',
				url: monarchSettings.ajaxurl,
				data: {
						action : 'generate_modal_warning',
						message : $message,
						ok_link : link,
						generate_warning_nonce : monarchSettings.generate_warning
					},
				success: function( data ){
					display_warning( data );
				}
			});
		}

		$( '.et-monarch-color-picker' ).wpColorPicker();

		function check_conditional_options( $current_trigger ){
			if ( undefined != $current_trigger.data( "enables_2" ) ) {
				var j = 2;
			} else {
				var j = 1;
			}

			for ( var i = 1; i <= j; i++ ) {

				$option_name = '[name="et_social[' + $current_trigger.data("enables_" + i ) + ']"]';
				$section_name = '[data-name="et_social[' + $current_trigger.data("enables_" + i ) + ']"]';
				$triggered_option = $( $option_name ).length ? $( $option_name ) : $( $section_name );

				// Check whether we need to enable/disable single option and not the entire section
				if ( ! ( $triggered_option ).hasClass( 'et_social_form' ) ) {
					$triggered_option = $( $option_name ).parent();
				}
				$all_triggers = $('[data-enables_1=' + $current_trigger.data("enables_" + i ) + '], [data-enables_2=' + $current_trigger.data("enables_" + i ) + ']' );
				$option_enabled = false;
				$all_triggers.each(function(){
					if ( $triggered_option.data( 'condition' ) == $( this ).children( 'input' ).prop('checked') ) {
						$triggered_option.removeClass( 'et_social_hidden_option' ).addClass( 'et_social_visible_option' );
						$option_enabled = true;
					} else {
						if ( !$option_enabled ) {
							$triggered_option.addClass( 'et_social_hidden_option' ).removeClass( 'et_social_visible_option' );
						}
					}
				});
			}
		}

		$( '.et_social_conditional' ).click( function() {
			check_conditional_options( $( this ) );
		});

		if ( $( '.et_social_conditional' ).length ) {
			$( '.et_social_conditional' ).each( function() {
				check_conditional_options( $( this ) );
			});
		}

		$( '.et_social_form span.more_info' ).click( function() {
			$( this ).find( '.et_social_more_text' ).fadeToggle( 400 );
		});

		$( '.et_social_form' ).on( 'click', '.et_authorize_api', function() {
			var $this_el = $(this).parent(),
				client_id = $this_el.parent().find( '.input .api_option_client_id' ).val(),
				client_secret = $this_el.parent().find( '.input .api_option_client_secret' ).val(),
				network_name = $this_el.closest( '.et_social_form' ).find( '> h2' ).text(),
				options_fromform = $( '#et_monarch_options' ).serialize(),
				api_key;
				if ( 'Twitter' == network_name ) {
					api_key = $this_el.parent().find( '.input .api_option_api_key' ).val();
					var api_secret = $this_el.parent().find( '.input .api_option_api_secret' ).val(),
						token = $this_el.parent().find( '.input .api_option_token' ).val(),
						token_secret = $this_el.parent().find( '.input .api_option_token_secret' ).val();
				}
				if ( 'Youtube' == network_name ) {
					api_key = $this_el.parent().find( '.input .api_option_api_key' ).val();
				}

			$.ajax({
					type: "POST",
					dataType: "json",
					url: monarchSettings.ajaxurl,
					data: {
						action : 'monarch_authorize_network',
						nonce : monarchSettings.monarch_nonce,
						client_id : client_id,
						client_secret : client_secret,
						network_name : network_name,
						api_key : api_key,
						api_secret : api_secret,
						token : token,
						token_secret : token_secret,
						options : options_fromform
					},
					beforeSend: function (){
						$this_el.find( '.spinner' ).addClass( 'spinner_visible' );
					},
					success: function( data ){
						$this_el.find( '.spinner' ).removeClass( 'spinner_visible' );

						if ( typeof data.error_message !== 'undefined' ) {
							generate_warning( data.error_message );
						} else if ( typeof data.authorization_url !== 'undefined' ) {
							window.location = data.authorization_url;
						}
					}
			});

			return false;
		} );

		$( 'label[for="et_social[follow_networks_use_api]"]' ).click( function() {
			$( '#sortable_follow_networks_networks_sorting' ).toggleClass( 'et_social_api_enabled' );
		});

		$( '.et_social_location_selector' ).on( 'change', 'select', function() {
			var selected_value = $( this ).val();
			$.ajax({
				type: "POST",
				url: monarchSettings.ajaxurl,
				data: {
					action : 'get_share_stats_graphs',
					get_stats_nonce : monarchSettings.get_stats,
					monarch_location : selected_value,
					monarch_all_stats : 'all_stats'
				},
				beforeSend: function ( xhr ){
					$( '.et_social_location_selector .spinner' ).addClass( 'spinner_visible' );
				},
				success: function( data ){
					$( '.et_social_location_selector .spinner' ).removeClass( 'spinner_visible' );
					$( '#et_social_globalstats' ).remove();
					$( '#et_social_stats_container' ).remove();
					$( '.et_social_tab_content_header_stats' ).append( data );
					calculate_bars_size();
				}
			});
		});

		$('.et_social_form' ).on('click', '.et_save_google_settings', function() {
			var $form_container = $(this).closest('ul');
			var google_fonts_val = $form_container.find('#et_use_google_fonts').prop('checked') ? 'on' : 'off';
			var $spinner = $form_container.find('.spinner');

			$.ajax({
				type: 'POST',
				url: monarchSettings.ajaxurl,
				data: {
					action : 'monarch_save_google_settings',
					google_settings_nonce : monarchSettings.google_settings,
					et_monarch_use_google_fonts : google_fonts_val
				},
				beforeSend: function() {
					$spinner.addClass('spinner_visible');
				},
				success: function(data) {
					$spinner.removeClass('spinner_visible');
				}
			});

			return false;
		});
		
		$( '.et_social_form' ).on( 'click', '.et_authorize_updates', function() {
			var $form_container = $( this ).closest( 'ul' ),
				username = $form_container.find( '.updates_option_username' ).val(),
				api_key = $form_container.find( '.updates_option_api_key' ).val(),
				$spinner = $form_container.find( '.spinner' );

			$.ajax({
				type: 'POST',
				url: monarchSettings.ajaxurl,
				data: {
					action : 'monarch_save_updates_settings',
					updates_settings_nonce : monarchSettings.updates_settings,
					et_monarch_updates_username : username,
					et_monarch_updates_api_key : api_key
				},
				beforeSend: function() {
					$spinner.addClass( 'spinner_visible' );
				},
				success: function( data ){
					$spinner.removeClass( 'spinner_visible' );
				}
			});

			return false;
		});

	});

	function get_url_parameter( param_name ) {
		var page_url = window.location.search.substring(1);
		var url_variables = page_url.split('&');
		for ( var i = 0; i < url_variables.length; i++ ) {
			var curr_param_name = url_variables[i].split( '=' );
			if ( curr_param_name[0] == param_name ) {
				return curr_param_name[1];
			}
		}
	}

	function maybe_set_location_hash_after_oauth_redirect() {
		var state = get_url_parameter( 'state' );

		if ( 'string' === typeof state && state.indexOf( 'linkedin_' ) !== -1 ) {
			window.location.hash = '#tab_et_social_tab_content_follow_networks';
		}
	}

})(jQuery);