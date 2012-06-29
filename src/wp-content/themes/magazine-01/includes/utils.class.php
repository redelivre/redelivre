<?php

class utils {

    static function getPostExcerpt($post, $max_len, $more_link = false) {
        $excerpt = '';
        if (trim($post->post_excerpt)) {
            $content = apply_filters('get_the_excerpt', $post->post_excerpt);
        } else {
            $content = apply_filters('the_content', $post->post_content);
            $content = strip_tags($content);
        }


        if ($content) {
            if($more_link){
                $link_url = get_permalink($post->ID);
                $more_link = "<a href=\"$link_url\">...</a>";
                $more_link = apply_filters('utils_excerpt_more_link', $more_link, $post);
                
                $tp = $more_link;
            }else{
                $tp = '...';
            }
            
            $content = str_ireplace('<br>', "\n", str_ireplace('<br />', "\n", $content));
            
            $content = strip_tags($content);
            
            if (strpos($content, '<!-- more -->') > 0) {
                $exploded = explode('<!-- more -->', $content);
                $excerpt = $exploded[0];
                $excerpt = utf8_encode(substr(utf8_decode($excerpt), 0, $max_len)) . $tp;
            } else {
                if (strlen($content) > $max_len)
                    while ($content[$max_len] != ' ' && $max_len > 0)
                        $max_len--;
                
                $excerpt = utf8_encode(substr(utf8_decode($content), 0, $max_len-1)) . $tp;
            }
        }
        
        $excerpt = nl2br($excerpt);
        
        return $excerpt;
    }

    static function postExcerpt($post, $max_len, $more_url = '', $more_label = '...') {
        $excerpt = self::getPostExcerpt($post, $max_len, $more_url, $more_label);
        echo apply_filters('the_excerpt', $excerpt);
    }

    static function getAttchedImages($post_id) {
        $args = array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_parent' => $post_id,
            'post_mime_type' => 'image'
        );

        $attachments = get_posts($args);

        return $attachments;
    }

    static function getYoutubeVideoIdFromVideoURL($youtube_url) {
        if (preg_match("/[?&]v=([^&]+)/", $youtube_url, $matches) || preg_match("#youtube.com/v/([^&]+)#", $youtube_url, $matches))
            return $matches[1];
        else
            return null;
    }

    static function getVimeoVideoIdFromVideoURL($video_url) {
        if (preg_match("/http:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/", $video_url, $matches))
            return $matches[2];
        else
            return null;
    }

    static function getVideoTypeFromURL($video_url) {
        if (preg_match('#^https?://(www\.)?(youtube|vimeo).com/#', $video_url, $source))
            return strtolower($source[2]);
        else
            return null;
    }

}

?>
