jQuery(document).ready(function(){

	// Replace WYSIWYG Editor with SirTrevor
	jQuery('.wp-switch-editor.switch-html').click();
	jQuery('#ed_toolbar').hide();
	jQuery('#wp-content-editor-tools').hide();
	jQuery('td#wp-word-count').hide();
	jQuery('#postdivrich').hide();
	jQuery('#postdivrich').before('<iframe id=sir-trevor-wp src=/wp-content/plugins/sir-trevor-wp/editor.php width=100% height=500px>');

	// Load SirTrevor with Post Contents
	jQuery('#sir-trevor-wp').load(function(){
		jQuery('#sir-trevor-wp').get(0).contentWindow.set(jQuery('textarea.wp-editor-area').val());
	});

	// Add Button to Use Normal Editor (Only for New Posts)
	if(window.location.href.indexOf('?') == -1){
		jQuery('#edit-slug-box').append('<a href="?stwp_off" class="right button button-small">Use Code Editor</a>');
	}

	// On Form Submit, Grab Sir Trevor Value
	jQuery('form#post').submit(function(event){
		var json = jQuery('#sir-trevor-wp').get(0).contentWindow.get();
		console.log(json);
		console.log(json == false);

		// If Uploads Still in Progress
		if(json == false){
			jQuery('#publishing-action .spinner').hide();
			jQuery('#publishing-action input').removeClass('button-primary-disabled');
			alert("Uploads in Progress!\n\nWait for uploads to complete and then try again.");
			return false;
		}
		
		// Fill TextEditor with JSON
		jQuery('textarea.wp-editor-area').val(json);
	});


	// Update Height of iframe
	var height = 0;
	var newheight;
	setInterval(function(){
		newheight = jQuery('#sir-trevor-wp').get(0).contentWindow.height();
		if(height < newheight){
			height = newheight;
			jQuery('#sir-trevor-wp').height(height+30);
		}
	}, 40);

});