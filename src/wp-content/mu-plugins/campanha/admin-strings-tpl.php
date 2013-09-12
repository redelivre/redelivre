<?php

Campaign::saveDefinedSettingsStrings();


echo '<form action="" method="post" enctype="multipart/form-data">';

$campaignDefinedSettingsStrings = Campaign::getStrings();

admin_strings_tpl_loop($campaignDefinedSettingsStrings);

foreach ($campaignDefinedSettingsStrings as $key => $value)
{
	if($key != 'value' && $key != 'label')
	{
		admin_strings_tpl_loop($campaignDefinedSettingsStrings[$key], $key);
	}
}

echo '<input type="submit" value="Salvar">';
echo '</form>';

function admin_strings_tpl_loop($list, $prefix = '')
{
	foreach ($list['label'] as $key => $label)
	{
		echo '<p><label>'.$label.'</label><br><input type="text" value="'.$list['value'][$key].'" name="'.$prefix.'settings_strings['.$key.']" size="80"></p>';
	}
}