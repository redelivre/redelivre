<?php 

$post_type_object = get_post_type_object('object');

// objetos criados pelos usuários
$suggested = new WP_Query(array('posts_per_page' => -1, 'post_type' => 'object', 'meta_key' => '_user_created', 'meta_value' => true));
$suggestedLabels = get_theme_option('suggested_labels');

get_header();

?>
    
<section id="main-section" class="span-15 prepend-1 append-1">
    <h2><?php echo $post_type_object->labels->name; ?></h2>
    <?php if (is_tax('object_type')) :
        $termDiscription = term_description( '', get_query_var( 'taxonomy' ) );
        
        if ($termDiscription != '') : ?>
            <div class="ementa-do-tema">
                <h1><?php wp_title("",true); ?></h1>
                <div class="append-bottom"></div>
                <?php echo $termDiscription; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php echo get_theme_option('object_list_intro'); ?>
    <?php endif; ?> 
    
    <section class="tema">
        <ul>
            <?php
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    html::part('loop-single-list-title');
                }
            }
            ?>
        </ul>

        <?php if ($suggested->have_posts()) : ?>
        <h4><?php echo $suggestedLabels['list']; ?></h4>
        <ul>
            <?php
                while ($suggested->have_posts()) {
                    $suggested->the_post();
                    html::part('loop-single-list-title');
                }
            ?>
        </ul>
        <?php endif; ?>
        
        <?php if (!have_posts() && !$suggested->have_posts()) : ?>
            <p><?php echo $post_type_object->labels->not_found; ?></p>
        <?php endif; ?>
        <?php html::part('add_new_object'); ?>
    </section>    
</section>
<!-- #main-section -->
    
<?php get_sidebar(); ?>
<?php get_footer(); ?>
