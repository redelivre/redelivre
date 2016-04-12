<?php

include('../../../../wp-load.php');

if (!current_user_can('manage_options') || !get_theme_option('use_evaluation')) {
    die('Você não deveria estar aqui');
}

if ($wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = 'municipio'")) {
    // somente o tema filho usado pelo MinC possui no perfil do usuário o
    // o município e o estado dos usuários
    $user_location = true;
} else {
    $user_location = false;
}

$votes = get_all_votes();

foreach ($votes as $key => $vote) {
    $votes[$key]['vote'] = get_vote_label($vote['vote']);
    $votes[$key]['user_email'] = get_the_author_meta('user_email', $vote['user_id']);
    $votes[$key]['user_name'] = get_the_author_meta('display_name', $vote['user_id']);
    $votes[$key]['post_title'] = get_the_title($vote['post_id']);
    $votes[$key]['permalink'] = get_post_permalink($vote['post_id']);
    
    $terms = get_the_terms($vote['post_id'], 'object_type');
    
    if (!empty($terms)) {
        $mainTerm = array_shift($terms);
        $votes[$key]['post_term'] = $mainTerm->name;
    } else {
        $votes[$key]['post_term'] = '';
    }
    
    if ($user_location) {
        $votes[$key]['user_city'] = get_user_meta($vote['user_id'], 'municipio', true);
        $votes[$key]['user_state'] = get_user_meta($vote['user_id'], 'estado', true);
    }
}

header('Pragma: public');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
header('Content-Disposition: attachment; filename=avaliacoes.xls');

?>

<?php

if ($user_location) {
    $location_header = '<td>Município</td><td>UF</td>';
} else {
    $location_header = '';
}

$objectLabels = get_theme_option('object_labels');
$objectSingularName = $objectLabels['singular_name'];

$taxonomyLabels = get_theme_option('taxonomy_labels');
$taxonomySingularName = $taxonomyLabels['singular_name'];

echo utf8_decode("
<table>
    <tr>
        <td>Voto</td>
        <td>Usuário</td>
        <td>Nome</td>
        {$location_header}
        <td>{$objectSingularName}</td>
        <td>Link</td>
        <td>{$taxonomySingularName}</td>
    </tr>"
);
    
?>

<?php foreach ($votes as $vote) : ?>

    <?php ob_start(); ?>
    
    <tr>
        <td><?php echo $vote['vote']; ?></td>
        <td><?php echo $vote['user_email']; ?></td>
        <td><?php echo $vote['user_name']; ?></td>
        <?php if ($user_location) : ?>
            <td><?php echo $vote['user_city']; ?></td>
            <td><?php echo $vote['user_state']; ?></td>
        <?php endif; ?>
        <td><?php echo $vote['post_title']; ?></td>
        <td><?php echo $vote['permalink']; ?></td>
        <td><?php echo $vote['post_term']; ?></td>
    </tr>

    <?php echo utf8_decode(ob_get_clean()); ?>
<?php endforeach; ?>

</table>