<?php 

$evaluationLabel = get_theme_option('evaluate_button');

?>

<?php if (get_theme_option('evaluation_show_on_list') && (get_theme_option('evaluation_public_results') || is_user_logged_in())) : ?>
    <div class="show_evaluation" title="<?php _e('avaliação', 'consulta'); ?>">
        <span class="count_object_votes_icon"></span>
    	<span class="count_object_votes">
    		<?php echo count_votes($post->ID); ?>
    	</span>
    	<?php echo $evaluationLabel; ?>
    </div>
<?php endif; ?>