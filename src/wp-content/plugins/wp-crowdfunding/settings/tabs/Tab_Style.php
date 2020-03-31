<?php
defined( 'ABSPATH' ) || exit;

    $arr =  array(
                // #Style Seperator
                array(
                    'type'      => 'seperator',
                    'label'     => __('Style Settings','wp-crowdfunding'),
                    'top_line'  => 'true',
                    ),

                // #Enable Color Scheme
                array(
                    'id'        => 'wpneo_enable_color_styling',
                    'type'      => 'checkbox',
                    'value'     => 'true',
                    'label'     => __('Enable Color Styling','wp-crowdfunding'),
                    'desc'      => __('Enable color styling option for custom color layout.','wp-crowdfunding'),
                    ),
    
                // #Button Background Color
                array(
                    'id'        => 'wpneo_color_scheme',
                    'type'      => 'color',
                    'label'     => __('Color Scheme','wp-crowdfunding'),
                    'desc'      => __('Select color scheme of plugins.','wp-crowdfunding'),
                    'value'     => '#1adc68',
                    ),

                // #Button Background Color
                array(
                    'id'        => 'wpneo_button_bg_color',
                    'type'      => 'color',
                    'label'     => __('Button BG Color','wp-crowdfunding'),
                    'desc'      => __('Select button background color.','wp-crowdfunding'),
                    'value'     => '#1adc68',
                    ),

                // #Button Background Hover Color
                array(
                    'id'        => 'wpneo_button_bg_hover_color',
                    'type'      => 'color',
                    'label'     => __('Button BG Hover Color','wp-crowdfunding'),
                    'desc'      => __('Select button background hover color.','wp-crowdfunding'),
                    'value'     => '#2554ec',
                    ),
                
                // #Button Text Color
                array(
                    'id'        => 'wpneo_button_text_color',
                    'type'      => 'color',
                    'label'     => __('Button Text Color','wp-crowdfunding'),
                    'desc'      => __('Select button text color.','wp-crowdfunding'),
                    'value'     => '#fff',
                    ),

                // #Button Text Hover Color
                array(
                    'id'        => 'wpneo_button_text_hover_color',
                    'type'      => 'color',
                    'label'     => __('Button Text Hover Color','wp-crowdfunding'),
                    'desc'      => __('Select button text hover color.','wp-crowdfunding'),
                    'value'     => '#fff',
                    ),
                
                // #Custom CSS
                array(
                    'id'        => 'wpneo_custom_css',
                    'type'      => 'textarea',
                    'label'     => __('Custom CSS','wp-crowdfunding'),
                    'desc'      => __('Put custom CSS here.','wp-crowdfunding'),  
                    'value'     => '',
                    ),
                
                
                // #Save Function
                array(
                    'id'        => 'wpneo_crowdfunding_admin_tab',
                    'type'      => 'hidden',
                    'value'     => 'tab_style',
                    ),
    );
wpcf_function()->generator( $arr );