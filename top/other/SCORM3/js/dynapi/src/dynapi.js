/*
   DynAPI Distribution
   Main DynAPI class

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.  
*/
DynAPI = {
	loaded : false,
	plugins : [],
	wasDragging : false,
	librarypath : '',
	packages : [],

	addPackage : function(pckg) {
		if (this.packages[pckg]) return;
		DynAPI.packages[pckg] = {};
		DynAPI.packages[pckg].libs = [];
	},

	addLibrary : function(path,files) {
		var pckg = path.substring(0,path.indexOf('.'));
		if (!pckg) {
			alert("Incorrect DynAPI.addLibrary usage:\n\nExample: DynAPI.addLibrary('dynapi.ext',['inline.js'])");
			return;
		}
		var name = path.substring(path.indexOf('.')+1);
		if (!DynAPI.packages[pckg]) DynAPI.addPackage(pckg);
		if (DynAPI.packages[pckg].libs[name]) {
			alert("DynAPI Error: Library "+name+" already exists");
			return;
		}
		DynAPI.packages[pckg].libs[name] = files;
	},

	toString : function() {
		return "DynAPI";
	},

	getDocument : function(id) {
		if (id) return DynDocument.dyndocsID[id];
		else return DynAPI.document;
	},

	resizeHandler : function() {
		var doc = this.dyndoc;
		var w = doc.getWidth();
		var h = doc.getHeight();
		doc.findDimensions();
		if (is.ns4 && (w!=doc.getWidth() || h!=doc.getHeight())) doc.recreateAll();
		if (DynAPI.onResize) DynAPI.onResize();
		for (var i=0;i<DynAPI.plugins.length; i++) {
			if (DynAPI.plugins[i].onResize) DynAPI.plugins[i].onResize();
		}
	},

	loadHandler : function() {
			if (!DynDocument) {
				if (DynAPI.onLoad) DynAPI.onLoad();
				return;
			}
	        DynAPI.document=new DynDocument(self);
	        DynAPI.document.findDimensions();
	        if (DynAPI.findLayers) DynAPI.findLayers(DynAPI.document);
    		//if (DynAPI.document.captureMouseEvents) DynAPI.document.captureMouseEvents();
			//if (DynAPI.document.captureKeyEvents) DynAPI.document.captureKeyEvents();
    		if (DynAPI.document.invokeEvent) DynAPI.document.invokeEvent('beforeload');
    		if (DynAPI.document.invokeEvent) DynAPI.document.invokeEvent('load');
    		if (DynAPI.onLoad) DynAPI.onLoad();
    		for (var i=0;i<DynAPI.plugins.length; i++) {
				if (DynAPI.plugins[i].onLoad) DynAPI.plugins[i].onLoad();
			}
    		DynAPI.loaded=true;
    		if (DynAPI.document.invokeEvent) DynAPI.document.invokeEvent('afterload');
	},

	removeFromArray : function(array, index, id) {
		var which=(typeof(index)=="object")?index:array[index];
		if (id) { 
			delete array[which.id];
			return;
		}
		for (var i=0; i<array.length; i++) {
			if (array[i] == which) {
				for(var x=i; x<array.length-1; x++) array[x]=array[x+1];
				array.length -= 1;
				break;
			}
		}
		return array;
	},

	unloadHandler : function() { 
		for (var i=0;i<DynAPI.plugins.length; i++)
			if (DynAPI.plugins[i].onUnload) DynAPI.plugins[i].onUnload();
		if (DynAPI.onUnload) DynAPI.onUnload();
	},

	setLibraryPath : function(path) { 
		if (path.substring(path.length-1)!='/') path+='/';
		DynAPI.librarypath=path;
	},

	mountplugin : function (plugin) { 
		if (!plugin.pluginName) alert(DynAPI.toString()+'\n\nError occured\nAn invalid plugin was added to the DynApi code:\n\n'+plugin.constructor.toString());
		else DynAPI.plugins[DynAPI.plugins.length] = plugin;
	},
	include : function(src,path) { 
		src=src.split('.'); 
		if (src[src.length-1] == 'js') src.length -= 1; 
		var path=path||DynAPI.librarypath||''; 
		if (path.substr(path.length-1) != "/") path += "/"; 
		var pckg=src[0]; 
		var grp=src[1]; 
		var file=src[2]; 
		if (file=='*') { 
			if (DynAPI.packages[pckg]) group=DynAPI.packages[pckg].libs[grp]; 
			if (group) for (var i in group) document.write('<script language="Javascript1.2" src="'+path+pckg+'/'+grp+'/'+group[i]+'.js"><\/script>');
			else alert('include()\n\nThe following package could not be loaded:\n'+src+'\n\nmake sure you specified the correct path.');
		} else document.write('<script language="Javascript1.2" src="'+path+src.join('/')+'.js"><\/script>');
	} 
};
onload = DynAPI.loadHandler;
onunload = DynAPI.unloadHandler;
onresize = DynAPI.resizeHandler;

DynAPI.document=false;
DynAPI.addPackage('dynapi');
DynAPI.addLibrary('dynapi.api',["browser","dynlayer","dyndocument","events","dragevent"]);
DynAPI.addLibrary('dynapi.ext',["inline","layer","dragdrop","functions"]);
DynAPI.addLibrary('dynapi.gui',["button","buttonimage","dynimage","label","list","loadpanel","pushpanel","scrollbar","scrollpane","sprite","viewport"]);
DynAPI.addLibrary('dynapi.util',["circleanim","cookies","debug","hoveranim","imganim","pathanim","thread"]);

DynDocument = null;
