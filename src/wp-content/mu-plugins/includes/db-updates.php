<?php

global $current_blog;

if ( is_multisite() &&  !get_blog_option($current_blog->blog_id, 'db-update-1')) {
    update_blog_option($current_blog->blog_id, 'db-update-1', true);
    $d = 'a:15:{s:11:"wpa_version";s:3:"3.1";s:17:"wpa_pref_link_mp3";i:1;s:13:"wpa_tag_audio";i:0;s:19:"wpa_track_permalink";i:0;s:19:"wpa_style_text_font";s:10:"Sans-serif";s:19:"wpa_style_text_size";s:4:"18px";s:21:"wpa_style_text_weight";s:6:"normal";s:29:"wpa_style_text_letter_spacing";s:6:"normal";s:20:"wpa_style_text_color";s:7:"inherit";s:20:"wpa_style_link_color";s:4:"#24f";s:26:"wpa_style_link_hover_color";s:4:"#02f";s:21:"wpa_style_bar_base_bg";s:4:"#eee";s:21:"wpa_style_bar_load_bg";s:4:"#ccc";s:25:"wpa_style_bar_position_bg";s:4:"#46f";s:19:"wpa_style_sub_color";s:4:"#aaa";}';
    update_blog_option($current_blog->blog_id, 'wpaudio_options', $d);
}

global $wpdb;

if( is_multisite() && !get_blog_option(1, 'db-create-1'))
{
	$table_name = "plans";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		$wpdb->query('CREATE TABLE IF NOT EXISTS `elections` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`slug` varchar(32) NOT NULL,
			primary key (id))'
		);
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS `plans` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`election_id` int(11) NOT NULL,
			`item_order` int(11) DEFAULT NULL,
			`name` varchar(255) NOT NULL,
			`price` int(11) NOT NULL,
			primary key (id))'
		);
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS `capabilities` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`plan_id` int(11) NOT NULL,
			`name` varchar(255) NOT NULL,
			`slug` varchar(32) NOT NULL,
			`value` int(4) NOT NULL,
			primary key (id))'
		);
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS `campaigns` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`plan_id` int(11) NOT NULL,
			`blog_id` int(11) NOT NULL,
			`election_id` int(11) NOT NULL,
			`domain` varchar(255) NOT NULL,
			`own_domain` varchar(255) NOT NULL,
			`candidate_number` int(11) NULL,
			`status` bool NOT NULL,
			`creation_date` datetime NOT NULL,
			`location` varchar(255) NOT NULL,
			`observations` varchar(255) NULL,
			primary key (id))'
		);
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS `states` (
			`id` int(11) NOT NULL,
			`name` varchar(32) NOT NULL,
			`uf` char(2) NOT NULL,
			PRIMARY KEY (`id`))'
		);
		
		$wpdb->query('CREATE TABLE IF NOT EXISTS `cities` (
			`id` int(11) NOT NULL,
			`state_id` int(11) NOT NULL,
			`name` varchar(128) NOT NULL,
			PRIMARY KEY (`id`))'
		);
		
		$wpdb->query('CREATE TABLE `transaction_log` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `date` datetime DEFAULT NULL,
		    `valor` varchar(255) DEFAULT NULL,
		    `user_id` int(11) DEFAULT NULL,
		    `campaign_id` int(11) DEFAULT NULL,
		    `id_transacao` int(11) DEFAULT NULL,
		    `numero_pedido` int(11) DEFAULT NULL,
		    `response` TEXT DEFAULT NULL,
		    `aprovada` tinyint(1) DEFAULT 0,
		    PRIMARY KEY (`id`))'
		);
	}
	update_blog_option(1, 'db-create-1', true);
}

if( is_multisite() && !get_blog_option(1, 'db-sample-1') )
{
	$table_name = "elections";
	if($wpdb->get_var(" select count(*) from $table_name ") == 0 )
	{
		$wpdb->query("INSERT INTO `elections` VALUES (1, '".date('Y')."')");
	}
	$table_name = "plans";
	if($wpdb->get_var(" select count(*) from $table_name ") == 0 )
	{
		$wpdb->query("INSERT INTO `plans` VALUES (1, 1, 1, 'Full', 100000)");
	}
	$table_name = "capabilities";
	if($wpdb->get_var(" select count(*) from $table_name ") == 0 )
	{
		include 'db-capabilities.php';
		foreach ($capabilities as $capability)
		{
			$wpdb->query($capability);
		}
	}
	
	update_blog_option(1, 'db-sample-1', true);
}

if( is_multisite() && !get_blog_option(1, 'db-location-1')  )
{
	$table_name = "cities";
	if($wpdb->get_var(" select count(*) from $table_name ") == 0 )
	{
		include 'db-cities.php';
		foreach ($cities as $city)
		{
			$wpdb->query($city);
		}
	}
	$table_name = "states";
	if($wpdb->get_var(" select count(*) from $table_name ") == 0 )
	{
		include 'db-states.php';
		foreach ($states as $state)
		{
			$wpdb->query($state);
		}
	}
	update_blog_option(1, 'db-location-1', true);
}
