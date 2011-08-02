/*
   DynAPI Distribution
   Key Event Extensions by Henrik Våglin (hvaglin@yahoo.com)

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser]
*/

KeyEvent=function() {
	
};

KeyEvent.prototype.getSource=function() { return this.src; };

KeyEvent.prototype.setEvent=function(src,e) {
	this.src=src;
    this.which=(is.ns4)?e.which:e.keyCode;
	this.curKey = String.fromCharCode(this.which).toLowerCase();
	var alt,ctrl,shft;
	if (is.ie){
		alt=(e.altKey||e.altLeft||e.keyCode==18)?true:false;
		ctrl=(e.ctrlKey||e.ctrlLeft||e.keyCode==17)?true:false;
		shft=(e.shiftKey||e.shiftLeft||e.keyCode==16)?true:false;
	}
	if (is.ns){
		var m=e.modifiers;
		alt=(m==1||m==3||m==5||m==7)?true:false;
		ctrl=(m==2||m==3||m==6||m==7)?true:false;	
		shft=(m==4||m==5||m==6||m==7)?true:false;
    }
	this.controlKey=alt; //this doesn't work properly but on keypress
	this.orig=e;
	return this;
};

KeyEvent.prototype.getKey=function() { return this.curKey; };

DynDocument.prototype.keys=new KeyEvent();
DynDocument.prototype.captureKeyEvents=function() {
	if (this.KeyEventsCaptured) return;
	this.KeyEventsCaptured=true;
	if (!this.eventListeners) this.eventListeners=[];
	this.hasEventListeners=true;
	if (is.ns4) this.doc.captureEvents(Event.KEYPRESS | Event.KEYDOWN | Event.KEYUP);
	this.doc.onkeypress=this.doc.onkeydown=this.doc.onkeyup=function(e) {
		if (is.ie) var e=this.lyrobj.elm.event;
		var realsrc=is.ie?e.srcElement:e.target;
		var src=realsrc.lyrobj;
		if (!src) {
			src=DynAPI.getDocument(realsrc.elm);
			if (!src) return true;
		}
		var evt=this.lyrobj.keys;
		evt.setEvent(src,e);
        src.invokeEvent(e.type,evt);
	}
};