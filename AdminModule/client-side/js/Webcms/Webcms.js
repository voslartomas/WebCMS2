/**
 * 
 * @author
 * @returns 
 */
function Webcms() {
	this.version = '0.1-beta';
	this.defaultConfirm = 'Opravdu si přejete provést akci?'
	this.externals = new Array();

	this.init();
}

Webcms.prototype = {
	self: null,
	init: function() {
		self = this;

		/**
		 * Nette ajax Grido extension.
		 * @author Petr BugyĂ­k
		 * @param {jQuery} $
		 */
		"use strict";
		$.nette.ext('grido',
				{
					load: function()
					{
						this.selector = $('.grido');
						this.selector.grido();
					},
					success: function(payload)
					{
						this.selector.trigger('success.ajax.grido', payload);

						//scroll up after ajax update
						$('html, body').animate({scrollTop: 0}, 400);
					}
				}, {
			selector: null
		});

		var longRun = false;
		$(document).on('click', ".ajax", function() {
			if ($(this).hasClass('longRun')) {
				longRun = true;
			} else {
				longRun = false;
			}
		});

		$.nette.init(function(ajaxHandler) {
			$(document).on('click', 'a.ajax:not(.no-ajax)', ajaxHandler);
			$(document).on('click', 'form.ajax :submit', ajaxHandler);
		});

		// links context menu
		$(document).on('contextmenu', 'a', function(e) {
			e.preventDefault();

			$('.context-menu').remove();
			if (!$(this).hasClass('favourite')) {
				$(this).append('<div style="position: absolute;" class="nav navbar context-menu"><a class="btn btn-default ajax" href="'+basePath+'/admin?do=addToFavourite&link=' + $(this).attr('href') + '&title=' + $(this).html() + '">Add to favourite</a></div>')
			} else {
				$(this).append('<div style="position: absolute;" class="nav navbar context-menu"><a class="btn btn-default ajax" href="'+basePath+'/admin?do=removeFromFavourite&idFav=' + $(this).data('id') + '">Remove</a></div>')
			}
		});

		$(document).on('click', function() {
			$('.context-menu').remove();
		});

		//ajax loader animation
		$(document).ajaxStart(function() {
			if (longRun) {
				$('.spinner-wrapper').show();
			} else {
				$('#loader').addClass("active");
			}
		}).ajaxStop(function() {
			self.afterReload();
			
			$('.context-menu').remove();
			$('#loader').removeClass("active");
			$('.spinner-wrapper').hide();
		});		
		

		self.afterReload();
		self.__registerListeners();

	},
	afterReload: function() {

		$(".datepicker:not(.k-input)").kendoDatePicker({
			format: 'dd.MM.yyyy'
		});

		self.initTextEditors();
	},
	registerExternal: function(ext) {
		this.externals.push(ext);
	},
	onStart: function() {
		// init wysiwyg editor
		self.initTextEditors();

		// load external functions
		for (var i = 0; i < this.externals.length; i++) {
			eval(this.externals[i]);
		}
	},
	/* Global systems listeners registering. */
	__registerListeners: function() {
		// register bootbox confirm window for all danger buttons
		$(document).on("click", ".btn-danger", function(e) {
			e.preventDefault();
			var anchor = this;
			var title = $(this).data("confirm");

			if (typeof title === "undefined")
				title = self.defaultConfirm;

			bootbox.confirm(title, function(confirmed) {
				if (confirmed)
					window.location.href = anchor.href;
			});
		});

		$(document).on('click', ".jq_head a.ajax", function() {
			$(".jq_head a.ajax").parent().removeClass('active');

			$(this).parent().addClass('active');
		});

		$(document).on('change', "#languageChanger", function() {
			$(this).parent().submit();
		});
		
		$(document).on('mouseover', ".navbar-brand",function(){
			$(".sidebar .well").show('fast', 'swing');
		});
		
		$(".content").mouseover(function(){
			
			if($(".sidebar .well").css('display') === 'block'){
				setTimeout("self.hideSidebar()", 500);
			}
		});
	},
	
	hideSidebar : function(){
		$(".sidebar .well").hide('fast', 'swing');
	},
	initTextEditors: function() {

		$(".editor:not(.k-content)").ckeditor({
			filebrowserBrowseUrl: basePath + '/admin/filesystem?dialog=1&multiple=0',
			filebrowserImageBrowseUrl: basePath + '/admin/filesystem?dialog=1&filter=images&multiple=0',
			allowedContent: true
		});

	}
};

/* Filesystem */
function Filesystem() {

	this.path = new String();

	this.init();
};

Filesystem.prototype = {
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

/* WebCMS tour */
function Webcmstour() {

	this.steps = [];
	this.tutorialTrigger = "#tutorial";

	this.init();
}
;

Webcmstour.prototype = {
	
	bootTour : null,
	
	selfwt: null,
	
	init: function() {
		
		selfwt = this;

		this.__registerListeners();
	},
	setSteps: function(steps) {
		this.steps = steps;
	},
	getSteps: function() {
		return this.steps;
	},
	__registerListeners: function() {
		
		$.getJSON( basePath + "/../libs/webcms2/webcms2/AdminModule/client-side/src/tourSteps.json", function( data ) {
			$.each( data, function( key, val ) {
				$.each(val, function (key2, val2){
					var stepObj = {};
					
					stepObj.path = val2.path;
					stepObj.backdrop = false;
					stepObj.element = val2.element;
					stepObj.title = val2.title;
					stepObj.content = val2.content;
					stepObj.placement = val2.placement;
					
					selfwt.steps.push(stepObj);
				});
			});
		
			// Instance the tour
			selfwt.bootTour = new Tour({
				basePath: basePath + '/admin',
				debug: true,
				backdrop: false,
				template: "<div class='popover tour'>\n\
								<div class='arrow'></div>\n\
								<h3 class='popover-title'></h3>\n\
								<div class='popover-content'></div>\n\
								<div class='popover-navigation'> \n\
									<button class='btn btn-default' data-role='prev'>« Předchozí</button>\n\
									<button class='btn btn-default' data-role='next'>Další »</button>\n\
									<button class='btn btn-default' data-role='end'>Vypnout nápovědu</button>\n\
								</div>\n\
							</div>",


				/* Zrejme nutne predem implementovat History push state, pro zmenu adresy
				 * redirect : function(path){
					$.nette.ajax(path);
				}*/
			});

			selfwt.bootTour.addSteps(selfwt.getSteps());

			// Initialize the tour
			selfwt.bootTour.init();

			// Start the tour
			selfwt.bootTour.start();

			$(selfwt.tutorialTrigger).on("click", function() {
				
				selfwt.bootTour.restart();
			});
		
		});
	}
	
	
};

var webcmstour;

$(function() {
	webcmstour = new Webcmstour();
	webcms = new Webcms();
	filesystem = new Filesystem();
});
