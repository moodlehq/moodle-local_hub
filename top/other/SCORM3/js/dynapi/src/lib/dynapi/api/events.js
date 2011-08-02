/*
   DynAPI Distribution
   Event Classes

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
*/ 
DynEvent=function(type,src,target) {
	this.type=type;
	this.src=src;
	this.target=target;
};
DynEvent.prototype.getType=function() {return this.type;};
DynEvent.prototype.getSource=function() {return this.src;};
DynEvent.prototype.getTarget=function() {return this.target;};
EventListener=function(target) {this.target=target;};
EventListener.prototype.handleEvent=function(type,e) {
	if (type=='mousedown' || type=='mouseup') {
		if (e.button==2) type='md'+type;
		if (e.button==3) type='rt'+type;
	}
	if (this["on"+type]) this["on"+type](e);
};
MouseEvent=function() {};
MouseEvent.prototype.getType=function() {return this.type;};
MouseEvent.prototype.getSource=function() {return this.src;};
MouseEvent.prototype.getTarget=function() {return this.target;};
MouseEvent.prototype.setEvent=function(src,e) {
	this.browserReturn=true;
	this.bubble=true;
	this.src=src;
	this.type=e.type;
	this.pageX=is.ie?e.x+document.body.scrollLeft:e.pageX-window.pageXOffset;
	this.pageY=is.ie?e.y+document.body.scrollTop:e.pageY-window.pageYOffset;
	this.x=is.ie?e.offsetX:e.layerX;
	this.y=is.ie?e.offsetY:e.layerY;
	var b = is.ie?e.button:e.which;
	if (is.ie) {
		if (b==2) b = 3;
		else if (b==4) b = 2;
	} else { }
	this.button = b;
	var alt,ctrl,shft;
	if (is.ie){
		alt = (e.altKey || e.altLeft)?true:false;
		ctrl = (e.ctrlKey || e.ctrlLeft)?true:false;
		shft =  (e.shiftKey || e.shiftLeft)?true:false;
	}
	else if (is.ns){
		var m = e.modifiers;
		alt = (m==1 || m==3 || m==5 || m==7)?true:false;
		ctrl = (m==2 || m==3 || m==6 || m==7)?true:false;	
		shft = (m==4 || m==5 || m==6 || m==7)?true:false;	
	}
	else { }
	this.altKey = alt;
	this.ctrlKey = ctrl;
	this.shiftKey = shft;
	this.orig=e;
};
MouseEvent.prototype.bubbleEvent=function() {
	if (!this.bubble || this.src.getClass()==DynDocument || this.src.parent==null) return;
	this.x+=this.src.x;
	this.y+=this.src.y;
	this.src=this.src.parent;
	this.src.invokeEvent(this.type,this);
	this.bubbleEvent();
	return;
};
MouseEvent.prototype.getX=function() {return this.x};
MouseEvent.prototype.getY=function() {return this.y};
MouseEvent.prototype.getPageX=function() {return this.pageX};
MouseEvent.prototype.getPageY=function() {return this.pageY};
MouseEvent.prototype.setBubble=function(b) {this.bubble=b};
MouseEvent.prototype.cancelBrowserEvent=function() {this.browserReturn=false};
DynLayer.prototype.captureMouseEvents=function() {
	if (!this.eventListeners) this.eventListeners=[];
	this.hasEventListeners=true;
	if (!this.created) return false;
	var elm=this.elm;
	if (is.ns4) elm.captureEvents(Event.MOUSEDOWN | Event.MOUSEUP | Event.CLICK | Event.DBLCLICK);
	//elm.onmousedown=elm.onmouseup=elm.onmouseover=elm.onmouseout=elm.onclick=elm.ondblclick=DynLayer.prototype.EventMethod;
    elm.onmousedown=elm.onmouseup=elm.onmouseover=elm.onmouseout=elm.ondblclick=DynLayer.prototype.EventMethod;
	if (is.ie5) this.elm.oncontextmenu=function() {return true};
};
DynLayer.prototype.EventMethod = function(e) {
	var dyndoc=this.lyrobj.dyndoc;
	if (is.ie) {
		var e=dyndoc.elm.event;
		e.cancelBubble=true;
		if (e.type=="click" && DynAPI.wasDragging) {
			DynAPI.wasDragging=false;
			return true;
		}
		if (e.type=="mouseout" && this.contains(e.toElement)) { return true };
		if (e.type=="mouseover" && this.contains(e.fromElement)) { return true };
	}
	var realsrc=is.ie?e.srcElement:e.target;

	for(;is.ie && !realsrc.lyrobj && realsrc.parentElement && realsrc.parentElement!=realsrc;realsrc=realsrc.parentElement);
    
	var src=realsrc.lyrobj||dyndoc;
	if (!src) return true;
    
    var evt=dyndoc._e;
    
	evt.setEvent(src,e);
	var type=evt.type;
	src.invokeEvent(type,evt);

	if(is.ns && (e.type=="mouseover" || e.type=="mouseout")) return false;

	evt.bubbleEvent();
    
    if ((is.ie) && (src == dyndoc)) { return; }
    
    return evt.browserReturn;
};
DynLayer.prototype.addEventListener=function(listener) {
	if (!this.hasEventListeners) this.captureMouseEvents();
	for (var i in this.eventListeners) if (this.eventListeners[i]==listener) return;
	this.eventListeners[this.eventListeners.length]=listener;
};
DynLayer.prototype.removeEventListener=function(listener) {
	DynAPI.removeFromArray(this.eventListeners, listener, false);
};
DynLayer.prototype.removeAllEventListeners=function() {
	if (!this.hasEventListeners) return;
	for (var i in this.eventListeners) delete this.eventListeners[i];
	this.eventListeners=[];
	this.hasEventListeners = false;
};
DynLayer.prototype.invokeEvent=function(type,e) {
	if (!this.hasEventListeners) return;
	if (is.ie && type=='mouseover' && this.elm.contains(e.orig.fromElement)) return;
	if (is.ie && type=='mouseout' && this.elm.contains(e.orig.toElement)) return;

	var orig=null;
	if (e && is.ns) { 
		orig=e.orig;
		e.cancelBubble=false;
	}
	if (is.ns4 && is.platform=="other") {
		if (type=="mousedown") {
			if (this.dbltimer!=null) type="dblclick";
			else this.dbltimer=setTimeout(this+'.dbltimer=null',300);
		}
	}
	for (var i=0;i<this.eventListeners.length;i++) {
		if (e) e.target=this.eventListeners[i].target;
		else {
			e=new DynEvent(type,this);
			e.target=this.eventListeners[i].target;
    			if (is.ns) e.cancelBubble=false;
		}
		this.eventListeners[i].handleEvent(type,e);
	}
	if (is.ns && e) {
		if (e.cancelBubble) return;
		if (orig && orig.target.handleEvent && orig.target!=this.elm) orig.target.handleEvent(type,orig);
		}
	if (is.ns4 && is.platform=="other" && type=="mouseup") this.invokeEvent("click",e);
	if (this.parentComponent) {
		if (e) e.src=this.parentComponent;
		else e=new DynEvent(type,this);
		this.parentComponent.invokeEvent(type,e);
	}
};
DynDocument.prototype._e=new MouseEvent()
DynDocument.prototype.captureMouseEvents=function() {
	if (this.mouseEventsCaptured) return;
	this.mouseEventsCaptured=true;
	if (!this.eventListeners) this.eventListeners=[];
	this.hasEventListeners=true;
	//if (is.ns4) this.doc.captureEvents(Event.MOUSEMOVE | Event.MOUSEDOWN | Event.MOUSEUP | Event.CLICK | Event.DBLCLICK);
    if (is.ns4) this.doc.captureEvents(Event.MOUSEMOVE | Event.MOUSEDOWN | Event.MOUSEUP);
	//this.doc.onmousemove=this.doc.onmousedown=this.doc.onmouseup=this.doc.onclick=this.doc.ondblclick=DynDocument.prototype.EventMethod;
    this.doc.onmousemove=this.doc.onmousedown=this.doc.onmouseup=this.doc.ondblclick=DynDocument.prototype.EventMethod;
	if (is.ie5) this.doc.oncontextmenu=function(){ return true };
};
DynDocument.prototype.EventMethod=DynLayer.prototype.EventMethod
DynDocument.prototype.releaseMouseEvents = function() {
	this.mouseEventsCaptured = false;
	this.doc.onmousemove = this.doc.onmousedown = this.doc.onmouseup = this.doc.onclick = this.doc.ondblclick = function(e) { return false };
};
DynDocument.prototype.addEventListener=DynLayer.prototype.addEventListener;
DynDocument.prototype.removeEventListener=DynLayer.prototype.removeEventListener;
DynDocument.prototype.removeAllEventListeners=DynLayer.prototype.removeAllEventListeners;
DynDocument.prototype.invokeEvent=function(type,e) {
	if (!this.hasEventListeners) return;
	var orig=null;
	if (e && is.ns) { 
		orig=e.orig;
		e.cancelBubble=false;
	}
	if (is.ns4 && is.platform=="other") {
		if (type=="mousedown") {
			if (this.dbltimer!=null) type="dblclick";
			else this.dbltimer=setTimeout(this+'.dbltimer=null',300);
		}
	}
	for (var i=0;i<this.eventListeners.length;i++) {
		if (e) e.target=this.eventListeners[i].target;
		else {
			e=new DynEvent(type,this);
			e.target=this.eventListeners[i].target;
    			if (is.ns) e.cancelBubble=false;
		}
		this.eventListeners[i].handleEvent(type,e);
	}
	if (i!=0 || e)
		if (is.ns) {
			if (e.cancelBubble) return;
			if (orig && orig.target.handleEvent && !orig.target.URL) orig.target.handleEvent(orig);
		}
	if (is.ns4 && is.platform=="other" && type=="mouseup") this.invokeEvent("click",e);
};