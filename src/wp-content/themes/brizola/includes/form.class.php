<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of form
 *
 * @author rafael
 */
class form {
    
    static function cidade_uf_autocomplete($params = array()){
        $field_uid = uniqid('cuf-');
        
        $params = $params + array(
            'classes'           => '',  // classes do input visivel
            'field_id'          => 'cidade-uf', // id do input visivel
            'field_name'        => 'cidade_uf', // nome do input visivel
            'cidade_field_name' => 'cidade', // propriedade name do campo invisivel de cidade
            'uf_field_name'     => 'uf', // propriedade name do campo invisivel de uf
            'cidade_property'   => 'id', // qual coluna da tabela cidade deve ser salva (possiveis: id ou nome)
            'uf_property'       => 'id', // qual coluna da tabela uf deve ser salva (possiveis: id, sigla ou nome)
            'cidade'            => '', // valor da propriedade cidade
            'uf'                => '', // valor da propriedade uf
            'field_value'       => '' // valor da campo visivel, que será exibido quando não for achada a cidade no banco de dados a partir dos valores de cidade e uf
        );
        
        $cidade = '';
        if($params['cidade']){
            global $wpdb;
            $cidade = $wpdb->get_var("
            SELECT
                CONCAT(ibge_cidades.nome,' / ',ibge_ufs.sigla) as val
            FROM 
                ibge_cidades,
                ibge_ufs
            WHERE
                ibge_cidades.{$params[cidade_property]} = '{$params[cidade]}' AND
                ibge_ufs.{$params[uf_property]} = '{$params[uf]}'");
        }
        $cidade = $cidade ? $cidade : $params['field_value'];
        ?>
        <input type="text" id="<?php echo $params['field_id'] ?>" name="<?php echo $params['field_name'] ?>" class="cidade-uf-autocomplete <?php echo $params['classes'] ?>" data-uf-property="<?php echo $params['uf_property'] ?>" data-cidade-property="<?php echo $params['cidade_property'] ?>" data-uid="<?php echo $field_uid; ?>" value="<?php echo $cidade ?>" />
        <input type="hidden" id="<?php echo $field_uid;?>-cidade" name="<?php echo $params['cidade_field_name'] ?>" value="<?php echo $params['cidade'] ?>" />
        <input type="hidden" id="<?php echo $field_uid;?>-uf" name="<?php echo $params['uf_field_name'] ?>" value="<?php echo $params['uf'] ?>" />
        <?php
    }
}

?>
