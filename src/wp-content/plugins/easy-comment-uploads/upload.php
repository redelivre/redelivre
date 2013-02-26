<?php require ('../../../wp-blog-header.php'); ?>
<!doctype html5>
<html>
    <head>
        <script type="text/javascript">
        // Write txt to comment field
        function write_comment (text) {
            // Handle commentMCE
            if (parent.parent.tinyMCE && parent.parent.tinyMCE.get('comment')) {
                editor = parent.parent.tinyMCE.get('comment');
                editor.setContent(editor.getContent()
                    + '\n<p>' + text + '</p>');
                return;
            }

            // Handle nicEdit
            if (parent.parent.nicEditors
                && parent.parent.nicEditors.findEditor('comment')) {
                editor = parent.parent.nicEditors.findEditor('comment');
                editor.setContent((editor.getContent() != '<br>' ?
                    editor.getContent().replace(/(<(p|div)><br><\/(p|div)>)+$/,
                    '') : '') + '<p>' + text + '</p>');
                return;
            }

            // Handle standard comment forms
            comment = parent.parent.document.getElementById("comment")
                || parent.parent.document.getElementById("comment-p1")
                || parent.parent.document.forms["commentform"].comment;
            comment.value = comment.value.replace(/[\n]+$/, '')
                + (comment.value.length > 0 ? '\n' : '') + text + '\n';
        }
        </script>
    </head>

    <body>
        <?php
        // Check referer
        wp_verify_nonce ($_REQUEST ['_wpnonce'], 'ecu_upload_form')
            || write_js ("alert ('Invalid Referer')")
            || die ('Invalid referer');

        // Get needed info
        $target_dir = ecu_upload_dir_path ();
        $target_url = ecu_upload_dir_url ();
        $images_only = get_option ('ecu_images_only');
        $max_file_size = get_option ('ecu_max_file_size');

        if (!file_exists ($target_dir))
            mkdir ($target_dir);

        $target_path = find_unique_target ($target_dir
            . basename($_FILES['file']['name']));
        $target_name = basename ($target_path);

        // Debugging message example
//      write_js ("alert ('$target_url')");

        // Default values
        $filecode = "";
        $filelink = "";

        // Detect whether the uploaded file is an image
        $is_image = preg_match ('/(jpeg|png|gif)/i', $_FILES['file']['type']);
        $type = ($is_image) ? "img" : "file";

        if (!$is_image && $images_only) {
            $alert = "Sorry, you can only upload images.";
        } else if (filetype_blacklisted() && !filetype_whitelisted()) {
            $alert = "You are attempting to upload a file with a disallowed/unsafe filetype!";
        } else if (!(filetype_whitelisted() || get_option('ecu_file_extension_whitelist') === false)) {
            $alert = "You may only upload files with the following extensions: "
                . implode(', ', get_option('ecu_file_extension_whitelist'));
        } else if ($max_file_size != 0 && $_FILES['file']['size']/1024 > $max_file_size) {
            $alert = "The file you've uploaded is too big ("
                . round($_FILES['file']['size']/1024, 1)
                . "KiB).  Please choose a smaller image and try again.";
        } else if (ecu_user_uploads_per_hour() != -1 && ecu_user_uploads_in_last_hour() >= ecu_user_uploads_per_hour()) {
            $alert = "You are only permitted to upload "
                . (string)ecu_user_uploads_per_hour() . " files per hour.";
        } else if (move_uploaded_file ($_FILES['file']['tmp_name'], $target_path)) {
            $filelink = $target_url . $target_name;
            $filecode = "[$type]$filelink" . "[/$type]";

            // Add the filecode to the comment form
            write_js("write_comment(\"$filecode\");");

            // Post info below upload form
            write_html_form("<div class='ecu_preview_file'><a href='$filelink'>$target_name</a><br />$filecode</div>");

            if ($is_image)
                write_html_form("<a href='$filelink' rel='lightbox[new]'><img class='ecu_preview_img' src='$filelink' /></a><br />");

            ecu_user_record_upload_time();
        } else {
            $alert = "There was an error uploading the file, please try again!";
        }

        // Alert the user of any errors
        if (isset($alert))
            js_alert($alert);

        // Check upload against blacklist and return true unless it matches
        function filetype_blacklisted() {
            $blacklist = ecu_get_blacklist();
            return preg_match("/\\.((" . implode('|', $blacklist)
                . ")|~)(\\.|$)/i", $_FILES['file']['name']);
        }

        // Check upload against whitelist and return true if it matches
        function filetype_whitelisted() {
            if (get_option('ecu_file_extension_whitelist') === false)
                return false;
            $whitelist = get_option('ecu_file_extension_whitelist');
            return preg_match("/^[^\\.]+\\.(" . implode('|', $whitelist)
                . ")$/i", $_FILES['file']['name']);
        }

        // Write script as js to the page
        function write_js($script) {
            echo "<script type=\"text/javascript\">$script\n</script>\n";
        }

        // Send message to user in an alert
        function js_alert($msg) {
            write_js("alert('$msg');");
        }

        // Write html to the preview iframe
        function write_html_form ($html) {
            write_js("parent.parent.document.getElementById('ecu_preview').innerHTML = \"$html\" + parent.parent.document.getElementById('ecu_preview').innerHTML");
        }

        // Find a unique filename similar to $prototype
        function find_unique_target ($prototype) {
            if (!file_exists ("$prototype")) {
                return $prototype;
            } else {
                $i = 1;
                $prototype_parts = pathinfo ($prototype);
                $ext = $prototype_parts['extension'];
                $dir = $prototype_parts['dirname'];
                $name = $prototype_parts['filename'];
                $dot = $ext == '' ? '' : '.';
                while (file_exists ("$dir/$name-$i$dot$ext")) { ++$i; }
                return "$dir/$name-$i$dot$ext";
            }
        }

        ?>
    </body>
</html>
