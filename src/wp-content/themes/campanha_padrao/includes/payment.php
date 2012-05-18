<?php


function payment_menu() {
    
    // Por padrão criamos uma página exclusiva para as opções desse site
    // Mas se quiser você pode colocar ela embaixo de aparencia, opções, ou o q vc quiser. O modelo para todos os casos estão comentados abaixo
    
    $topLevelMenuLabel = 'Pagamento';
    $page_title = 'Pagamentos';
    $menu_title = 'Pagamentos';
    
    /* Top level menu */
    add_submenu_page('payments', $page_title, $menu_title, 'manage_options', 'payments', 'payment_page_callback_function');
    add_menu_page($topLevelMenuLabel, $topLevelMenuLabel, 'manage_options', 'payments', 'payment_page_callback_function');
    
}

function payment_page_callback_function() {
    global $campaign;
    
    $isPaid = $campaign->isPaid();
    $plan = Plan::getById($campaign->plan_id);
    $price = $plan->price;
    
?>
    <div class="wrap span-20">
        
        <h2>Pagamento</h2>
        
        <?php if ($isPaid): ?>
            
            <p>Seu pagamento está confirmado e sua campanha já está ativa</p>
            <p>Quer fazer um upgrade de plano? ______________-Aguarde aqui___________</p>
            
        <?php else: ?>
            
            
            <p>Como você gostaria de fazer seu pagamento?</p>
            
            <div id="pgto-cartao" class="pagamento">
            
                <h3>Cartão de Crédito</h3>
                
                
                
            
            </div>
            
            <div id="pgto-cartao" class="pagamento">
            
                <h3>Boleto Bancário</h3>
            
            </div>
            
            
        <?php endif; ?>
        
    </div>

<?php } 


add_action('admin_menu', 'payment_menu');
