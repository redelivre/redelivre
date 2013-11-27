jQuery(document).ready(function()
{
	jQuery('#page_template').append(
		'<option value="mobilize">Mobilize</option>');
	if (templateData['slug'] == 'mobilize')
	{
		jQuery('#page_template option[value="mobilize"]').prop(
				'selected', true);
	}
});
