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
	wp_enqueue_script('sir-trevor-wp', plugins_url('sir-trevor-wp.js', __FILE__), array('jquery'));
	wp_enqueue_style('sir-trevor-wp', plugins_url('sir-trevor-wp.css', __FILE__));
}

// Process
add_filter('the_content', 'stwp_modify_content');
function stwp_modify_content($content){
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
					$output .= renderList($item['data']);
					break;
				case 'image':
					$output .= renderImage($item['data']);
					break;
				case 'video':
					$output .= renderVideo($item['data']);
					break;
				default:
					$output .= print_r($item, true);
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
	$data['text'] = str_replace("\n\n",'</p><p>',$data['text']);
	$data['text'] = str_replace("\n",'<br>',$data['text']);
	return '<p>'.$data['text'].'</p>';
}
function renderList($data){
	$items = explode(' - ', $data['text']);
	array_shift($items);
	$return = '<ul>';
	foreach($items as $item)
		$return .= '<li>'.$item.'</li>';
	$return .= '</ul>';
	return $return;
}
function renderImage($data){
	return '<p><a href="'.$data['file']['full'].'" target=_blank><img src="'.$data['file']['url'].'" /></a></p>';
}
function renderVideo($data){
	switch($data['source']){
		case 'youtube':
			return '<p><iframe src="//www.youtube-nocookie.com/embed/'.$data['remote_id'].'" frameborder="0" allowfullscreen=""></iframe></p>';
		case 'vimeo':
			return '<p><iframe src="http://player.vimeo.com/video/'.$data['remote_id'].'?title=0&byline=0" frameborder="0" allowfullscreen=""></iframe></p>';
	}
}

?>