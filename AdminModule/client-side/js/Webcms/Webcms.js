/**
 * 
 * @author
 * @returns 
 */
function Webcms(){
	this.version = '0.1-beta';
	this.defaultConfirm = 'Opravdu si přejete provést akci?'
	
	this.init();
}

Webcms.prototype = {
	self : null,
	
	init : function (){
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
		
		$.nette.init(function (ajaxHandler) {
			$('a.ajax:not(.no-ajax)').live('click', ajaxHandler);
			$('form.ajax :submit').live('click', ajaxHandler);
		});

		//ajax loader animation
		$(document).ajaxStart( function() {
			   $('#loader').addClass("active"); 
		} ).ajaxStop ( function(){
				self.initTextEditors();
				$('#loader').removeClass("active"); 
		});

		self.initTextEditors();
		self.__registerListeners();
		
	},
	/* Global systems listeners registering. */
	__registerListeners : function(){
		// register bootbox confirm window for all danger buttons
		$(".btn-danger").live("click", function(e){
			e.preventDefault();
			var anchor = this;
			var title = $(this).data("confirm");
			
			if(typeof title === "undefined")
				title = self.defaultConfirm;
			
			bootbox.confirm(title, function(confirmed){
					if(confirmed)
						window.location.href = anchor.href;
			});
		});
		
		$(".jq_head a.ajax").live('click', function(){
			$(".jq_head a.ajax").parent().removeClass('active');
			
			$(this).parent().addClass('active');
		});
		
		$("#languageChanger").live('change', function(){
			$(this).parent().submit();
		});
	},
			
	initTextEditors : function(){
		$(".editor").kendoEditor({
			tools: [
				"bold",
                "italic",
                "underline",
                "strikethrough",
                "justifyLeft",
                "justifyCenter",
                "justifyRight",
                "justifyFull",
                "insertUnorderedList",
                "insertOrderedList",
                "indent",
                "outdent",
                "createLink",
                "unlink",
                "insertImage",
                "subscript",
                "superscript",
                "createTable",
                "addRowAbove",
                "addRowBelow",
                "addColumnLeft",
                "addColumnRight",
                "deleteRow",
                "deleteColumn",
                "viewHtml"
			],
			encoded : false
		});
	}
};

$(function(){
	webcms = new Webcms();
});