<?php

if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $user = new stdClass();
    foreach ($_POST as $n => $v) {
        $user->{$n} = $v;
    }

    $user_login = $_POST['user_login']; 
    $user_email = $_POST['user_email'];
    $user_pass = $_POST['user_pass'];
    $errors = array();

    if (username_exists($user_login)) {
        $errors['user'] =  __('Já existe um usário com este nome no nosso sistema. Por favor, escolha outro nome.', 'campanha');
    }

    if (email_exists($user_email)) {
        $errors['email'] =  __('Este e-mail já está registrado em nosso sistema. Por favor, cadastre-se com outro e-mail.', 'campanha');
    }
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] =  __('O e-mail informado é inválido.', 'campanha');
    }

    if (strlen($user_login) == 0) {
        $errors['user'] =  __('O nome de usuário é obrigatório para o cadastro no site.', 'campanha');
    }

    if (strlen($user_login) > 0 && strlen($user_login) < 3) {
        $errors['user'] =  __('Nome de usuário muito curto. Escolha um com 3 letras ou mais.', 'campanha');
    }

    if (!preg_match('/^([a-z0-9-]+)$/', $user_login)) {
        $errors['user'] =  __('O nome de usuário escolhido é inválido. Por favor, escolha outro nome de usuário.', 'campanha');
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
        
        //Modifica este metadado para não exibir o painel de Boas vindas
        update_user_meta($user_id, 'show_welcome_panel', 0);
        
        // depois de fazer o registro, faz login
        if (is_ssl() && force_ssl_login() && !force_ssl_admin() && (0 !== strpos($redirect_to, 'https')) && (0 === strpos($redirect_to, 'http'))) {
            $secure_cookie = false;
        } else {
            $secure_cookie = '';
        }

        $user = wp_signon(array('user_login' => $user_login, 'user_password' => $user_pass), $secure_cookie);

        if (!is_wp_error($user) && !$reauth) {
            wp_safe_redirect(admin_url());
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

<section id="signup">
    <div id="cadastre-se">
        <h1>Cadastre-se gratuitamente e faça um teste.</h1>
        <p>Você só paga na hora de escolher um plano e publicar seu site ou blog.</p>
        <?php
        if (is_user_logged_in()) {
            _e('Você já está cadastrado!', 'campanha');
        } else {
            if (isset($msgs)) {
                print_msgs($msgs);
            }
            require(TEMPLATEPATH . '/register_form.php');
        }
        ?>
    </div>
</section>

<?php get_footer(); ?>
