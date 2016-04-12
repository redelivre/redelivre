<?php
class html{
    /**
     * Imprime uma tag img... dados, nome de arquivo, alt [, complemento [, array html_attributes] ]
     * @param string $filename
     * @param string $alt
     */
    static function image($filename, $alt){
        $html_attributes = array();
        $complement = null;
        for ($i=2; $i<func_num_args(); $i++){
            $arg = func_get_arg($i);
            if(is_array($arg))
                $html_attributes = $arg;
                
            if(is_string($arg))
                $complement = $arg;
        }
        
        echo self::getImage($filename, $alt, $complement, $html_attributes);
    }
    
    /**
     * Retorna uma tag img... dados nome de arquivo, alt [, complemento [, html_attributes] ]
     * @param string $filename
     * @param string $alt
     */
    
    static function getImage($filename, $alt){
        $html_attributes = array();
        $complement = null;
        for ($i=2; $i<func_num_args(); $i++){
            $arg = func_get_arg($i);
            if(is_array($arg))
                $html_attributes = $arg;
                
            if(is_string($arg))
                $complement = $arg;
        }
        $url = self::getImageUrl($filename, $complement);
        $img_attr = "";
        foreach ($html_attributes as $attr=>$val)
            $img_attr.= $attr.'="'.$val.'" ';
            
        $alt = htmlentities(utf8_decode($alt));
        return "<img src=\"$url\" alt=\"$alt\" $img_attr/>";
    }
    
    /**
     * Retorna a url da imagem dados nome de arquivo e complemento
     * @param string $filename
     * @param string $complement null
     */
    static function getImageUrl($filename, $complement = null){
        $filenames = array();
        $filename = 'img/'.$filename;
        
        if(is_string($complement))
            $filenames[] = preg_replace('/\.[^\.]+$/', '-'.$complement.'$0', $filename);
            
        $filenames[] = $filename;
        
        foreach ($filenames as $fname){
            if(file_exists(STYLESHEETPATH.'/'.$fname)){
                return get_stylesheet_directory_uri().'/'.$fname;
            }
            
            if(file_exists(TEMPLATEPATH.'/'.$fname)){
                return get_template_directory_uri().'/'.$fname;
            }
        }
        return $filename;
    }
    
    /**
     * Semelhante ao get_template_part, porém os arquivos devem ficar dentro da pasta parts/ do thema,<br/> 
     * Se um array for enviado como segundo ou terceiro parâmetro este será extraido usando extract($array_passado, EXTR_PREFIX_INVALID, 'var');<br/>
     * Se um string for enviado como segundo ou terceiro parâmetro, este será utilizado como o segundo parâmetro do get_template_part
     *   
     * @param string $slug
     * 
     * @example part('slug'); inclui o arquivo parts/slug.php
     * @example part('slug', 'name'); inclui o arquivo parts/slug-name.php
     * @example part('slug', array('var1'=>'valor da var'); inclui o arquivo parts/slug.php e torna $var1 acessível
     * @example part('slug', 'name', array('var1'=>'valor da var'); inclui o arquivo parts/slug-name.php e torna $var1 acessível
     */
    static function part($slug){
        $_part_vars = array();
        $name = null;
        for ($i=1; $i<func_num_args(); $i++){
            $arg = func_get_arg($i);
            if(is_array($arg))
                $_part_vars = $arg;
                
            if(is_string($arg))
                $name = $arg;
        }
        
        $slug = 'parts/'.$slug;
        
        if($name)
            $templates[] = "{$slug}-{$name}.php";
         
        $templates[] = "{$slug}.php";
        
        // extraindo as variaveis
        extract($_part_vars, EXTR_PREFIX_INVALID, 'var');
        
        
        global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
    
    	if ( is_array( $wp_query->query_vars ) )
    		extract( $wp_query->query_vars, EXTR_SKIP );
        
        
        foreach ($templates as $filename){
            if(file_exists(STYLESHEETPATH.'/'.$filename)){
                require STYLESHEETPATH.'/'.$filename;
                return;
            }
            
            if(file_exists(TEMPLATEPATH.'/'.$filename)){
                require TEMPLATEPATH.'/'.$filename;
                return;
            }
        }
    } 
}
