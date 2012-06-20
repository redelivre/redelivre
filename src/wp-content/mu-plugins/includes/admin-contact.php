<?php

add_action('admin_init', 'campanha_contact_init');
add_action('admin_menu', 'campanha_contact_add_page');
add_action('update_option_campanha_contact_menu_entry', 'campanha_toggle_contact_menu_entry', 10, 2);

/**
 * Init plugin options to white list our options
 */
function campanha_contact_init(){
    register_setting('campanha_contact', 'campanha_contact_enabled', 'campanha_contact_validate' );
    register_setting('campanha_contact', 'campanha_contact_menu_entry', 'campanha_contact_validate' );
}

/**
 * Load up the menu page
 */
function campanha_contact_add_page() {
    
    //Se o tema ativo não suportar, não damos essa opção:
    if ( !file_exists(STYLESHEETPATH . '/tpl-contato.php') && !file_exists(TEMPLATEPATH . '/tpl-contato.php'))
        return;
    
    add_menu_page('Contato', 'Contato', 'read', 'campaign_contact', 'campanha_contact_do_page');
}

/**
 * Enable or disable menu entry to contact page.
 */
function campanha_toggle_contact_menu_entry($oldValue, $newValue) {
    $menu = wp_get_nav_menu_object('main');
    $items = wp_get_nav_menu_items('main');
    $menuItem = null;
    
    if ($menu) {
        foreach ($items as $item) {
            if ($item->post_title == 'Contato') {
                $menuItem = $item;
            }
        }
    
        if ($newValue == 'on' && !$menuItem) {
            wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                'menu-item-title' => 'Contato',
                'menu-item-url' => home_url('/contato'), 
                'menu-item-status' => 'publish')
            );
        } else if (empty($newValue) && $menuItem) {
            wp_delete_post($menuItem->ID, true);
        }
    }
}

/**
 * Create the options page
 */
function campanha_contact_do_page() {
    if ( ! isset( $_REQUEST['settings-updated'] ) )
        $_REQUEST['settings-updated'] = false;
    ?>
    <div class="wrap">
        <h2>Página de contato</h2>

        <?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
            <div class="updated fade"><p><strong>Opções salvas</strong></p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('campanha_contact'); ?>
            <p><label><input type="checkbox" name="campanha_contact_enabled" <?php if (get_option('campanha_contact_enabled')) echo ' checked="checked" '; ?>> Habilitar página de contato</label></p>
            <p><label><input type="checkbox" name="campanha_contact_menu_entry" <?php if (get_option('campanha_contact_menu_entry')) echo ' checked="checked" '; ?>> Habilitar link para a página de contato no menu principal</label></p>
            <p class="submit">
                <input type="submit" class="button-primary" value="Salvar opções" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function campanha_contact_validate($input) {
    if ($input != 'on') {
        $input = false;
    }

    return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/



/*** Handle Ajax ***/

add_action('wp_ajax_form-contato', 'campanha_handle_form_contato');
add_action('wp_ajax_nopriv_form-contato', 'campanha_handle_form_contato');

function campanha_handle_form_contato() {

    $msg = '';
    
    foreach($_POST as $campo => $valor) {
    
        $msg .= "$campo: $valor \n";
    
    }
    
    $email = get_option('admin_email');
    
    // generate the response
    $response = json_encode(array('success' => wp_mail( $email, 'Novo contato no site', $msg, "From: 'Carteiro Campanha Completa' <noreply@campanhacompleta.com.br>" ) ));
 
    // response output
    header( "Content-Type: application/json" );
    echo $response;
    
    die;


}

function campanha_the_contact_form() {


    ?>
    
    <form method='post' id="formcontato">
        <input type='hidden' name='action' value='form-contato' />
        <div class="clearfix">
            <label for="nome">Nome</label>
            <div id="error-for-nome"></div>
            <input type="text" name="nome" value="" id="nome">
        </div>
        <div class="clearfix">
            <label for="email">E-mail</label>
            <div id="error-for-email"></div>
            <input type="text" name="email" value="" id="email">
        </div>
        <div class="clearfix">
            <label for="telefone">Telefone</label>
            <div id="error-for-telefone"></div>
            <input type="text" name="telefone" value="" id="telefone">
        </div>
        <div class="clearfix">
            <label for="mensagem">Mensagem</label>
            <div id="error-for-mensagem"></div>
            <textarea id="mensagem" name="mensagem"></textarea>
        </div>
        
        <p class='success feedback' id='contato-success' style='display:none;'>Formulário enviado com sucesso!</p>
        <p class='error feedback' id='contato-error' style='display:none;'>Erro ao enviar o formulário. Tente novamente.</p>
        
        
   
		<p>
			<img src="<?php echo WPMU_PLUGIN_URL; ?>/img/ajax-loader.gif" id="contato-loader" style="display:none;" />
			<input id="contact-submit" type="submit" name="" value="Enviar"/>
		</p>

        
        
    </form>
    
    
    <?php


}
