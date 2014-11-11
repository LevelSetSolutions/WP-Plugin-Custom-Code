<?php
/*
	Plugin Name: Levelset Custom Code
	Plugin URI: 
	Description: Provides separate text areas for adding single-use CSS and JavaScript.  No more JavaScript inside of the WYSIWYG editor!
	Version: 0.1
	Author: LevelSet Solutions
	Author URI: http://www.levelsetsolutions.com/
	License: 
*/

//SETUP

	function lss_custom_code_install() {
		    //Do some installation work
			//database table creation
	}
	register_activation_hook(__FILE__,'lss_custom_code_install');

//SCRIPTS

	function lss_custom_code_scripts(){
		wp_register_script('lss_custom_code_script', plugin_dir_url( __FILE__ ).'js/lss-custom-code.js');
		wp_enqueue_script('lss_custom_code_script');
	}
	add_action('wp_enqueue_scripts','lss_custom_code_scripts');

//HOOKS

	add_action('init','lss_custom_code_init');

	/* FUNCTIONS */
	
	function lss_custom_code_init(){
		run_sub_process();
	}
	function run_sub_process(){
	}
