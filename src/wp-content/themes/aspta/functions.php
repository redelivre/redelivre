<?php
/*
 * Revista Agriculturas e Campanha
 * 
 * Inclui os arquivos relacionados com estas duas áreas do site
 */
include( WP_CONTENT_DIR . '/themes/aspta/functions-revista.php' );
include( WP_CONTENT_DIR . '/themes/aspta/functions-campanha.php' );


/*
 * Admin Bar
 * Retira a admin bar do site
 */
function my_function_admin_bar(){
    return false;
}
add_filter( 'show_admin_bar' , 'my_function_admin_bar' );


/*
 * Thumbnails
 * Adiciona suporte a thumbnails de posts e cria um tamanho especial para o destaque da capa, para as imagens
 * dos Programas e para a área da Revista Agriculturas
 */ 
add_theme_support( 'post-thumbnails' );
add_image_size( 'destaque', 365, 270, true );
add_image_size( 'programas', 150, 150, true );
add_image_size( 'campanha', 150, 150, true );
add_image_size( 'revista', 178, 253, true );
add_image_size( 'revista-miniatura', 111, 158, true );


/*
 * Page Excerpts
 * Adiciona suporte a resumos em páginas
 */
add_post_type_support( 'page', 'excerpt' );


/*
 * IE Hacks
 */
function ie_hacks() { ?>
	<!--[if IE]>
  		<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/style-ie.css" type="text/css" media="screen, projection" />
  	<![endif]-->
  	<?php 
}
add_action( 'wp_head', 'ie_hacks' );



/*
 * jQuery Cycle
 */
function enqueue_jcycle(){
	wp_enqueue_script( 'jcycle', get_bloginfo( 'stylesheet_directory' ) . '/extensions/cycle/jquery.cycle.min.js', array( 'jquery' ), false, true );
}
add_action( 'wp_print_scripts', 'enqueue_jcycle' );


function define_jcycle() { ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.linkcat-slug-banners-rotativos .blogroll').cycle({
				fx: 'fade',
				speed: '500',
				timeout: '10000',
				height: '300'
			});
		});
	</script>
<?php }
add_action( 'wp_footer', 'define_jcycle' );


function childtheme_doctitle() {
	global $cat, $tag;
	
	$site_name = get_bloginfo('name');
    $separator = '|';
        	
    if ( is_single() ) {
      $content = single_post_title('', FALSE);
    }
    elseif ( is_home() || is_front_page() ) { 
      $content = get_bloginfo('description');
    }
    elseif ( is_page() ) { 
      $content = single_post_title('', FALSE); 
    }
    elseif ( is_search() ) { 
      $content = __('Search Results for:', 'thematic'); 
      $content .= ' ' . esc_html(stripslashes(get_search_query()));
    }
	elseif ( is_tag() ) { 
      $content = __('Tag Archives:', 'thematic');
      $content .= ' ' . $tag;
    }
    elseif ( is_category() ) {
     	if ( get_query_var( 'taxonomy' ) == 'programas' ) {
  			$category = get_category( $cat );
  			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); 
  			$content = 'Arquivo de ' . $category->name . ' dentro do ' . $term->name;
    	}
    	else {
    		$content = __('Category Archives:', 'thematic');
      		$content .= ' ' . single_cat_title("", false);	
    	}
    }
    elseif ( is_404() ) { 
      $content = __('Not Found', 'thematic'); 
    }
    elseif ( is_tax() ) {
    	$content .= 'Arquivos para ';
    	if ( is_tax( 'temas-de-intervencao' ) )
	    	$content .= 'o tema de intervenção: ';
	    elseif ( is_tax( 'itens-de-campanha' ) )
	    	$content .= 'o item de campanha: ';
		$content .= thematic_get_term_name();
    }
    else { 
      $content = get_bloginfo('description');
    }

    if (get_query_var('paged')) {
      $content .= ' ' .$separator. ' ';
      $content .= 'Página';
      $content .= ' ';
      $content .= get_query_var('paged');
    }
    
    return $content;

}
add_filter ( 'thematic_doctitle', 'childtheme_doctitle' );



/*
 * Widget Links
 * 
 * Coloca o slug da categoria de link dentro de sua classe
 */
function childtheme_link_args( $args ) {
	
	if ( ! empty ( $args['category'] ) ) {
		$linkclass = 'linkcat-slug-' . get_term_by( 'id', $args['category'], 'link_category' )->slug;
		$args['category_before'] = '<li id="%id" class="widgetcontainer widget_links ' . $linkclass . '">';
	}
	
	return $args;
	
}
add_filter( 'widget_links_args', 'childtheme_link_args' );



/*
 * Body class
 * Adiciona o slug das páginas ao body class (exemplo: "page-slug")
 */
function childtheme_body_class( $classes ){
	global $post;
	
	if ( is_page() )
		$classes[] = 'page-' . $post->post_name;
	elseif ( is_singular( 'revista' ) && $post->post_parent != 0 )
		$classes[] = 'single-revista-artigo';
	
	return $classes;
}
add_filter( 'body_class', 'childtheme_body_class' );


/*
 * Page comments
 * Retira os comentários das páginas
 */
function remove_comments() {
	remove_post_type_support( 'page', 'comments' );
}
add_action( 'init', 'remove_comments' );


/*
 * Widgets
 * Remove os widgets que não estão sendo usados
 */
function remove_widgets() {

    unregister_widget( 'WP_Widget_Archives' );
    unregister_widget( 'WP_Widget_Calendar' );
    //unregister_widget( 'WP_Widget_Categories' );
    //unregister_widget( 'WP_Widget_Links' );
    unregister_widget( 'WP_Widget_Meta' );
    unregister_widget( 'WP_Widget_Pages' );
    //unregister_widget( 'WP_Widget_Recent_Comments' );
    unregister_widget( 'WP_Widget_Recent_Posts' );
    unregister_widget( 'WP_Widget_RSS' );
    unregister_widget( 'WP_Widget_Search' );
    unregister_widget( 'WP_Widget_Tag_Cloud' );
    //unregister_widget( 'WP_Widget_Text' );

}
add_action( 'widgets_init', 'remove_widgets' );


/*
 * Actions da Thematic
 * Remove algumas actions desnecessárias da Thematic
 */
function remove_thematic_actions() {
	// Menu
	remove_action( 'thematic_header', 'thematic_access', 9 );
	
	// Navegação superior
	remove_action( 'thematic_navigation_above', 'thematic_nav_above', 2 );
	if ( is_single() )
		remove_action( 'thematic_navigation_below', 'thematic_nav_below', 2 );
}
add_action( 'wp', 'remove_thematic_actions' );


/*
 * Thematic Content Lenght
 * 
 *  Muda os excerpts para 'full' em videos em audios
 */
function childtheme_content( $content ) {
	
	if ( in_category( array( 'videos', 'audios' ) ) )
		$content = 'full';
	
	return $content;
}
add_filter( 'thematic_content' , 'childtheme_content' );


/*
 * Custom Taxonomies
 * Criando as taxonomias personalizadas 'Temas de intervenção' / 'Programas'
 */
function build_taxonomies() {

	// Temas de intervenção
	  $labels = array(
	    'name' 				=> 'Temas de intervenção',
	    'singular_name'	 	=> 'Tema de intervenção',
	    'search_items' 		=> 'Pesquisar temas',
	    'all_items' 		=> 'Todos os temas',
	    'parent_item' 		=> 'Tema pai',
	    'parent_item_colon' => 'Tema pai: ',
	    'edit_item' 		=> 'Editar tema', 
	    'update_item' 		=> 'Atualizar tema',
	    'add_new_item' 		=> 'Adicionar Novo Tema de Intervenção',
	    'new_item_name' 	=> 'Novo tema',
	    'menu_name' 		=> 'Temas de intervenção'
	  ); 	
	
	  register_taxonomy( 'temas-de-intervencao', 'post', array(
	    'hierarchical'		=> true,
	    'labels' 			=> $labels,
	    'show_ui' 			=> true,
	  	'show_in_nav_menus' => false,
	    'query_var' 		=> true,
	  	'capabilities'      => array('edit_terms' => false,'manage_terms' => false),
	    'rewrite' 			=> array( 'slug' => 'aspta-temas-de-intervencao' ),
	  ));

	  // Programas
	  $labels = array(
	    'name' 				=> 'Programas',
	    'singular_name'	 	=> 'Programa',
	    'search_items' 		=> 'Pesquisar programas',
	    'all_items' 		=> 'Todos os programas',
	    'parent_item' 		=> 'Programa pai',
	    'parent_item_colon' => 'Programa pai: ',
	    'edit_item' 		=> 'Editar programa', 
	    'update_item' 		=> 'Atualizar programa',
	    'add_new_item' 		=> 'Adicionar Novo Programa',
	    'new_item_name' 	=> 'Novo programa',
	    'menu_name' 		=> 'Programas'
	  ); 	
	
	  register_taxonomy( 'programas', 'post', array(
	    'hierarchical'		=> true,
	    'labels' 			=> $labels,
	    'show_ui' 			=> true,
	  	'show_in_nav_menus' => false,
	    'query_var' 		=> true,
	  	'capabilities'      => array('edit_terms' => false,'manage_terms' => false),
	    'rewrite' 			=> array( 'slug' => 'aspta-programas' ),
	  ));
}

add_action( 'init', 'build_taxonomies', 0 );


/*
 * Jaiminho
 * Filtra o plugin 
 */
function fon( $fon ) {
	global $thematic_widgetized_areas;
	print_r($thematic_widgetized_areas);
}
//add_filter( 'jaiminho', 'fon' );


/*
 * Widgets
 * Transforma a primary aside em área Geral e cria condicionais para Biblioteca e Programas
 */
function remove_widget_area($content) {
	
	$content['Primary Aside']['args']['name'] = 'Geral';
	
	unset ( $content['1st Subsidiary Aside'] );
	unset ( $content['2nd Subsidiary Aside'] );
	unset ( $content['3rd Subsidiary Aside'] );
	unset ( $content['Index Top'] );
	unset ( $content['Index Insert'] );
	unset ( $content['Index Bottom'] );
	unset ( $content['Single Top'] );
	unset ( $content['Single Insert'] );
	unset ( $content['Single Bottom'] );
	unset ( $content['Page Top'] );
	unset ( $content['Page Bottom'] );
	
    return $content;
    
}
add_filter( 'thematic_widgetized_areas', 'remove_widget_area' );


function change_primary_aside( $content ) {

	$content['Primary Aside']['function'] = 'childtheme_primary_aside';
	$content['Secondary Aside']['args']['name'] = 'Banners';
	$content['Secondary Aside']['args']['description'] = 'Área secundária, usada para os banners.';
	
	$content['Programas'] = array(
		'admin_menu_order' => 200,
        'args' => array (
        	'name' => 'Programas',
        	'id' => 'primary-aside-programas',
        	'description' => __('Área para widgets relacionados com os Programas.', 'childtheme'),
        	'before_widget' => thematic_before_widget(),
			'after_widget' => thematic_after_widget(),
			'before_title' => thematic_before_title(),
			'after_title' => thematic_after_title(),
		),
		'action_hook'	=> 'thematic_primary_aside',
		'function'		=> 'childtheme_primary_aside',
		'priority'	=> 10,
	);
	
	$content['Revista'] = array(
		'admin_menu_order' => 200,
        'args' => array (
        	'name' => 'Revista',
        	'id' => 'primary-aside-revista',
        	'description' => __('Espaço para área da Revista.', 'childtheme'),
        	'before_widget' => thematic_before_widget(),
			'after_widget' => thematic_after_widget(),
			'before_title' => thematic_before_title(),
			'after_title' => thematic_after_title(),
		),
		'action_hook'	=> 'thematic_primary_aside',
		'function'		=> 'childtheme_primary_aside',
		'priority'	=> 10
	);
	
	$content['Campanha'] = array(
		'admin_menu_order' => 200,
        'args' => array (
        	'name' => 'Campanha',
        	'id' => 'primary-aside-campanha',
        	'description' => __('Área de widgets da Campanha', 'childtheme'),
        	'before_widget' => thematic_before_widget(),
			'after_widget' => thematic_after_widget(),
			'before_title' => thematic_before_title(),
			'after_title' => thematic_after_title(),
		),
		'action_hook'	=> 'thematic_primary_aside',
		'function'		=> 'childtheme_primary_aside',
		'priority'	=> 10,
	);

	return $content;

}

add_filter( 'thematic_widgetized_areas','change_primary_aside', 9 );


/*
 * Condicional para informar qual sidebar será incluída
 */
function childtheme_primary_aside() {
	
	//TODO Biblioteca

	if ( is_sidebar_active('primary-aside') ) {
	// antiga condicional if (is_sidebar_active('primary-aside') && is_sidebar_active('primary-aside-programas'))
		echo thematic_before_widget_area('primary-aside');
		
		// Programas
		if ( is_page( 'programas' ) || is_child_of( 'programas' ) || is_tax( 'programas' ) ) {
			dynamic_sidebar( 'primary-aside-programas' );
		}
		elseif ( is_singular( 'revista' ) || is_page( 'revista-agriculturas') || is_child_of( 'revista-agriculturas' ) ) {
			dynamic_sidebar( 'primary-aside-revista' );
		}
		elseif ( is_page( 'campanha' ) || is_tax( 'itens-de-campanha') ) {
			dynamic_sidebar( 'primary-aside-campanha' );
		}
		else {
			dynamic_sidebar( 'primary-aside' );

		}

		echo thematic_after_widget_area('primary-aside');

	}

}

      
/**
 * Checks if the current / given page is descendant of another one.  
 * 
 * @uses $wpdb
 * @uses $post 
 * 
 * @param string|int $parent 
 * @param string|int $child Optional.
 * @return bool
 */
function is_child_of ( $parent, $child = '' ) {
	global $wpdb, $post;

	if ( is_numeric( $parent ) ) 
		$parent_id = (int) $parent;
	else 
		$parent_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_name = '$parent'" );
		
		
	if ( empty( $child ) ) : 
		$child = $post;		
	else :
		if ( is_numeric( $child ) )
			$child_id = (int) $child;
		else
			$child_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_name = '$child'" );
			
		$child = get_page( $child_id );
	endif;
			
	
	if ( $child->post_parent  && ( $parent_id != $child->ID ) ) :
		$ancestors = get_post_ancestors( $child->ID );
		return ( in_array( $parent_id, $ancestors ) ) ? true : false; 
	else :
		return false;
	endif;
}



/*
 *	Menu superior
 *	Cria o menu superior para contato, feed e searchform() 
 */
function childtheme_override_brandingopen() { ?>
	<div id="top-menu">
		<div id="servicos">
			<a href="<?php echo get_page_link( get_page_by_title( 'Contato' )->ID ); ?>" class="contato">Contato</a>
			<a href="<?php bloginfo( 'rss2_url' ); ?>" class="feed">Assine o feed</a>
		</div><!-- #servicos -->
		
		<div id="pesquisa">
			<?php get_search_form(); ?>
		</div><!-- #pesquisa -->
	</div><!-- #top-menu -->
	
	<div id="branding">
	<?php
}


/*
 * Tamanho do resumo
 * Muda o tamanho do resumo para a capa do site
 */
function custom_excerpt_length( $length ) {
	
	return ( is_home() ) ? 40 : 55;
	
}
add_filter( 'excerpt_length', 'custom_excerpt_length' );


/*
 * Estilo do resumo
 * Retira os [...] e troca por algo menos horrível
 */
function trim_excerpt( $text ) {
	return str_replace( '[...]', '&hellip;', $text );
}
add_filter( 'get_the_excerpt', 'trim_excerpt' );


/*
 * Novo menu
 * Cria o novo menu automaticamente
 */
function childtheme_access() { ?> 
	<div id="access">
		<?php wp_nav_menu( array( 'menu' => 'aspta', 'sort_column' => 'menu_order', 'theme_location' => 'primary' ) ); ?>
	</div><!-- #access -->
	<?php
}
add_action( 'thematic_header', 'childtheme_access' );


/*
 * Destaque
 * Cria o destaque na capa do site
 */
function destaque() {
	global $post, $destaque; ?>
	<?php if ( is_home() ) : ?>
	<div id="wrapper-destaque">
		<div id="destaque">
			<?php
				$arrayStickys = array_reverse(get_option('sticky_posts'));

				foreach ($arrayStickys as $postID) {
					$status = get_post($postID);
					$status = $status->post_status;

					if ($status != 'publish') {
						array_shift($arrayStickys);
					}
					else {
						break;
					}
				}

				$sticky = new WP_Query('p='.$arrayStickys[0]);

				if ( $sticky->have_posts() ) : while ( $sticky->have_posts() ) :
					$sticky->the_post();
					$destaque[] = $sticky->post->ID;
			?>
			<div id="wrapper-post">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></h2>
				<div class="entry-content">
					<?php the_excerpt(); ?> 
				</div>
				
				<a href="<?php the_permalink(); ?>" class="leia-mais" title="<?php the_title_attribute('before=Continue lendo "&after="'); ?>">Continue lendo</a>
			</div>
			
			<div class="post-thumbnail">
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php echo the_post_thumbnail( 'destaque', array( 'title' => get_the_title() ) ); ?>
				</a>
			</div>
			
			<?php endwhile; endif; ?>
		</div><!-- #destaque -->
	</div><!-- #wrapper-destaque -->
	<?php endif;
}
add_action( 'thematic_belowheader', 'destaque' );


function my_awesome_breadcrumbs() {
 
  $delimiter = '&raquo;';
  $home = 'Home'; // text for the 'Home' link
  $before = '<span class="current">'; // tag before the current crumb
  $after = '</span>'; // tag after the current crumb
 
  if ( !is_home() && !is_front_page() || is_paged() ) {
 
    echo '<div id="crumbs">';
 
    global $post;
    $homeLink = get_bloginfo('url');
    echo '<a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
 
    if ( is_category() ) {
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();
      $thisCat = $cat_obj->term_id;
      $thisCat = get_category($thisCat);
      $parentCat = get_category($thisCat->parent);
      if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
      echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
 
    } elseif ( is_day() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
      echo $before . get_the_time('d') . $after;
 
    } elseif ( is_month() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo $before . get_the_time('F') . $after;
 
    } elseif ( is_year() ) {
      echo $before . get_the_time('Y') . $after;
 
    } elseif ( is_single() && !is_attachment() ) {
      if ( get_post_type() != 'post' ) {
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a> ' . $delimiter . ' ';
        echo $before . get_the_title() . $after;
      } else {
        $cat = get_the_category(); $cat = $cat[0];
        echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
        echo $before . get_the_title() . $after;
      }
 
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' ) {
      $post_type = get_post_type_object(get_post_type());
      echo $before . $post_type->labels->singular_name . $after;
 
    } elseif ( is_attachment() ) {
      $parent = get_post($post->post_parent);
      $cat = get_the_category($parent->ID); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
      echo $before . get_the_title() . $after;
 
    } elseif ( is_page() && !$post->post_parent ) {
      echo $before . get_the_title() . $after;
 
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
      echo $before . get_the_title() . $after;
 
    } elseif ( is_search() ) {
      echo $before . 'Search results for "' . get_search_query() . '"' . $after;
 
    } elseif ( is_tag() ) {
      echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
 
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $before . 'Articles posted by ' . $userdata->display_name . $after;
 
    } elseif ( is_404() ) {
      echo $before . 'Erro' . $after;
    }
 
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
 
    echo '</div>';
 
  }
}

/*
 * Breadcrumbs
 * Adiciona as migalhas ao topo das páginas internas
 */
function childtheme_belowheader(){ ?>
	<div id="breadcrumbs">
		<?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<p>Você está aqui: ','</p>'); ?>
		<?php //my_awesome_breadcrumbs(); ?>
	</div><!-- #breadcrumbs -->
	<?php
}
add_action( 'thematic_belowheader', 'childtheme_belowheader' );


/*
 * Page title
 * 
 * Adiciona títulos corretos para as taxonomias
 */
function childtheme_title( $content ) {
	global $taxonomy, $cat, $tag;
	
  	if ( is_tag() ) {
  		$content = '<h1 class="page-title">';
		$content .= __('Tag Archives:', 'thematic');
		$content .= ' <span>';
		$content .= $tag;
		$content .= '</span></h1>';
  	}
	elseif ( is_category ( ) && get_query_var( 'taxonomy' ) == 'programas' ) {
  		$content = '<h1 class="page-title">';
  		$category = get_category( $cat );
  		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); 
  		$content .= 'Arquivo de <span>' . $category->name . '</span>' . ' dentro do ' . '<span>' . $term->name . '</span>';
  		$content .= '</h1>';
	  	$content .= '<div class="archive-meta">';
		$content .= term_description();
		//$content .= apply_filters('archive_meta', category_description());
		$content .= '</div>'; 
  	}
  	elseif ( is_tax( 'programas' ) ) {
  		$content = '<h1 class="page-title">';
		$content .= 'Arquivos do ';
		$content .= ' <span>';
		$content .= thematic_get_term_name();
		$content .= '</span>';
		$content .= '</h1>';
	  	$content .= '<div class="archive-meta">';
		$content .= term_description();
		//$content .= apply_filters('archive_meta', category_description());
		$content .= '</div>';
  	}
  	elseif ( is_tax( 'temas-de-intervencao' ) ) {
  		$content = '<h1 class="page-title">';
		$content .= 'Arquivos para o tema de intervenção: ';
		$content .= ' <span>';
		$content .= thematic_get_term_name();
		$content .= '</span>';
		$content .= '</h1>';
	  	$content .= '<div class="archive-meta">';
		$content .= term_description();
		//$content .= apply_filters('archive_meta', category_description());
		$content .= '</div>';
  	}
	elseif ( is_tax( 'itens-de-campanha' ) ) {
  		$content = '<h1 class="page-title">';
		$content .= 'Arquivos para o item de campanha: ';
		$content .= ' <span>';
		$content .= thematic_get_term_name();
		$content .= '</span>';
		$content .= '</h1>';
	  	$content .= '<div class="archive-meta">';
		$content .= term_description();
		//$content .= apply_filters('archive_meta', category_description());
		$content .= '</div>';
  	}
  	
  	
 	return $content;
}
add_filter( 'thematic_page_title', 'childtheme_title' );


/*
 * Post header
 * Retira o autor
 */
function childtheme_postheader_postmeta() {
	global $post;
	
	
 	 
    $postmeta = '<div class="entry-meta">';
    if ( is_singular( 'revista' ) && $post->post_parent != 0 ) {
    	$coauthors = coauthors_posts_links( ', ', ' e ', 'Por ', ' | ', false);
	if ($coauthors != "Por  | ")
		$postmeta .= $coauthors;
    }
    $postmeta .= '<span class="meta-sep meta-sep-entry-date"></span>';
    $postmeta .= thematic_postmeta_entrydate();
    
    $postmeta .= thematic_postmeta_editlink();
                   
    $postmeta .= "</div><!-- .entry-meta -->\n";
    
    return $postmeta;
    
}
add_filter( 'thematic_postheader_postmeta', 'childtheme_postheader_postmeta' );


/*
 * Post header - entry date
 * Retira o texto da data
 */
function childtheme_post_meta_entrydate() {
	$entrydate = '<span class="meta-prep meta-prep-entry-date"></span>';
    $entrydate .= '<span class="entry-date"><abbr class="published" title="';
    $entrydate .= get_the_time(thematic_time_title()) . '">';
    $entrydate .= get_the_time(thematic_time_display());
    $entrydate .= '</abbr></span>';
    
    return $entrydate;
}
	    
add_filter( 'thematic_post_meta_entrydate', 'childtheme_post_meta_entrydate' );


/*
 * Compartilhamento
 * Função para englobar os compartilhadores de conteúdo
 */
function aspta_sharecontent(){ 
	
	$sharecontent = '<div class="entry-share">';
	//$sharecontent .= '<iframe class="facebook" src="http://www.facebook.com/plugins/like.php?href=' . get_permalink() . '&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=25" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:25px;" allowTransparency="true"></iframe>';
	//$sharecontent .= '<a href="http://twitter.com/share" class="twitter-share-button" data-text="' . get_the_title() . '" data-count="horizontal" style="height:25px;">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
	//$sharecontent .= '<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script><a href="http://twitter.com/share" class="twitter-share-button">Tweet</a>';
	$sharecontent .= '<a class="comment-link" href="#respond" title ="' . __('Post a comment', 'thematic') . '">Faça um comentário</a>';
	$sharecontent .= '<script>function twitter_button() {u=location.href;t=document.title;window.open("http://twitter.com/share?url=' . get_permalink() . '&text=' . get_the_title() . '","sharer","toolbar=0,status=0,width=626,height=436");return false;}</script><div class="share-on-twitter"><a href="http://twitter.com/share?url=' . get_permalink() . '&text=' . get_the_title() . '" onclick="return twitter_button()" target="_blank" class="twitter_share_link">Compartilhe no Twitter</a></div>';
	$sharecontent .= '<script>function fbs_click() {u=location.href;t=document.title;window.open("http://www.facebook.com/sharer.php?u='. get_permalink() . '&t='. get_the_title() .'","sharer","toolbar=0,status=0,width=626,height=436");return false;}</script><a href="http://www.facebook.com/sharer.php?u='. get_permalink() . '&t='. get_the_title() .'" onclick="return fbs_click()" target="_blank" class="fb_share_link">Compartilhe no Facebook</a>';
	$sharecontent .= '</div><!--.entry-share -->';
	
	return $sharecontent;
}


/*
 * Post footer taxonomies
 * Lista as taxonomias da ASPTA no footer do post
 */
function aspta_postfooter_programas() { 
	
	$programas = get_the_term_list( $post->ID, 'programas', '', ', ', $aspta_programas_separator );
	
	if ( ! empty ( $programas ) ) {
		$apta_programas = '<span class="programas-links">';
		$aspta_programas .= ' dentro de ';
		$aspta_programas .= $programas;
		$aspta_programas .= '</span>';
	}
	
	return $aspta_programas;
	
}

function aspta_postfooter_temas() {
	
	$temas = get_the_term_list( $post->ID, 'temas-de-intervencao', ' com os temas ', ', ', $aspta_temas_separator );
	
	if ( ! empty ( $temas ) ) {
		$aspta_temas = '<span class="temas-links">';
		$aspta_temas .= $temas;
		$aspta_temas .= '</span>';
		
	}
	
	return $aspta_temas;
}



function childtheme_postfooter_posttags() {
    $tagtext = ( aspta_postfooter_temas( ) == false ) ? ' e com as tags ' : ' e as tags ';
    $posttags = get_the_tag_list("<span class=\"tag-links\"> $tagtext ",', ','</span>');
    
	return $posttags;
}

add_filter( 'thematic_postfooter_posttags', 'childtheme_postfooter_posttags' ); 


function aspta_postfooter_campanha() {
	$postcategory = '<span class="cat-links">';
	$postcategory .= __('This entry was posted in ', 'thematic') . get_the_term_list( $post->ID, 'itens-de-campanha', '', ', ' );
	$postcategory .= '</span>';
		
	return $postcategory;
}


/*
 * Post footer
 * Modifica o footer do post para conter as taxonomias novas e o YARPPlugin
 */
function childtheme_postfooter() {
	global $id, $post;
	
	 if ( is_singular( array( 'post', 'revista' ) ) ) {
	    if( function_exists( 'related_posts' ) ) {
	    	if( related_posts_exist() ) {
	    		$postfooter = '<div class="entry-related-posts">';
	    		$postfooter .= related_posts('', false);
	    		$postfooter .= '</div>';
	    	}
	    }
    }
    
    if ($post->post_type == 'page' && current_user_can('edit_posts')) { /* For logged-in "page" search results */
        $postfooter .= '<div class="entry-utility">' . thematic_postfooter_posteditlink();
        $postfooter .= "</div><!-- .entry-utility -->\n";    
    }
    elseif ($post->post_type == 'page') { /* For logged-out "page" search results */
        $postfooter = '';
    }
    else {
        if (is_single()) {
        	if ( is_singular( 'revista' ) )
        		$postfooter .= '<div class="entry-utility">' . aspta_sharecontent();
        	elseif ( is_singular ( 'campanha' ) )
        		$postfooter .= '<div class="entry-utility">' . aspta_postfooter_campanha() . thematic_postfooter_posttags() . aspta_sharecontent();
        	else 
            	$postfooter .= '<div class="entry-utility">' . thematic_postfooter_postcategory() . aspta_postfooter_programas() . aspta_postfooter_temas() . thematic_postfooter_posttags() . aspta_sharecontent();
        }
        elseif ( is_tax ('itens-de-campanha' ) ) {
        	$postfooter .= '<div class="entry-utility">' . aspta_postfooter_campanha() . aspta_postfooter_programas() . aspta_postfooter_temas() . thematic_postfooter_posttags() . thematic_postfooter_postcomments();
        }
        else {
            $postfooter .= '<div class="entry-utility">' . thematic_postfooter_postcategory() . aspta_postfooter_programas() . aspta_postfooter_temas() . thematic_postfooter_posttags() . thematic_postfooter_postcomments();
        }
        $postfooter .= "</div><!-- .entry-utility -->\n";    
    }
    
    return $postfooter;
}
add_filter( 'thematic_postfooter', 'childtheme_postfooter' ); 


/*
 * Rodapé
 * Troca o footer padrão da Thematic
 */
function childtheme_footer() { ?>
	<div id="wrapper-siteinfo">
		<div id="sitecontents">
			<?php wp_nav_menu( array( 'menu' => 'aspta-footer', 'sort_column' => 'menu_order', 'theme_location' => 'primary' ) ); ?>
			
			<div id="programas-footer">
				<h6>Programas locais</h6>
				<?php
				
					$programas = get_pages( array(
                    	'child_of'		=> get_page_by_title( 'Programas' )->ID,
                    	'sort_column'	=> 'menu_order',
                    ));
                    
                    if ( $programas ) {
                    	foreach ( $programas as $programa ) { ?>
                    	<div>
                    		<h3><a href="<?php echo get_permalink( $programa->ID ); ?> "><?php echo $programa->post_title; ?></a></h3>
                    		<ul>
                    			<?php
                    			query_posts( array( 'programas' => $programa->post_name, 'caller_get_posts' => 1, 'posts_per_page' => 2 ) ); 
                    			while ( have_posts() ) : the_post(); ?>
                    			<li><a href="<?php the_permalink(); ?>"><?php echo strip_tags(get_the_title()); ?></a></li>	
                    			<?php endwhile; ?>
                    		</ul>
                    	</div> 
                    	<?php
                    	}	
                    }
                    
				?>
				
			</div>
			<?php
			// Menu  
			 /*	 wp_nav_menu( array( 'menu' => 'gleisi', 'sort_column' => 'menu_order', 'theme_location' => 'primary' ) ); 
		  
			      
			<div id="to-the-top">
				<a href="#topo">Voltar ao topo &uarr;</a>
			</div>
			*/
			 ?>
		</div><!-- #sitecontents -->
		
		<div id="sitename">
			<a href="<?php bloginfo( 'url' ); ?>" title="Ir para a capa">AS&ndash;PTA</a>
		</div><!-- #sitename -->
		
		<div id="sitecredits">
			<p>Orgulhosamente criado com <a href="http://br.wordpress.org" title="WordPress">WordPress</a> pela <a href="http://ethymos.com.br" title="Ethymos">Ethymos</a></p>
		</div><!-- #sitecredits -->
	</div>
	
<?php
} 
add_action( 'thematic_footertext', 'childtheme_footer' );

// Adiciona a campanha nos feeds 
function feed_request($result) {
	if (isset($result['feed']) && !isset($result['post_type'])) {
		$result['post_type'] = get_post_types( array( 'public' => true ) );	
	}
		
	return $result;
}
add_filter('request', 'feed_request');

function filter_search($query) {
    if ($query->is_search || $query->is_tag) {
		$query->set('post_type', array('post', 'campanha'));
    };
    return $query;
};
add_filter('pre_get_posts', 'filter_search');

add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );

?>
