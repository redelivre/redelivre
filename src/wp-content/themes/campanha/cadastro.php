<?php

if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $user = new stdClass();
    foreach ($_POST as $n => $v) {
        $user->{$n} = $v;
    }

    // email is used for login instead of user name.
    // so the value for this field doesn't matter.
    $user_login = md5($_POST['user_email']);
    $user_email = $_POST['user_email'];
    $user_pass = $_POST['user_pass'];
    $errors = array();

    if (email_exists($user_email)) {
        $errors['email'] =  __('Este e-mail já está registrado em nosso sistema. Por favor, cadastre-se com outro e-mail.', 'campanha');
    }
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] =  __('O e-mail informado é inválido.', 'campanha');
    }

    if (strlen($user_email) == 0) {
        $errors['email'] =  __('O e-mail é obrigatório para o cadastro no site.', 'campanha');
    }

    if (strlen($user_pass) == 0) {
        $errors['pass'] =  'A senha é obrigatória para o cadastro no site.';
    }
    
    if (!sizeof($errors) > 0) {
        $data['user_login'] = $user_login;
        $data['user_pass'] = $user_pass;
        $data['user_email'] =  $user_email;
        
        $data['role'] = 'subscriber';
        $user_id = wp_insert_user($data);

        if (!$user_id || is_wp_error($user_id)) {
            if ($errmsg = $user_id->get_error_message('blog_title')) {
                echo $errmsg;
            }
        }

        $msgs['success'] = 'Cadastro efetuado com sucesso';
        
        // depois de fazer o registro, faz login
        if (is_ssl() && force_ssl_login() && !force_ssl_admin() && (0 !== strpos($redirect_to, 'https')) && (0 === strpos($redirect_to, 'http'))) {
            $secure_cookie = false;
        } else {
            $secure_cookie = '';
        }

        $user = wp_signon(array('user_login' => $user_login, 'user_password' => $user_pass), $secure_cookie);

        if (!is_wp_error($user) && !$reauth) {
            wp_safe_redirect(get_author_posts_url($user_id));
            exit();
        }
    } else {
        foreach ($errors as $type => $msg) {
            $msgs['error'][] = $msg;
        }
    }
}

?>

<?php get_header(); ?>

<section id="signup" class="grid_16 clearfix box-shadow text-center">
    <div id="cadastre-se" class="col-12">
        <h2>Cadastre-se gratuitamente e faça um teste.</h2>
        <p>Você só paga na hora de escolher um plano e publicar seu site ou blog.</p>
        <?php
        if (is_user_logged_in()) {
            _e('Você já está cadastrado!', 'campanha');
        } else {
            if (isset($msgs)) {
                print_msgs($msgs);
            }
            require(TEMPLATEPATH . '/cadastro_form.php');
        }
        ?>
    </div>
</section>

<?php get_footer(); ?>
