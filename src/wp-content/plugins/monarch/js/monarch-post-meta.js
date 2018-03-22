(function($){
	$(document).ready(function() {
		$( '#monarch-override-locations' ).click(function(){
			$( '#monarch_settings_box' ).toggle();
		});

		$( '.et_monarch_stats_tab' ).click( function() {
			var clicked_tab = $( this );
			if ( !clicked_tab.hasClass( 'et_monarch_active_tab' ) ) {
				$( '.et_monarch_tab_content' ).removeClass( 'et_monarch_hidden_tab_content' );
				$( '.et_monarch_stats_tab' ).removeClass( 'et_monarch_active_tab' );
				clicked_tab.addClass( 'et_monarch_active_tab' );

				if ( clicked_tab.hasClass( 'tab_all_time' ) ) {
					$( '.et_monarch_past_week_content' ).addClass( 'et_monarch_hidden_tab_content' );
				} else {
					$( '.et_monarch_all_time_content' ).addClass( 'et_monarch_hidden_tab_content' );
				}

				calculate_bars_size();
			}

			return false;
		});

		calculate_bars_size();

		function calculate_bars_size() {
			// Calculate and change the height for the graph bars in each row
			$( 'ul.et_social_graph' ).each( function() {
				var this_el = $( this );

				if ( this_el.hasClass( 'et_social_graph_alltime' ) ) {
					this_el.find( 'li > div' ).css( { 'width' : 0 } );
					resize ( this_el, false );
				} else {
					this_el.find( 'li > div' ).css( { 'height' : 0 } );
					resize ( this_el, true );
				}
			});

			$( 'ul.et_social_graph li > div > div' ).each( resize_network );
		}

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

		$( '.stats_tabs_content' ).on( 'mouseenter', '.et_social_hover_item', function(){
			var $this_el = $( this );
			var value = $this_el.attr( 'value' );
			var type = $this_el.attr( 'type' );
			var action = $this_el.data( 'action' );
			var display_message = 'like' == action ? monarchSettings.like_text : monarchSettings.share_text;

			$( '<div class="et_social_tooltip"><strong>' + type + '</strong><br>' + display_message + value + '</div>' ).appendTo( $this_el );

		}).on( 'mouseleave', '.et_social_hover_item', function(){
			$( this ).find( 'div.et_social_tooltip' ).remove();
		});
	});
})(jQuery)