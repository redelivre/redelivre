function buildLayoutString()
{
	var s = [];
	jQuery('#mobilize-layout option').each(function()
		{
			s.push(jQuery(this).attr('name').split("-", 2)[1]);
		});

	return s.join(';');
}

function initializeLayoutEditor()
{
	jQuery('#mobilize-layout option').each(function()
		{
			var template = jQuery('#mobilize-layout-template option[name=' +
					jQuery(this).attr('name') + ']');
			if (template.length)
			{
				jQuery(this).append(template.val());
			}
			else
			{
				jQuery(this).remove();
			}
		});
}

jQuery(document).ready(function()
{
	jQuery('#page_template option[value="mobilize_force_dropdown"]').remove();
	jQuery('#page_template').append(
		'<option value="mobilize">Mobilize</option>');
	if (templateData['slug'] == 'mobilize')
	{
		jQuery('#page_template option[value="mobilize"]').prop(
				'selected', true);
		jQuery('#mobilize-edit-customize').insertAfter(
				jQuery('.edit-form-section'));
		jQuery('.edit-form-section').hide();

		// Para não ficar feio enquanto carrega a página
		jQuery('#mobilize-edit-customize').show();

		jQuery('#mobilize-layout').width(
				jQuery('#mobilize-layout-template').width());
		jQuery('#mobilize-layout').height(
				jQuery('#mobilize-layout-template').height());
		jQuery('#mobilize-down').width(
				jQuery('#mobilize-up').width());

		initializeLayoutEditor();

		if (!jQuery('#mobilize-custom').is(':checked'))
		{
			jQuery('#mobilize-layout-editor').hide();
		}

		jQuery('#mobilize-add').click(function()
				{
					if (jQuery('#mobilize-layout option').length < 16)
					{
						jQuery(
								'#mobilize-layout-template option:selected').clone().appendTo(
									'#mobilize-layout');
						jQuery('#mobilize-layout-string').val(buildLayoutString());
					}
				});
		jQuery('#mobilize-del').click(function()
				{
					jQuery('#mobilize-layout option:selected').remove();
					jQuery('#mobilize-layout-string').val(buildLayoutString());
				});
		jQuery('#mobilize-up').click(function()
				{
					jQuery('#mobilize-layout option:selected').insertBefore(
							jQuery('#mobilize-layout option:selected').prev());
					jQuery('#mobilize-layout-string').val(buildLayoutString());
				});
		jQuery('#mobilize-down').click(function()
				{
					jQuery('#mobilize-layout option:selected').insertAfter(
							jQuery('#mobilize-layout option:selected').next());
					jQuery('#mobilize-layout-string').val(buildLayoutString());
				});

		jQuery('#mobilize-custom-label').click(function()
				{
					if (jQuery('#mobilize-custom').is(':checked'))
					{
						jQuery('#mobilize-layout-editor').slideDown();
					}
					else
					{
						jQuery('#mobilize-layout-editor').slideUp();
					}
				});
	}
});
