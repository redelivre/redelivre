<?php

require_once('../../../wp-load.php');

if (array_key_exists('id', $_GET)
		&& !current_user_can('manage_eletro_widgets')) {
	die;
}

$options = get_option('eletro_widgets');

if (is_array($options) && array_key_exists($_GET['id'], $options)) {
	header('Content-disposition: attachment; '
			. 'filename=' . urlencode($_GET['id']));
	header('Content-type: application/json');
	echo json_encode($options[$_GET['id']]);
}

?>
