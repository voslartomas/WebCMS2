/* WebCMS tour */
function WebcmsTour(context) {
	
	this.context = context;
	this.steps = [];
	this.tutorialTrigger = "#tutorial";

	this.init();
};

WebcmsTour.prototype = {
	
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
		
		// define steps
		return [
			{
				"path": "/",
				"element": "#tBrand",
				"title": selfwt.context.translator.getTranslation('Homepage'),
				"content": selfwt.context.translator.getTranslation("Homepage description"),
				"placement": "bottom"
			},
			{
				"path": "/pages",
				"element": "#tPages",
				"title": selfwt.context.translator.getTranslation("Pages"),
				"content": selfwt.context.translator.getTranslation("Pages description"),
				"placement": "bottom"
			},
			{
				"path": "/pages/sorting",
				"element": "#tSorting",
				"title": selfwt.context.translator.getTranslation("Pages sorting"),
				"content": selfwt.context.translator.getTranslation("Pages sorting description"),
				"placement": "bottom"
			},
			{
				"path": "/users",
				"element": "#tUsers",
				"title": selfwt.context.translator.getTranslation("Users"),
				"content": selfwt.context.translator.getTranslation("Users description"),
				"placement": "top"
			},
			{
				"path": "/users/roles",
				"element": "#tRoles",
				"title": selfwt.context.translator.getTranslation("Roles"),
				"content": selfwt.context.translator.getTranslation("Roles description"),
				"placement": "top"
			},
			{
				"path": "/languages",
				"element": "#tLanguages",
				"title": selfwt.context.translator.getTranslation("Languages"),
				"content": selfwt.context.translator.getTranslation("Languages description"),
				"placement": "top"
			},
			{
				"path": "/languages/translates",
				"element": "#tTranslates",
				"title": selfwt.context.translator.getTranslation("Translations"),
				"content": selfwt.context.translator.getTranslation("Translations description"),
				"placement": "top"
			},
			{
				"path": "/languages/cloning",
				"element": "#tCloning",
				"title": selfwt.context.translator.getTranslation("Cloning"),
				"content": selfwt.context.translator.getTranslation("Cloning description"),
				"placement": "top"
			},
			{
				"path": "/languages/translator",
				"element": "#tTranslator",
				"title": selfwt.context.translator.getTranslation("Translator"),
				"content": selfwt.context.translator.getTranslation("Translator description"),
				"placement": "top"
			},
			{
				"path": "/settings",
				"element": "#tSettings",
				"title": selfwt.context.translator.getTranslation("Basic settings"),
				"content": selfwt.context.translator.getTranslation("Basic settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/pictures",
				"element": "#tPictures",
				"title": selfwt.context.translator.getTranslation("Picture settings"),
				"content": selfwt.context.translator.getTranslation("Picture settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/emails",
				"element": "#tEmails",
				"title": selfwt.context.translator.getTranslation("Email settings"),
				"content": selfwt.context.translator.getTranslation("Email settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/boxes-settings",
				"element": "#tBoxes",
				"title": selfwt.context.translator.getTranslation("Boxes settings"),
				"content": selfwt.context.translator.getTranslation("Boxes settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/seo-settings",
				"element": "#tSeo",
				"title": selfwt.context.translator.getTranslation("Seo settings"),
				"content": selfwt.context.translator.getTranslation("Seo settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/api",
				"element": "#tApi",
				"title": selfwt.context.translator.getTranslation("API settings"),
				"content": selfwt.context.translator.getTranslation("API settings description"),
				"placement": "top"
			},
			{
				"path": "/settings/project",
				"element": "#tProject",
				"title": selfwt.context.translator.getTranslation("Project settings"),
				"content": selfwt.context.translator.getTranslation("Project settings description"),
				"placement": "top"
			},
			{
				"path": "/filesystem",
				"element": "#tFilesystem",
				"title": selfwt.context.translator.getTranslation("Filesystem"),
				"content": selfwt.context.translator.getTranslation("Filesystem description"),
				"placement": "bottom"
			},
			{
				"path": "/update",
				"element": "#tUpdate",
				"title": selfwt.context.translator.getTranslation("Modules"),
				"content": selfwt.context.translator.getTranslation("Modules description"),
				"placement": "top"
			},
			{
				"path": "/update/functions",
				"element": "#tFunctions",
				"title": selfwt.context.translator.getTranslation("System"),
				"content": selfwt.context.translator.getTranslation("System description"),
				"placement": "top"
			}
		];
	},
	__registerListeners: function() {
		
		var keys = {
			'Homepage': '',
			'Homepage description': '',
			'Pages': '',
			'Pages description': '',
			'Pages sorting': '',
			'Pages sorting description': '',
			'Users': '',
			'Users description': '',
			'Roles': '',
			'Roles description': '',
			'Languages': '',
			'Languages description': '',
			'Translations': '',
			'Translations description': '',
			'Cloning': '',
			'Cloning description': '',
			'Translator': '',
			'Translator description': '',
			'Basic settings': '',
			'Basic settings description': '',
			'Picture settings': '',
			'Picture settings description': '',
			'Email settings': '',
			'Email settings description': '',
			'Boxes settings': '',
			'Boxes settings description': '',
			'Seo settings': '',
			'Seo settings description': '',
			'API settings': '',
			'API settings description': '',
			'Project settings': '',
			'Project settings description': '',
			'Filesystem': '',
			'Filesystem description': '',
			'Modules': '',
			'Modules description': '',
			'System': '',
			'System description': '',
			'« Previous': '',
			'Next »': '',
			'Turn off tour': ''
		};
		
		// load translations first, then initialize the rest
		selfwt.context.translator.loadTranslations(keys, function(){
			
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
									<button class='btn btn-default' data-role='prev'>" + selfwt.context.translator.getTranslation('« Previous') + "</button>\n\
									<button class='btn btn-default' data-role='next'>" + selfwt.context.translator.getTranslation('Next »') + "</button>\n\
									<button class='btn btn-default' data-role='end'>" + selfwt.context.translator.getTranslation('Turn off tour') + "</button>\n\
								</div>\n\
							</div>",

				// Zrejme nutne predem implementovat History push state, pro zmenu adresy
				//redirect : function(path){
				//	$.nette.ajax(path);
				//}
			});
			
			selfwt.bootTour.addSteps(selfwt.getSteps());

			// Initialize the tour
			selfwt.bootTour.init();

			// Start the tour
			selfwt.bootTour.start();
		});

		$(selfwt.tutorialTrigger).on("click", function() {

			selfwt.bootTour.restart();
		});
	}
	
};