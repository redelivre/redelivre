<?php

$msg = '';
$error = '';

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'
    && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
{
    try {
        $campaign = Campaign::getById($_REQUEST['id']);
        $campaign->delete();
        $msg = "Projeto $campaign->domain removida com sucesso.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$campaignTable = new CampaingTable;

?>

<div class="wrap">
    <h2>Seus Projetos</h2>
    
    <?php if (!empty($msg)) : ?>
        <div class="updated"><p><?php echo $msg; ?></p></div>
    <?php endif; ?>
    
    <?php if (!empty($error)) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
    <?php endif; ?>
    
    <?php if ($campaignTable->prepare_items()) : ?>
	    <form action="" method="get" id="ms-search">
			<?php $campaignTable->search_box( __( 'Procurar Projeto' ), 'projetos' ); ?>
			<input type="hidden" name="action" value="projetosSearch" />
		</form>
        <?php $campaignTable->display(); ?>
    <?php else : ?>
        <p>Você ainda não criou nenhuma projeto. Para isso vá para a <a href="<?php echo admin_url(CAMPAIGN_NEW_URL); ?>">página de criação de projeto</a>.</p>
    <?php endif; ?>
</div>
