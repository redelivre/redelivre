<?php

savePlataformSettings();

echo '<form action="" method="post" enctype="multipart/form-data">';

$plataformSettingsStrings = getPlataformSettings();

foreach ($plataformSettingsStrings['label'] as $key => $label)
{
	$display = '';
	if(!is_super_admin() && array_key_exists($key, $plataformSettingsStrings['perm']) && $plataformSettingsStrings['perm'][$key] == 'S')
	{
		$display = 'display:none;';
	}
	$type = array_key_exists($key, $plataformSettingsStrings['type']) ? $plataformSettingsStrings['type'][$key] : 'input';
	$value = $plataformSettingsStrings['value'][$key];
	switch ($type)
	{
		case 'password':
			echo '<p style="'.$display.'"><label>'.$label.'</label><br><input type="password" value="'.$value.'" name="plataform_settings_strings['.$key.']" size="80"></p>';
		break;
		case 'dropdown':
			if(array_key_exists($key, $plataformSettingsStrings['options']))
			{?>
				<p style="<?php echo $display; ?>"><label><?php echo $label; ?></label><br>
					<select name="plataform_settings_strings[<?php echo $key; ?>]"><?php
						foreach ($plataformSettingsStrings['options'][$key] as $optvalue => $optlabel)
						{
							$selected = $value == $optvalue ? ' selected="selected" ' : '';?>
							<option value="<?php echo $optvalue; ?>" <?php echo $selected; ?>>
								<?php echo $optlabel; ?>
							</option><?php
						}?>
					</select>
				</p><?php
			}
		break;
		case 'yesno':?>
			<p style="<?php echo $display; ?>"><label><?php echo $label; ?></label><br>
				<select name="plataform_settings_strings[<?php echo $key; ?>]">
					<option value="S" <?php echo $value ==  'S'? ' selected="selected" ' : ''; ?>><?php _e('Sim', 'redelivre'); ?></option>
					<option value="N" <?php echo $value ==  'N'? ' selected="selected" ' : ''; ?>><?php _e('Não', 'redelivre'); ?></option>
				</select>
			</p><?php
		break;
		case 'input':
		default:
			echo '<p style="'.$display.'"><label>'.$label.'</label><br><input type="text" value="'.$value.'" name="plataform_settings_strings['.$key.']" size="80"></p>';
		break;
	}	
}

echo '<input type="submit" value="Salvar">';
echo '</form>';