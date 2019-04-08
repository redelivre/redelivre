<?php 

$post_type_object = get_post_type_object('object');
$include = array();

if ($objectTypeSlug = $wp_query->get('object_type')) {
    $objectType = get_term_by('slug', $objectTypeSlug, 'object_type');
    $include[] = $objectType->term_id;
}

$types = get_terms('object_type', array('orderby' => 'id', 'order' => 'ASC', 'include' => $include));
$suggestedLabels = get_theme_option('suggested_labels');

get_header();

?>
    
<section id="main-section" class="span-15 prepend-1 append-1">
    <h2><?php echo $post_type_object->labels->name; ?></h2>
    
    <?php echo get_theme_option('object_list_intro'); ?>
    
    <?php foreach ($types as $type): ?>
        <section class="tema">
            <?php
            
            $objects = new WP_Query(
                array('posts_per_page' => -1, 'post_type' => 'object', 'object_type' => $type->slug, 'meta_key' => '_user_created', 'meta_value' => false)
            );
            $suggested_objects = new WP_Query(
                array('posts_per_page' => -1, 'post_type' => 'object', 'meta_key' => '_user_created', 'meta_value' => true, 'object_type' => $type->slug)
            );
            
            $termDescription = term_description( $type->term_id, 'object_type' );
    
            if ($termDescription != '') : ?>
                <header>
                    <h1><a href="<?php echo get_term_link($type->slug, 'object_type'); ?>"><?php echo $type->name; ?></a></h1>
                </header>
            <?php endif; ?>
            
            <ul>
                <?php 
                if ($objects->have_posts()) {
                    while ($objects->have_posts()) {
                        $objects->the_post();
                        html::part('loop-single-list-title-taxonomy');
                    }
                }
                ?>
            </ul>
            
            <?php if ($suggested_objects->have_posts()) : ?>
                <h4><?php echo $suggestedLabels['list']; ?></h4>
                <ul>
                    <?php 
                        while ($suggested_objects->have_posts()) {
                            $suggested_objects->the_post();
                            html::part('loop-single-list-title-taxonomy');
                        }
                    ?>
                </ul>
            <?php endif; ?>
            <?php
            
            if (!$objects->have_posts() && !$suggested_objects->have_posts()) {
                $post_type_object = get_post_type_object('object');
                echo '<p>';
                echo $post_type_object->labels->not_found;
                echo '</p>';
            }
            
            html::part('add_new_object');
            
            ?>
        </section>
    <?php endforeach; ?>
</section>
<!-- #main-section -->
    
<?php get_sidebar(); ?>
<?php get_footer(); ?>
