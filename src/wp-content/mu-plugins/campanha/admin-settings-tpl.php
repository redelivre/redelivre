<?php

savePlataformSettings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$plataformSettingsStrings = getPlataformSettings();

foreach ($plataformSettingsStrings['label'] as $key => $label)
{
	$display = '';
	if(array_key_exists($key, $plataformSettingsStrings['perm']) && $plataformSettingsStrings['perm'][$key] == 'S')
	{
		$display = 'display:none;';
	}
	
	if ($key == 'emailPassword') {
		echo '<p style="'.$display.'"><label>'.$label.'</label><br><input type="password" value="'.$plataformSettingsStrings['value'][$key].'" name="plataform_settings_strings['.$key.']" size="80"></p>';

		continue;
	}

	echo '<p style="'.$display.'"><label>'.$label.'</label><br><input type="text" value="'.$plataformSettingsStrings['value'][$key].'" name="plataform_settings_strings['.$key.']" size="80"></p>';
}

echo '<input type="submit" value="Salvar">';
echo '</form>';