<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-Print 2.50										|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Printer Friendly Comments Template										|
|	- wp-content/plugins/wp-print/print-comments.php					|
|																							|
+----------------------------------------------------------------+
*/
?>


<?php if($comments) : ?>
	<?php $comment_count = 1; global $text_direction; ?>
	<span style='float:<?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>' id='comments_controls'><?php print_comments_number(); ?> (<a  href="#" onclick="javascript:document.getElementById('comments_box').style.display = 'block'; return false;"><?php _e('Mostrar', 'delibera'); ?></a> | <a href="#" onclick="javascript:document.getElementById('comments_box').style.display = 'none'; return false;"><?php _e('Esconder', 'delibera'); ?></a>)</span>
	<div id="comments_box">
		<?php
			$opts = delibera_get_config();
			$validacao = delibera_comment_number($post->ID, 'validacao');
			$discussao = delibera_comment_number($post->ID, 'discussao');
			$encaminhamento = delibera_comment_number($post->ID, 'encaminhamento');
			$voto = delibera_comment_number($post->ID, 'voto');
			//$resolucao = delibera_comment_number($post->ID, 'resolucao');
		?>
		<p id="CommentTitle"><?php print_comments_number(); ?> <?php _e('para', 'delibera'); ?> "<?php the_title(); ?>"</p>
		<?php if($opts['validacao'] == 'S' && $validacao > 0) {?><p id="CommentTitle"><?php echo sprintf(_n('%s Validação', '%s Validações', $validacao, 'delibera'), number_format_i18n($validacao)); ?></p>
		<?php }if($discussao > 0) {?><p id="CommentTitle"><?php echo sprintf(_n('%s Opnião', '%s Opniões', $discussao, 'delibera'), number_format_i18n($discussao)); ?></p>
		<?php }if($encaminhamento > 0) {?><p id="CommentTitle"><?php echo sprintf(_n('%s Proposta', '%s Propostas', $encaminhamento, 'delibera'), number_format_i18n($encaminhamento)); ?></p>
		<?php }if($voto > 0) {?><p id="CommentTitle"><?php echo sprintf(_n('%s Voto', '%s Votos', $voto, 'delibera'), number_format_i18n($voto)); ?></p>
		<?php //}if($resolucao > 0) {?><p id="CommentTitle"><?php echo sprintf(_n('%s Resolução', '%s Resoluções', $resolucao, 'delibera'), number_format_i18n($resolucao)); ?></p>
		<?php }foreach ($comments as $comment) :
				$ncurtiu = delibera_numero_curtir($comment->comment_ID, 'comment');
				$tipo = delibera_get_comment_type($comment);
		?>
			<div class="print-comment-body <?php echo $tipo?>">
				<p class="CommentDate">
					<strong>#<?php echo number_format_i18n($comment_count); ?> <?php delibera_get_comment_type_label($comment, $tipo, TRUE); ?></strong> <?php _e('de', 'delibera'); ?> <u><?php comment_author(); ?></u> <?php _e('em', 'delibera'); ?> <?php comment_date(sprintf(__('%s @ %s', 'delibera'), get_option('date_format'), get_option('time_format'))); echo $ncurtiu > 0 ? ", $ncurtiu "._n('concordou', 'concordaram', $ncurtiu, 'delibera')  : '';echo " (".delibera_get_quem_curtiu($comment->comment_ID, 'comment', 'string').")";?>
				</p>
				<div class="CommentContent">
					<?php if ($comment->comment_approved == '0') : ?>
						<p><em><?php _e('Seu comentário está aguardando moderação.', 'delibera'); ?></em></p>
					<?php endif; ?>
					<?php print_comments_content(); ?>
				</div>
			</div>
			<?php $comment_count++; ?>
		<?php endforeach; ?>
		<hr class="Divider" style="text-align: center;" />
	</div>
<?php endif; ?>