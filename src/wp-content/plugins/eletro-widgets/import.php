<?php

require_once('../../../wp-load.php');

if (!current_user_can('manage_eletro_widgets')) {
	die;
}

// 128 kilobytes
define('EW_MAXIMUM_IMPORT_SIZE', 1 << 17);

function alertAndRedirect($message, $redirect) {
	echo '<script type="text/javascript">';
	echo "alert('", htmlentities($message), "');";
	echo "window.location.href = '", htmlentities($redirect), "';";
	echo '</script>';
}

$redirect = array_key_exists('redirect', $_POST)?
	$_POST['redirect'] : home_url();

if (!array_key_exists('canvas', $_POST)) {
	alertAndRedirect(__('No canvas specified', 'eletroWidgets'), $redirect);
	die;
}

if (!array_key_exists('importFile', $_FILES)
	|| !array_key_exists('error', $_FILES['importFile'])
	|| is_array($_FILES['importFile']['error'])) {
	alertAndRedirect(__('Invalid upload', 'eletroWidgets'), $redirect);
	die;
}

if ($_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
	alertAndRedirect(__('Upload failed', 'eletroWidgets'), $redirect);
	die;
}

if ($_FILES['importFile']['size'] > EW_MAXIMUM_IMPORT_SIZE) {
	alertAndRedirect(sprintf(__('The uploaded file is bigger than %d bytes',
					'eletroWidgets'), EW_MAXIMUM_IMPORT_SIZE), $redirect);
	die;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
if ($finfo->file($_FILES['importFile']['tmp_name']) !== 'text/plain') {
	alertAndRedirect(__('The uploaded file is not plain text', 'eletroWidgets'),
			$redirect);
	die;
}

$data = json_decode(
		file_get_contents($_FILES['importFile']['tmp_name']), true);

if (!is_array($data)) {
	alertAndRedirect(__('The uploaded file is not valid json', 'eletroWidgets'),
			$redirect);
	die;
}

// Everything's OK, save it
$options = get_option('eletro_widgets', array());
$options[$_POST['canvas']] = $data;
update_option('eletro_widgets', $options);

wp_redirect($redirect);
die;

?>
