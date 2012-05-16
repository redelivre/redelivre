<?php

$errors = array();

if (!empty($_POST)) {
    $domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_URL);
    $candidate_number = filter_input(INPUT_POST, 'candidate_number', FILTER_SANITIZE_NUMBER_INT);
    $plan_id = filter_input(INPUT_POST, 'plan_id', FILTER_SANITIZE_NUMBER_INT);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_NUMBER_INT);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_NUMBER_INT);
    
    $campaign = new Campaign(
        array('domain' => $domain, 'plan_id' => $plan_id, 'candidate_number' => $candidate_number,
            'state' => $state, 'city' => $city)
    );
    
    if ($campaign->validate()) {
        $campaign->create();
    } else {
        $errors = $campaign->errorHandler->errors;
    }
}

global $user;
$campaigns = Campaign::getAll($user->ID);

?>

<div class="wrap">
    <h2>Suas campanhas</h2>
    <table class="widefat fixed">
        <thead>
            <tr class="thead">
                <th>Domínio</th>
                <th>Número do candidato</th>
                <th>Plano</th>
                <th>Status</th>
                <th>Data de criação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><a href="<?php echo $campaign->domain; ?>" target="_blank"><?php echo $campaign->domain ?></a></td>
                    <td><?php echo $campaign->candidate_number; ?></td>
                    <td><?php echo Plan::getName($campaign->plan_id); ?></td>
                    <td><?php echo $campaign->getStatus(); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($campaign->creation_date)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 id="form_title">Nova campanha</h2>

<?php
if (!empty($errors)) {
    print_msgs($errors);
}
?>

<form action="<?php echo site_url() . '/wp-admin/admin.php?page=campaigns'; ?>" method="post" enctype="multipart/form-data">
    <table class="form-table">
        <tbody>
            <tr class="form-field">
                <th scope="row"><label for="domain">Domínio</label></th>
                <td>
                    <input type="text" value="<?php if (isset($_POST['domain'])) echo $_POST['domain']; ?>" name="domain">
                    <small>Endereço para acessar o site da campanha</small>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="candidate_number">Número do candidato</label></th>
                <td>
                    <input type="text" value="<?php if (isset($_POST['candidate_number'])) echo $_POST['candidate_number']; ?>" name="candidate_number">
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="plan_id">Selecione um plano</label></th>
                <td>
                    <?php foreach (Plan::getAll() as $plan): ?>
                        <input type="radio" name="plan_id" value="<?php echo $plan->id; ?>" <?php if (isset($_POST['plan_id']) && $_POST['plan_id'] == $plan->id) echo ' checked '; ?>><?php echo $plan->name; ?><br>
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
                            <option value="<?php echo $state->id; ?>" <?php if (isset($_POST['state']) && $_POST['state'] == $state->id) echo ' selected="selected" '; ?>>
                                <?php echo $state->name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="city">Cidade</label>
                    <select name="city" id="city">
                        <?php
                        if (isset($_POST['state'])) {
                            City::printCitiesSelectBox($_POST['state']);
                        } else {
                            echo '<option value="">Selecione um estado...</option>';
                        }
                        ?>
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