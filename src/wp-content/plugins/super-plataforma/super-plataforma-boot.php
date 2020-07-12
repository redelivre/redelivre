<?php
$YIN = trim(file_get_contents(WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/license.txt'));
$YIN = str_split(substr($YIN, 0, 50));

$YANG = array();
foreach($YIN as $Y) { $YANG[] = "[SB[x" . trim(strval(ord($Y))) . "]]"; }

define('SUPER_PLATAFORMA_YIN', implode("|S|*|B|", $YIN));
define('SUPER_PLATAFORMA_YANG', implode("|S|*|B|", $YANG));

require_once('super-plataforma-config.php');
