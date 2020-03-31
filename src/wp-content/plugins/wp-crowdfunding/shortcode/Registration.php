<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Registration {

    function __construct() {
        add_shortcode( 'wpcf_registration', array( $this, 'registration_callback' ) );
        
        add_action( 'wp_ajax_wpcf_registration', array( $this, 'registration_save_action' ) );
        add_action( 'wp_ajax_nopriv_wpcf_registration', array( $this, 'registration_save_action' ) );
    }
    
    public function registration_callback() {
        ob_start();
        if ( is_user_logged_in() ) { ?>
            <h3 class="wpneo-center"><?php _e("You are already logged in.","wp-crowdfunding"); ?></h3>
        <?php } else {
          global $reg_errors,$reg_success;
          ?>
            <div class="wpneo-user-registration-wrap">
                <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" id="wpneo-registration" method="post">
                <?php echo wp_nonce_field( 'wpcf_form_action', 'wpcf_form_action_field', true, false ); ?>
                    <?php
                    $regisration_data = array(
                        array(
                            'id'            => 'fname',
                            'label'         => __( "First Name" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter First Name', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => '',
                            'warpclass'     => 'wpneo-first-half',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'lname',
                            'label'         => __( "Last Name" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter Last Name', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => '',
                            'warpclass'     => 'wpneo-second-half',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'username',
                            'label'         => __( "Username *" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter Username', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => 'required',
                            'warpclass'     => '',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'password',
                            'label'         => __('Password *', 'wp-crowdfunding'),
                            'type'          => 'password',
                            'placeholder'   => __('Enter Password', 'wp-crowdfunding'),
                            'class'         => 'required',
                            'warpclass'     => '',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'email',
                            'label'         => __( "Email *" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter Email', 'wp-crowdfunding'),
                            'value'         => '',
                            'warpclass'     => 'wpneo-first-half',
                            'class'         => 'required',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'website',
                            'label'         => __( "Website" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter Website', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => '',
                            'warpclass'     => 'wpneo-second-half',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'nickname',
                            'label'         => __( "Nickname" , "wp-crowdfunding" ),
                            'type'          => 'text',
                            'placeholder'   => __('Enter Nickname', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => '',
                            'warpclass'     => '',
                            'autocomplete'  => 'off',
                        ),
                        array(
                            'id'            => 'bio',
                            'label'         => __( "About / Bio" , "wp-crowdfunding" ),
                            'type'          => 'textarea',
                            'placeholder'   => __('Enter About / Bio', 'wp-crowdfunding'),
                            'value'         => '',
                            'class'         => '',
                            'warpclass'     => '',
                            'autocomplete'  => 'off',
                        )
                    );
    
                    $regisration_meta = apply_filters('wpcf_user_registration_fields', $regisration_data );
    
                    foreach( $regisration_meta as $item ){ ?>
                        <div class="wpneo-single <?php echo (isset($item['warpclass'])? $item['warpclass'] : "" ); ?>">
                            <div class="wpneo-name"><?php echo (isset($item['label'])? $item['label'] : "" ); ?></div>
                            <div class="wpneo-fields">
                                <?php
                                switch ($item['type']){
                                    case 'text':
                                    echo '<input type="text" id="'.$item['id'].'" autocomplete="'.$item['autocomplete'].'" class="'.$item['class'].'" name="'.$item['id'].'" placeholder="'.$item['placeholder'].'">';
                                        break;
                                    case 'password':
                                    echo '<input type="password" id="'.$item['id'].'" autocomplete="'.$item['autocomplete'].'" class="'.$item['class'].'" name="'.$item['id'].'" placeholder="'.$item['placeholder'].'">';
                                        break;
                                    case 'textarea':
                                    echo '<textarea id="'.$item['id'].'" autocomplete="'.$item['autocomplete'].'" class="'.$item['class'].'" name="'.$item['id'].'" ></textarea>';
                                        break;
                                    case 'submit':
                                    echo '<input type="submit" id="'.$item['id'].'"  class="'.$item['class'].'" name="'.$item['id'].'" />';
                                        break;
                                    case 'shortcode':
                                    echo do_shortcode($item['shortcode']);
                                        break;
                                } ?>
                            </div>
                        </div>
                    <?php } ?>
    
                    <div class="wpneo-single wpneo-register">
                        <a href="<?php echo get_home_url(); ?>" class="wpneo-cancel-campaign"><?php _e("Cancel","wp-crowdfunding"); ?></a>
                        <input type="hidden" name="action" value="wpcf_registration" />
                        <input type="hidden" name="current_page" value="<?php echo get_the_permalink(); ?>" />
                        <input type="submit" class="wpneo-submit-campaign" id="user-registration-btn" value="<?php _e('Sign UP', 'wp-crowdfunding'); ?>" name="submits" />
                    </div>
    
                </form>
            </div>
            <?php
        }
        return ob_get_clean();
    }
    
    
    
    // register a new user
    public function registration_save_action() {
        if ( ! isset( $_POST['wpcf_form_action_field'] ) || ! wp_verify_nonce( $_POST['wpcf_form_action_field'], 'wpcf_form_action' ) ) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your status did not verify.', 'wp-crowdfunding'))));
            exit;
        }
    
        //Add some option
        do_action( 'wpcf_before_user_registration_action' );

        $username = $password = $email = $website = $first_name = $last_name = $nickname = $bio = '';
        // sanitize user form input
        $username   =   sanitize_user($_POST['username']);
        $password   =   sanitize_text_field($_POST['password']);
        $email      =   sanitize_email($_POST['email']);
        $website    =   esc_url_raw($_POST['website']);
        $first_name =   sanitize_text_field($_POST['fname']);
        $last_name  =   sanitize_text_field($_POST['lname']);
        $nickname   =   sanitize_text_field($_POST['nickname']);
        $bio        =   implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['bio'])));
        $this->registration_validation( $username , $password , $email , $website , $first_name , $last_name , $nickname , $bio );
        $this->complete_registration( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio );
    }
    
    public function complete_registration( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio ) {
        global $reg_errors;
        if ( count($reg_errors->get_error_messages()) < 1 ) {
            $userdata = array(
                'user_login'    =>  $username,
                'user_email'    =>  $email,
                'user_pass'     =>  $password,
                'user_url'      =>  $website,
                'first_name'    =>  $first_name,
                'last_name'     =>  $last_name,
                'nickname'      =>  $nickname,
                'description'   =>  $bio
            );
            $user_id = wp_insert_user( $userdata );
    
            //On success
            if ( !is_wp_error( $user_id ) ) {
                WC()->mailer(); // load email classes
                do_action( 'wpcf_after_registration', $user_id );
    
                $saved_redirect_uri = get_option('wpcf_user_reg_success_redirect_uri');
                $redirect = $saved_redirect_uri ? $saved_redirect_uri : esc_url( home_url( '/' ) );
                die(json_encode(array('success'=> 1, 'message' => __('Registration complete.', 'wp-crowdfunding'), 'redirect' => $redirect )));
            } else {
                $errors = '';
                if ( is_wp_error( $reg_errors ) ) {
                    foreach ( $reg_errors->get_error_messages() as $error ) {
                        $errors .= '<strong>'.__('ERROR','wp-crowdfunding').'</strong>:'.$error.'<br />';
                    }
                }
                die(json_encode(array('success'=> 0, 'message' => $errors )));
            }
        } else {
            $errors = '';
            if ( is_wp_error( $reg_errors ) ) {
                foreach ( $reg_errors->get_error_messages() as $error ) {
                    $errors .= '<strong>'.__('ERROR','wp-crowdfunding').'</strong>:'.$error.'<br />';
                }
            }
            die(json_encode(array('success'=> 0, 'message' => $errors )));
        }
    }
    
    public function registration_validation( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio ) {
        global $reg_errors;
        $reg_errors = new \WP_Error;
    
        if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
            $reg_errors->add('field', __('Required form field is missing','wp-crowdfunding'));
        }
    
        if ( strlen( $username ) < 4 ) {
            $reg_errors->add('username_length', __('Username too short. At least 4 characters is required','wp-crowdfunding'));
        }
    
        if ( username_exists( $username ) )
            $reg_errors->add('user_name', __('Sorry, that username already exists!','wp-crowdfunding'));
    
        if ( !validate_username( $username ) ) {
            $reg_errors->add('username_invalid', __('Sorry, the username you entered is not valid','wp-crowdfunding'));
        }
    
        if ( strlen( $password ) < 6 ) {
            $reg_errors->add('password', __('Password length must be greater than 6','wp-crowdfunding'));
        }
    
        if ( !is_email( $email ) ) {
            $reg_errors->add('email_invalid', __('Email is not valid','wp-crowdfunding'));
        }
    
        if ( email_exists( $email ) ) {
            $reg_errors->add('email', __('Email Already in use','wp-crowdfunding'));
        }
    
        if ( !empty( $website ) ) {
            if ( !filter_var($website, FILTER_VALIDATE_URL) ) {
                $reg_errors->add('website', __('Website is not a valid URL','wp-crowdfunding'));
            }
        }
    }
}