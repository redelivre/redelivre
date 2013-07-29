<?php get_header(); ?>
<div class="container">
	 <div class="row">
		<div class="span12 miolo">
			<?php if(get_theme_mod('banner-home')) : ?>
				<div class="banner">
					<img src="<?php echo get_theme_mod('banner-home'); ?>" />
				</div>
			<?php endif; ?>
			
			<div class="span4">
				
				<div class="info">
					<?php echo get_option('_mobilize_apresentacao'); ?>
				</div>
				<div class="widgets">
			
					<?php get_sidebar()?>
						
				</div>
			</div>
			
			<!-- slider -->
			
			<div class="span7">
				    <div id="myCarousel" class="carousel slide">
				    <ol class="carousel-indicators">
				    <li data-target="#myCarousel" data-slide-to="0" class="active">
				    
				    
				    </li>
				    <li data-target="#myCarousel" data-slide-to="1"></li>
				    <li data-target="#myCarousel" data-slide-to="2"></li>
				    </ol>
				    <!-- Carousel items -->
				    <div class="carousel-inner">
				    <div class="active item">…</div>
				    <div class="item">Aqui esta a descrição</div>
				    <div class="item">…</div>
				    </div>
				    <!-- Carousel nav -->
				    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
				    <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
				    </div>
			</div>
			
			<!-- fim-slider -->
			
			
       </div>
    </div>
</div>			
						
					


<?php get_footer(); ?>
