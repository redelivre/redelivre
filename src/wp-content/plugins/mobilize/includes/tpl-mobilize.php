<?php 

get_header();

$smartView = new smartView(INC_MOBILIZE.'/views/template.php');
$smartView->content = implode("\n", array(mobilize_template_chamada(), mobilize_shortag()));
$smartView->display(true);

get_footer();