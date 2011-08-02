/*
   DynAPI Distribution
   DragEvent Class
  
   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
*/ 
function DragEvent(type,src) {
	this.type=type;
	this.src=src;
	this.dragEnabled=true;
	this.bubble=false;
}
DragEvent.prototype.getType=function() {return this.type;};
DragEvent.prototype.getSource=function() {return this.src;};
DragEvent.prototype.getTarget=function() {return this.target;};
DragEvent.prototype.bubble=false;
DragEvent.prototype.getX=function() {return this.x;};
DragEvent.prototype.getY=function() {return this.y;};
DragEvent.prototype.getPageX=function() {return this.pageX;};
DragEvent.prototype.getPageY=function() {return this.pageY;};
DragEvent.prototype.setBubble=function(b) {this.bubble=b;};
DragEvent.prototype.cancelDrag=function() {this.dragEnabled=false;};
DragEvent.dragPlay=0;
DynAPI.document.dragevent=null;
DragEvent.lyrListener=new EventListener();
DragEvent.lyrListener.onmousedown=function(e) {
	e.cancelBrowserEvent();
	if (DynAPI.document.dragevent) return;
	var lyr=e.getSource();
	if (is.ie) lyr.doc.body.onselectstart = function() { return false; }
	lyr.dragevent=new DragEvent("dragstart",lyr);
	DynAPI.document.dragevent=lyr.dragevent;
	DynAPI.document.dragobject=lyr;
	var de=lyr.dragevent;
	de.isDragging=false;
	de.x=e.getPageX()-e.getSource().getPageX();
	de.y=e.getPageY()-e.getSource().getPageY();
	de.pageX=e.getPageX();
	de.pageY=e.getPageY();
	de.parentPageX=lyr.parent.getPageX();
	de.parentPageY=lyr.parent.getPageY();
    e.setBubble(true);
};
DragEvent.docListener=new EventListener();
DragEvent.docListener.onmousemove=function(e) {
	var de = DynAPI.document.dragevent;
	if (!de) return;
	if (!de.isDragging && ((Math.abs(de.pageX-e.getPageX())-DragEvent.dragPlay>0) ||      
	(Math.abs(de.pageY-e.getPageY())-DragEvent.dragPlay>0)) ) {
		de.isDragging=true;
		de.src.invokeEvent("dragstart",de);
		e.setBubble(de.bubble);
	}
	if (!de.isDragging) return;
	else if (!de.dragEnabled) {
		lyr.invokeEvent("mouseup");
		return;
	}
	var lyr=de.src;
	if (!lyr) return;
	de.type="dragmove";
	de.pageX=e.getPageX();
	de.pageY=e.getPageY();
	var x=de.pageX-de.parentPageX-de.x;
	var y=de.pageY-de.parentPageY-de.y;
	if (lyr.dragBoundary) {
		var dB=lyr.dragBoundary;
		if (dB=="parent") {
			var b=lyr.parent.getHeight();
			var r=lyr.parent.getWidth();
			var l=0;
			var t=0;
		} else {
			var b=dB[2];
			var r=dB[1];
			var l=dB[3];
			var t=dB[0];
		}
		var w=lyr.w;
		var h=lyr.h;
		if (x<l) x=l;
		else if (x+w>r) x=r-w;
		if (y<t) y=t;
		else if (y+h>b) y=b-h;
	}
	lyr.moveTo(x,y);
	lyr.invokeEvent("dragmove",de);
    e.cancelBrowserEvent();
	e.setBubble(de.bubble);
};
DragEvent.docListener.onmouseup=function(e) {
	var de=DynAPI.document.dragevent;
	if (!de) return;
	if (!de.isDragging) {
    	de.type="dragend";
    	de.src=null;
    	e.setBubble(true);
    	DynAPI.document.dragevent=null;
    	DynAPI.document.dragobject=null;
		return;
	}
	var lyr=de.src;
	if (!lyr) return;
	if (is.ie) lyr.doc.body.onselectstart = null;
	if (lyr.parent.DragDrop) lyr.parent.DragDrop(lyr);
	de.type="dragend";
	if (is.ie) DynAPI.wasDragging=true;
	de.isDragging=false;
	lyr.invokeEvent("dragend",de);
	de.src=null;
	DynAPI.document.dragevent=null;
	e.setBubble(de.bubble);
};
DragEvent.setDragBoundary=function(dlyr,t,r,b,l) {
	var a=arguments;
	if (a.length==0) return;
	if (a.length==1) dlyr.dragBoundary="parent";
	else if (a.length==5) dlyr.dragBoundary=new Array(t,r,b,l);
};
DragEvent.enableDragEvents=function() {
	var dlyr = arguments[0];
	if (dlyr.constructor!=DynDocument);
		dlyr.addEventListener(DragEvent.lyrListener);
	for (var i=1;i<arguments.length;i++) {
		var lyr=arguments[i];
		lyr.addEventListener(DragEvent.lyrListener);
	}
	if (dlyr.constructor!=DynDocument) DynAPI.document.addEventListener(DragEvent.docListener);
	else dlyr.addEventListener(DragEvent.docListener);
};
DragEvent.disableDragEvents=function() {
	for (var i=0;i<arguments.length;i++) {
		var lyr=arguments[i];
		lyr.removeEventListener(DragEvent.lyrListener);
	}
};
