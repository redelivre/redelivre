<?php

class html {

    protected static $tabIndex = 0;

    /**
     * Imprime uma caixa de mensagem
     * @param array $msg
     * @param string $extra_class uma cla
     * @param string $id id da div que engloba as mensagens
     * @example html::messages(array('error' => array('primeira mensagem de erro', 'segunda mensagem de erro')));
     * @example html::messages(array('error' => array('primeira mensagem de erro', 'segunda mensagem de erro')), 'uma-classe-css');
     * @example html::messages(array('error' => array('primeira mensagem de erro', 'segunda mensagem de erro')), 'uma-classe-css outra-classe-css', 'id-da-div');
     */
    static function messages($msg, $extra_classes = '', $id = '') {
        echo self::getMessages($msg, $extra_classes, $id);
    }

    static function getMessages($msg, $extra_classes = '', $id = '') {
        $html = "";
        if (is_array($msg)) {
            if (is_array($extra_class))
                $extra_classes = implode(' ', $extra_classes);

            foreach ($msg as $type => $msgs) {
                if (!$msgs)
                    continue;

                $html .= "<div class='$type $extra_classes' id='$id'><ul>";
                if (!is_array($msgs)) {
                    $html .= "<li>$msgs</li>";
                } else {
                    foreach ($msgs as $m) {
                        $html .= "<li>$m</li>";
                    }
                }
                $html .= "</ul></div>";
            }
        }

        return $html;
    }

    /**
     * Imprime uma tag img... dados, nome de arquivo, alt [, complemento [, array html_attributes] ]
     * @param string $filename
     * @param string $alt
     */
    static function image($filename, $alt) {
        $html_attributes = array();
        $complement = null;
        for ($i = 2; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (is_array($arg))
                $html_attributes = $arg;

            if (is_string($arg))
                $complement = $arg;
        }

        echo self::getImage($filename, $alt, $complement, $html_attributes);
    }

    /**
     * Retorna uma tag img... dados nome de arquivo, alt [, complemento [, html_attributes] ]
     * @param string $filename
     * @param string $alt
     */
    static function getImage($filename, $alt) {
        $html_attributes = array();
        $complement = null;
        for ($i = 2; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (is_array($arg))
                $html_attributes = $arg;

            if (is_string($arg))
                $complement = $arg;
        }
        $url = self::getImageUrl($filename, $complement);
        $img_attr = "";
        foreach ($html_attributes as $attr => $val)
            $img_attr.= $attr . '="' . $val . '" ';

        $alt = htmlentities(utf8_decode($alt));
        return "<img src=\"$url\" alt=\"$alt\" $img_attr/>";
    }

    /**
     * Retorna a url da imagem dados nome de arquivo e complemento
     * @param string $filename
     * @param string $complement null
     */
    static function getImageUrl($filename, $complement = null) {
        $filenames = array();
        $filename = 'img/' . $filename;

        if (is_string($complement))
            $filenames[] = preg_replace('/\.[^\.]+$/', '-' . $complement . '$0', $filename);

        $filenames[] = $filename;

        foreach ($filenames as $fname) {
            if (file_exists(STYLESHEETPATH . '/' . $fname)) {
                return get_stylesheet_directory_uri() . '/' . $fname;
            }

            if (file_exists(TEMPLATEPATH . '/' . $fname)) {
                return get_template_directory_uri() . '/' . $fname;
            }
        }
        return $filename;
    }

    /**
     * Semelhante ao get_template_part, porém os arquivos devem ficar dentro da pasta parts/ do thema,<br/> 
     * Se um array for enviado como segundo ou terceiro parâmetro este será extraido usando extract($array_passado, EXTR_PREFIX_INVALID, 'var');<br/>
     * Se um string for enviado como segundo ou terceiro parâmetro, este será utilizado como o segundo parâmetro do get_template_part
     *   
     * @param string $slug
     * 
     * @example part('slug'); inclui o arquivo parts/slug.php
     * @example part('slug', 'name'); inclui o arquivo parts/slug-name.php
     * @example part('slug', array('var1'=>'valor da var'); inclui o arquivo parts/slug.php e torna $var1 acessível
     * @example part('slug', 'name', array('var1'=>'valor da var'); inclui o arquivo parts/slug-name.php e torna $var1 acessível
     */
    static function part($slug) {
        $_part_vars = array();
        $name = null;
        for ($i = 1; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (is_array($arg))
                $_part_vars = $arg;

            if (is_string($arg))
                $name = $arg;
        }

        $slug = 'parts/' . $slug;

        if ($name)
            $templates[] = "{$slug}-{$name}.php";

        $templates[] = "{$slug}.php";

        // extraindo as variaveis
        extract($_part_vars, EXTR_PREFIX_INVALID, 'var');


        global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

        if (is_array($wp_query->query_vars))
            extract($wp_query->query_vars, EXTR_SKIP);


        foreach ($templates as $filename) {
            if (file_exists(STYLESHEETPATH . '/' . $filename)) {
                require STYLESHEETPATH . '/' . $filename;
                return;
            }

            if (file_exists(TEMPLATEPATH . '/' . $filename)) {
                require TEMPLATEPATH . '/' . $filename;
                return;
            }
        }
    }

    static function getVideoThumbURL($video_url, $size = 'small') {
        $result = null;

        $video_type = utils::getVideoTypeFromURL($video_url);

        if ($video_type == 'vimeo') {
            $video_id = utils::getVimeoVideoIdFromVideoURL($video_url);
            $api_endpoint = 'http://vimeo.com/api/v2/video/' . $video_id . '.xml';

            $curl = curl_init($api_endpoint);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $str_xml = curl_exec($curl);
            curl_close($curl);

            $xml = simplexml_load_string($str_xml);

            $result = $xml->video->{'thumbnail_medium'};
        } elseif ($video_type == 'youtube') {
            $video_id = utils::getYoutubeVideoIdFromVideoURL($video_url);

            if ($size == 'small')
                $result = "http://img.youtube.com/vi/$video_id/0.jpg";
            else
                $result = "http://img.youtube.com/vi/$video_id/1.jpg";
        }

        return $result;
    }

    static function videoThumbURL($video_url, $size) {
        echo self::getVideoThumbURL($video_url, $size);
    }

    static function getVideo($video_url, $width = '100%', $height = '100%') {
        $video_type = utils::getVideoTypeFromURL($video_url);
        if ($video_type == 'vimeo') {
            $video_id = utils::getVimeoVideoIdFromVideoURL($video_url);
            return "<iframe src=\"http://player.vimeo.com/video/{$video_id}?title=0&amp;byline=0&amp;portrait=0\" width=\"$width\" height=\"$height\" frameborder=0 webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
        } elseif ($video_type == 'youtube') {
            $video_id = utils::getYoutubeVideoIdFromVideoURL($video_url);
            ob_start();
            ?>
            <iframe width="420" height="315" src="http://www.youtube.com/embed/-A1jt2VC5yA" frameborder="0" allowfullscreen></iframe>
            <?php
            return ob_get_clean();
        } else {
            return null;
        }
    }

    static function video($video_url, $width = '100%', $height = '100%') {
        echo self::getVideo($video_url, $width, $height);
    }

    /**
     * cria um slideshow com as imagens anexadas ao post com o ID informado
     * @param int $post_id
     * @param string $image_size
     * @param array() $img_attributes 
     */
    static function slideshow($post_id, $image_size = 'medium', $img_attributes = array()) {
        $images = utils::getAttchedImages($post_id);
        $uid = uniqid('hl-slideshow-');
        echo '<div id="' . $uid . '" class="hl-slideshow clear">';

        foreach ($images as $image):
            $img_src = wp_get_attachment_image_src($image->ID, $image_size);
            $img_attr = "";
            //$_styles = ''
            foreach ($img_attributes as $key => $val) {
                $img_attr .= " {$key}=\"{$val}\"";
            }
            ?>
            <img src="<?php echo $img_src[0]; ?>" <?php echo $img_attr; ?>/>
            <?php
        endforeach;

        echo '</div>';
        ?>
        <script type="text/javascript">
            (function($){
                $(document).ready(function(){
                    $('#<?php echo $uid; ?> img').css({
                        position: 'absolute'
                    }).hide();
                    $('#<?php echo $uid; ?> img:first').show().load(function(){
                        $('#<?php echo $uid; ?>').height($(this).height());
                    });
                        
                    $('#<?php echo $uid; ?>').each(function(){
                        var delay = 6000;
                        var $slideshow = $(this);
                        function change($slideshow){
                            var $visible = $slideshow.find('img:first');
                            $slideshow.css('height',$visible.height());
                            var $next = $visible.next();
                            $next.fadeIn();
                            $visible.fadeOut(function(){
                                    
                                $slideshow.find('img:last').after($visible);
                                $slideshow.data('timeout',setTimeout(function(){change($slideshow)},delay));
                            });
                        };
                        $slideshow.data('timeout',setTimeout(function(){change($slideshow)},delay));
                    });
                });                        
            })(jQuery);
        </script>
        <?php
    }

    static function getPrintMeta($id, $meta_name, $after = "", $before = "") {
       $meta = get_post_meta($id, $meta_name, true);
        if($meta) {
            return $after.$meta.$before;
        }
        return "";
    }

    static function printMeta($id, $meta_name, $after = "", $before = "") {
        echo self::getPrintMeta($id, $meta_name, $after, $before);
    }
}