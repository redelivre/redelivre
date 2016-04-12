<?php

class utils {

    static function getPostExcerpt($post, $max_len, $more_link = false, $more_label = false) {
        $excerpt = '';
        if (trim($post->post_excerpt)) {
            $content = apply_filters('get_the_excerpt', $post->post_excerpt);
        } else {
            $content = apply_filters('the_content', $post->post_content);
            $content = strip_tags($content);
        }

        if (!$more_label) {
            $more_label = '...';
        }

        if ($content) {
            if ($more_link) {
                $link_url = get_permalink($post->ID);
                $more_link = "<br /><a href=\"$link_url\">$more_label</a>";
                $more_link = apply_filters('utils_excerpt_more_link', $more_link, $post);

                $tp = $more_link;
            } else {
                $tp = '...';
            }

            $content = str_ireplace('<br>', "\n", str_ireplace('<br />', "\n", $content));

            $content = strip_tags($content);

            if (strpos($content, '<!-- more -->') > 0) {
                $exploded = explode('<!-- more -->', $content);
                $excerpt = $exploded[0];
                $excerpt = utf8_encode(substr(utf8_decode($excerpt), 0, $max_len)) . $tp;
            } else {
                if (strlen($content) > 0)
                    while (isset($content[$max_len]) && $content[$max_len] != ' ' && $max_len > 0)
                        $max_len--;

                $excerpt = utf8_encode(substr(utf8_decode($content), 0, $max_len - 1)) . $tp;
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

    static function getResizedImagePath($filename, $size, $fit = 'inside', $quality = 80) {
    	if(!class_exists('WideImage', false)) require_once dirname(__FILE__) . '/lib/wideimage/WideImage.php';

        if (file_exists($filename)) {
            $height = $width = null;
            $size = explode(',', $size);

            if (is_array($size)) {
                if (count($size) == 2) {
                    $width = $size[0];
                    $height = $size[1];
                } else if (count($size) == 1) {
                    $width = $size[0];
                    $height = $size[0];
                }
            }
            $thumb_fname = preg_replace("/(.*)(\.[[:alnum:]]+)/", "$1-{$width}x{$height}-{$fit}$2", $filename);
            $thumbs_dir = dirname($thumb_fname) . '/r-thumbs';
            $thumb_fname = str_replace(dirname($thumb_fname), $thumbs_dir, $thumb_fname);

            if (!is_dir($thumbs_dir) && !file_exists($thumbs_dir))
                mkdir($thumbs_dir);


            if (!file_exists($thumb_fname)) {
                $img = WideImage::load($filename);
                if ($fit == 'crop') {
                    $resized = $img->resize($width, $height, 'outside')->crop('center', 'center', $width, $height);
                } else {
                    $resized = $img->resize($width, $height, $fit);
                }
                $thumb_fname = preg_replace("/\.[a-z]{3,4}$/i", '.jpg', $thumb_fname);
                $resized->saveToFile($thumb_fname, $quality);
            }

            return $thumb_fname;
        } else {
            return null;
        }
    }

    /**
     * Converte uma data do formato do mysql (yyyy-mm-dd) para o formato brasileiro (dd/mm/yyyy)
     * @param string $sql_date
     * @return string 
     */
    static function sqlDate2brDate($sql_date) {
        return preg_replace("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", "$3/$2/$1", $sql_date);
    }

    /**
     * Converte uma data do formato brasileiro (dd/mm/yyyy) para o formato do mysql (yyyy-mm-dd)
     * @param string $br_date
     * @return string 
     */
    static function brDate2sqlDate($br_date) {
        return preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $br_date);
    }

}

?>
