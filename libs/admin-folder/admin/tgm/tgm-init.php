<?php

/**
 * TGM Init Class
 */
include_once ('class-tgm-plugin-activation.php');

function redux_builder_alancpazetto_register_required_plugins() {

	$plugins = array(
		array(
			'name' 	   => 'Redux Framework',
			'slug' 	   => 'redux-framework',
			'required' => true,
		),
	);

	$config = array(
		'domain'       		=> 'redux-framework',         	// Text domain - likely want to be the same as your theme.
		'default_path' 		=> '',                         	// Default absolute path to pre-packaged plugins
		//'parent_menu_slug' 	=> 'plugins.php', 				// Default parent menu slug
		//'parent_url_slug' 	=> 'plugins.php', 				// Default parent URL slug
                'parent_slug'           => 'plugins.php',
                'capability'            => 'manage_options',
		'menu'         		=> 'install-required-plugins', 	// Menu slug
		'has_notices'      	=> true,                       	// Show admin notices or not
		'is_automatic'    	=> true,					   	// Automatically activate plugins after installation or not
	);

	tgmpa( $plugins, $config );

}
add_action( 'tgmpa_register', 'redux_builder_alancpazetto_register_required_plugins' );