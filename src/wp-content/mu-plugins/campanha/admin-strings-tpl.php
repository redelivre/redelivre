<?php

Campaign::saveDefinedSettingsStrings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$campaignDefinedSettingsStrings = Campaign::getStrings();

foreach ($campaignDefinedSettingsStrings as $fieldName => $fieldValue)
{
	echo '<p><label>'.$fieldValue['label'].'</label><br><input type="text" value="'.$fieldValue['value'].'" name="settings_strings['.$fieldName.'][value]" size="80"></p>';
}

echo '<input type="submit" value="Salvar">';
echo '</form>';