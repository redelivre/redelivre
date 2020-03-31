<?php
defined( 'ABSPATH' ) || exit;

// #Social Share Settings (Tab Settings)
$arr =  array(
            // #Listing Page Seperator
            array(
                'type'      => 'seperator',
                'label'     => __('Social Share Settings','wp-crowdfunding'),
                'desc'      => __(''),
                'top_line'  => 'true',
                ),
                
            // #Enable Embed Option
            array(
                'id'        => 'wpcf_embed_share',
                'type'      => 'checkbox',
                'value'     => 'true',
                'label'     => __('Embed Option','wp-crowdfunding'),
                'desc'      => __('Embed Option in Single Campaign.','wp-crowdfunding'),
            ),

            // Social Share
            array(
                'id'        => 'wpcf_social_share',
                'type'      => 'checkbox',
                'multiple'  => 'true',
                'option'    => array(
                                    'twitter' => __( 'Twitter', 'wp-crowdfunding' ),
                                    'facebook' => __( 'Facebook', 'wp-crowdfunding' ),
                                    'pinterest' => __( 'Pinterest', 'wp-crowdfunding' ),
                                    'linkedin' => __( 'Linkedin', 'wp-crowdfunding' ),
                                    'tumblr' => __( 'Tumblr', 'wp-crowdfunding' ),
                                    'blogger' => __( 'Blogger', 'wp-crowdfunding' ),
                                    'delicious' => __( 'Delicious', 'wp-crowdfunding' ),
                                    'digg' => __( 'Digg', 'wp-crowdfunding' ),
                                    'reddit' => __( 'Reddit', 'wp-crowdfunding' ),
                                    'stumbleupon' => __( 'Stumbleupon', 'wp-crowdfunding' ),
                                    'pocket' => __( 'Pocket', 'wp-crowdfunding' ),
                                    'wordpress' => __( 'WordPress', 'wp-crowdfunding' ),
                                    'whatsapp' => __( 'Whatsapp', 'wp-crowdfunding' ),
                                ),
                'label'     => __('Multiple Checkbox','wp-crowdfunding'),
                'desc'      => __('Select Multiple Checkbox form.','wp-crowdfunding'),
            ),

            // #Save Function
            array(
                'id'        => 'wpcf_varify_share',
                'type'      => 'hidden',
                'value'     => 'true',
            ),
);
wpcf_function()->generator( $arr );
