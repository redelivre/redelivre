<?php get_header(); ?>
    <section id="main-section" class="clearfix">
		<section id="home-features" class="hl-carrousel clearfix" data-scroll-num="1" >
			<nav id="left">
				<a class="hl-nav-left">Anterior</a>  <!-- qualquer elemento com a classe hl-nav-left -->
			</nav>
			<div class="hl-wrapper">				
				<?php
				$sticky = get_option( 'sticky_posts' );
				rsort( $sticky );
				$sticky = array_slice( $sticky, 0, 5 );
				?>
				<?php $homefeatures = new WP_Query( array( 'post__in' => $sticky ) ); ?>
				<?php if ($homefeatures->have_posts()) : while ($homefeatures->have_posts()) : $homefeatures->the_post(); ?>
					<?php html::part('loop','feature'); ?>
				<?php endwhile; ?>
				<?php else : ?>
					<p><?php _e('No results found.', 'tema2'); ?></p>              
				<?php endif; ?>
			</div>
			<nav id="right">
				<a class="hl-nav-right">Próximo</a>  <!-- qualquer elemento com a classe hl-nav-right -->
			</nav>
		</section>
		<!-- #home-features -->
		<section id="home-other-features" class="clearfix">
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-1','loop-other-features') ?></div>
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-2','loop-other-features') ?></div>
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-3','loop-other-features') ?></div>
			<div class="col-3"><?php echo new WidgetUniquePost('unique-post-4','loop-other-features') ?></div>
		</section>
		<!-- #home-other-features -->
    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
