/* global ajaxurl */

/**
 * @typedef {Object} jQuery
 */
(function($) {
  let elNotice = $(".smush-notice");
  const btnAct = elNotice.find(".smush-notice-act");

  elNotice.fadeIn(500);

  // Hide the notice after a CTA button was clicked
  function removeNotice() {
    elNotice.fadeTo(100, 0, () =>
      elNotice.slideUp(100, () => elNotice.remove())
    );
  }

  btnAct.on("click", () => {
    removeNotice();
    notifyWordpress(btnAct.data("msg"));
  });

  elNotice.find(".smush-notice-dismiss").on("click", () => {
    removeNotice();
    notifyWordpress(btnAct.data("msg"));
  });

  // Notify WordPress about the users choice and close the message.
  function notifyWordpress(message) {
    elNotice.attr("data-message", message);
    elNotice.addClass("loading");

    //Send a ajax request to save the dismissed notice option
    $.post(ajaxurl, { action: "dismiss_upgrade_notice" });
  }

  // Dismiss the update notice.
  $(".wp-smush-update-info").on("click", ".notice-dismiss", e => {
    e.preventDefault();
    elNotice = $(this);
    removeNotice();
    $.post(ajaxurl, { action: "dismiss_update_info" });
  });

  // Dismiss S3 support alert.
  $("div.wp-smush-s3support-alert").on(
    "click",
    ".sui-notice-dismiss > a",
    () => {
      elNotice = $(this);
      removeNotice();
      $.post(ajaxurl, { action: "dismiss_s3support_alert" });
    }
  );
})(jQuery);
