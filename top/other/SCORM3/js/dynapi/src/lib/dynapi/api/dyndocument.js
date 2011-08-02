/*
   DynAPI Distribution
   DynDocument Class

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
*/ 
function DynDocument(frame) {
	this.elm = frame;
	this.elm.lyrobj = this;
	this.doc = frame.document;
	this.doc.lyrobj = this;
	this.all = [];
	this.children = [];
	this.id = frame.name||"DynDocument"+DynDocument.nullCount++;

	frame.onresize = DynAPI.resizeHandler;
	this.dyndoc = this;
	this.fgColor = this.doc.fgColor||'';
	this.bgColor = this.doc.bgColor||'';
	frame.dyndoc = this;

	DynDocument.dyndocs[DynDocument.dyndocs.length]=this;
	DynDocument.dyndocsID[this.id]=this;
	return this;
}
DynDocument.dyndocs = [];
DynDocument.dyndocsID = [];
DynDocument.nullCount=0;
DynDocument.prototype.isChild = false;
DynDocument.prototype.created = true;
DynDocument.prototype.isDynDocument = true;
DynDocument.prototype.toString=function() {
	return "DynAPI.getDocument('"+this.id+"')";
};
DynDocument.prototype.getClass = DynLayer.prototype.getClass;
DynDocument.prototype.addChild = DynLayer.prototype.addChild;
DynDocument.prototype.removeChild = DynLayer.prototype.removeChild;
DynDocument.prototype.deleteChild = DynLayer.prototype.deleteChild;
DynDocument.prototype.deleteAllChildren = DynLayer.prototype.deleteAllChildren;
DynDocument.prototype.addChildID = DynLayer.prototype.addChildID;

DynDocument.prototype.recreateAll = function() {
    if (!is.ns4) return;
    this.setBgColor(this.bgColor);
    this.setFgColor(this.fgColor);
    for (var i in this.all) {
		this.all[i].elm = null;
	}
    for (var i=0; i<this.children.length; i++) { 
        DynLayer.deleteElement(this.children[i]);
        DynLayer.createElement(this.children[i]);
    }
};
DynDocument.prototype.getBgColor = DynLayer.prototype.getBgColor;
DynDocument.prototype.getX=DynDocument.prototype.getY = DynDocument.prototype.getPageX = DynDocument.prototype.getPageY = function() {
	return 0;
};
DynDocument.prototype.getWidth = function() {
	if (!this.w) this.findDimensions();
	return this.w;
};
DynDocument.prototype.getHeight = function() {
	if (!this.h) this.findDimensions();
	return this.h
};
DynDocument.prototype.findDimensions = function() {
	this.w=(is.ns)? this.elm.innerWidth : this.doc.body.clientWidth;
	this.h=(is.ns)? this.elm.innerHeight : this.doc.body.clientHeight;
};
DynDocument.prototype.setBgColor = function(color) {
	if (color == null) color='';
	if (color == '' && is.ns4) color = '#ffffff';
	this.bgColor = color;
	this.doc.bgColor = color;
};
DynDocument.prototype.setFgColor = function(color) {
	if (color == null) color='';
	if (color == '' && is.ns4) color='#ffffff';
	this.fgColor = color;
	this.doc.fgColor = color;
};
DynDocument.prototype.load = function(path) {
	this.doc.location = path;
};
