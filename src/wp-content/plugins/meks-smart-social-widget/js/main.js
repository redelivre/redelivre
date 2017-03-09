(function($) {
	$(document).ready(function($) {

		/* Social widget handlers */

		$("body").on("click", "a.mks_add_social", function(e) {
			e.preventDefault();

			var widget_holder = $(this).closest('.widget-inside');
			var cloner = widget_holder.find('.mks_social_clone');

			widget_holder.find('.mks_social_container').append('<li>' + cloner.html() + '</li>');

		});

		$("body").on("click", ".mks-remove-social", function(e) {
			var delete_item = confirm('Are you sure you want to delete this icon?');
			delete_item ? $(this).closest('li').remove() : '';
		});

		/* Init sortable */
		mks_social_sortable();

		$(document).on('widget-added', function(e) {
			mks_social_sortable();
		});

		$(document).on('widget-updated', function(e) {
			mks_social_sortable();
		});

		/*  Sortable function */
		function mks_social_sortable() {
			$(".mks-social-sortable").sortable({
				revert: false,
				cursor: "move",
				delay: 100,
				placeholder: "mks-social-sortable-drop"
			});
		}

	});

})(jQuery);