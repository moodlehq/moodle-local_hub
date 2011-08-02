Utility = {

	trim : function(str) {
	   return str.replace(/^\s*/, '').replace(/\s*$/, ''); 
	},
	
	getHostName : function() {
		return location.protocol+"//"+document.location.host;
	},
	
	getMapping : function() {
		return document.location.pathname.substring(0,document.location.pathname.indexOf('/',1));
	}

};