<?php

if (!empty($_POST)) {
    $domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_URL);
    $plan = filter_input(INPUT_POST, 'plan', FILTER_SANITIZE_NUMBER_INT);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_NUMBER_INT);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_NUMBER_INT);
    
    $campaign = new Campaign(array('domain' => $domain, 'plan' => $plan, 'state' => $state, 'city' => $city));
    
    if ($campaign->validate()) {
        $campaign->save();
    } else {
        echo 'tratar erros!'; die;
    }
}

$campaigns = Campaign::getAll();

?>

<div class="wrap">
    <h2>Suas campanhas</h2>
    <table class="widefat fixed">
        <thead>
            <tr class="thead">
                <th>Domínio</th>
                <th>Plano</th>
                <th>Status</th>
                <th>Data de criação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><?php echo $campaign->domain ?></td>
                    <td><?php echo Plan::getName($campaign->plan_id) ?></td>
                    <td><?php echo $campaign->status ?></td>
                    <td><?php echo $campaign->creation_date ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 id="form_title">Nova campanha</h2>

<form action="<?php echo site_url() . '/wp-admin/admin.php?page=campaigns'; ?>" method="post" enctype="multipart/form-data">
    <table class="form-table">
        <tbody>
            <tr class="form-field">
                <th scope="row"><label for="domain">Domínio</label></th>
                <td>
                    <input type="text" value="" name="domain">
                    <small>Endereço para acessar o site da campanha</small>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="plan">Selecione um plano</label></th>
                <td>
                    <?php foreach (Plan::getAll() as $plan): ?>
                        <input type="radio" name="plan" value="<?php echo $plan->id; ?>"><?php echo $plan->name; ?><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="state">Localização</label></th>
                <td>
                    <label for="state">Estado</label>
                    <select name="state" id="state">
                        <option value="">Selecione</option>
                        <?php foreach (State::getAll() as $state): ?>
                            <option value="<?php echo $state->id; ?>">
                                <?php echo $state->name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="city">Cidade</label>
                    <select name="city" id="city">
                        <option value="">Selecione um estado...</option>
                    </select>
                </td>
            </tr>
        </tr>
            
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" value=" Salvar " name="submit" class="button-primary">
    </p>
</form>