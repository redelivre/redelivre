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

    protected function widget($config) {
        $post = $this->getPostFromPermalink($config);

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
        ?>
<small>copie a url do post que você deseja e cole no espaço abaixo. </small><br/>
<input type="text" name="permalink" value="<?php echo $config['permalink']; ?>" style="width: 100%"/>
        <?php
    }

    protected function getFormTitle() {
        return 'URL do Post';
    }

}
?>
