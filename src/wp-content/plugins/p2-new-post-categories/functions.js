( function( $ ) {

	var P2NewPostCategories = {
		
		/**
		 * Initialization
		 */
		construct : function() {
			P2NewPostCategories.dropdown         = $( '#p2-new-post-category' );
			P2NewPostCategories.dropdown_default = P2NewPostCategories.dropdown.val();
			
			$( document ).on( 'p2_new_post_submit_success', P2NewPostCategories.new_post );
		},

		/**
		 * Assign the selected category to the new post
		 * @param object event
		 * @param object data
		 */
		new_post : function( event, data ) {
			$.post(
				ajaxUrl.replace( '?p2ajax=true', '' ), {
					'action'                      : 'p2npc_assign_category',
					'post_id'                     : parseInt( data.post_id ),
					'category_id'                 :	parseInt( P2NewPostCategories.dropdown.val() ),
					'p2npc_assign_category_nonce' : $( '#p2npc_assign_category_nonce' ).val()
				}
			);
			
			P2NewPostCategories.dropdown.val( parseInt( P2NewPostCategories.dropdown_default ) ).change();
		}
	};
	
	P2NewPostCategories.construct();

} )( jQuery );