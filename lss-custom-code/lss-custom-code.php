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

	// Settings Menu

	function lss_custom_code_menu() {
		add_options_page('LSS Custom Code Options', 'LSS Custom Code', 'manage_options', 'lss-custom-code-menu', 'lss_custom_code_options');
	
		// register settings
		add_action( 'admin_init', 'lss_custom_code_register_settings' );
	}
	add_action('admin_menu','lss_custom_code_menu');

	function lss_custom_code_options() {
	    include('admin/lss-custom-code-admin.php');
	}

	function lss_custom_code_register_settings() {
		// add the secion
		add_settings_section(
			'option_test_section',
			'Test Options Section',
			'option_test_section_callback_function',
			'lss-custom-code-settings-group'
		);			
		// Add the field(s)
		add_settings_field(
			'lss_option_test',
			'Testing an option',
			'option_test_callback_function',
			'lss-custom-code-settings-group',
			'option_test_section'
		);
		register_setting( 'lss-custom-code-settings-group', 'lss_option_test' );
	}

	// ------------------------------------------------------------------
	// Settings section callback function
	// This function is needed if we added a new section. This function 
	// will be run at the start of our section

	function option_test_section_callback_function() {
		echo '<p>Intro text for our settings section</p>';
	}

	// ------------------------------------------------------------------
	// Callback function for our example setting
	// creates a checkbox true/false option. Other types are surely possible

	function option_test_callback_function() {
		echo '<input name="lss_option_test" id="lss_option_test" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'lss_option_test' ), false ) . ' /> Option Test Checkbox';
	}






	// Output CSS on post/page load

	function lss_custom_code_add_to_header() {
		
		global $post; 	//get current post
		$customCSS = get_post_meta( $post->ID, '_lss_custom_code_css' );
		if ($customCSS[0] != "") echo '<style> ' . $customCSS[0] . ' </style>';
	}
	add_action('wp_head', 'lss_custom_code_add_to_header');

	// Output JavaScript on post/page load

	function lss_custom_code_add_to_footer() {
		
		global $post; 	//get current post
		$customJavaScript = get_post_meta( $post->ID, '_lss_custom_code_javascript' );		// esc_js() ?
		if ($customJavaScript[0] != "") {
			echo '<script> ' . $customJavaScript[0] . ' </script>';
		}
	}
	add_action('wp_footer', 'lss_custom_code_add_to_footer');


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
		echo '<textarea id="lss_custom_code_' . $typeOfCode . '" name="lss_custom_code_' . $typeOfCode . '" value="' . esc_attr( $value ) . '" rows=5/>' . $value . '</textarea>';
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

	// Style the boxes

	function my_custom_fonts() {
		echo '	<style>
					#lss_custom_code_css,
					#lss_custom_code_javascript {
						width: 95%;
						background-color: rgb(49, 42, 38);
					}
					#lss_custom_code_css {
						color: rgb(255, 205, 0);
					}
					#lss_custom_code_javascript {
						color: rgb(0, 255, 185);
					} 
				</style>';
	}
	add_action('admin_head', 'my_custom_fonts');