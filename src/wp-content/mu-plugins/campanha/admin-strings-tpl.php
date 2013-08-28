<?php

Campaign::saveDefinedSettingsStrings();

// Campaign::saveDefinedSettingsStrings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$campaignDefinedSettingsStrings = Campaign::getStrings();

foreach ($campaignDefinedSettingsStrings['label'] as $key => $label)
{
	echo '<p><label>'.$label.'</label><br><input type="text" value="'.$campaignDefinedSettingsStrings['value'][$key].'" name="settings_strings[value]['.$key.']" size="80"></p>';
}

echo '<input type="submit" value="Salvar">';
echo '</form>';