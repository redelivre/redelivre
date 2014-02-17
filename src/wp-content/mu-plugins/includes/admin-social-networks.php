<?php
$msg = "";
if ( isset( $_POST['admin_social_networks'] ) && wp_verify_nonce( $_POST['admin_social_networks'], 'admin_social_networks' ) ) {
    $msg = '<div class="updated settings-error"><p>';
    $msg .= __( 'Settings saved.' );
    $msg .= '</p></div>';
    update_option( 'campanha_social_networks', $_POST['social_networks'] );
}

$option = get_option( 'campanha_social_networks' );
?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>Redes Sociais</h2>
    <form method="post">
        <?php settings_fields( 'campaign_social_networks' ); ?>
        <?php echo $msg; ?>
        <?php wp_nonce_field('admin_social_networks', 'admin_social_networks'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="rede-0">Página do Facebook</label></th>
                <td><input id="rede-0" type="text" name="social_networks[facebook-page]" value="<?php echo @htmlentities($option['facebook-page']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-1">Perfil do Facebook</label></th>
                <td><input id="rede-1" type="text" name="social_networks[facebook]" value="<?php echo @htmlentities($option['facebook']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-2">Twitter</label></th>
                <td><input id="rede-2" type="text" name="social_networks[twitter]" value="<?php echo @htmlentities($option['twitter']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-3">Google+</label></th>
                <td><input id="rede-3" type="text" name="social_networks[google]" value="<?php echo @htmlentities($option['google']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-4">YouTube</label></th>
                <td><input id="rede-4" type="text" name="social_networks[youtube]" value="<?php echo @htmlentities($option['youtube']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-5">Flickr</label></th>
                <td><input id="rede-5" type="text" name="social_networks[flickr]" value="<?php echo @htmlentities($option['flickr']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-6">Instagram</label></th>
                <td><input id="rede-6" type="text" name="social_networks[instagram]" value="<?php echo @htmlentities($option['instagram']) ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="rede-7">Pinterest</label></th>
                <td><input id="rede-7" type="text" name="social_networks[pinterest]" value="<?php echo @htmlentities($option['pinterest']) ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php do_settings_sections( 'campaign_social_networks' ); ?>
        <?php submit_button(); ?>
    </form>
</div>
