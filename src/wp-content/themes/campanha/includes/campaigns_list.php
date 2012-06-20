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

?>

<div class="wrap">
    <h2>Suas campanhas</h2>
    
    <?php if (isset($_GET['success'])) : ?>
        <div class="updated"><p>Campanha criada com sucesso.</p></div>
    <?php endif; ?>
    
    <?php if (!empty($msg)) : ?>
        <div class="updated"><p><?php echo $msg; ?></p></div>
    <?php endif; ?>
    
    <?php if (!empty($error)) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
    <?php endif; ?>
    
    <?php if ($campaigns) : ?>
        <table class="widefat fixed">
            <thead>
                <tr class="thead">
                    <th>Sub-domínio</th>
                    <th>Domínio próprio</th>
                    <?php if (is_super_admin()) echo '<th>Usuário</th>'; ?>
                    <th>Número do candidato</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><a href="<?php echo $campaign->domain; ?>" target="_blank"><?php echo $campaign->domain ?></a> (<a href="<?php echo $campaign->domain; ?>/wp-admin" target="_blank">admin</a>)</td>
                        <td><a href="<?php echo $campaign->own_domain; ?>" target="_blank"><?php echo $campaign->own_domain ?></a></td>
                        <?php if (is_super_admin()) echo "<td>{$campaign->campaignOwner->data->user_login}</td>"; ?>
                        <td><?php echo $campaign->candidate_number; ?></td>
                        <td><?php echo Plan::getName($campaign->plan_id); ?></td>
                        <td><?php echo $campaign->getStatus(); ?></td>
                        <td><a href="<?php echo CAMPAIGN_LIST_URL . "&action=delete&id=$campaign->id"; ?>">Remover</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Você ainda não criou nenhuma campanha. Para isso vá para a <a href="<?php echo admin_url(CAMPAIGN_NEW_URL); ?>">página de criação de campanha</a>.</p>
    <?php endif; ?>
</div>