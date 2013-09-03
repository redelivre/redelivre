<?php

savePlataformSettings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$plataformSettingsStrings = getPlataformSettings();

foreach ($plataformSettingsStrings['label'] as $key => $label)
{
	if ($key == 'emailPassword') {
		echo '<p><label>'.$label.'</label><br><input type="password" value="'.$plataformSettingsStrings['value'][$key].'" name="plataform_settings_strings[value]['.$key.']" size="80"></p>';

		break;
	}

	echo '<p><label>'.$label.'</label><br><input type="text" value="'.$plataformSettingsStrings['value'][$key].'" name="plataform_settings_strings[value]['.$key.']" size="80"></p>';
}

echo '<input type="submit" value="Salvar">';
echo '</form>';