<?php

// create campanha completa custom tables
if (!get_option('db-update-1')) {
    update_option('db-update-1', 1);
    
	$wpdb->query('CREATE TABLE `elections` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`slug` varchar(32) NOT NULL,
		primary key (id))');
	
	$wpdb->query('CREATE TABLE `plans` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`election_id` int(11) NOT NULL,
		`name` varchar(255) NOT NULL,
		`price` int(11) NOT NULL,
		primary key (id))');
		
	$wpdb->query('CREATE TABLE `capabilities` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`plan_id` int(11) NOT NULL,
		`name` varchar(255) NOT NULL,
		`slug` varchar(32) NOT NULL,
		`access` int(4) NOT NULL,
		primary key (id))');
	
	$wpdb->query('CREATE TABLE `campaigns` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL,
		`plan_id` int(11) NOT NULL,
		`blog_id` int(11) NOT NULL,
		`election_id` int(11) NOT NULL,
		`domain` varchar(255) NOT NULL,
		`status` bool NOT NULL,
		`creation_date` datetime NOT NULL,
		`location` varchar(255) NOT NULL,
		primary key (id))');
    
	$wpdb->query('CREATE TABLE `transaction_log` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`campaign_id` int(11) NOT NULL,
		`user_id` int(11) NOT NULL,
		`creation_date` datetime NOT NULL,
		`value` int(11) NOT NULL,
		primary key (id))');
}
