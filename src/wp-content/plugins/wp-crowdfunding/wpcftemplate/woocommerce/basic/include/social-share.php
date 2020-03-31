<?php
defined( 'ABSPATH' ) || exit;

$enable_social = false;
$post_id = get_the_ID();
$social = get_option( 'wpcf_social_share' , array() );
$embed = get_option( 'wpcf_embed_share' ) == 'true' ? true : false;

$description = apply_filters( 'the_excerpt', get_post_field('post_excerpt', $post_id) );
$post_thumbnail_url = '';
if ( has_post_thumbnail() ) {
    $post_thumbnail_id = get_post_thumbnail_id( $post_id );
    $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
}
?>
    <?php if( is_array( $social ) ){ ?>
        <?php if( !empty( $social ) ){ ?>
            <?php $enable_social = true; ?>
            <div class="social-container">
                <span><?php _e('Share: ','wp-crowdfunding'); ?></span>
                <div class="links">
                    <?php if( in_array( 'facebook', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_facebook"><i class="wpneo-icon wpneo-icon-facebook"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'twitter', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_twitter"><i class="wpneo-icon wpneo-icon-twitter"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'pinterest', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_pinterest"><i class="wpneo-icon wpneo-icon-pinterest"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'linkedin', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_linkedin"><i class="wpneo-icon wpneo-icon-linkedin"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'tumblr', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_tumblr"><i class="wpneo-icon wpneo-icon-tumblr"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'blogger', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_blogger"><i class="wpneo-icon wpneo-icon-blogger"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'delicious', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_delicious"><i class="wpneo-icon wpneo-icon-delicious"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'digg', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_digg"><i class="wpneo-icon wpneo-icon-digg"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'reddit', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_reddit"><i class="wpneo-icon wpneo-icon-reddit"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'stumbleupon', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_stumbleupon"><i class="wpneo-icon wpneo-icon-stumbleupon"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'pocket', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_pocket"><i class="wpneo-icon wpneo-icon-pocket"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'wordpress', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_wordpress"><i class="wpneo-icon wpneo-icon-wordpress"></i> </a>
                    <?php } ?>
                    <?php if( in_array( 'whatsapp', $social ) ){ ?>
                        <a href="javascript:;" class="wpcf-share s_whatsapp"><i class="wpneo-icon wpneo-icon-whatsapp"></i> </a>
                    <?php } ?>
                    <?php if( $embed ){ ?>
                        <a href="javascript:;" class="embedlink" data-postid="<?php echo the_permalink( $post_id ); ?>"><i class="wpneo-icon wpneo-icon-embed" data-postid="<?php echo $post_id; ?>"></i></a>
                    <?php } ?>
                </div>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function($){
                    $('.wpcf-share').ShareLink({
                        title: "<?php echo get_the_title( $post_id ); ?>",
                        text: "<?php echo sanitize_title(wp_strip_all_tags($description)); ?>",
                        image: "<?php echo $post_thumbnail_url; ?>",
                        url: "<?php echo get_permalink( $post_id ); ?>"
                    });
                });
            </script>
        <?php } ?>
    <?php } ?>

    <?php if( !$enable_social && $embed ){ ?>
        <div class="social-container">
            <span><?php _e('Share: ','wp-crowdfunding'); ?></span>
            <div class="links">
                <a href="javascript:;" class="embedlink" data-postid="<?php echo the_permalink( $post_id ); ?>"><i class="wpneo-icon wpneo-icon-embed" data-postid="<?php echo $post_id; ?>"></i></a>
            </div>
        </div>
    <?php } ?>