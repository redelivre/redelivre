<?php

/*
- Cadastrar primeiro oembed em um meta _guarani_featured_video
- Parar de cadastrar após o primeiro
- Verificar mudança de ordem dos oembeds e atualizar o meta
- Remover do the_content o primeiro vídeo encontrado (talvez fazer isso pela referência ao meta)
*/



add_action( 'wp_insert_post', array( 'FeaturedVideo', 'init' ) );

/**
 * @package oEmbed_Featured_Image
 */
class FeaturedVideo
{
    /**
     * The post thumbnail ID
     *
     * @var int
     */
    private $_thumb_id;

    /**
     * The post ID
     *
     * @var int
     */
    private $_post_id;
    
    /**
     * The counter
     *
     * @var int
     */
    private $_i = 1;
    
    private $_post_format;

    /**
     * Sets up an instance if called statically, and attempts to set the featured
     * image from an embed in the post content (if one has not already been set).
     *
     * @param  int $post_id
     * @return object|null
     */
    public function init( $post_id )
    {
    	
        if ( ! isset( $this ) )
            return new FeaturedVideo( $post_id );

        global $wp_embed;

        $this->_post_id = absint( $post_id );
        
        if ( isset( $_POST['post_format'] ) )
        	$this->_post_format = $_POST['post_format'];
        
        if ( ! wp_is_post_revision( $this->_post_id ) )
        {
        
        	if ( $content = get_post_field( 'post_content', $this->_post_id, 'raw' ) ) {
        	
        		$featured_video = get_post_meta( $this->_post_id, '_guarani_featured_video', true );
        		
        		// Se a URL do meta não for encontrada no post_content ou se o post_format não for mais 'video', apagamos o custom field
        		if ( $featured_video && ( false === strpos( $content, $featured_video ) || $this->_post_format != 'video' ) )
        				delete_post_meta( $this->_post_id, '_guarani_featured_video');
        	
        		// Os filtros só são aplicados post_format 'video'
        		if ( $this->_post_format == 'video' )
        		{
	                add_filter( 'oembed_dataparse', array( $this, 'oembed_dataparse' ), 10, 3 );
	                $wp_embed->autoembed( $content );                
	                remove_filter( 'oembed_dataparse', array( $this, 'oembed_dataparse' ), 10, 3 );
                }

            }
            
        }       
             
    }

    /**
     * @see init()
     */
    public function __construct( $post_id )
    {
        $this->init( $post_id );
    }

    /**
     * Callback for the "oembed_dataparse" hook, which will fire on a successful
     * response from the oEmbed provider.
     *
     * @see WP_oEmbed::data2html()
     *
     * @param string $return The embed HTML
     * @param object $data   The oEmbed response
     * @param string $url    The oEmbed content URL
     */
    public function oembed_dataparse( $return, $data, $url )
    {
    
	    // Procura apenas o primeiro oembed do tipo 'video'
        if ( in_array( @ $data->type, array( 'video' ) ) && $this->_i == 1 )
        {

        	// Se a url for diferente da cadastrada no meta, atualiza o custom field
        	if ( $url != get_post_meta( $this->_post_id, '_guarani_featured_video', true ) )
            	update_post_meta( $this->_post_id, '_guarani_featured_video', $url );
	            
	        $this->_i++;

        }
        
    }
    
}


/**
 * Remove do post_content o link gravado no meta _guarani_featured_video
 * 
 */
function guarani_remove_featured_video_url( $content ) {

	global $post;
	
	if ( has_post_format( 'video', $post->ID ) && $featured_video = get_post_meta( $post->ID, '_guarani_featured_video', true ) )
  		$content = str_replace( $featured_video, '', $content );
  		
  	return $content;
}

add_action( 'the_content', 'guarani_remove_featured_video_url', 1 );

?>