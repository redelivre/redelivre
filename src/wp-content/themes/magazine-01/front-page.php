<?php get_header(); ?>
    <section id="main-section" class="clearfix">
		
        <?php $homefeatures = new WP_Query( 'posts_per_page=-1&meta_key=_home&meta_value=1&ignore_sticky_posts=1' ); ?>
        <section id="home-features" class="hl-carrousel clearfix" data-scroll-num="1">
			<?php if ($homefeatures->have_posts()) : ?>
				<div class="hl-wrapper">
					<h3>Destaques</h3>
					<?php while ($homefeatures->have_posts()) : $homefeatures->the_post(); ?>
						<?php html::part('loop','feature'); ?>
					<?php endwhile; ?>
				</div>
				<?php if ($homefeatures->post_count > 1) : ?>
    				<nav class="clearfix">
                        <a class="hl-nav-left">Anterior</a>
                        <a class="hl-nav-right">Próximo</a>  <!-- qualquer elemento com a classe hl-nav-right -->
                    </nav>
                <?php endif; ?>
			<?php else :?>
				<div class="empty-feature">
					<?php if (current_user_can('edit_theme_options')): ?>
                    <p>Para exibir posts aqui acesse o <a href="<?php echo admin_url('edit.php'); ?>">painel de administração</a> e marque a caixa de seleção "Destaque". Você pode marcar quantos posts quiser.</p>
                    <?php endif; ?>
				</div>
			<?php endif; ?>
		</section>        
		<!-- #home-features -->
		<section id="home-other-features" class="clearfix col-9">			
			<h3>Outras Notícias</h3>
			<div class="col-3 first"><?php echo new WidgetUniquePost('unique-post-1','loop-other-features') ?></div>
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-2','loop-other-features') ?></div>
			<div class="col-3 last"><?php echo new WidgetUniquePost('unique-post-3','loop-other-features') ?></div>
			<div class="clear"></div>
			<div class="col-3 first"><?php echo new WidgetUniquePost('unique-post-4','loop-other-features') ?></div>
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-5','loop-other-features') ?></div>
			<div class="col-3 last"><?php echo new WidgetUniquePost('unique-post-6','loop-other-features') ?></div>
		</section>
		<!-- #home-other-features -->
		<aside id="home-sidebar" class="col-3">
			<?php if (!dynamic_sidebar('Home')): ?>

				<?php if (current_user_can('manage_options')): ?>
					<h3>Widgets</h3>
					<div class="empty-feature">
						<p>Para exibir widgets aqui acesse o <a href="<?php echo admin_url('widgets.php'); ?>">painel de administração</a> e arraste widgets para o box "Home".</p>
					</div>
					
					
				<?php endif; ?>
			
			<?php endif; ?>
		</aside>
    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
