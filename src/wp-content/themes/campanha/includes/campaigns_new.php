<?php

$errors = array();

if (!empty($_POST)) {
    $domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_STRING);
    $own_domain = filter_input(INPUT_POST, 'own_domain', FILTER_SANITIZE_URL);
    $candidate_number = filter_input(INPUT_POST, 'candidate_number', FILTER_SANITIZE_NUMBER_INT);
    $plan_id = filter_input(INPUT_POST, 'plan_id', FILTER_SANITIZE_NUMBER_INT);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_NUMBER_INT);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_NUMBER_INT);
    
    $campaign = new Campaign(
        array('domain' => $domain, 'own_domain' => $own_domain, 'plan_id' => $plan_id, 'candidate_number' => $candidate_number,
            'state' => $state, 'city' => $city)
    );
    
    if ($campaign->validate()) {
        $campaign->create();
        wp_redirect(admin_url(CAMPAIGN_LIST_URL) . '&success');
    } else {
        $errors = $campaign->errorHandler->errors;
    }
}

// hack to make the redirection above work (without using noheader
// the call to wp_redirect generate a headers already sent warning)
if (isset($_GET['noheader'])) {
    require_once(ABSPATH . 'wp-admin/admin-header.php');
}

?>

<div class="wrap">
    <h2 id="form_title">Nova campanha</h2>
    
    <?php
    if (!empty($errors)) {
        print_msgs($errors);
    }
    ?>
    
    <form action="<?php echo admin_url(CAMPAIGN_NEW_URL) . '&noheader'; ?>" method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tbody>
                <tr class="form-field">
                    <th scope="row"><label for="domain">Sub-domínio</label></th>
                    <td>
                        <input type="text" value="<?php if (isset($_POST['domain'])) echo $_POST['domain']; ?>" name="domain" style="display: block;">
                        <small>São recomendados apenas os caracteres a-z e 0-9.</small> <br />
                        <small>O sub-domínio será usado para acessar o seu site caso não possua um domínio próprio. Por exemplo, se preencher nesse campo "joao" o sub-domínio será joao.campanhacompleta.com.br.</small>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="own_domain">Domínio próprio (opcional)</label></th>
                    <td>
                        <input type="text" value="<?php if (isset($_POST['own_domain'])) echo $_POST['own_domain']; ?>" name="own_domain" style="display: block;">
                        <small>Caso possua informe aqui o domínio próprio do seu site (ele será usado no lugar do sub-domínio)</small>
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
</div>
