<?php
/**
 * The template for displaying Archive for Post Type Agenda
 *
 * @package Mobilize
 * @since Mobilize 1.0
 */
 
global $paged;
if(isset($_GET['eventos'])){
	$showing_past = ($paged > 0 || $_GET['eventos'] == 'passados');
}
get_header();

?>

	<div id="primary" class="site-content row">
		<div id="content" class="container" role="main">
			<div class="span12 miolo">
				<div class="span4 sid-int">
					<?php get_sidebar(); ?>
				</div>
		
		
		<div class="span7 sid-int">
			<?php
			if(isset($showingPast)): ?>
				<h2 class="clearfix">
					<?php _e('Eventos Passados', 'mobilize'); ?>
					<a class="view-events" href="<?php echo add_query_arg('eventos', ''); ?>"><?php _e('Ver próximos eventos &raquo;', 'mobilize'); ?></a>
				</h2>
			<?php else: ?>
				<h2 class="clearfix">
					<?php _e('Próximos eventos', 'mobilize'); ?>
					<a class="view-events" href="<?php echo add_query_arg('eventos', 'passados'); ?>"><?php _e('Ver eventos passados &raquo;', 'mobilize'); ?></a>
				</h2>
			<?php endif; ?>
	    
            <?php if ( have_posts()) : ?>
            
                <?php get_template_part('loop', 'agenda'); ?>
			
            <?php else : ?>
                    <p class="post"><?php _e('Nenhum evento encontrado', 'mobilize'); ?></p>              
            <?php endif;
             ?>
		</div>
            
       <div class="span7 arquivo-agenda">     
		<?php if ( have_posts() ) : ?>

			<header class="archive-header archive-agenda-header">
				<?php  if ( $showing_past ) : ?>
					<h1 class="archive-title agenda-title"><?php _e( 'Past events', 'guarani' ); ?></h1>
					<a class="view-events" href="<?php echo add_query_arg( 'eventos', '' ); ?>"><?php _e( 'View next events &raquo;', 'mobilize' ); ?></a>
				<?php else: ?>
					<h1 class="archive-title agenda-title"><?php _e( 'Próximos Eventos', 'mobilize' ); ?></h1>
					<a class="view-events" href="<?php echo add_query_arg( 'eventos', 'passados' ); ?>"><?php _e( 'View past event &raquo;', 'mobilize' ); ?></a>
				<?php endif; ?>
			</header><!-- .archive-header -->
			
			<ul class="agenda-events-list span7">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					//get_template_part( 'content', get_post_format() );
				?>
				
				<?php if ( $date_start = get_post_meta( $post->ID, '_data_inicial', true ) ) : ?>
				<li>
					
					<?php
					$date_end = get_post_meta( $post->ID, '_data_final', true );
					if ( $date_end && $date_end != $date_start ) :
						/* translators: Initial & final date for the event */
						printf(
							'%1$s to %2$s',
							date( get_option( 'date_format' ), strtotime( $date_start ) ),
							date( get_option( 'date_format' ), strtotime( $date_end ) )
						);
					else :
						echo date( get_option( 'date_format' ), strtotime( $date_start ) );
					endif;
					?>
				
				<h1 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'guarani' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
				</li>
				<?php endif; ?>
				

			<?php endwhile; ?>
			
			</ul><!-- .agenda-events-list -->

		<?php else : ?>
			<?php get_template_part( 'no-results', 'archive' ); ?>
		<?php endif; ?>
			  </div>
			</div>
		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php get_footer(); ?>