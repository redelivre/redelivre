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
		
		//metabox subtitulo
		add_action('add_meta_boxes', array($this, 'metabox_subtitulo'));
		add_action('save_post', array($this, 'metabox_subtitulo_save'));
	}
	
	/**
	*
	*/
	public function theme_customizer($wp_customize){
		require_once get_template_directory() . '/lib/control.class.php';

		// Cores
		$wp_customize->add_section('cores', array('title' => __('Cores', '_mobilize'), 'priority' => 30));
		$wp_customize->add_setting('cor-1', array('default' => '#ee720d', 'transport' => 'refresh'));
		$wp_customize->add_setting('cor-2', array('default' => '#f7ad40', 'transport' => 'refresh'));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cor-1', array(
			'label'        => __( 'Cor 1', '_mobilize' ),
			'section'    => 'cores',
			'settings'   => 'cor-1',
		)));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cor-2', array(
			'label'        => __( 'Cor 2', '_mobilize' ),
			'section'    => 'cores',
			'settings'   => 'cor-2',
		)));
		
		// 
		// Logo		
		$wp_customize->add_section('logo', array('title' => __('Logo', '_mobilize'), 'priority' => 30 ));
		$wp_customize->add_setting('logo', array('default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo', array(
			'label'        => _x( 'Logo', 'theme-customizer', '_mobilize' ),
			'section'    => 'logo',
			'settings'   => 'logo',
		)));
		
		//Banner home
		$wp_customize->add_section('banner-home', array('title' => __('Banner', '_mobilize'), 'priority' => 30 ));
		$wp_customize->add_setting('banner-home', array('default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'banner-home', array(
			'label'        => _x( 'Banner', 'theme-customizer', '_mobilize' ),
			'section'    => 'banner-home',
			'settings'   => 'banner-home',
		)));
		
		// Background		
		$wp_customize->add_section('background', array(	'title' => __('Fundo', '_mobilize'),	'priority' => 30 ));
		$wp_customize->add_setting('background-imagem', array( 'default' => '', 'transport' => 'refresh'));
		$wp_customize->add_setting('background-cor', array( 'default' => '', 'transport' => 'refresh'));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'background-imagem', array(
			'label'        => _x( 'Imagem', 'theme-customize', '_mobilize' ),
			'section'    => 'background',
			'settings'   => 'background-imagem',
		)));
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background-cor', array(
			'label'        => _x( 'Cor', 'theme-customize', '_mobilize' ),
			'section'    => 'background',
			'settings'   => 'background-cor',
		)));

		// Presentation
		$wp_customize->add_section('presentation',
				array('title' => __('Apresentação', '_mobilize'), 'priority' => 30));

		$wp_customize->add_setting('show-presentation', array('default' => true));
		$wp_customize->add_control('show-presentation',
				array('label' => __('Mostrar apresentação', '_mobilize'),
					'section' => 'presentation',
					'type' => 'checkbox'));

		$wp_customize->add_setting('presentation',
				array('default' => __('Customize a apresentação', '_mobilize')));
		$wp_customize->add_control(new MobilizeControl($wp_customize,
					'presentation',
					array('label' => __('Apresentação', '_mobilize'),
						'section' => 'presentation',
						'type' => 'textarea')));

		// Contact
		$wp_customize->add_section('contact',
				array('title' => __('Página de Contato', '_mobilize'), 'priority' => 30));

		$wp_customize->add_setting('show-phone-number', array('default' => true));
		$wp_customize->add_control('show-phone-number',
				array('label' => __('Mostrar telefone', '_mobilize'),
					'section' => 'contact',
					'type' => 'checkbox'));

		$wp_customize->add_setting('show-email', array('default' => true));
		$wp_customize->add_control('show-email',
				array('label' => __('Mostrar e-mail', '_mobilize'),
					'section' => 'contact',
					'type' => 'checkbox'));

		$wp_customize->add_setting('show-address', array('default' => true));
		$wp_customize->add_control('show-address',
				array('label' => __('Mostrar endereço', '_mobilize'),
					'section' => 'contact',
					'type' => 'checkbox'));

		$wp_customize->add_setting('email', array('default' => 'name@example.com'));
		$wp_customize->add_control('email',
				array('label' => __('E-mail', '_mobilize'),
					'section' => 'contact',
					'type' => 'text'));

		$wp_customize->add_setting('phone-number', array('default' => '(00) 0000-0000'));
		$wp_customize->add_control('phone-number',
				array('label' => __('Telefone', '_mobilize'),
					'section' => 'contact',
					'type' => 'text'));

		$wp_customize->add_setting('address',
				array('default' => "Endereço: Rua Coronel Sílvio da Silva\n"
					. "Nº: 23\n"
					. "Complemento: ap. 107\n"
					. "Bairro: Marajó\n"
					. "Cidade: Belo Horizonte\n"
					. "Estado: Minas Gerais\n"
					. "CEP: 80808-080"));
		$wp_customize->add_control(new MobilizeControl($wp_customize,
					'address',
					array('label' => __('Endereço', '_mobilize'),
						'section' => 'contact',
						'type' => 'textarea')));
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

}
