<?php
/*
 * 
 * Revista Agriculturas
 * 
 * Funções relacionadas com a Revista: criação de post type, registro de taxonomias e cadastro de meta boxes
 * 
 */

function teste_henrique(){
    $query = new WP_Query(array(
            /*'post_type' => 'revista',*/
            'tax_query' => array(array(
                    'taxonomy' => 'post_tag',
                    'field' => 'slug',
                    'terms' => array('revista')
                ))
        ));

    return $query;
}

// Cria o post type 'revista'
function revista_create_post_type() {
	
	$args = array(
		'labels' => array(
			'name' 			=> 'Revista Agriculturas',
			'singular_name' => 'Revista',
			'add_new'		=> 'Adicionar nova Revista ou Publicação',
			'add_new_item'	=> 'Adicionar nova Revista ou Publicação',
			'edit_item'		=> 'Editar Revista ou Publicação',
			'view_item'		=> 'Visualizar'
		),
	
		'menu_position'		=> 5,
		'public' 			=> true,
		//'has_archive'		=> true,
		'supports'			=> array( 'title', 'author', 'editor', 'excerpt', 'comments','page-attributes' ),
		'hierarchical'		=> true,
        'taxonomies'        => array('post_tag'),	
	);	
	
	register_post_type( 'revista', $args );
}

add_action( 'init', 'revista_create_post_type' );


// Cria as taxonomias personalizadas
function revista_build_taxonomies() {

	  $labels = array(
	    'name' 				=> 'Publicações',
	    'singular_name'	 	=> 'Publicação',
	    'search_items' 		=> 'Pesquisar publicações',
	    'all_items' 		=> 'Todas as publicações',
	    'edit_item' 		=> 'Editar publicação', 
	    'update_item' 		=> 'Atualizar publicação',
	    'add_new_item' 		=> 'Adicionar Nova Publicação',
	    'new_item_name' 	=> 'Nova publicação',
	  ); 	
	
	  register_taxonomy( 'publicacoes', 'revista', array(
	    'hierarchical'		=> true,
	    'labels' 			=> $labels,
	    'show_ui' 			=> true,
	  	'show_in_nav_menus' => false,
	    'query_var' 		=> true,
	    'rewrite' 			=> array( 'slug' => 'publicacoes' ),
	  ));

}
//add_action( 'init', 'revista_build_taxonomies' );


/*
 * Query
 * Adiciona o post type 'revista' às queries do site
 */
function aspta_post_filter( $query ) {
  global $wp_query;

  if ( !is_preview() && !is_admin() && !is_singular() && !is_404() ) {
    if ($query->is_feed) {
    	$query->set( 'post_type' , array( 'post', 'revista', 'campanha' ) );
    } else {
      $my_post_type = get_query_var( 'post_type' );
      if ( empty( $my_post_type ) )
        $query->set( 'post_type' , 'any' );
    }
  }

  return $query;
}
//add_filter( 'pre_get_posts' , 'aspta_post_filter' ); 



/*
 * Meta boxes
 * 
 * Adiciona novas meta boxes para:
 * 
 * 1. Atributos de página: retira o meta box padrão e adiciona um que apenas mostre as páginas mãe
 * 2. Envio de arquivo através do media uploader nativo do WordPress
 *  
 */


// Adiciona as meta boxes
add_action( 'add_meta_boxes', 'my_meta_boxes' );

// Salva os dados
add_action( 'save_post', 'my_meta_uploader_save' );

// Gera o media uploader
add_action( 'admin_head', 'my_meta_uploader_script' );



function my_meta_boxes()
{ 
    // Box para upload
	add_meta_box( 'my_meta_uploader', 'Upload de arquivo', 'my_meta_uploader_setup', 'revista', 'normal', 'high' );
	
	// Box para definir a página mãe do artigo
//    add_meta_box( 'my_meta_page', 'Página mãe', 'my_meta_page_setup', 'revista', 'side', 'low' );

}


// Adiciona os campos para a meta box da revista
function my_meta_page_setup($post) {
	$post_type_object = get_post_type_object( $post->post_type );
	
    if ( $post_type_object->hierarchical ) {
        $pages = wp_dropdown_pages( array( 'post_type' => $post->post_type, 'depth' => 1, 'exclude_tree' => $post->ID, 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __( '(no parent)' ), 'sort_column'=> 'menu_order, post_title', 'echo' => 0 ) );
        if ( ! empty($pages) ) { ?>
			<p>Apenas selecione uma página mãe caso esteja cadastrando um artigo. Se estiver cadastrando uma Revista, deixe este campo em branco.</p>
			<label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
			<?php
			echo $pages; 
        }
    }
}


// Adiciona os campos para a meta box de upload
function my_meta_uploader_setup()
{
	global $post;
 
	$meta = get_post_meta( $post->ID, 'upload_file', true );
	?>
	
	<p>
		Clique no botão para fazer o upload de um documento. Após o término do upload, clique em <em>Inserir no post</em>.
	</p>
	<p>
		<input id="upload_file" type="text" size="80" name="upload_file" style="width: 85%;" value="<?php if(!empty($meta)) echo $meta; ?>" />
		<input id="upload_file_button" type="button" class="button" value="Fazer upload" />
	</p>
	
	<?php 
}

function my_meta_uploader_save( $post_id ) {
	
	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
 
	$current_data = get_post_meta( $post_id, 'upload_file', true );	
 
	$new_data = $_POST['upload_file'];
 
	if ( $current_data ) 
	{
		if ( is_null( $new_data ) )
			delete_post_meta( $post_id, 'upload_file' );
		else
			update_post_meta( $post_id, 'upload_file', $new_data );
	}
	elseif ( !is_null( $new_data ) )
	{
		add_post_meta( $post_id, 'upload_file', $new_data, true);
	}
 
	return $post_id;
}



// Adiciona o script para uma cópia do uploader padrão do WP
function my_meta_uploader_script() { ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
	
			var formfield;
			var header_clicked = false;
	
			jQuery( '#upload_file_button' ).click( function() {
				formfield = jQuery( '#upload_file' ).attr( 'name' );
				tb_show( '', 'media-upload.php?TB_iframe=true' );
				header_clicked = true;
				
				return false;
			});
	
	
			/*
			user inserts file into post. only run custom if user started process using the above process
			window.send_to_editor(html) is how wp would normally handle the received data
			*/
	
			window.original_send_to_editor = window.send_to_editor;
	
			// Override send_to_editor function from original script. Writes URL into the textbox. Note: If header is not clicked, we use the original function.
			window.send_to_editor = function( html ) {
				if ( header_clicked ) {
					fileurl = jQuery( html ).attr( 'href' );
					jQuery( '#upload_file' ).val( fileurl );
					header_clicked = false;
					tb_remove();
				}
				else
				{
			  		window.original_send_to_editor( html );
			  	}
			}
	
		});
  </script>
<?php 
}




/*
 * Download do arquivo
 * 
 * Adiciona um parágrafo com um link para o arquivo do meta box de upload
 */

function revista_add_pdf( $content ) {
	
	global $post;
	
	$meta = get_post_meta( $post->ID, 'upload_file', true );
	
	if ( is_singular( 'revista' ) && $meta != '' ) {
		$content .= '<p class="download-revista">';
		$content .= '<a href="' . $meta . '" title="Clique para fazer o download do arquivo">';
		$content .= 'Faça o download do arquivo';
		$content .= '</a>';
		$content .= '</p>';
	}
	/*
	 * 
	 * O problema se mantem no link de download (signature) 
	 * 
	 * 
	elseif ( is_singular( 'revista' ) && $post->post_parent == 0 ) {
		$documentid = get_post_meta( $post->ID, '_issu_documentid', true );

		if ( $documentid ) {
			$content .= '<p class="download-revista">';
			$content .= '<a href="http://document.issuu.com/';
			$content .= $documentid;	
			$content .= '/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305227539&Signature=wK9eQ4h7PsKZL3VWBmVEk5CoggI%3D';
			$content .= '" title="Clique para fazer o download da revista completa">';
			$content .= 'Faça o download da revista';
			$content .= '</a>'; 
			$content .= '</p>';
		}
		
	}
	
	
	//http://document.issuu.com/110406203056-24a89312c2f64724879a1c7e5dfd112f/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305228895&Signature=wK9eQ4h7PsKZL3VWBmVEk5CoggI%3D
	//http://document.issuu.com/110406203056-24a89312c2f64724879a1c7e5dfd112f/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305227539&Signature=mavOQ2%2B2evxz9pdn4AiF7WyWZvc%3D
	 * 
	 */
	
	return $content;
	
}
add_filter( 'the_content', 'revista_add_pdf' );

	
/*
 * Issuu Embed
 * 
 * Gera o embed do Issuu sem usar o plugin, o que facilita a definição de campos default para o usuário
 */

function issuu_parser($content)
{
    $content = preg_replace_callback("/\[issuu ([^]]*)\]/i", "issuu_switcher", $content);
    return $content;
}

function getValueWithDefault($regex, $params, $default)
{
    $matchCount = preg_match_all($regex, $params, $matches);
    if ($matchCount) {
        return $matches[1][0];
    } else {
        return $default;
    }
}

function issuu_switcher($matches)
{
    $v = getValueWithDefault('/v=([\S]*)/i', $matches[1], 1);
    switch ($v) {
    case 1:
        return issuu_reader_1($matches);
    case 2:
        return issuu_reader_2($matches);
    default:
        return $matches;
    }
}

function issuu_reader_1($matches)
{
	global $documentid;
	
    $folderid = getValueWithDefault('/folderid=([\S]*)/i', $matches[1], '');
    $documentid = getValueWithDefault('/documentid=([\S]*)/i', $matches[1], '');
    $username = getValueWithDefault('/username=([\S]*)/i', $matches[1], '');
    $docname = getValueWithDefault('/docname=([\S]*)/i', $matches[1], '');
    //$loadinginfotext = getValueWithDefault('/loadinginfotext=([\S]*)/i', $matches[1], '');
    $tag = getValueWithDefault('/tag=([\S]*)/i', $matches[1], '');
    $showflipbtn = getValueWithDefault('/showflipbtn=([\S]*)/i', $matches[1], 'false');
    $proshowmenu = getValueWithDefault('/proshowmenu=([\S]*)/i', $matches[1], 'false');
    $proshowsidebar = getValueWithDefault('/proshowsidebar=([\S]*)/i', $matches[1], 'false');
    $autoflip = getValueWithDefault('/autoflip=([\S]*)/i', $matches[1], 'false');
    $autofliptime = getValueWithDefault('/autofliptime=([\S]*)/i', $matches[1], 6000);
    //$backgroundcolor = getValueWithDefault('/backgroundcolor=([\S]*)/i', 'FFFFFF', '');
    $layout = getValueWithDefault('/layout=([\S]*)/i', $matches[1], '');
    //$height = getValueWithDefault('/height=([\S]*)/i', $matches[1], 301);
    //$width = getValueWithDefault('/width=([\S]*)/i', $matches[1], 450);
    $unit = 'px';//getValueWithDefault('/unit=([\S]*)/i', $params, 'px');
    $viewmode = getValueWithDefault('/viewmode=([\S]*)/i', $matches[1], '');
    $pagenumber = getValueWithDefault('/pagenumber=([\S]*)/i', $matches[1], 1);
    //$logo = getValueWithDefault('/logo=([\S]*)/i', $matches[1], '');
    //$logooffsetx = getValueWithDefault('/logooffsetx=([\S]*)/i', $matches[1], 0);
    $logooffsety = getValueWithDefault('/logooffsety=([\S]*)/i', $matches[1], 0);
	//$showhtmllink = getValueWithDefault('/showhtmllink=([\S]*)/i', $matches[1], 'false');
    
    $viewerurl = "http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf";
    $standaloneurl = "http://issuu.com/$username/docs/$docname?mode=embed";
    $moreurl = "http://issuu.com/search?q=$tag";
    
    /*
     * Criando os padrões para a Revista Agriculturas
     */ 
    $loadinginfotext = get_post( $post->ID )->post_title;
    $backgroundcolor = 'FFFFFF';
    $height = 400;
    $width = 610;
    $logo = get_bloginfo( 'stylesheet_directory' ) . '/images/logo-revista.png';
    $logooffsetx = 10;
    $logooffsety = 35; 
    $showhtmllink = 'false';
    
    $flashvars = "mode=embed";
    if ($folderid) {
        // load folder parameters
        $flashvars = "$flashvars&amp;folderId=$folderid";
    } else {
        // load document parameters
        if ($documentid) {
            $flashvars = "$flashvars&amp;documentId=$documentid";
        }
        if ($docname) {
            $flashvars = "$flashvars&amp;docName=$docname";
        }
        if ($username) {
            $flashvars = "$flashvars&amp;username=$username";
        }
        if ($loadinginfotext) {
            $flashvars = "$flashvars&amp;loadingInfoText=$loadinginfotext";
        }
    }
    if ($showflipbtn == "true") {
        $flashvars = "$flashvars&amp;showFlipBtn=true";
    }
    if ($proshowmenu == "true") {
        $flashvars = "$flashvars&amp;proShowMenu=true";
    }
    if ($proshowsidebar == "true") {
        $flashvars = "$flashvars&amp;proShowSidebar=true";
    }
    if ($autoflip == "true") {
        $flashvars = "$flashvars&amp;autoFlip=true";
        if ($autofliptime) {
            $flashvars = "$flashvars&amp;autoFlipTime=$autofliptime";
        }
    }
    if ($backgroundcolor) {
        $flashvars = "$flashvars&amp;backgroundColor=$backgroundcolor";
        $standaloneurl = "$standaloneurl&amp;backgroundColor=$backgroundcolor";
    }
    if ($layout) {
        $flashvars = "$flashvars&amp;layout=$layout";
        $standaloneurl = "$standaloneurl&amp;layout=$layout";
    }
    if ($viewmode) {
        $flashvars = "$flashvars&amp;viewMode=$viewmode";
        $standaloneurl = "$standaloneurl&amp;viewMode=$standaloneurl";
    }
    if ($pagenumber > 1) {
        $flashvars = "$flashvars&amp;pageNumber=$pagenumber";
        $standaloneurl = "$standaloneurl&amp;pageNumbe=$pagenumber";
    }
    if ($logo) {
        $flashvars = "$flashvars&amp;logo=$logo&amp;logoOffsetX=$logooffsetx&amp;logoOffsetY=$logooffsety";
        $standaloneurl = "$standaloneurl&amp;logo=$logo&amp;logoOffsetX=$logooffsetx&amp;logoOffsetY=$logooffsety";
    }
    
    return ( ($showhtmllink == 'true') ? '<div>' : '') . 
           '<object style="width:' . $width . $unit . ';height:' . $height . $unit. '" ><param name="movie" value="' . $viewerurl . '?' . $flashvars . '" />' . 
           '<param name="allowfullscreen" value="true"/><param name="menu" value="false"/>' . 
           '<embed src="' . $viewerurl . '" type="application/x-shockwave-flash" style="width:' . $width . $unit . ';height:' . $height . $unit . '" flashvars="' .
           $flashvars . '" allowfullscreen="true" menu="false" /></object>' . 
           ( ($showhtmllink == 'true') ? ( '<div style="width:' . $width . $unit . ';text-align:left;">' . 
           ( $folderid ? '' : ('<a href="' . $standaloneurl . '" target="_blank">Open publication</a> - ') ) . 
           'Free <a href="http://issuu.com" target="_blank">publishing</a>' . 
           ( $folderid ? '' : ( $tag ? (' - <a href="' . $moreurl. '" target="_blank">More ' . urldecode($tag) . '</a>') : '' ) ) . '</div></div>' ) : '');
}

function issuu_reader_2($matches)
{
    $viewMode = getValueWithDefault('/[\s]+viewMode=([\S]*)/i', $matches[1], 'doublePage');
    $autoFlip = getValueWithDefault('/[\s]+autoFlip=([\S]*)/i', $matches[1], 'false');
    $width = getValueWithDefault('/[\s]+width=([\S]*)/i', $matches[1], 420);
    $height = getValueWithDefault('/[\s]+height=([\S]*)/i', $matches[1], 300);
    $unit = getValueWithDefault('/[\s]+unit=([\S]*)/i', $matches[1], 'px');
    $embedBackground = getValueWithDefault('/[\s]+embedBackground=([\S]*)/i', $matches[1], '');
    $pageNumber = getValueWithDefault('/[\s]+pageNumber=([\S]*)/i', $matches[1], 1);
    $titleBarEnabled = getValueWithDefault('/[\s]+titleBarEnabled=([\S]*)/i', $matches[1], 'false');
    $shareMenuEnabled = getValueWithDefault('/[\s]+shareMenuEnabled=([\S]*)/i', $matches[1], 'true');
    $showHtmlLink = getValueWithDefault('/[\s]+showHtmlLink=([\S]*)/i', $matches[1], 'true');
    $proSidebarEnabled = getValueWithDefault('/[\s]+proSidebarEnabled=([\S]*)/i', $matches[1], 'false');
    // Renamed proShowSidebar to proSidebarEnabled (Feb. 2011)
    if ($proSidebarEnabled == 'false') { // Backward compatible
        $proSidebarEnabled = getValueWithDefault('/[\s]+proShowSidebar=([\S]*)/i', $matches[1], 'false');
    }
    $printButtonEnabled = getValueWithDefault('/[\s]+printButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $shareButtonEnabled = getValueWithDefault('/[\s]+shareButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $searchButtonEnabled = getValueWithDefault('/[\s]+searchButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $linkTarget = getValueWithDefault('/[\s]+linkTarget=([\S]*)/i', $matches[1], '_blank');
    $backgroundColor = getValueWithDefault('/[\s]+backgroundColor=([\S]*)/i', $matches[1], '');
    $theme = getValueWithDefault('/[\s]+theme=([\S]*)/i', $matches[1], 'default');
    $backgroundImage = getValueWithDefault('/[\s]+backgroundImage=([\S]*)/i', $matches[1], '');
    $backgroundStretch = getValueWithDefault('/[\s]+backgroundStretch=([\S]*)/i', $matches[1], 'false');
    $backgroundTile = getValueWithDefault('/[\s]+backgroundTile=([\S]*)/i', $matches[1], 'false');
    $layout = getValueWithDefault('/[\s]+layout=([\S]*)/i', $matches[1], '');
    $logo = getValueWithDefault('/[\s]+logo=([\S]*)/i', $matches[1], '');
    $documentId = getValueWithDefault('/[\s]+documentId=([\S]*)/i', $matches[1], '');
    $name = getValueWithDefault('/[\s]+name=([\S]*)/i', $matches[1], '');
    $username = getValueWithDefault('/[\s]+username=([\S]*)/i', $matches[1], '');
    $tag = getValueWithDefault('/[\s]+tag=([\S]*)/i', $matches[1], '');
    $scriptAccessEnabled = getValueWithDefault('/[\s]+scriptAccessEnabled=([\S]*)/i', $matches[1], 'false');
    $id = getValueWithDefault('/[\s]+id=([\S]*)/i', $matches[1], '');
    
    $domain = 'issuu.com';
    
    $readerUrl = 'http://static.' . $domain . '/webembed/viewers/style1/v2/IssuuReader.swf';
    $openUrl = 'http://' . $domain . '/' . $username . '/docs/' . $name . '?mode=embed';
    $moreUrl = 'http://' . $domain . '/search?q=' . $tag;
    
    $flashVars = 'mode=mini';
    // ****** embed options ******
    // layout
    if ($viewMode == 'doublePage') { // default value
    } else {
        $flashVars = $flashVars . '&amp;viewMode=' . $viewMode;
    }
    if ($autoFlip == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;autoFlip=' . $autoFlip;
    }
    // color
    if ($embedBackground) {
        $flashVars = $flashVars . '&amp;embedBackground=' . $embedBackground;
    }
    // start on
    if ($pageNumber == 1) { // default value
    } else {
        $flashVars = $flashVars . '&amp;pageNumber=' . $pageNumber;
    }
    // show
    if ($titleBarEnabled == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;titleBarEnabled=' . $titleBarEnabled;
    }
    if ($shareMenuEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;shareMenuEnabled=' . $shareMenuEnabled;
    }
    if ($proSidebarEnabled == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;proSidebarEnabled=' . $proSidebarEnabled;
    }
    // ****** fullscreen options ******
    // show
    if ($printButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;printButtonEnabled=' . $printButtonEnabled;
    }
    if ($shareButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;shareButtonEnabled=' . $shareButtonEnabled;
    }
    if ($searchButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;searchButtonEnabled=' . $searchButtonEnabled;
    }
    // links
    if ($linkTarget == '_blank') { // default value
    } else {
        $flashVars = $flashVars . '&amp;linkTarget=' . $linkTarget;
    }
    // design
    if ($backgroundColor) {
        $flashVars = $flashVars . '&amp;backgroundColor=' . $backgroundColor;
    }
    if ($theme == 'default') { // default value
    } else {
        $flashVars = $flashVars . '&amp;theme=' . $theme;
    }
    if ($backgroundImage) {
        $flashVars = $flashVars . '&amp;backgroundImage=' . $backgroundImage;
    }
    if ($backgroundStretch == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;backgroundStretch=' . $backgroundStretch;
    }
    if ($backgroundTile == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;backgroundTile=' . $backgroundTile;
    }
    if ($layout) {
        $flashVars = $flashVars . '&amp;layout=' . $layout;
    }
    if ($logo) {
        $flashVars = $flashVars . '&amp;logo=' . $logo;
    }
    // ****** document information ******
    if ($documentId) {
        $flashVars = $flashVars . '&amp;documentId=' . $documentId;
    }
    
    return ( ($showHtmlLink == 'true') ? '<div>' : '' ) .
           '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" style="width:' . $width . $unit . ';height:' . $height . $unit. '" ' . 
           ( ($id) ? ('id="' . $id . '" ') : '' ) . '><param name="movie" value="' . $readerUrl . '?' . $flashVars . '" />' . 
           '<param name="allowfullscreen" value="true"/>' . 
           ( ($linkTarget == '_blank' && $scriptAccessEnabled == 'false') ? '' : '<param name="allowscriptaccess" value="always"/>' ) . 
           '<param name="menu" value="false"/><param name="wmode" value="transparent"/>' . 
           '<embed src="' . $readerUrl . '" type="application/x-shockwave-flash" style="width:' . $width . $unit . ';height:' . $height . $unit . '" flashvars="' .
           $flashVars . '" allowfullscreen="true" ' . 
           ( ($linkTarget == '_blank' && $scriptAccessEnabled == 'false') ? '' : 'allowscriptaccess="always" ' ) . 
           'menu="false" wmode="transparent" /></object>' . 
           ( ($showHtmlLink == 'true') ? ( '<div style="width:' . $width . $unit . ';text-align:left;">' . 
           '<a href="' . $openUrl . '" target="_blank">Open publication</a> - ' . 
           'Free <a href="http://' . $domain . '" target="_blank">publishing</a>' . 
           '</div>' . 
           ( $tag ? (' - <a href="' . $moreUrl. '" target="_blank">More ' . urldecode($tag) . '</a>') : '' ) . '</div></div>' ) : '');
}

add_filter( 'the_content', 'issuu_parser' );


/*
 * 
 * Revista - Thumbnail
 * 
 * Adiciona um custom field com o id da revista para mostrar automaticamente as capas
 * 
 */
function set_revista_thumbnail( $post_id ) {
	global $post;
	
	// Procura o id da revista
	preg_match('/documentid=([\S]*)/i', $_POST['content'], $matches );
	
	
	$current_data = get_post_meta( $post_id, '_issu_documentid', true );	
	$new_data = $matches[1];
	
	
 
	if ( $current_data ) 
	{
		if ( is_null( $new_data ) )
			delete_post_meta( $post_id, '_issu_documentid' );
		else 
			update_post_meta( $post_id, '_issu_documentid', $new_data );
	}
	elseif ( ! is_null( $new_data ) )
	{
		add_post_meta( $post_id, '_issu_documentid', $new_data, true);
	}
	
}

add_action( 'publish_revista', 'set_revista_thumbnail' );


/**
 * Mostra o thumbnail da revista
 * 
 * @param $size O tamanho da imagem [large | medium | small]
 */
function the_revista_thumbnail( $size = 'small' ) {
	
	global $post;
	
	$documentid = get_post_meta( $post->ID, '_issu_documentid', true );
	
	if ( $documentid ) {
	
		$imgurl = '<img src=http://image.issuu.com/';
		$imgurl .= $documentid;
		$imgurl .= '/jpg/page_1_thumb_';
		$imgurl .= $size . '.jpg';
		$imgurl .= ' title="' . get_the_title( $post->ID ) . '"';
		$imgurl .= ' alt="' . get_the_title( $post->ID ) . '"';
		$imgurl .= ' />';
		
	}
	else {

		$imgsrc = get_bloginfo( 'stylesheet_directory' ) . '/images/revista-miniatura.png';
		$imgurl = '<img src="' . $imgsrc . '" alt="' . get_the_title( $post->ID ) . '" />';
		
	}
	
	echo $imgurl;
		
}

?>
