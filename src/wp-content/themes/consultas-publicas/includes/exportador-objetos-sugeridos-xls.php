<?php

include('../../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Você não deveria estar aqui');
}
    
$inicio = $_POST['data_inicial'];
$fim = $_POST['data_final'];

global $wpdb;

if ($inicio && $fim) {
    $q = $wpdb->prepare(
        "SELECT * FROM $wpdb->posts p, $wpdb->postmeta pm
        WHERE p.ID = pm.post_id AND pm.meta_key = '_user_created' AND pm.meta_value = 1 AND post_type = 'object' AND post_date >= %s AND post_date <= %s
        ORDER BY post_date",
        $inicio, $fim
    );
} else {
    $q = "SELECT * FROM $wpdb->posts p, $wpdb->postmeta pm WHERE p.ID = pm.post_id AND pm.meta_key = '_user_created' AND pm.meta_value = 1 AND post_type = 'object' ORDER BY post_date";
}

$objects = $wpdb->get_results($q);

header('Pragma: public');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
header('Content-Disposition: attachment; filename=metas-sugeridas-consulta.xls');

?>

<?php echo utf8_decode('

<table>

    <tr>
        <td colspan="4">Dados da meta</td>
        <td >Tema</td>
        <td colspan="11">Dados do autor da meta</td>
        
    </tr>
    <tr>
        <td>ID</td>
        <td>Data</td>
        <td>Meta</td>
        <td>Ementa</td>
        
        <td></td>
        
        <td>ID</td>
        <td>Nome</td>
        <td>E-mail</td>
    </tr>'); ?>

<?php foreach ($objects as $object) : ?>

    <?php ob_start(); ?>
    
    <tr>
        <td><?php echo $object->ID; ?></td>
        <td><?php echo date('d/m/Y', strtotime($object->post_date)); ?></td>
        <td><?php echo $object->post_title; ?></td>
        <td><?php echo $object->post_content; ?></td>
        
        <?php
        $the_terms = get_the_terms($object->ID, 'object_type');
        ?>
        
        <td><?
            if (is_array($the_terms)) {
                foreach ($the_terms as $the_term) {
                    echo $the_term->name;
                }
            }
            ?>
        </td>
        
        <?php $author = get_userdata($object->post_author); ?>
        
        <td><?php echo $object->post_author; ?></td>
        <td><?php echo $author->display_name; ?></td>
        <td><?php echo $author->user_email; ?></td>
    </tr>

    <?php 
    
    $o = ob_get_clean();
    echo utf8_decode($o);
    
    ?>

<?php endforeach; ?>

</table>