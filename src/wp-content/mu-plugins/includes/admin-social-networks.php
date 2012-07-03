<?php
$msg = "";
if (isset($_POST['admin_social_networks']) && wp_verify_nonce($_POST['admin_social_networks'], 'admin_social_networks')) {
    $msg = "<div class='updated settings-error'>As configurações foram salvas</div>";
    update_option('campanha_social_networks', $_POST['social_networks']);
}

$option = get_option('campanha_social_networks');
?>
<style>
    label { display:inline-block; width:150px; }
    input[type="text"] { width: 350px; }
    
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>Redes Sociais</h2>
    <form method="post">
        <?php echo $msg; ?>
        <?php wp_nonce_field('admin_social_networks', 'admin_social_networks'); ?>
        <p>
            <label for="rede-0">Página do Facebook:</label> <input id="rede-0" type="text" name="social_networks[facebook-page]" value="<?php echo @htmlentities($option['facebook-page']) ?>"/><br/>

            <label for="rede-1">Perfil do Facebook:</label> <input id="rede-1" type="text" name="social_networks[facebook]" value="<?php echo @htmlentities($option['facebook']) ?>"/><br/>

            <label for="rede-2">Twitter:</label> <input id="rede-2" type="text" name="social_networks[twitter]" value="<?php echo @htmlentities($option['twitter']) ?>"/><br/>

            <label for="rede-3">Google+:</label> <input id="rede-3" type="text" name="social_networks[google]" value="<?php echo @htmlentities($option['google']) ?>"/><br/>
            
            <label for="rede-4">Youtube:</label> <input id="rede-4" type="text" name="social_networks[youtube]" value="<?php echo @htmlentities($option['youtube']) ?>"/><br/>
        </p>
        <input type="submit" name="submit" class="button-primary" value="Salvar configurações" />

    </form>
</div>
