<?php
/**
 * @package Sir-Trevor-WP
 * @version 1.0.2
 */
/*
Plugin Name: Sir Trevor WP
Author: Nick Eyre
Version: 1.0.2
Author URI: http://nickeyre.com
Description: An intuitive, block-based content editor for Wordpress.
*/


/*

ADMIN PANEL

*/


// Start Sir-Trevor Editor if New Post or if Editing an Existing JSON Post
add_action( 'admin_enqueue_scripts', 'stwp_check_admin_page' );
function stwp_check_admin_page( $hook_suffix ) {
	global $pagenow;
	if($pagenow == 'post-new.php' && !isset($_GET['stwp_off']))
		stwp_activate();
	else if($pagenow == 'post.php' && $_GET['action'] == 'edit' && !isset($_GET['stwp_off'])){
		$stwp_post = get_post($_GET['post']);
		if(json_decode($stwp_post->post_content))
			stwp_activate();
	}
}

// Start Sir-Trevor Editor (Add Scripts & Register Hooks)
function stwp_activate() {
	// Load JS Scripts & Styles
	wp_enqueue_script('eventable', plugins_url('lib/eventable.js', __FILE__), array('jquery'));
	wp_enqueue_script('sir-trevor', plugins_url('lib/sir-trevor-min.js', __FILE__), array('jquery','underscore','eventable'));

	// Load Custom Blocks
	$files = scandir(plugin_dir_path(__FILE__).'custom-blocks/');
	foreach($files as $id=>$file)
		if(strlen($file) > 2)
			wp_enqueue_script('sir-trevor-'.$id, plugins_url('custom-blocks/'.$file, __FILE__), array('sir-trevor'));

	// Load Remaining Scripts
	wp_enqueue_script('sir-trevor-wp', plugins_url('sir-trevor-wp.js', __FILE__), array('jquery','sir-trevor'));
	wp_enqueue_style('sir-trevor', plugins_url('lib/sir-trevor.css', __FILE__));
	wp_enqueue_style('sir-trevor-icons', plugins_url('lib/sir-trevor-icons.css', __FILE__));
	wp_enqueue_style('sir-trevor-wp', plugins_url('sir-trevor-wp.css', __FILE__));
}

/*

AJAX

*/

// Get Nonce for Image Upload
add_action('wp_ajax_stwp_nonce', 'wp_ajax_stwp_nonce');
function wp_ajax_stwp_nonce(){
	if(strpos($_SERVER['HTTP_REFERER'], get_site_url()) == 0 && current_user_can('edit_posts'))
		echo wp_create_nonce('media-form');
	die();
}

// Get URLs of Uploaded Images
add_action('wp_ajax_stwp_imgurl', 'wp_ajax_stwp_imgurl');
function wp_ajax_stwp_imgurl(){
	if(strpos($_SERVER['HTTP_REFERER'], get_site_url()) == 0 && current_user_can('edit_posts')){
		$imagefull = wp_get_attachment_image_src($_GET['id'], 'full');
		$imagedisp = wp_get_attachment_image_src($_GET['id'], 'large');
		echo json_encode(array('full'=>$imagefull[0], 'disp'=>$imagedisp[0]));
	}
	die();
}


/*

FRONT-END RENDERING

*/

// Process
add_filter('the_content', 'stwp_modify_content');
function stwp_modify_content($content){
	require_once 'lib_michelf_markdown/Markdown.inc.php';

	// Check if Sir Trevor Post
	$json = get_post();
	$json = json_decode($json->post_content, true);

	// Process Sir Trevor Posts
	if($json){
		$output = '';
		foreach($json['data'] as $block){
			$template = 'block-templates/'.$block['type'].'.php';
			ob_start();
			$block = $block['data'];
			include $template;
			$output .= ob_get_clean();
		}
		return $output;
	}

	// Pass Normal Posts Through
	else
		return $content;
}

/**
 * DISABLE TINYMCE
 */
function disable_visual_editor($userID) {
	global $wpdb;
	$wpdb->query("UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = 'false' WHERE `meta_key` = 'rich_editing'");
}
function enable_visual_editor($userID) {
	global $wpdb;
	$wpdb->query("UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = 'true' WHERE `meta_key` = 'rich_editing'");
}
add_action('profile_update','disable_visual_editor');
add_action('user_register','disable_visual_editor');
register_activation_hook( __FILE__,'disable_visual_editor');
register_deactivation_hook(__FILE__, 'enable_visual_editor');