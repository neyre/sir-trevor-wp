function set(data){
	$('textarea').html(data);

	new SirTrevor.Editor({
		el: jQuery('.wp-editor-area'),
		blockTypes: ["Heading", "Text", "Image", "List", "Video"]
	});

	// Set Upload URL so Photo Uploads Work
	SirTrevor.setDefaults({
	  uploadUrl: '/wp-admin/media-new.php'
	});

	// Modified File Upload Function
	// Changes:
	//     - Get a Nonce Code so Wordpress Accepts the Upload
	//     - Accept Non-JSON Response and get the Photo URL
	//     - Add html-upload form field.
	SirTrevor.fileUploader = function(block, file, success, error) {
  
		var uid  = [block.blockID, (new Date()).getTime(), 'raw'].join('-');
		var data = new FormData();

		data.append('async-upload', file);
		data.append('html-upload', 'Upload');

		block.resetMessages();

		// Get Nonce
		$.get('nonce.php',function(nonce,status,xhr){
			data.append('_wpnonce', nonce);

			var callbackSuccess = function(data){
				var imgid = jQuery(data).find('#the-list').children(":first").attr('id');
				imgid = imgid.substr(imgid.indexOf('-')+1,10);

				// Get Image URL
				$.get('image.php',{id: imgid}, function(url, status, xhr){

					var data = {file: {url: url}};

					SirTrevor.log('Upload callback called');

					if (!_.isUndefined(success) && _.isFunction(success)) {
						_.bind(success, block)(data);
					}

				});
			};

			var callbackError = function(jqXHR, status, errorThrown){
			  SirTrevor.log('Upload callback error called');

			  if (!_.isUndefined(error) && _.isFunction(error)) {
			    _.bind(error, block)(status);
			  }
			};

			var xhr = $.ajax({
			  url: SirTrevor.DEFAULTS.uploadUrl,
			  data: data,
			  cache: false,
			  contentType: false,
			  processData: false,
			  type: 'POST'
			});

			block.addQueuedItem(uid, xhr);

			xhr.done(callbackSuccess)
			   .fail(callbackError)
			   .always(_.bind(block.removeQueuedItem, block, uid));

			return xhr;
		});
	};
}


function get(data){
	// If Pending Uploads
	if($('input#submit').attr('disabled') == 'disabled'){
		return false;
	}

	// Compute & Get JSON
	SirTrevor.onBeforeSubmit()
	return SirTrevor.instances[0].$el.val();
}


function height(){
	return $('form').height();
}