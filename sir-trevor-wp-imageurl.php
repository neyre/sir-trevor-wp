<?php
// Load Wordpress Without Theme
require('./../../../wp-load.php');

// Check Referer & Permissions
if(strpos($_SERVER['HTTP_REFERER'], get_site_url()) !== 0 || !current_user_can('edit_posts')){
	status_header(404);
	nocache_headers();
	include( get_404_template() );
	exit;
}

// Return Image URL
$imagefull = wp_get_attachment_image_src($_GET['id'], 'full');
$imagedisp = wp_get_attachment_image_src($_GET['id'], 'large');
echo json_encode(array('full'=>$imagefull[0], 'disp'=>$imagedisp[0]));