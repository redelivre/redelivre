<?php

$msg = '';
$error = '';

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'
    && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
{
    try {
        $campaign = Campaign::getById($_REQUEST['id']);
        $campaign->delete();
        $msg = "Campanha $campaign->domain removida com sucesso.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (is_super_admin()) {
    $campaigns = Campaign::getAll();
} else {
    $user = wp_get_current_user();
    $campaigns = Campaign::getAll($user->ID);
}

$campaignTable = new CampaingTable;
$campaignTable->prepare_items();

?>

<div class="wrap">
    <h2>Suas campanhas</h2>
    
    <?php if (!empty($msg)) : ?>
        <div class="updated"><p><?php echo $msg; ?></p></div>
    <?php endif; ?>
    
    <?php if (!empty($error)) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
    <?php endif; ?>
    
    <?php if ($campaigns) : ?>
        <?php $campaignTable->display(); ?>
    <?php else : ?>
        <p>Você ainda não criou nenhuma campanha. Para isso vá para a <a href="<?php echo admin_url(CAMPAIGN_NEW_URL); ?>">página de criação de campanha</a>.</p>
    <?php endif; ?>
</div>