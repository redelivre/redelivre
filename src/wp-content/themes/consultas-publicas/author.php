<?php 

$list_type = get_theme_option('list_type');

if ($list_type == 'title_taxonomy' && !taxonomy_exists('object_type')) {
    $list_type = 'title';
}

$labels = get_theme_option('suggested_labels');
$authorInfo = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));

get_header();

?>	
<section id="main-section" class="span-15 prepend-1 append-1">
    <header>
	    <h1><?php echo $authorInfo->display_name;?></h1>
	</header>
    
    <?php do_action('consulta_user_profile', array($authorInfo)); ?>
    
    <h4><?php echo $labels['list_user_page']; ?></h4>
    <section class="tema">
        <ul>
            <?php
            if ($list_type == 'title') {
                html::part('loop-single-list-title');
            } else if ($list_type == 'title_taxonomy') {
                html::part('loop-single-list-title-taxonomy');
            } else {
                html::part('loop-single-list-normal');
            }
            ?>
        </ul>
    </section>   
</section>
<!-- #main-section -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
