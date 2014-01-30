<?php get_header(); ?>
<div id="primary" class="site-content row">
	<div id="content" role="main" class="container">
		<div class="span12 miolo">
			<?php if(get_theme_mod('banner-home')) : ?>
				<div class="banner">
					<img src="<?php echo get_theme_mod('banner-home'); ?>" />
				</div>
			<?php endif; ?>
			
			<div class="span4 sidebar-home">
				<?php if (get_theme_mod('show-presentation')): ?>
					<pre class="info"><?php
						echo esc_html(get_theme_mod('presentation'));
					?></pre>
				<?php endif; ?>
				<div class="widgets">
					<?php get_sidebar()?>
				</div>
			</div>
			
			<div class="row">			
				<div class="span7 destaques <?php echo (!get_theme_mod('banner-home')) ? 'sem-banner' : ''; ?>">
					<div class="flexslider bg-cor-1">
			  			<ul class="slides">
					  		<?php $slider = $ethymos->query->slider(); ?>
					  		<?php if($slider->have_posts()) : while($slider->have_posts()) : $slider->the_post(); ?>
								<li class="flex-slider-item">
								  <h2 class="span7 dest-titulo"> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								  <div class="clearfix"></div>
								  <div class="slider">
								    <?php the_post_thumbnail('banner-home'); ?>
								    <div class="slider-content-data">
								    	<span class="slider-date"><?php the_time('d/m/Y'); ?></span>
									    <?php the_excerpt(); ?>
									  	<a href="<?php the_permalink(); ?>"><?php _e('Leia mais', 'mobilize'); ?></a>
									  	<div class="clearfix"></div>
								    </div>
								    <div class="clearfix"></div>
								   </div>
							    </li>
					    	<?php endwhile; else : ?>
								<?php _e('Nenhum conteúdo cadastrado para o slider', 'mobilize'); ?>
							<?php endif; ?>
			  			</ul>
					</div>
					
					<div class="clearfix"></div>

					<!-- <div class="dest-navigator">
			    		<a class="prev browse left"><</a>
						<a class="next browse right"></a>
					</div> -->
				</div>
				<div class="span7 sub-destaques">
					<div class="title-sub-dest span7">
						<?php _e("+DESTAQUES", 'mobilize'); ?>
					</div>

					<?php $destaques = $ethymos->query->destaques(3); ?>
					<?php if($destaques->have_posts()) : while($destaques->have_posts()) : $destaques->the_post(); ?>
						<div class="not-1 span7 borda-cor-1">
							<div class="sub-dest-img span3">
								<?php the_post_thumbnail('destaques-home'); ?>
							</div>
							
							<div class="sub-dest-tex span3">
							   <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
							</div>
							
							<div class="sub-dest-desc span4">
							   <p><?php the_excerpt(); ?></p>
							</div>
							
							<a href="<?php the_permalink(); ?>" class="sub-dest-seguir"><?php _e('Continue lendo', 'mobilize'); ?></a>
						</div>
					<?php endwhile; else : ?>
					<div id="default">
						<?php _e('Nenhum destaque cadastrado', 'mobilize'); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
    </div>
</div>
<?php get_footer(); ?>
