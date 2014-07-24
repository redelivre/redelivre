<?php 

$sectionFunction = array(
		'description' => mobilize_template_chamada,
		'socialnetworks' => mobilize_template_social,
		'banners' => mobilize_template_banners,
		'sticker' => mobilize_template_adesive,
		'share' => mobilize_template_enviar,
		'contribute' => mobilize_template_contribua
);

get_header();

$smartView = new smartView(INC_MOBILIZE.'/views/template.php');

$layout = Mobilize::getPageLayout(get_the_ID());

$content = array();
if ($layout === false)
{
	$content[] = mobilize_template_chamada();
	$content[] = mobilize_shortag();
}
else {
	foreach ($layout as $item)
	{
		if (array_key_exists($item, $sectionFunction))
		{
			$content[] = $sectionFunction[$item]();
		}
	}
}
$smartView->content = implode("\n", $content);

$smartView->display(true);

get_footer();
