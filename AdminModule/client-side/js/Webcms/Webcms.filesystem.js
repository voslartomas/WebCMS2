/* WebcmsFilesystem */
function WebcmsFilesystem(context) {

	this.context = context;
	this.path = new String();

	this.init();
};

WebcmsFilesystem.prototype = {
	selffs: null,
	init: function() {
		// defaults values
		selffs = this;

		this.__registerListeners();
	},
	setPath: function(path) {
		this.path = path;
	},
	getPath: function(path) {
		return this.path;
	},
	__registerListeners: function() {

		$(document).on('click', '.filesDialog', function(e) {
			e.preventDefault();

			$(document).off('click', '.jq_file');

			var options = {
				container: $(this).data('container'),
				containerId: $(this).data('container-id')
			};

			$('.jq_filesAdd').on('click', function(e) {
				e.preventDefault();

				// 
				$('.jq_selected:checked').each(function() {

					var single = $(this).attr('type') == 'radio' ? true : false;

					var data = $(this).data();
					var id = parseInt($('input:radio:last').val()) + 1;
					if (!single) {
						$(options.container).append('<div class="col-md-3 jq_fileBox"><div class="img-thumbnail"><img src="' + data.thumbnail + '" /><input type="hidden" name="files[]" value="' + data.path + '" /><input class="form-control" type="text" name="fileNames[]" /><input class="form-control" type="radio" name="fileDefault[]" value="' + id + '" /><span class="btn btn-default jq_fileDelete">&times</span></div></div>');
					} else {
						$(options.container).html('<div class="col-md-3 jq_fileBox"><div class="img-thumbnail"><img src="' + data.thumbnail + '" /><input id="filePath" type="hidden" name="files[]" value="' + data.path + '" /><input class="form-control" type="text" name="fileNames[]" /><input class="form-control" type="radio" name="fileDefault[]" value="' + id + '" /><span class="btn btn-default jq_fileDelete">&times</span></div></div>');
					}

					$(this).attr('checked', false);
				});
			});
		});

		$(".jq_fileDelete").on('click', function(e) {
			e.preventDefault();

			$(this).closest('.jq_fileBox').remove();
		});
	}
};