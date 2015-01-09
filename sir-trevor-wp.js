jQuery(document).ready(function(){

	// Remove Wordpress Drag to Upload
	setTimeout(function(){jQuery('.uploader-editor').remove();},1000);

	// Add New Editor
	jQuery('.acf_postbox .field_type-wysiwyg textarea, #content.wp-editor-area').each( function() {
		new SirTrevor.Editor({
			el: jQuery(this),
			blockTypes: ["Heading", "Text", "Image", "List", "Video", "Code"]
		});
	});

	// Add Button to Use Normal Editor (Only for New Posts)
	if(window.location.href.indexOf('?') == -1){
		jQuery('#edit-slug-box').append('<a href="?stwp_off" class="right button button-small">Use Code Editor</a>');
	}


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
		jQuery.get(ajaxurl,{action: 'stwp_nonce'},function(nonce,status,xhr){
			data.append('_wpnonce', nonce);

			var callbackSuccess = function(data){

				// Get Last Uploaded Image ID
				jQuery.get('/wp-admin/upload.php?mode=list',function(data,status,xhr){
					var imgid = jQuery(data).find('#the-list').children(":first").attr('id');
					imgid = imgid.substr(imgid.indexOf('-')+1,10);

					// Get Image URL
					jQuery.get(ajaxurl,{action:'stwp_imgurl',id: imgid}, function(url, status, xhr){

						var data = {file: {url: url.disp, full: url.full}};

						SirTrevor.log('Upload callback called');

						if (!_.isUndefined(success) && _.isFunction(success)) {
							_.bind(success, block)(data);
						}

					}, 'json');

				});
			};

			var callbackError = function(jqXHR, status, errorThrown){
			  SirTrevor.log('Upload callback error called');

			  if (!_.isUndefined(error) && _.isFunction(error)) {
			    _.bind(error, block)(status);
			  }
			};

			var xhr = jQuery.ajax({
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

	// Disable Save Post Button as well as submit button.
	SirTrevor.Submittable.intialize = function(){
      this.$submitBtn = this.$form.find("input[type='submit'], input#save-post");

      var btnTitles = [];

      _.each(this.$submitBtn, function(btn){
        btnTitles.push($(btn).attr('value'));
      });

      this.submitBtnTitles = btnTitles;
      this.canSubmit = true;
      this.globalUploadCount = 0;
      this._bindEvents();
    };

});