(function ($) {
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
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery(function ($) {
		$(".datepicker").datepicker({
			dateFormat: 'yy-mm-dd'
		});

		function is_email_enabled() {
			if ($("#pi_ewcl_enable_email").is(":checked")) {
				$("#row_pi_ewcl_email, #row_pi_ewcl_email_subject, #row_pi_ewcl_email_message, #row_pi_ewcl_email_frequency").fadeIn();
			} else {
				$("#row_pi_ewcl_email, #row_pi_ewcl_email_subject, #row_pi_ewcl_email_message, #row_pi_ewcl_email_frequency").fadeOut();
			}
		}

		$("#pi_ewcl_enable_email").on('change', function () {
			is_email_enabled();
		});

		$("#pi_ewcl_enable_email").trigger('change');


		function pi_ewcl_extra_field() {
			this.init = function () {
				this.row_count = 0;
				this.addSavedToRow();
				this.detectAddClick();
				this.detectRemoveClick();
			}

			this.addSavedToRow = function () {
				var parent = this;
				$.each(window.pi_extra_custom_field, function (index, value) {
					parent.addRow(this.row_count, value.field);
				});
			}

			this.getSavedCount = function () {
				return window.pi_extra_custom_field.length
			}

			this.detectAddClick = function () {
				var parent = this;
				$("#pi-ewcl-add-custom-meta").on("click", function () {
					parent.addRow(this.row_count);
				});
			}

			this.detectRemoveClick = function () {
				var parent = this;
				$(document).on("click", '.pi-ews-remove-row', function () {
					var row = $(this).data('row');
					parent.removeRow(row);
				});
			}

			this.removeRow = function (row) {
				var id = "#pi-extra-row-" + row;
				$(id).remove();
			}

			this.addRow = function (count, field = "") {

				var template = this.getRow(this.row_count, field);
				$("#pi-ewcl-field-container").append(template);
				this.row_count = this.row_count + 1;
			}

			this.getRow = function (count, field) {
				var template = '<div id="pi-extra-row-' + count + '" class="row my-3 pi-ews-extra-row"><div class="col-6"><input class="form-control" required type="text" name="pi_customer_added_fields[' + count + '][field]" value="' + field + '" placeholder="e.g: whatsapp_number"></div><div class="col-6"><a href="javascript:void(0);" class="pi-ews-remove-row btn btn-secondary" data-row="' + count + '">Remove</a></div></div>';
				return template;
			}
		}
		var extra_field_obj = new pi_ewcl_extra_field();
		extra_field_obj.init();

	})

})(jQuery);
