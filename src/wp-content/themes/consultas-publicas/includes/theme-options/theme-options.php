<?php

function get_theme_default_options() {
    return array(
        'pagina_help' => site_url('sobre'),
        'pagina_sugerir' => site_url('sugerir-uma-meta'),
        'data_encerramento' => '2011-10-01',
        
        'object_labels' => ObjectPostType::get_default_labels(),
        'taxonomy_labels' => ObjectPostType::get_taxonomy_default_labels(),
        
        'taxonomy_url' => 'tipo',
        'object_url' => 'objeto',
        
        'allow_suggested' => false,
        'suggested_labels' => array(
            'title' => 'Adicionar novo objeto',
            'description' => 'Utilize essa página para criar um novo objeto.',
            'success' => 'Objeto criado com sucesso!',
            'list' => 'Objetos sugeridos pelos usuários',
        ),
        'enable_taxonomy' => false,
        
        'list_type' => 'normal',
        'object_list_intro' => '',
        'list_order' => 'desc',
        'list_order_by' => 'creation_date',

        'use_evaluation' => false,
        'evaluation_show_on_list' => false,
        'evaluation_public_results' => false,
        'evaluate_button' => 'Votar!',
        'evaluation_labels' => array(
            'label_1' => __('Concordo', 'consulta'),
            'label_2' => __('Não concordo', 'consulta'),
            'label_3' => '',
            'label_4' => '',
            'label_5' => '',
        ),
        'evaluation_text' => __('Você concorda com esta proposta?', 'consulta'),
        'evaluation_type' => 'percentage',
        
        'pagina_participe' => ''
    );

}

function get_theme_option($option_name) {
    $option = wp_parse_args( 
        get_option('theme_options'), 
        get_theme_default_options()
    );
    return isset($option[$option_name]) ? $option[$option_name] : false;
}

add_action('admin_init', 'theme_options_init');
add_action('admin_menu', 'theme_options_menu');

add_action('admin_print_scripts-toplevel_page_theme_options', 'theme_options_js');
add_action('admin_print_styles-toplevel_page_theme_options', 'theme_options_css');

function theme_options_init() {
    register_setting('theme_options_options', 'theme_options', 'theme_options_validate_callback_function');
}

function theme_options_menu() {
    $topLevelMenuLabel = __('Opções da Consulta', 'consulta');
    $page_title = 'Opções';
    $menu_title = 'Opções';
    
    /* Top level menu */
    add_theme_page('theme_options', $page_title, $menu_title, 'manage_options', 'theme_options_page_callback_function');
    
    add_menu_page($topLevelMenuLabel, $topLevelMenuLabel, 'manage_options', 'theme_options', 'theme_options_page_callback_function');
}

function theme_options_js() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('theme_options_js', get_template_directory_uri() . '/js/theme-options.js', array('jquery'));
}

function theme_options_css() {
    wp_enqueue_style('theme-options', get_template_directory_uri() . '/css/ui-lightness/jquery-ui-1.9.1.custom.min.css');
}

function theme_options_validate_callback_function($input) {
    foreach ($input as $key => $value) {
        if (is_array($value)) {
            $input[$key] = theme_options_validate_callback_function($value);
        } else if (in_array($key, array('pagina_help', 'pagina_sugerir', 'taxonomy_url', 'object_url', 'suggested_object_url'))) {
            $input[$key] = sanitize_title($value);
        } else if (is_string($value)) {
            $input[$key] = strip_tags($value);
        }
    }
    
    return $input;
}


function theme_options_page_callback_function() {
    // hack alert: limpa o cache das regras de redirecionamento para atualizar os links
    // dos customs post types quando muda o slug de um deles. não dá para fazer isso logo
    // depois de salvar a opção pois nesse momento o valor do slug do objeto em si ainda não
    // foi atualizado. 
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
    ?>

    <style>
    #abas-secoes { padding-left: 4px; border-bottom: 1px solid #DDDDDD; list-style: none; margin: 0px 0px 22px }
    #abas-secoes:after {
      content: "\0020";
      display: block;
      height: 0;
      clear: both;
      visibility: hidden;
      overflow:hidden;
    }
    #abas-secoes li { float: left; margin-right: 4px; margin-bottom: -1px; padding: 5px 6px; border: 1px solid #DDDDDD; border-radius: 6px 6px 0 0; -moz-border-radius: 6px 6px 0 0; -webkit-border-radius: 6px 6px 0 0; font-weight: bold; }
    #abas-secoes li:hover { background-color: #EEEEEE; } 
    #abas-secoes li.active:hover { background-color: #FFF; } 
    #abas-secoes li.active { border-bottom: 1px solid #fff; }
    #abas-secoes a { display: block; color: #999; cursor: pointer; }
    #abas-secoes a:hover { text-decoration: none;  }
    
    #exemplo_resultado { padding: 15px; border: 1px solid grey; }
    </style>
    
    <div class="wrap span-20">
        <h2><?php echo __('Opções da Consulta', 'consulta'); ?></h2>
        
        <form action="options.php" method="post" class="clear prepend-top">
            <?php 
            settings_fields('theme_options_options');
            $options = wp_parse_args( 
                get_option('theme_options'), 
                get_theme_default_options()
            );
            ?>            
            
            <ul id="abas-secoes" >
                <li class="active"><a id="aba-outras">Opções Gerais</a></li>
                <li><a id="aba-objeto">Objeto da consulta</a></li>
                <li><a id="aba-listagem">Tipo de listagem</a></li>
                <li><a id="aba-quantitativa">Avaliação quantitativa</a></li>
            </ul>
            
            <div id="aba-objeto-container" class="aba-container">
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php _e('Objeto da Consulta', 'consulta'); ?></h3>
                    <p>
                    <?php _e('Quais são os objetos da sua consulta? Itens de um projeto de lei? Metas de um Plano? Utilize esta página para dar o nome adequado aquilo que você está colocando sob consulta. Preencha as opções abaixo substituindo o termo "objeto" pelo nome do objeto da sua consulta.', 'consulta'); ?>
                    </p>
                    <table class="wp-list-table widefat fixed">
                        <tr>
                            <td><label for="name">Nome do objeto da consulta (plural)</label></td>
                            <td><input type="text" id="name" class="text" name="theme_options[object_labels][name]" value="<?php echo htmlspecialchars($options['object_labels']['name']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="singular_name">Nome do objeto da consulta (singular)</label></td>
                            <td><input type="text" id="singular_name" class="text" name="theme_options[object_labels][singular_name]" value="<?php echo htmlspecialchars($options['object_labels']['singular_name']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="add_new">Adicionar novo</label></td>
                            <td><input type="text" id="add_new" class="text" name="theme_options[object_labels][add_new]" value="<?php echo htmlspecialchars($options['object_labels']['add_new']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="add_new_item">Adicionar novo objeto</label></td>
                            <td><input type="text" id="add_new_item" class="text" name="theme_options[object_labels][add_new_item]" value="<?php echo htmlspecialchars($options['object_labels']['add_new_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="edit_item">Editar objeto</label></td>
                            <td><input type="text" id="edit_item" class="text" name="theme_options[object_labels][edit_item]" value="<?php echo htmlspecialchars($options['object_labels']['edit_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="new_item">Novo objeto</label></td>
                            <td><input type="text" id="new_item" class="text" name="theme_options[object_labels][new_item]" value="<?php echo htmlspecialchars($options['object_labels']['new_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="view_item">Ver objeto</label></td>
                            <td><input type="text" id="view_item" class="text" name="theme_options[object_labels][view_item]" value="<?php echo htmlspecialchars($options['object_labels']['view_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="search_items">Buscar objetos</label></td>
                            <td><input type="text" id="search_items" class="text" name="theme_options[object_labels][search_items]" value="<?php echo htmlspecialchars($options['object_labels']['search_items']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="not_found">Nenhum Objeto Encontrado</label></td>
                            <td><input type="text" id="not_found" class="text" name="theme_options[object_labels][not_found]" value="<?php echo htmlspecialchars($options['object_labels']['not_found']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="not_found_in_trash">Nenhum Objeto na Lixeira</label></td>
                            <td><input type="text" id="not_found_in_trash" class="text" name="theme_options[object_labels][not_found_in_trash]" value="<?php echo htmlspecialchars($options['object_labels']['not_found_in_trash']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="object_url">Endereço base para os objetos da consulta</label></td>
                            <td><?php echo site_url(); ?>/<input type="text" id="object_url" class="text" name="theme_options[object_url]" value="<?php echo htmlspecialchars($options['object_url']); ?>"/></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php echo __('Objeto criados por usuários', 'consulta'); ?></h3>
                    
                    <input type="checkbox" id="allow_suggested" name="theme_options[allow_suggested]" <?php checked('on', $options['allow_suggested']); ?> />
                    <label for="allow_suggested"><?php echo __('Usuários podem criar novos objetos na consulta', 'consulta'); ?></label>
                    
                    <div id="allow_suggested_labels_container">
                        <p><?php _e('Use os campos abaixo para controlar os textos exibidos na página que permite ao usuário criar um novo objeto.', 'consulta'); ?></p>
                        <table class="wp-list-table widefat fixed">
                            <tr>
                                <td><label for="suggested_object_title">Título da página para criar novo objeto</label></td>
                                <td><input type="text" id="suggested_object_title" class="text" name="theme_options[suggested_labels][title]" value="<?php echo htmlspecialchars($options['suggested_labels']['title']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="suggested_object_description">Descrição da página para criar novo objeto</label></td>
                                <td><input type="text" id="suggested_object_description" class="text" name="theme_options[suggested_labels][description]" value="<?php echo htmlspecialchars($options['suggested_labels']['description']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="suggested_object_success">Mensagem quando um novo objeto é criado</label></td>
                                <td><input type="text" id="suggested_object_success" class="text" name="theme_options[suggested_labels][success]" value="<?php echo htmlspecialchars($options['suggested_labels']['success']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="suggested_object_list">Título da listagem de objetos sugeridos</label></td>
                                <td><input type="text" id="suggested_object_list" class="text" name="theme_options[suggested_labels][list]" value="<?php echo htmlspecialchars($options['suggested_labels']['list']); ?>"/></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php echo __('Categorização dos Objetos da Consulta', 'consulta'); ?></h3>
                    
                    <p>
                    <?php _e('Os objetos da sua consulta podem ser agrupados dentro de uma classificação. Por exemplo, as metas de um plano podem estar agrupadas em diferentes temas. Neste caso, sua taxonomia seria "temas". Use os campos abaixo para dar um nome para a sua classificação.', 'consulta'); ?>
                    </p>
                    
                    <input type="checkbox" id="enable_taxonomy" name="theme_options[enable_taxonomy]" <?php checked('on', $options['enable_taxonomy']); ?> />
                    <label for="enable_taxonomy"><?php echo __('Habilitar categorização dos objetos', 'consulta'); ?></label>
                    
                    <div id="taxonomy_labels_container">
                    <table class="wp-list-table widefat fixed">
                        <tr>
                            <td><label for="name">Nome da taxonomia (plural)</label></td>
                            <td><input type="text" id="name" class="text" name="theme_options[taxonomy_labels][name]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['name']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="singular_name">Nome da taxonomia (singular)</label></td>
                            <td><input type="text" id="singular_name" class="text" name="theme_options[taxonomy_labels][singular_name]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['singular_name']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="add_new_item">Adicionar novo tipo</label></td>
                            <td><input type="text" id="add_new_item" class="text" name="theme_options[taxonomy_labels][add_new_item]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['add_new_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="edit_item">Editar tipo</label></td>
                            <td><input type="text" id="edit_item" class="text" name="theme_options[taxonomy_labels][edit_item]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['edit_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="new_item_name">Nome do novo tipo</label></td>
                            <td><input type="text" id="new_item_name" class="text" name="theme_options[taxonomy_labels][new_item_name]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['new_item_name']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="search_items">Buscar tipos</label></td>
                            <td><input type="text" id="search_items" class="text" name="theme_options[taxonomy_labels][search_items]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['search_items']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="all_items">Todos os tipos</label></td>
                            <td><input type="text" id="all_items" class="text" name="theme_options[taxonomy_labels][all_items]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['all_items']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="parent_item">Tipo pai</label></td>
                            <td><input type="text" id="parent_item" class="text" name="theme_options[taxonomy_labels][parent_item]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['parent_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="parent_item_colon">Tipo pai:</label></td>
                            <td><input type="text" id="parent_item_colon" class="text" name="theme_options[taxonomy_labels][parent_item_colon]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['parent_item_colon']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="update_item">Atualizar tipo</label></td>
                            <td><input type="text" id="update_item" class="text" name="theme_options[taxonomy_labels][update_item]" value="<?php echo htmlspecialchars($options['taxonomy_labels']['update_item']); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="taxonomy_url">Endereço base para a taxonomia do objeto</label></td>
                            <td><?php echo site_url(); ?>/<input type="text" id="taxonomy_url" class="text" name="theme_options[taxonomy_url]" value="<?php echo htmlspecialchars($options['taxonomy_url']); ?>"/></td>
                        </tr>
                    </table>
                    </div>
                </div>
            </div>
            </div>
            
            <div id="aba-listagem-container" class="aba-container">
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php _e('Tipo de listagem', 'consulta'); ?></h3>
                    
                    <p><?php _e('Como você gostaria de listar seus objetos', 'consulta'); ?></p>
                    
                    <input type="radio" name="theme_options[list_type]" id="list_type_normal" value="normal" <?php checked('normal', $options['list_type']); ?>/>
                    <label for="list_type_normal"><b>Normal - </b></label> Listagem corrida, estilo blog.
                    <br/><br/>
                    <input type="radio" name="theme_options[list_type]" id="list_type_title" value="title" <?php checked('title', $options['list_type']); ?>/>
                    <label for="list_type_title"><b>Apenas títulos - </b></label> Lista apenas com os títulos dos objetos
                    <br/><br/>
                    <input type="radio" name="theme_options[list_type]" id="list_type_title_taxonomy" value="title_taxonomy" <?php checked('title_taxonomy', $options['list_type']); ?>/>
                    <label for="list_type_title_taxonomy"><b>Apenas títulos agrupados por categoria - </b></label> Lista apenas com os títulos dos objetos agrupados por tipo de objeto.
                    <br/><br/>
                    <?php _e('Texto introdutório para a página de listagem de objetos', 'consulta'); ?><br/>
                    <textarea name="theme_options[object_list_intro]" id="object_list_intro" ><?php echo $options['object_list_intro']; ?></textarea>
                    <br/><br/>
                    <label for="list_order_by">Ordernar objetos por</label>
                    <select name="theme_options[list_order_by]" id="list_order_by">
                        <option value="creation_date" <?php selected('creation_date', $options['list_order_by']); ?>>Data de criação</option>
                        <option value="title" <?php selected('title', $options['list_order_by']); ?>>Título</option>
                    </select>
                    <br/><br/>
                    <label for="list_order">Ordernar objetos em ordem</label>
                    <select name="theme_options[list_order]" id="list_order">
                        <option value="asc" <?php selected('asc', $options['list_order']); ?>>Ascendente</option>
                        <option value="desc" <?php selected('desc', $options['list_order']); ?>>Descendente</option>
                    </select>
                </div>
            </div>
            </div>
            
            <div id="aba-quantitativa-container" class="aba-container">
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php echo __('Avaliação quantitativa dos objetos da consulta', 'consulta'); ?></h3>
                    
                    <p><?php _e('Os objetos da sua consulta podem avaliados pelos usuários. O sistema permite até cinco valores diferentes para a avaliação. Por exemplo, a avaliação pode usar dois valores ("concordo" e "não concordo").', 'consulta'); ?></p>

                    <input type="checkbox" id="use_evaluation" name="theme_options[use_evaluation]" value="on" <?php checked('on', $options['use_evaluation']); ?> />
                    <label for="use_evaluation"><?php _e('Permitir que os usuários avaliem os objetos', 'consulta'); ?></label>

                    <div id="use_evaluation_labels_container">
                        <br/><br/>
                        <input type="checkbox" id="evaluation_show_on_list" name="theme_options[evaluation_show_on_list]" value="on" <?php checked('on', $options['evaluation_show_on_list']); ?> />
                        <label for="evaluation_show_on_list"><?php _e('Exibir avaliação na listagem de objetos por título ou por título e taxonomia', 'consulta'); ?></label>
                        <br/><br/>
                        <input type="checkbox" id="evaluation_public_results" name="theme_options[evaluation_public_results]" value="on" <?php checked('on', $options['evaluation_public_results']); ?> />
                        <label for="evaluation_public_results"><?php _e('Exibir resultado da avaliação para os usuários', 'consulta'); ?></label>
                        <br/><br/>
                        <?php _e('Texto introdutório para a avaliação quantitativa', 'consulta'); ?><br/>
                        <textarea name="theme_options[evaluation_text]" id="object_list_intro" ><?php echo $options['evaluation_text']; ?></textarea>
                        <br/><br/>
                        <table class="wp-list-table widefat fixed">
                            <tr>
                                <td><label for="evaluate_button"><?php _e('Texto do botão para avaliar exibido na listagem de objetos', 'consulta'); ?></label></td>
                                <td><input type="text" id="evaluate_button" class="text" name="theme_options[evaluate_button]" value="<?php echo htmlspecialchars($options['evaluate_button']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="label_1"><?php _e('Nome do primeiro valor (1)', 'consulta'); ?></label></td>
                                <td><input type="text" id="label_1" class="text" name="theme_options[evaluation_labels][label_1]" value="<?php echo htmlspecialchars($options['evaluation_labels']['label_1']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="label_2"><?php _e('Nome do segundo valor (2)', 'consulta'); ?></label></td>
                                <td><input type="text" id="label_2" class="text" name="theme_options[evaluation_labels][label_2]" value="<?php echo htmlspecialchars($options['evaluation_labels']['label_2']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="label_3"><?php _e('Nome do terceiro valor (3)', 'consulta'); ?></label></td>
                                <td><input type="text" id="label_3" class="text" name="theme_options[evaluation_labels][label_3]" value="<?php echo htmlspecialchars($options['evaluation_labels']['label_3']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="label_4"><?php _e('Nome do quarto valor (4)', 'consulta'); ?></label></td>
                                <td><input type="text" id="label_4" class="text" name="theme_options[evaluation_labels][label_4]" value="<?php echo htmlspecialchars($options['evaluation_labels']['label_4']); ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="label_5"><?php _e('Nome do quinto valor (5)', 'consulta'); ?></label></td>
                                <td><input type="text" id="label_5" class="text" name="theme_options[evaluation_labels][label_5]" value="<?php echo htmlspecialchars($options['evaluation_labels']['label_5']); ?>"/></td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Tipo de resultado', 'consulta'); ?></h3>
                    
                        <input type="radio" name="theme_options[evaluation_type]" id="evaluation_type_percentage" value="percentage" class="radio_evaluation_type" <?php checked('percentage', $options['evaluation_type']); ?>/>
                        <label for="evaluation_type_percentage"><b>Porcentagem de cada resposta</b></label><br/>
                        <br/>
                        
                        <input type="radio" name="theme_options[evaluation_type]" id="evaluation_type_average" value="average" class="radio_evaluation_type" <?php checked('average', $options['evaluation_type']); ?>/>
                        <label for="evaluation_type_average"><b>Média das respostas</b></label><br/>
                        <br/>
                        
                        <p>Exemplo de resultado:</p>
                        <div id="exemplo_resultado">
                            <?php html::image('ex_avaliacao_perce.png', __('Exemplo de resultado', 'consulta'), array('id' => 'perce') ); ?>
                            <?php html::image('ex_avaliacao_media.png', __('Exemplo de resultado', 'consulta'), array('id' => 'media') ); ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            
            <div id="aba-outras-container" class="aba-container">
            <div class="span-20 ">
                <div class="span-6 last">
                    <h3><?php echo __('Opções Gerais', 'consulta'); ?></h3>
                    
                    <table class="wp-list-table widefat fixed">
                        
                        <tr>
                        <td><label for="pagina_participe"><?php _e('Página com instruções para participação', 'consulta'); ?></label></td>
                        <td>
                            <p><?php _e('Selecione uma página para ativar o botão "Participe" na sua barra lateral.', 'consulta'); ?></p>
                            <?php wp_dropdown_pages(array(
                                'name' => 'theme_options[pagina_participe]',
                                'selected' => $options['pagina_participe'],
                                'show_option_none' => 'Não mostrar botão "Participe"'
                            )); ?>
                        </td>
                        </tr>
                        <tr>
                            <td><label for="data_encerramento"><?php _e('Data de encerramento da consulta', 'consulta'); ?></label></td>
                            <td><input type="text" id="data_encerramento" class="text" name="theme_options[data_encerramento]" value="<?php echo htmlspecialchars($options['data_encerramento']); ?>"/></td>
                        </tr>
                    </table>
                </div>
            </div>
            </div>
            
            <p class="textright clear prepend-top">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
            </p>
        </form>
    </div>

<?php 

}

