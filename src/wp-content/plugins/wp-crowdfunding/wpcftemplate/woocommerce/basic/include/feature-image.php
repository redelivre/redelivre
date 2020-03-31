<?php
defined( 'ABSPATH' ) || exit;
global $post, $woocommerce, $product;
$funding_video = trim(get_post_meta($post->ID, 'wpneo_funding_video', true)); ?>
<div class="wpneo-campaign-single-left-info">
    <?php if (!empty($funding_video)) { ?>
        <div class="wpneo-post-img">
            <?php echo wpcf_function()->get_embeded_video($funding_video); ?>
            <?php do_action( 'woocommerce_product_thumbnails' ); ?>
        </div>
    <?php
    } else {
        $columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
        $post_thumbnail_id = get_post_thumbnail_id( $post->ID );
        $full_size_image   = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
        $image_title       = get_post_field( 'post_excerpt', $post_thumbnail_id );
        $placeholder       = has_post_thumbnail() ? 'with-images' : 'without-images';
        $wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
            'woocommerce-product-gallery',
            'woocommerce-product-gallery--' . $placeholder,
            'woocommerce-product-gallery--columns-' . absint( $columns ),
            'images',
        ) );?>
        <div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
            <figure class="woocommerce-product-gallery__wrapper">
                <?php
                $attributes = array(
                    'title'                   => $image_title,
                    'data-src'                => $full_size_image[0],
                    'data-large_image'        => $full_size_image[0],
                    'data-large_image_width'  => $full_size_image[1],
                    'data-large_image_height' => $full_size_image[2],
                );
                if ( has_post_thumbnail() ) {
                    $html  = '<div data-thumb="' . get_the_post_thumbnail_url( $post->ID, 'shop_thumbnail' ) . '" class="woocommerce-product-gallery__image"><a itemprop="image" class="woocommerce-main-image cloud-zoom" href="'.esc_url( $full_size_image[0] ).'">';
                    $html .= get_the_post_thumbnail( $post->ID, 'shop_single', $attributes );
                    $html .= '</a></div>';
                } else {
                    $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
                    $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'backer' ) );
                    $html .= '</div>';
                }
                echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, get_post_thumbnail_id( $post->ID ) );
                do_action( 'woocommerce_product_thumbnails' );
                ?>
            </figure>
        </div>
    <?php
    } ?>
    <?php do_action( 'wpcf_after_feature_img' ); ?>
</div>