<?php

$msgs = array();

if (!is_super_admin()) {
    print_msgs(array('error' => 'Você não tem permissão para editar projetos.'));
    die;
}

if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    try {
        $campaign = Campaign::getById($_REQUEST['id']);
    } catch (Exception $e) {
        echo $e->getMessage();
        die;
    }
} else {
    print_msgs(array('error' => 'Projeto não encontrado'));
    die;
}

if (!empty($_POST)) {
    $campaign->own_domain = filter_input(INPUT_POST, 'own_domain', FILTER_SANITIZE_URL);
    $campaign->candidate_number = filter_input(INPUT_POST, 'candidate_number', FILTER_SANITIZE_NUMBER_INT);
    $campaign->plan_id = filter_input(INPUT_POST, 'plan_id', FILTER_SANITIZE_NUMBER_INT);
    $campaign->state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_NUMBER_INT);
    $campaign->city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_NUMBER_INT);
    $campaign->observations = filter_input(INPUT_POST, 'observations', FILTER_SANITIZE_STRING);
    $campaign->status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    
    if ($campaign->validate()) {
        $campaign->update();
        
        $msgs = array('updated' => 'Projeto atualizado com sucesso.');
    } else {
        $msgs = $campaign->errorHandler->errors;
    }
}

?>

<div class="wrap">
    <h2 id="form_title">Editar Projeto <?php echo $campaign->domain; ?></h2>
    
    <?php
    if (!empty($msgs)) {
        print_msgs($msgs);
    }
    ?>
    
    <form action="<?php echo admin_url(CAMPAIGN_EDIT_URL) . "&id={$campaign->id}"; ?>" method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tbody>
                <tr class="form-field">
                    <th scope="row"><label for="domain">Sub-domínio</label></th>
                    <td>
                        <input type="text" value="<?php echo $campaign->domain; ?>" name="domain" style="display: block;" disabled="disabled">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="own_domain">Domínio próprio (opcional)</label></th>
                    <td>
                        <input type="text" value="<?php if (isset($_POST['own_domain'])) { echo $_POST['own_domain']; } else if (isset($campaign->own_domain)) { echo $campaign->own_domain; } ?>" name="own_domain" style="display: block;">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="state">Localização</label></th>
                    <td>
                        <label for="state">Estado</label>
                        <select name="state" id="state">
                            <option value="">Selecione</option>
                            <?php
                            $campaignState = (isset($_POST['state'])) ? $_POST['state'] : $campaign->state;
                            
                            foreach (State::getAll() as $state):?>
                                <option value="<?php echo $state->id; ?>" <?php if ($campaignState == $state->id) echo ' selected="selected" '; ?>><?php echo $state->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <label for="city">Cidade</label>
                        <select name="city" id="city">
                            <?php
                            City::printCitiesSelectBox($campaignState, $campaign->city);
                            ?>
                        </select>
                    </td>
                </tr>
                <?php if (is_super_admin()) : ?>
                    <tr class="form-field">
                        <th scope="row"><label for="observations">Observações</label></th>
                        <td><input type="text" value="<?php if (isset($_POST['observations'])) { echo $_POST['observations']; } else if (isset($campaign->observations)) { echo $campaign->observations; }  ?>" name="observations"></td>
                    </tr>
                <?php endif; ?>
                <tr class="form-field">
                    <th scope="row"><label for="plan_id">Selecione um plano</label></th>
                    <td>
                        <?php
                        $campaignPlan = (isset($_POST['plan_id'])) ? $_POST['plan_id'] : $campaign->plan_id;
                        foreach (Plan::getAll() as $plan): ?>
                            <input type="radio" name="plan_id" class="radio" value="<?php echo $plan->id; ?>" <?php if ($campaignPlan == $plan->id) echo ' checked '; ?>> <?php echo $plan->name; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php if (is_super_admin()) : ?>
                    <tr class="form-field">
                        <th scope="row"><label for="status">Status</label></th>
                        <td>
                            <input type="radio" name="status" class="radio" value="0" <?php if ($campaign->status == 0) echo ' checked '; ?>> Pagamento pendente
                            <input type="radio" name="status" class="radio" value="1" <?php if ($campaign->status == 1) echo ' checked '; ?>> Ativo
                        </td>
                    </tr>
                <?php endif; ?>      
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" value="Atualizar" name="submit" class="button-primary">
        </p>
    </form>
</div>