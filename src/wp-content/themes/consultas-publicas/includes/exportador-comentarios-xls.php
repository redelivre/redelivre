<?php


include('../../../../wp-load.php');

if (!current_user_can('manage_options'))
    die('Você não deveria estar aqui');
    
    
$inicio = $_POST['data_inicial'];
$fim = $_POST['data_final'];


global $wpdb;

if ($inicio && $fim) {
    $q = $wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_date >= %s AND comment_date <= %s  ORDER BY comment_date", $inicio, $fim);
} else {
    $q = $wpdb->prepare("SELECT * FROM $wpdb->comments ORDER BY comment_date");
}

$comments = $wpdb->get_results($q);


header('Pragma: public');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
header('Content-Disposition: attachment; filename=comentarios-consulta.xls');

?>

<?php echo utf8_decode('

<table>

    <tr>
        <td colspan="6">Dados do comentário</td>
        <td colspan="2">Conteúdo relacionado</td>
        <td colspan="11">Dados do autor do comentário</td>
        
    </tr>
    <tr>
        <td>ID</td>
        <td>Data</td>
        <td>Comentario</td>
        <td>Resposta a</td>
        <td>Sugestão de alteração?</td>
        <td>Aprovado?</td>
        
        <td>Tipo</td>
        <td>Título</td>
        
        <td>ID</td>
        <td>Nome</td>
        <td>E-mail</td>
        <td>UF</td>
        <td>Ocupação</td>
        <td>Ocupação (outra)</td>
        <td>Atuação</td>
        <td>Atuação (outra)</td>
        <td>Categoria</td>
        <td>Sub Categoria</td>
        <td>Instituição</td>
    </tr>'); ?>



<?php foreach ($comments as $c) : ?>

    <?php ob_start(); ?>
    
    <tr>
    
        <td><?php echo $c->comment_ID; ?></td>
        <td><?php echo date('d/m/Y', strtotime($c->comment_date)); ?></td>
        <td><?php echo $c->comment_content; ?></td>
        <td><?php echo $c->comment_parent; ?></td>
        <td><?php echo get_comment_meta($c->comment_ID, 'sugestao_alteracao', true) ? 'Sim' : 'Não'; ?></td>
        <td><?php echo $c->comment_approved; ?></td>
        
        <?php $post = $wpdb->get_row("SELECT post_title, post_type FROM $wpdb->posts WHERE ID = $c->comment_post_ID"); ?>
        
        <td><?php echo $post->post_type; ?></td>
        <td><?php echo $post->post_title; ?></td>
        
        <?php $author = get_userdata($c->user_id); ?>
        
        <td><?php echo $c->user_id; ?></td>
        <td><?php echo $author->display_name; ?></td>
        <td><?php echo $author->user_email; ?></td>
        <td><?php echo $author->estado; ?></td>
        <td><?php echo $author->ocupacao; ?></td>
        <td><?php echo $author->ocupacao_outra; ?></td>
        <td><?php echo $author->atuacao; ?></td>
        <td><?php echo $author->atuacao_outra; ?></td>
        <td><?php echo $author->categoria; ?></td>
        <td><?php echo $author->sub_categoria; ?></td>
        <td><?php echo $author->instituicao; ?></td>
    
    </tr>

    <?php 
    
    $o = ob_get_clean();
    echo utf8_decode($o);
    
    ?>



<?php endforeach; ?>

</table>


