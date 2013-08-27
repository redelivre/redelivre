<?php

Campaign::saveDefinedSettingsStrings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$campaignDefinedSettingsStrings = Campaign::getStrings();

foreach ($campaignDefinedSettingsStrings as $fieldName => $fieldValue)
{
	echo '<p><label>'.implode(' ', preg_split('/(?=[A-Z])/', $fieldName)).'</label><br><input type="text" value="'.$fieldValue.'" name="settings_strings['.$fieldName.']" size="80"></p>';
}

echo '<input type="submit" value="Salvar">';
echo '</form>';