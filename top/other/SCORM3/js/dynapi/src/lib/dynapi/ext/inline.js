/*
   DynAPI Distribution
   Inline Layers Extensions

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser]
*/

DynAPI.findLayers=function(dyndoc,or) {
	var divs=[];
	or=or||dyndoc;
	if (is.ns4) divs=dyndoc.doc.layers;
	if (is.ns5) divs=dyndoc.doc.getElementsByTagName("DIV");
	if (is.ie) divs=dyndoc.doc.all.tags("DIV");
	for (var i=0; i<divs.length; i++) {
		if(DynAPI.isDirectChildOf(divs[i],dyndoc.elm)) {
			var id=is.ns4? divs[i].name : divs[i].id;
			var dlyr = new DynLayer(id);
			dlyr.parent = dyndoc;
			DynLayer.assignElement(dlyr,divs[i]);
			dlyr.created = true;

			dlyr.dyndoc=or;
			if (dyndoc.getClass()!=DynDocument) dlyr.isChild=true;
			if (or.getClass()!=DynDocument) dlyr.isChild=true;
			else {
				DynAPI.removeFromArray(DynLayer.unassigned,dlyr,true);
				or.all[dlyr.id]=dlyr;
			}
			dyndoc.children[dyndoc.children.length]=dlyr;
			var index=id.indexOf("Div");
			if (index>0) dyndoc.doc.window[id.substr(0,index)] = dyndoc.all[id];
			if (is.ns4) {
				for (ict in dlyr.doc.images)
					dlyr.doc.images[ict].lyrobj=dlyr;
			}
			else if (is.ns5) {
				for (ict in dlyr.doc.images)
				dlyr.doc.images[ict].lyrobj=dlyr.elm;
			}
			else {
				for (ict in dlyr.elm.all.tags("img"))
					dlyr.elm.all.tags("img")[ict].lyrobj=dlyr;
			}
			if (dlyr.updateValues) dlyr.updateValues();
			DynAPI.findLayers(dlyr,or);
		}
	}
};

DynLayer.prototype.updateValues=function() {
	if (is.ns4) {
		this.x=parseInt(this.css.left);
		this.y=parseInt(this.css.top);
		this.w=this.css.clip.width;
		this.h=this.css.clip.height;
		this.clip=[this.css.clip.top,this.css.clip.right,this.css.clip.bottom,this.css.clip.left];
		this.bgImage = this.elm.background.src!=""?this.elm.background.src:null;
		this.html = this.innerHTML = this.elm.innerHTML = "";
	}
	else if (is.ie || is.ns5) {
		this.x=this.elm.offsetLeft;
		this.y=this.elm.offsetTop;
		this.w=is.ie4? this.css.pixelWidth||this.getContentWidth() : this.elm.offsetWidth;
		this.h=is.ie4? this.css.pixelHeight||this.getContentHeight() : this.elm.offsetHeight;
		this.bgImage = this.css.backgroundImage;
		this.bgColor = this.css.backgroundColor;
		this.html = this.innerHTML = this.elm.innerHTML;
	}
	this.z = this.css.zIndex;
	var b = this.css.visibility;
	this.visible = (b=="inherit"||b=="show"||b=="visible"||b=="");
};
DynAPI.getModel=function() {
	dom='DYNAPI OBJECT MODEL:\n\n+DynAPI\n';
	for (var i=0; i<DynDocument.dyndocs.length; i++) {
		dom+='  +'+DynDocument.dyndocs[i].toString()+'\n';
		for (var j in DynDocument.dyndocs[i].all)
			dom+='+'+DynDocument.dyndocs[i].all[j].toString()+'\n';
	}
	alert(dom);
};
DynAPI.isDirectChildOf = function(l, parent) {
	if(is.ns4) return (l.parentLayer == parent);
	if(is.ns5) {
		for(var p=l.parentNode;p!=dyndoc.doc;p=p.parentNode)
			if(p.tagName.toLowerCase()=='div') return p==parent;
		return !parent.tagName;
	}
	for(var p=l.parentElement;p;p=p.parentElement)
		if(p.tagName.toLowerCase()=='div') return p==parent;
	return !parent.tagName;
};
