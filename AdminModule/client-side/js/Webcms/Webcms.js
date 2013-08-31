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
		
		$(function(){
			$.nette.init(function (ajaxHandler) {
				$('a.ajax:not(.no-ajax)').live('click', ajaxHandler);
			});
			
			//ajax loader animation
			$(document).ajaxStart( function() {
				   $('#loader').addClass("active"); 
			} ).ajaxStop ( function(){
					$('#loader').removeClass("active"); 
			});
			
			self.__registerListeners();
		});
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
		
		$("#snippet--mainMenu a").click(function(){
			$("#snippet--mainMenu a").parent().removeClass('active');
			$(this).parent().addClass('active');
		});
	}
};

webcms = new Webcms();