<?php

if (!defined ('WP_UNINSTALL_PLUGIN')) {
    exit ();
}

$options = array ("ecu_images_only", "ecu_permission_required",
    "ecu-images_only", "ecu-premission_required");
$upload_dir =  WP_CONTENT_DIR . '/upload/';

foreach ($options as $i)
    delete_option ($i);

// Remove insecure files left over from old versions
if (file_exists ($upload_dir . 'upload.php'))
	unlink ($upload_dir . 'upload.php');

?>
