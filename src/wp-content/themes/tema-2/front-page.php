<?php get_header(); ?>
    <section id="main-section" class="clearfix">
		<section id="home-features" class="hl-carrousel clearfix" data-scroll-num="1" >
			<nav class="col-1">
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
			<nav class="col-1">
				<a class="hl-nav-right">Próximo</a>  <!-- qualquer elemento com a classe hl-nav-right -->
			</nav>
		</section>
		

    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
