/*
   DynAPI Distribution
   Layer Extensions

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser]
*/ 
DynLayer.prototype.setMaxSize=function(p,noev) {
	if (!this.created && !p) return;
	var w=h=0;
	if (this.created) {
		w=(is.ns?this.doc.width:parseInt(this.elm.scrollWidth));
		h=(is.ns?this.doc.height:parseInt(this.elm.scrollHeight));
	}
	if (typeof(p)=='object') {
		w=(w>p.w?w:p.w);
		h=(h>p.h?h:p.h);
	} else if (typeof(p)=='boolean') noevt=p;
	this.setSize(w,h,noev);
};
DynLayer.prototype.setPadding=function(p) {
	this.pad=p;
	if (this.created) {
		if (is.ie || is.ns5) this.elm.style.padding=p;
		else if (is.ns4) this.elm.padding=p;
	}
};
DynLayer.prototype.getPadding=function() { 
	return this.pad;
};
DynLayer.prototype.getTopZIndex=function() {
    if(!this.origZIndex||typeof(this.origZIndex)!="number") this.origZIndex=this.getZIndex();
    n=1;
    for (var i in this.parent.children) {
        child=this.parent.children[i].getComponent();
        z=child.getZIndex();
        if (n<=z) n=z+5;
    }
    return n;
};