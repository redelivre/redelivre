<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WidgetUniquePost
 *
 * @author rafael
 */
class WidgetVideoOrGallery extends WidgetUniquePost {

    protected function widget($config) {
        $post = $this->getPostFromPermalink($config);
        
        if ($post):
            $permalink = get_permalink($post->ID);
            $img = get_the_post_thumbnail($post->ID);
            $categories = wp_get_post_terms($post->ID, 'category');
            
            $type = isset($config['type']) ? $config['type'] : 'video';
            ?>

            <article id="" class="multimedia span-4 last">
                <p class="category"><?php foreach($categories as $i => $category): if($i > 0) echo ', ';?><a href="<?php echo get_category_link($category); ?>"><?php echo $category->name; ?></a><?php endforeach; ?></p>
                <h1><a href="<?php echo $permalink ?>"><?php echo $post->post_title; ?></a></h1>
                <?php if($type == 'video'): $video_url = get_post_meta($post->ID, '_video_url', true); ?> 
                    <?php html::video($video_url,'480','270'); ?>
                <?php else: ?>
                    <?php html::slideshow($post->ID,'post-grande'); ?>
                <?php endif; ?>
                <div class="excerpt"><a href="<?php echo $permalink ?>"><?php utils::postExcerpt($post, 144); ?></a></div>
            </article>

        <?php
        endif;
    }
    
    
    protected function form($config) {
        parent::form($config);
        
        $type = isset($config['type']) ? $config['type'] : 'video';
        ?>
<p>
<label><input type="radio" name="type" value="video" <?php if($type == 'video') echo 'checked="checked"'; ?>/> vídeo </label>
<label><input type="radio" name="type" value="gallery" <?php if($type == 'gallery') echo 'checked="checked"'; ?>/> galeria </label>
</p>
        <?
    }

    protected function getFormTitle() {
        return 'URL do Post';
    }

}
?>
