<?php
/*
Plugin Name: poMMo for WordPress
Plugin URI: http://wordpress.org/extend/plugins/pommo
Description: A Top Quark Plugin that integrates a fork of poMMo into Wordpress. Requires the 'topquark' Plugin to be installed and activated.
Version: 1.0.8
Author: Top Quark
Author URI: http://topquark.com
*/

add_action('init','pommo_init');
function pommo_init(){
	if (!class_exists('Bootstrap')){
		add_action('admin_notices','pommo_admin_notices');
	}
	else{
		$Bootstrap = Bootstrap::getBootstrap();
		$Bootstrap->registerPackage('poMMo','../../../pommo/');
		$Bootstrap->usePackage('poMMo');
	}
}

function pommo_admin_notices(){
	$notes = array();
	$errors = array();
	if (!class_exists('Bootstrap')){
		$errors[] = sprintf(__('The plugin "poMMo" requires the "Top Quark Architecture" plugin to be installed and activated.  This plugin can be downloaded from %sWordPress.org%s'),'<a href="http://wordpress.org/extend/plugins/topquark/" target="_blank">','</a>');
	}

    foreach ($errors as $error) {
        echo sprintf('<div class="error"><p>%s</p></div>', $error);
    }
    
    foreach ($notes as $note) {
        echo sprintf('<div class="updated fade"><p>%s</p></div>', $note);
    }
}

register_activation_hook(__FILE__,'poMMo_activate');
function poMMo_activate(){
	define('_IS_VALID',true);
	require_once(ABSPATH . 'wp-content/plugins/pommo/bootstrap.php');	
	$poMMo = & fireup('install'); 
	$poMMo->runInstall();
}



?>