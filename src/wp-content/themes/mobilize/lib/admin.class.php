<?php

class Ethymos_Admin{
	/**
	*
	*
	*/
	public function __construct(){
		
		add_action('customize_register', array($this, 'theme_customizer'));
		
		add_action('add_meta_boxes', array($this, 'metabox_equipe'));
		add_action('save_post', array($this, 'metabox_equipe_save'));
		
		add_action('admin_menu', array($this, 'theme_options'));
		
		//metabox subtitulo
		add_action('add_meta_boxes', array($this, 'metabox_subtitulo'));
		add_action('save_post', array($this, 'metabox_subtitulo_save'));
	}
	
	/**
	*
	*/
	public function theme_customizer($wp_customize){
		// Cores
		$wp_customize->add_section('cores', array('title' => __('Cores', 'mobilize'), 'priority' => 30));
		$wp_customize->add_setting('cor-1', array('default' => '#ee720d', 'transport' => 'refresh'));
		$wp_customize->add_setting('cor-2', array('default' => '#f7ad40', 'transport' => 'refresh'));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cor-1', array(
			'label'        => __( 'Cor 1', 'mobilize' ),
			'section'    => 'cores',
			'settings'   => 'cor-1',
		)));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cor-2', array(
			'label'        => __( 'Cor 2', 'mobilize' ),
			'section'    => 'cores',
			'settings'   => 'cor-2',
		)));
		
		// 
		// Logo		
		$wp_customize->add_section('logo', array('title' => __('Logo', 'mobilize'), 'priority' => 30 ));
		$wp_customize->add_setting('logo', array('default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo', array(
			'label'        => _x( 'Logo', 'theme-customizer', 'mobilize' ),
			'section'    => 'logo',
			'settings'   => 'logo',
		)));
		
		//Banner home
		$wp_customize->add_section('banner-home', array('title' => __('Banner', 'mobilize'), 'priority' => 30 ));
		$wp_customize->add_setting('banner-home', array('default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'banner-home', array(
			'label'        => _x( 'Banner', 'theme-customizer', 'mobilize' ),
			'section'    => 'banner-home',
			'settings'   => 'banner-home',
		)));
		
		// Background		
		$wp_customize->add_section('background', array(	'title' => __('Fundo', 'mobilize'),	'priority' => 30 ));
		$wp_customize->add_setting('background-imagem', array( 'default' => '', 'transport' => 'refresh'));
		$wp_customize->add_setting('background-cor', array( 'default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'background-imagem', array(
			'label'        => _x( 'Imagem', 'theme-customize', 'mobilize' ),
			'section'    => 'background',
			'settings'   => 'background-imagem',
		)));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background-cor', array(
			'label'        => _x( 'Cor', 'theme-customize', 'mobilize' ),
			'section'    => 'background',
			'settings'   => 'background-cor',
		)));
	}
	
	/**
	* configura o metabox subtitulo
	*
	*/
	public function metabox_subtitulo(){
		add_meta_box( 'meta-subtitulo', 'Op&ccedil;&otilde;es', array($this, 'metabox_subtitulo_exibe'), 'page');
	}
	
	/**
	* Exibe o conteúdo do metabox subtitulo
	*
	*/
	public function metabox_subtitulo_exibe(){
		include get_template_directory() . '/admin/metabox-subtitulo.php';
	}
	
	/**
	* Grava os dados do metabox subtitulo
	*
	*/
	public function metabox_subtitulo_save(){
		if(isset($_POST['meta-subtitulo'])){
			update_post_meta($_POST['post_ID'], '_subtitulo', $_POST['meta-subtitulo']);
		}		
	}

	/**
	* configura o metabox equipe
	*
	*/
	public function metabox_equipe(){
		add_meta_box( 'meta-equipe', 'Op&ccedil;&otilde;es', array($this, 'metabox_equipe_exibe'), 'equipe');
	}
	
	/**
	* Exibe o conteúdo do metabox equipe
	*
	*/
	public function metabox_equipe_exibe(){
		include get_template_directory() . '/admin/metabox-equipe.php';
	}
	
	/**
	* Grava os dados do metabox equipe
	*
	*/
	public function metabox_equipe_save(){
		if(isset($_POST['post_ID'])){
			if(isset($_POST['meta-link-facebook'])){ 
				update_post_meta($_POST['post_ID'], '_link-facebook', $_POST['meta-link-facebook']);
			}
			if(isset($_POST['meta-link-twitter'])){
				update_post_meta($_POST['post_ID'], '_link-twitter', $_POST['meta-link-twitter']);
			}
		}
	}
	
	/**
	*
	*
	*/
	public function theme_options(){
		add_theme_page('Opções', 'Opções', 'install_themes', 'opcoes', array($this, 'theme_options_exibe'));
	}
	
	/**
	*
	*
	*/
	public function theme_options_exibe(){
		include get_template_directory() . '/admin/theme-options.php';
	}
	
	/**
	*
	*
	*/
	public function theme_options_save(){
		if(isset($_POST['endereco'])){
			update_option('_mobilize_endereco', $_POST['endereco']);
		}
		
		if(isset($_POST['telefone'])){
			update_option('_mobilize_telefone', $_POST['telefone']);
		}
		
		if(isset($_POST['apresentacao'])){
			update_option('_mobilize_apresentacao', $_POST['apresentacao']);
		}
		
		return true;
	}

}