/* global WP_Smush */
/* global ajaxurl */

/**
 * Directory Smush module JavaScript code.
 *
 * @since 2.8.1  Separated from admin.js into dedicated file.
 */

import { createTree } from "jquery.fancytree";
import Scanner from "../smush/directory-scanner";

(function($) {
  "use strict";

  WP_Smush.directory = {
    selected: [],
    tree: [],
    wp_smush_msgs: [],

    init() {
      const self = this,
        progressDialog = $("#wp-smush-progress-dialog");

      let totalSteps = 0,
        currentScanStep = 0;

      // Make sure directory smush vars are set.
      if (typeof window.wp_smushit_data.dir_smush !== "undefined") {
        totalSteps = window.wp_smushit_data.dir_smush.totalSteps;
        currentScanStep = window.wp_smushit_data.dir_smush.currentScanStep;
      }

      // Init image scanner.
      this.scanner = new Scanner(totalSteps, currentScanStep);

      /**
       * Smush translation strings.
       *
       * @param {Array} wp_smush_msgs
       */
      this.wp_smush_msgs = window.wp_smush_msgs || {};

      /**
       * Folder select: Choose Folder in Directory Smush tab clicked.
       */
      $("div.sui-wrap").on("click", "button.wp-smush-browse", function(e) {
        e.preventDefault();

        // Hide all the notices.
        $("div.wp-smush-scan-result div.wp-smush-notice").hide();

        // Remove notice.
        $("div.wp-smush-info").remove();

        // Display file tree for directory Smush.
        self.initFileTree();
      });

      /**
       * Stats section: Directory Link
       */
      $("body").on("click", "a.wp-smush-dir-link", function(e) {
        if ($("div.sui-wrap button.wp-smush-browse").length > 0) {
          e.preventDefault();
          window.SUI.openModal(
            "wp-smush-list-dialog",
            "dialog-close-div",
            undefined,
            false
          );
          //Display File tree for Directory Smush
          self.initFileTree();
        }
      });

      /**
       * Smush images: Smush in Choose Directory modal clicked
       */
      $(".wp-smush-select-dir").on("click", function(e) {
        e.preventDefault();

        // If disabled, do not process
        if ($(this).attr("disabled")) {
          return;
        }

        const button = $(this);

        $("div.wp-smush-list-dialog div.sui-box-body").css({ opacity: "0.8" });
        $("div.wp-smush-list-dialog div.sui-box-body a").unbind("click");

        // Disable button
        button.attr("disabled", "disabled");

        const spinner = button.parent().find(".add-dir-loader");
        // Display the spinner
        spinner.addClass("sui-icon-loader sui-loading");

        const selectedFolders = self.tree.getSelectedNodes(),
          absPath = $('input[name="wp-smush-base-path"]').val(); // Absolute path.

        const paths = [];
        selectedFolders.forEach(function(folder) {
          paths.push(absPath + "/" + folder.key);
        });

        // Send a ajax request to get a list of all the image files
        const param = {
          action: "image_list",
          smush_path: paths,
          image_list_nonce: $('input[name="image_list_nonce"]').val()
        };

        $.get(ajaxurl, param, function(response) {
          window.SUI.closeModal();

          // TODO: check for errors.
          self.scanner = new Scanner(response.data, 0);
          self.showProgressDialog(response.data);
          self.scanner.scan();
        });
      });

      /**
       * On dialog close make browse button active.
       */
      $("#wp-smush-list-dialog").on("click", ".sui-dialog-close", function() {
        $(".wp-smush-browse").removeAttr("disabled");

        // Close the dialog.
        window.SUI.closeModal();

        $(
          ".wp-smush-select-dir, button.wp-smush-browse, a.wp-smush-dir-link"
        ).removeAttr("disabled");

        // Reset the opacity for content and scan button
        $(".wp-smush-select-dir, .wp-smush-list-dialog .sui-box-body").css({
          opacity: "1"
        });
      });

      /**
       * Cancel scan.
       */
      progressDialog.on(
        "click",
        "#cancel-directory-smush, .sui-dialog-close, .wp-smush-cancel-dir",
        function(e) {
          e.preventDefault();
          // Display the spinner
          $(this)
            .parent()
            .find(".add-dir-loader")
            .addClass("sui-icon-loader sui-loading");
          self.scanner
            .cancel()
            .done(
              () => (window.location.href = self.wp_smush_msgs.directory_url)
            );
        }
      );

      /**
       * Continue scan.
       */
      progressDialog.on(
        "click",
        ".sui-icon-play, .wp-smush-resume-scan",
        function(e) {
          e.preventDefault();
          self.scanner.resume();
        }
      );
    },

    /**
     * Init fileTree.
     */
    initFileTree() {
      const self = this,
        smushButton = $("button.wp-smush-select-dir"),
        ajaxSettings = {
          type: "GET",
          url: ajaxurl,
          data: {
            action: "smush_get_directory_list",
            list_nonce: $('input[name="list_nonce"]').val()
          },
          cache: false
        };

      // Object already defined.
      if (Object.entries(self.tree).length > 0) {
        return;
      }

      self.tree = createTree(".wp-smush-list-dialog .content", {
        autoCollapse: true, // Automatically collapse all siblings, when a node is expanded
        clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
        checkbox: true, // Show checkboxes
        debugLevel: 0, // 0:quiet, 1:errors, 2:warnings, 3:infos, 4:debug
        selectMode: 3, // 1:single, 2:multi, 3:multi-hier
        tabindex: "0", // Whole tree behaves as one single control
        keyboard: true, // Support keyboard navigation
        quicksearch: true, // Navigate to next node by typing the first letters
        source: ajaxSettings,
        lazyLoad: (event, data) => {
          data.result = new Promise(function(resolve, reject) {
            ajaxSettings.data.dir = data.node.key;
            $.ajax(ajaxSettings)
              .done(response => resolve(response))
              .fail(reject);
          });

          // Update the button text.
          data.result.then(smushButton.html(self.wp_smush_msgs.add_dir));
        },
        loadChildren: (event, data) => data.node.fixSelection3AfterClick(), // Apply parent's state to new child nodes:
        select: () =>
          smushButton.attr("disabled", !+self.tree.getSelectedNodes().length),
        init: () => smushButton.attr("disabled", true)
      });
    },

    /**
     * Show progress dialog.
     *
     * @param {number} items  Number of items in the scan.
     */
    showProgressDialog(items) {
      // Update items status and show the progress dialog..
      $(".wp-smush-progress-dialog .sui-progress-state-text").html(
        "0/" + items + " " + self.wp_smush_msgs.progress_smushed
      );

      window.SUI.openModal(
        "wp-smush-progress-dialog",
        "dialog-close-div",
        undefined,
        false
      );
    },

    /**
     * Update progress bar during directory smush.
     *
     * @param {number}  progress  Current progress in percent.
     * @param {boolean} cancel    Cancel status.
     */
    updateProgressBar(progress, cancel = false) {
      if (progress > 100) {
        progress = 100;
      }

      // Update progress bar
      $(".sui-progress-block .sui-progress-text span").text(progress + "%");
      $(".sui-progress-block .sui-progress-bar span").width(progress + "%");

      if (progress >= 90) {
        $(".sui-progress-state .sui-progress-state-text").text("Finalizing...");
      }

      if (cancel) {
        $(".sui-progress-state .sui-progress-state-text").text("Cancelling...");
      }
    }
  };

  WP_Smush.directory.init();
})(jQuery);
