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

// SETUP

	function lss_custom_code_install() {
		    //Do some installation work
			//database table creation
	}
	register_activation_hook(__FILE__,'lss_custom_code_install');

// SCRIPTS

	function lss_custom_code_scripts(){
		wp_register_script('lss_custom_code_script', plugin_dir_url( __FILE__ ).'js/lss-custom-code.js');
		wp_enqueue_script('lss_custom_code_script');
	}
	add_action('wp_enqueue_scripts','lss_custom_code_scripts');


// HOOKS
	
	function lss_custom_code_init(){	
	}
	add_action('init','lss_custom_code_init');

	function lss_custom_code_add_to_post() {
		
		global $post; 	//get current post
		$customCSS = get_post_meta( $post->ID, '_lss_custom_code_css' );

			echo '<style> ' . $customCSS[0] . ' </style>';
		//wp_add_inline_style( 'inline-custom-style', $customCSS[0] );
		//wp_enqueue_style( 'prefix-style', plugins_url('style.css', __FILE__) );
	}
	add_action('wp_head', 'lss_custom_code_add_to_post');


// PAGE & POST EDIT SCREEN

	// Add JavaScript and CSS boxes

	function lss_custom_code_add_meta_boxes() {

		$screens = array( 'post', 'page' );		// what about custom post types?

		foreach ( $screens as $screen ) {

			// JavaScript Box

			add_meta_box(
				'lss_custom_code_javascript_box',
				__( 'Custom Javascript', 'lss_custom_code_textdomain' ),
				'lss_custom_code_meta_box_callback',
				$screen, 'advanced', 'low', array( 'code' => 'javascript')
			);
			
			// CSS box

			add_meta_box(
				'lss_custom_code_css_box',
				__( 'Custom CSS', 'lss_custom_code_textdomain' ),
				'lss_custom_code_meta_box_callback',
				$screen, 'advanced', 'low', array( 'code' => 'css')
			);		
		}
	}
	add_action( 'add_meta_boxes', 'lss_custom_code_add_meta_boxes' );

	/**
	 * Print the box content
	 * @param WP_Post $post - the object for the current post/page
	 */

	function lss_custom_code_meta_box_callback( $post, $metabox ) {

		$typeOfCode = $metabox['args']['code'];		//javascript or css

		// add an nonce field so we can check for it later.
		wp_nonce_field( 'lss_custom_code_meta_box', 'lss_custom_code_meta_box_nonce' );

		// retrieve existing value from database	
		$value = get_post_meta( $post->ID, '_lss_custom_code_' . $typeOfCode, true );

		// echo '<label for="lss_custom_code_new_field">';
		// _e( 'Description for this field', 'lss_custom_code_textdomain' );
		// echo '</label> ';
		echo '<textarea id="lss_custom_code_' . $typeOfCode . '" name="lss_custom_code_' . $typeOfCode . '" value="' . esc_attr( $value ) . '" rows=5 style="width: 95%;"/>' . $value . '</textarea>';
	}

	/**
	 * Save the content
	 * @param int $post_id - the ID of the post being saved
	 */

	function lss_custom_code_save_meta_box_data( $post_id ) {

		// verify this came from our screen and with proper authorization, because the save_post action can be triggered at other times

			// check if our nonce is set
			if ( ! isset( $_POST['lss_custom_code_meta_box_nonce'] ) ) {
				return;
			}
			// verify that the nonce is valid
			if ( ! wp_verify_nonce( $_POST['lss_custom_code_meta_box_nonce'], 'lss_custom_code_meta_box' ) ) {
				return;
			}
			// if autosave, our form has not been submitted, so don't do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			// Check the user's permissions
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) { 	// POST
					return;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) { 	// PAGE
					return;
				}
			}

		// it is now safe for us to save the data

		if ( ! isset( $_POST['lss_custom_code_javascript'] ) && ! isset( $_POST['lss_custom_code_css'] ) ) {
			return;
		}
		else {
			if ( isset( $_POST['lss_custom_code_javascript'] )) {
				// sanitize user input
				$myJavaScript = sanitize_text_field( $_POST['lss_custom_code_javascript'] );
				// update meta field in database
				update_post_meta( $post_id, '_lss_custom_code_javascript', $myJavaScript );
			}
			if ( isset( $_POST['lss_custom_code_javascript'] )) {
				// sanitize user input
				$myCSS = sanitize_text_field( $_POST['lss_custom_code_css'] );
				// update meta field in database
				update_post_meta( $post_id, '_lss_custom_code_css', $myCSS );
			}
		}
	}
	add_action( 'save_post', 'lss_custom_code_save_meta_box_data' );