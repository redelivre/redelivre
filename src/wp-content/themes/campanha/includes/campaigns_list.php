<?php

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
                    <th>Data de criação</th>
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
                        <td><?php echo date('d/m/Y', strtotime($campaign->creation_date)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Você ainda não criou nenhuma campanha. Para isso vá para a <a href="<?php echo admin_url(CAMPAIGN_NEW_URL); ?>">página de criação de campanha</a>.</p>
    <?php endif; ?>
</div>