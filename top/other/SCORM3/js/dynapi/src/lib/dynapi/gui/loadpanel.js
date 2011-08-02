/*
   DynAPI Distribution
   LoadPanel Widget

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser, events]
*/
function LoadPanel(url) {
	this.DynLayer = DynLayer;
	this.DynLayer();
	this.autoH=true;
	
	// DPV Changed to true
	//this.autoW=false;
	this.autoW=true;
	this.isILayer=false;
	this.isIFrame=true;
	var l=new EventListener(this);
	l.onresize=function(e) {
		var o=e.getTarget();
		if (!o.created || o.isReloading) return;
		if (o.autoH && o.url) o.reload();
	};
	l.oncreate=function(e) {
		var o=e.getTarget();
		if (o.isILayer || o.isIFrame) o.insertInlineElements();
		o.findInlineElements();
		o.setURL(o.url);
		if (!o.isReloading && o.tempURL) {
			o.setURL(o.tempURL);
			delete o.tempURL;
		}
	};
	this.addEventListener(l);
	this.tempURL=url;
	return this;
};
LoadPanel.prototype=new DynLayer();
LoadPanel.prototype.setAutoResizeWidth=function(b) {
	this.autoW=b;
};
LoadPanel.prototype.setAutoResizeHeight=function(b) {
	this.autoH=b;
};
LoadPanel.prototype.useILayer=function(b) {
	if (is.ns4) {
		this.isILayer=b;
		if (this.created) this.reload();
	}
};
LoadPanel.prototype.useIFrame=function(b) {
	if (is.ie || is.dom) {
		this.isIFrame=b;
		if (this.created) this.reload();
	}
};
LoadPanel.prototype.insertInlineElements = function() {
	if (is.ns4 && this.isILayer) {
		this.setHTML('<ilayer></ilayer>');
	}
	else {
		if (is.ie5) this.setHTML('<DIV ID="'+this.id+'loadElement" STYLE="behavior:url(#default#download)" style="display: none;"></DIV>'); 
		if (is.ie4) {
			if (this.useBuffer) {
				this.setHTML('<IFRAME ID="'+this.id+'loadElement" STYLE="visibility: hidden; display: none;" onLoad="LoadQueue.loadHandler()"></IFRAME>');
			}
			else {
				this.setHTML('<IFRAME ID="'+this.id+'loadElement" WIDTH="+this.getWidth()+" HEIGHT="+this.getHeight()+" STYLE="visibility: hidden; display: none;" onLoad="LoadQueue.loadHandler()"></IFRAME>');
			}
		}
	}
};
LoadPanel.prototype.findInlineElements = function() {
	if (is.ns4) {
		if (this.isILayer) {
			this.loadElement = this.doc.layers[0];
		}
		else {
			this.loadElement = this.elm;
		}
	}
	else if (is.ie) {
		if (this.isIFrame) {
			this.loadElement=document.all[this.id+'loadElement'];
		} else {
			this.loadElement=this.elm;
		}
	}
};
LoadPanel.prototype.getFileScope=function() {
	if (!this.loadElement) return null;
	return this.loadElement;
};
LoadPanel.prototype.clearFile=function() {
	this.url = null
	if (this.isILayer) {
		this.loadElement.document.write('');
		this.loadElement.document.close();
	}
	else {
		this.reload();
	}
};
LoadPanel.prototype.getURL = function() {
	return this.url;
};
LoadPanel.prototype.setURL = function(url) {
	if (!url) return;
	if (!this.created) this.url=url;
	else LoadPanel.queue.add(url,this);
};
LoadPanel.prototype.reload = function() { 
	this.isReloading = true;
	var url = this.url;
	var p = this.parent;
	this.removeFromParent();
	this.html = '';
	p.addChild(this);
	this.isReloading = false;
}
LoadPanel.prototype.loadHandler = function(url) {
	this.url = url;
	if (is.ns4 && this.isILayer) {
		var w = this.loadElement.document.width;
		var h = this.loadElement.document.height;
	}
	else {
		var w = this.getContentWidth();
		var h = this.getContentHeight();
	}
	if (this.autoW) this.setWidth(w,false);
	if (this.autoH) this.setHeight(h,false);
	this.isReloading = false;
	this.invokeEvent('load');
};
function LoadQueue() {
	this.queue = new Array();
	this.index = 0;
};
LoadQueue.prototype.toString = function() {
	return "LoadPanel.queue";
};
LoadQueue.prototype.add = function(url,loadpanel) {
	var q = this.queue.length;
	this.queue[q] = [url,loadpanel];
	this.loadNext();
};
LoadQueue.prototype.loadNext = function() {
	if (!this.busy && this.queue[this.index]) {
		this.busy = true;
		var lpanel = this.currentLoadPanel = this.queue[this.index][1];
		var url = this.currentURL = this.queue[this.index][0];
		if (is.ns4) {
			DynAPI.document.releaseMouseEvents();
			var lyr=lpanel.elm;
			while(lyr.parentLayer!=window) lyr=lyr.parentLayer;
			lyr.onload=LoadQueue.loadHandler;
			lpanel.loadElement.onload=LoadQueue.loadHandler;
			lpanel.loadElement.src=url;
		}
		else if (is.ie5) {
			lpanel.loadElement.startDownload(url,LoadQueue.loadHandler);
		}
		else if (is.ie4) {
            if (lpanel.elm.innerHTML.indexOf("<IFRAME") == -1) lpanel.insertInlineElements();
			lpanel.timerID=setInterval("if (document.frames['"+lpanel.id+"loadElement'].document.readyState=='interactive') {clearInterval("+lpanel.toString()+".timerID);LoadQueue.loadHandler(document.frames['"+lpanel.id+"loadElement'].document.body.innerHTML)}",250);
			document.frames[lpanel.id+"loadElement"].document.location=url;
		}
		DynAPI.removeFromArray(this.queue,this.index);
	}
};
LoadQueue.loadHandler = function(e) {
	var q = LoadPanel.queue;
	var lp = q.currentLoadPanel;
	if (q.currentLoadPanel) {
		if (is.ie) {
			var lyr=lp.elm;
			lyr.innerHTML=e;
		}
		if (is.ns4) {
			var lyr = lp.elm;
			while(lyr.parentLayer != window) lyr = lyr.parentLayer;
			lyr.onload = function(){};
			lp.loadElement.onload = function(){};
		}
		setTimeout('LoadQueue.continueLoad()',100);
	}
};
LoadQueue.continueLoad = function() {
	var q = LoadPanel.queue;
	q.currentLoadPanel.loadHandler(q.currentURL);
	q.busy=false;
	if (is.ns4) window.stop();
	if (q.queue[q.index]) q.loadNext();
	else DynAPI.document.captureMouseEvents();
};
LoadPanel.queue = new LoadQueue();
