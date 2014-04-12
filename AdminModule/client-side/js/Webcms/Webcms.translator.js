function WebcmsTranslator(context){
	
	this.context = context;
	this.translations = {};
	
	this._init();
};

WebcmsTranslator.prototype = {
	
	selft : null,
	
	_init : function(){
		
		selft = this;
	},
	
	loadTranslations : function(keys, callback){
		$.ajax(basePath + '/admin/?do=getTranslations', { data : { keys : keys }, complete : function(data){
			
			var translations = data.responseJSON;
				
			for(var key in translations){
				selft.translations[key] = translations[key];
			}
				
			callback();
		}});
	},
	
	getTranslation : function(key){
		// go through the translations
		for(translationKey in this.translations){
			if(translationKey == key){
				return this.translations[key];
			}
		}
		
		var keys = {};
		keys[key] = '';
		
		// if translation doesn't exist, get it from the server
		this.loadTranslations(keys, function(){
			return selft.translations[key];
		});
	}
};