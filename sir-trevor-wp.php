<?php
/**
 * @package Sir-Trevor-WP
 * @version 0.1
 */
/*
Plugin Name: Sir Trevor WP
Author: Nick Eyre
Version: 0.1
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
	else if($pagenow == 'post.php' && $_GET['action'] == 'edit'){
		$stwp_post = get_post($_GET['post']);
		if(json_decode($stwp_post->post_content))
			stwp_activate();
	}
}

// Start Sir-Trevor Editor (Add Scripts & Register Hooks)
function stwp_activate() {
	// Load JS Scripts & Styles
	wp_enqueue_script('eventable', plugins_url('lib/eventable.js', __FILE__), array('jquery'));
	wp_enqueue_script('sir-trevor', plugins_url('lib/sir-trevor.js', __FILE__), array('jquery','underscore','eventable'));

	// Load Custom Blocks
	$files = scandir('../wp-content/plugins/sir-trevor-wp/custom-blocks/');
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
		foreach($json['data'] as $item){
			switch($item['type']){
				case 'heading':
					$output .= renderHeading($item['data']);
					break;
				case 'text':
					$output .= renderText($item['data']);
					break;
				case 'list':
					$output .= renderText($item['data']);
					break;
				case 'image':
					$output .= renderImage($item['data']);
					break;
				case 'video':
					$output .= renderVideo($item['data']);
					break;
				case 'code':
					$output .= renderCode($item['data']);
					break;
			}
		}
		return $output;
	}

	// Pass Normal Posts Through
	else
		return $content;
}


// Render Block Types
function renderHeading($data){
	return '<h4>'.$data['text'].'</h4>';
}
function renderText($data){
	return Michelf\Markdown::defaultTransform($data['text']);
}
function renderImage($data){
	// If Caption
	if($data['text'])
		return '<div class="wp-caption aligncenter"><a href="'.$data['file']['full'].'" target=_blank><img src="'.$data['file']['url'].'" /></a><p class=wp-caption-text>'.$data['text'].'</p></div>';

	// If No Caption
	return '<a href="'.$data['file']['full'].'" target=_blank><img src="'.$data['file']['url'].'" /></a>';
}
function renderVideo($data){
	switch($data['source']){
		case 'youtube':
			return '<p><iframe src="//www.youtube-nocookie.com/embed/'.$data['remote_id'].'" frameborder="0" allowfullscreen=""></iframe></p>';
		case 'vimeo':
			return '<p><iframe src="http://player.vimeo.com/video/'.$data['remote_id'].'?title=0&byline=0" frameborder="0" allowfullscreen=""></iframe></p>';
	}
}
function renderCode($data){
	if($data['caption'])
		return '<pre>'.$data['text'].'</pre><p class=wp-caption>'.$data['caption'].'</p>';
	return '<pre>'.$data['text'].'</pre>';
}

?>