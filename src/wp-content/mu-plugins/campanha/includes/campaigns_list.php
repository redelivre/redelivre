<?php

$msg = '';
$error = '';

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'
    && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
{
    try {
        $campaign = Campaign::getById($_REQUEST['id']);
        $campaign->delete();
        $msg = str_replace('{domain}', $campaign->domain, Campaign::getStrings('RemovidoSucesso'));
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$campaignTable = new CampaingTable;

?>

<div class="wrap">
    <h2><?php echo Campaign::getStrings('Seus'); ?></h2>
    
    <?php if (!empty($msg)) : ?>
        <div class="updated"><p><?php echo $msg; ?></p></div>
    <?php endif; ?>
    
    <?php if (!empty($error)) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
    <?php endif; ?>
    
    <?php if ($campaignTable->prepare_items()) : ?>
	    <form action="" method="get" id="ms-search">
			<?php $campaignTable->search_box( __( Campaign::getStrings('ProcurarProjeto') ), 'projetos' ); ?>
			<input type="hidden" name="action" value="projetos" />
			<input type="hidden" name="page" value="campaigns" />
		</form>
        <?php $campaignTable->display(); ?>
    <?php else : ?>
        <p><?php echo Campaign::getStrings('NaoCriou1');?> <a href="<?php echo admin_url(CAMPAIGN_NEW_URL); ?>"><?php echo Campaign::getStrings('NaoCriou2')?></a>.</p>
    <?php endif; ?>
</div>
