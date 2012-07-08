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
class WidgetUniquePost extends WidgetTemplate {

    public function getPostFromPermalink($config) {
        global $wpdb;
        $permalink = $config['permalink'];

        $post = null;
        preg_match('/.*\/(?<post_name>[^\/]+)/', $permalink, $matches);
        if (isset($matches['post_name'])) {
            $post_name = $matches['post_name'];

            $post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_name = '$post_name'");
        }

        return $post;
    }
    
    public function getLastPostFromCat($config) {
        $posts = get_posts(array(
            'numberposts' => 1,
            'category' => $config['cat']
        ));
        
        $post = false;
        
        if (is_array($posts) && sizeof($posts) > 0) {
            $post = $posts[0];
        }
        
        return $post;
        
    }

    protected function widget($config) {
        if ( (isset($config['widget_action']) && $config['widget_action'] == 'cat') || ( empty($config['widget_action']) && isset($config['cat']) && !empty($config['cat']) ) ) {
            $post = $this->getLastPostFromCat($config);
        } else {
            $post = $this->getPostFromPermalink($config);
        }
        
        if ($post):
            $permalink = get_permalink($post->ID);
            $categories = wp_get_post_terms($post->ID, 'category');
            ?>
            <article id="">
                <p class="category">
                    <?php foreach($categories as $i => $category): if($i > 0) echo ', ';?><a href="<?php echo get_category_link($category); ?>"><?php echo $category->name; ?></a><?php endforeach; ?>
                </p>
                <h3><a href="<?php echo $permalink ?>"><?php echo $post->post_title; ?></a></h3>
                <div class="excerpt"><a href="<?php echo $permalink ?>"><?php utils::postExcerpt($post, 144); ?></a></div>
            </article>
        <?php
        endif;
    }

    protected function form($config) {
		$id = uniqid('permalink');
        $action = isset($config['widget_action']) ? $config['widget_action'] : ''; 
        ?>
<input type="radio" name="widget_action" value="post" id="<?php echo $id; ?>_action_post" class="<?php echo $id; ?>_action" <?php if ($action == 'post' || $action == '') echo 'checked'; ?> />
<label for="<?php echo $id; ?>_action_post">Um post específico</label>
<div id="content-<?php echo $id; ?>-post" class="<?php echo $id; ?>_action_content"  >
    <label for="<?php echo $id; ?>">Copie a url do post e cole no campo abaixo.</label><br/>
    <input id="<?php echo $id; ?>" type="text" name="permalink" value="<?php echo $config['permalink']; ?>" />
</div>
<input type="radio" name="widget_action" value="cat" id="<?php echo $id; ?>_action_cat" class="<?php echo $id; ?>_action" <?php if ($action == 'post') echo 'checked'; ?> />
<label for="<?php echo $id; ?>_action_cat">O último post de uma categoria</label>
<div id="content-<?php echo $id; ?>-cat" class="<?php echo $id; ?>_action_content"  >
    <?php wp_dropdown_categories(array(
        'show_option_none' => '--------',
        'orderby' => 'name',
        'selected' => $config['cat']
    )); ?>
</div>
<br />
<p>Ao editar um post, escolha o seu "Formato" para determinar a maneira que ele será exibido neste espaço</p>
<br/>
        <?php
    }

    protected function getFormTitle() {
        return 'Exibir:';
    }

}
?>
