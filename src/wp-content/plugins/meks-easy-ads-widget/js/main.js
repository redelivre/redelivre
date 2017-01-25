(function($) {

	$(document).ready(function($) {

		/* Add next Ad */
		$("body").on("click", "a.mks-ads-add", function(e) {
			e.preventDefault();
			var widget_holder = $(this).closest('.widget-inside');
			var cloner = widget_holder.find('.mks-ads-clone');
			cloner.find('#tab-1').addClass('active');
			cloner.find('#tab-1').addClass('active');
			widget_holder.find('.mks-ads-container').append(cloner.html());
		});

		/* Remove Ad */
		$("body").on("click", ".mks-remove-ad", function(e) {
			var deleteItem = confirm('Are you sure you want to delete this ad?');
			deleteItem ? $(this).parent('li').remove() : '';
		});

		/* Tabs */
		$("body").on("click", "ul.mks-tabs li", function() {
			var tab_id = $(this).attr('data-tab');
			var this_tab = $(this);

			this_tab.siblings('li').removeClass('active');
			this_tab.parent('ul').siblings(".mks-tabs-wrapper").find('.mks-tab-content.active').removeClass('active');

			this_tab.addClass('active');
			var activeTab = this_tab.parent("ul").siblings(".mks-tabs-wrapper").find("#" + tab_id);
			activeTab.addClass('active');

			var tabType = activeTab.attr('data-type');
			activeTab.closest('.mks-tabs-wrapper').find('#tab-type').val(tabType);
		});

		
		/* Init sortable */
		mks_ads_sortable();

		$(document).on('widget-added', function(e) {
			mks_ads_sortable();
		});

		$(document).on('widget-updated', function(e) {
			mks_ads_sortable();
		});


		/* Show/hide custom size */

		$("body").on("click", "input.mks-ad-size", function(e) {
			if ($(this).val() == 'custom') {
				$(this).parent().next().show();
			} else {
				$(this).parent().next().hide();
			}
		});


		/* Show/hide rotation speed */

		$("body").on("click", "input.mks-ad-rotate", function(e) {
			if ($(this).is(":checked")) {
				$(this).parent().next().show();
			} else {
				$(this).parent().next().hide();
			}
		});

		/* Choose image from media file */
		var thumbImage;
		$("body").on("click", "a.mks-ads-select-image-btn", function(e) {
			e.preventDefault();
			var this_btn = $(this);
			var image = wp.media({
					title: 'Upload Image',
				}).open()
				.on('select', function(e) {
					var uploaded_image = image.state().get('selection').first();
					var thumbImage = uploaded_image.toJSON().url;
					this_btn.closest('li').find('.mks-ads-field-width').val(thumbImage);
				});
		});

		/*  Sortable function */
		function mks_ads_sortable() {
			$(".mks-ads-sortable").sortable({
				revert: false,
				cursor: "move",
				delay: 100,
				placeholder: "mks-ads-sortable-drop"
			});
		}


	});

})(jQuery);