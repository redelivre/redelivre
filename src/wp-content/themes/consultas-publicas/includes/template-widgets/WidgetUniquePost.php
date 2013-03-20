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

include_once ('WidgetTemplate.php');

class WidgetUniquePost extends WidgetTemplate {

    public function getPostFromPermalink($config) {
        global $wpdb;
        $permalink = $config['permalink'];

        $post = null;
        $matches = array();
        preg_match('/.*\/(?<post_name>[^\/]+)/', $permalink, $matches);
        if (isset($matches['post_name'])) {
            $post_name = $matches['post_name'];

            $post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_name = '$post_name'");
        }

        return $post;
    }

    protected function widget($config) {}

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
