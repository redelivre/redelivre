<?php
/**
 * Description of EasyAjax
 *
 * @author rafael
 */

class EasyAjax {
    static $admin = array();
    
    static function init(){
        $methods = get_class_methods(__CLASS__);
        
        foreach($methods as $method){
            if($method != 'init')
                if(in_array($method, self::$admin)){
                    add_action("wp_ajax_$method", array(__CLASS__,$method));
                }else{
                    add_action("wp_ajax_$method", array(__CLASS__,$method));
                    add_action("wp_ajax_nopriv_$method", array(__CLASS__,$method));
                }
        }
    }
    
    /**
     * usado no metodo form::cidade_uf_autocomplete()
     */
    static function get_ibge_cidade_uf(){
        global $wpdb;
        
        $value = $_REQUEST['value'];
        $value = preg_replace('/([^\/]+)\/.*/', '', $value);
        
        $vals = $wpdb->get_results("
            SELECT
                ibge_cidades.id as cidade_id,
                ibge_cidades.nome as cidade_nome,
                ibge_ufs.id as uf_id,
                ibge_ufs.sigla as uf_sigla,
                ibge_ufs.nome as uf_nome
            FROM 
                ibge_cidades,
                ibge_ufs
            WHERE
                ibge_cidades.nome LIKE '{$value}%' AND
                ibge_ufs.id = ibge_cidades.ufid
            ORDER BY
                ibge_cidades.nome ASC,
                ibge_ufs.sigla ASC
        ");
        $result = array('keys'=>array());
        foreach($vals as $val){
            $result['keys'][] = "{$val->cidade_nome} / {$val->uf_sigla}";
            $result["{$val->cidade_nome} / {$val->uf_sigla}"] = $val;
        }
        echo json_encode($result);
        die;
    }
}

EasyAjax::init();

?>
