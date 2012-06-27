<?php get_header(); ?>
    	<section id="main-section" class="wrap clearfix">
    		<div id="content" class="col-8">
    			<article id="post-<?php the_ID(); ?>" class="clearfix">                
    				<h2>Material Gráfico</h2>
    				<div class="post-content">										
    				    <?php if (function_exists('the_graphic_material')) the_graphic_material(); ?>
    				</div>
    				<!-- .post-content -->
    				<footer class="post-footer clearfix">
    					<?php get_template_part('interaction'); ?>
    				</footer>
    			</article>
    			<!-- .post -->
    		</div>
    	    <!-- #content -->
    		<aside id="sidebar" class="col-4 clearfix">
    			<?php get_sidebar(); ?>
    		</aside>
    	    <!-- #sidebar -->			       
    	</section>
        <!-- #main-section -->
        <section id="main-section" class="wrap clearfix">
            <div id="content" class="col-8">
                <p>Essa página está desabilitada</p>
            </div>
        </section>
<?php get_footer(); ?>
