/*
   DynAPI Distribution
   DynLayer Class

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
*/
function DynLayer() {
	var a=arguments;
	if (a.length==1 && a[0]!=null && typeof(a[0])=="object") this.setStyle(a[0]);
	else {
		this.id=a[0]||"JSDynLayer"+(DynLayer.nullCount++);
		this.x=a[1]||0;
		this.y=a[2]||0;
		this.w=a[3]||null;
		this.h=a[4]||null;
		this.bgColor=a[5]||null;
		this.visible=a[6]!='hidden';
		this.z=a[7]||0;
		this.bgImage=a[8]||null;
	}
	this.children=[];
	this.parent=null;
	this.isChild=false;
	this.created=false;
	this.html=null;
	this.bgImage=null;

	this.elm=null;
	this.doc=null;
	this.css=null;

	DynLayer.unassigned[this.id]=this;
}

// Static Properties/Methods
DynLayer.unassigned=[];
DynLayer.nullCount=0;
DynLayer.createElement=function(dlyr) {
	if (dlyr.created||!dlyr.parent||dlyr.elm!=null) return;
	if (dlyr.parent.isDocument) dlyr.dyndoc=dlyr.parent; else dlyr.dyndoc=dlyr.parent.dyndoc;
	var lyr;
	if (is.ns4) {
		var recycled=dlyr.parent.doc.recycled;
		if (recycled && recycled.length>0) {
	        	lyr=recycled[0];
			DynAPI.removeFromArray(recycled,recycled[0]);
		} else {
			lyr=new Layer(dlyr.w,dlyr.parent.elm);
			lyr.captureEvents(Event.LOAD);
			lyr.onload=function() {};
		}
	} else var parentElement=(dlyr.parent.isDynLayer)?dlyr.parent.elm:dlyr.parent.doc.body;
	if (is.ie4) {
		var code='<DIV id="'+dlyr.id+'" style="position:absolute; left:0px; top:0px; width:'+dlyr.w+'px;"></DIV>';
		parentElement.insertAdjacentHTML("beforeEnd", code);
		lyr=parentElement.children[parentElement.children.length-1];
	} else if (is.ie5 || is.ns5) {
		lyr=dlyr.dyndoc.doc.createElement("DIV");
		lyr.style.position="absolute";
		lyr.id=dlyr.id;
		parentElement.appendChild(lyr);
	}
	DynLayer.flagPrecreate(dlyr);
	DynLayer.assignElement(dlyr,lyr);
	if (is.ns4) dlyr.elm.moveTo(dlyr.x,dlyr.y);
	else {
		dlyr.css.left=dlyr.x;
		dlyr.css.top=dlyr.y;
	}
	if (dlyr.bgColor!=null) dlyr.setBgColor(dlyr.bgColor);
	if (dlyr.bgImage!=null) dlyr.setBgImage(dlyr.bgImage)
	else if (is.ie55 && dlyr.bgImage==null && dlyr.html==null) dlyr.setBgImage('javascript:null');
	if (dlyr.clip) {
		if (is.ns4) {
			var c=dlyr.elm.clip;
    			c.top=clip[0], c.right=clip[1], c.bottom=clip[2], c.left=clip[3];
		} else {
			dlyr.setClip(dlyr.clip);
		}
	}
	if (dlyr.z!=null) dlyr.css.zIndex=dlyr.z;
	dlyr.css.visibility=dlyr.visible? "inherit" : (is.ns4?"hide":"hidden");
	if (dlyr.w!=null) dlyr.setWidth(dlyr.w, false);
	if (dlyr.h!=null) dlyr.setHeight(dlyr.h, false);
	if (is.ns4) {
		dlyr.doc.write(dlyr.getInnerHTML());
		dlyr.doc.close();
	} else if (is.ie || is.dom) {
		dlyr.setHTML(dlyr.getInnerHTML(),false);
	}
	DynLayer.assignChildren(dlyr);

	if (dlyr.html!=null && (dlyr.w==null || dlyr.h==null)) {
		if (dlyr.w==null && dlyr.getContentWidth()>0) dlyr.setWidth(dlyr.getContentWidth(), false);
		if (dlyr.h==null && dlyr.getContentHeight()>0) dlyr.setHeight(dlyr.getContentHeight(), false);
	}
	dlyr.created=true;
	if (dlyr.hasEventListeners) dlyr.captureMouseEvents();
	dlyr.invokeEvent("resize");
	dlyr.invokeEvent('create');
};
DynLayer.deleteElement = function(dlyr) {
	DynLayer.flagDeleteChildren(dlyr);
	if (dlyr.elm) {
		if (is.ns4) {
			dlyr.elm.visibility = "hide";
			dlyr.elm.releaseEvents(Event.LOAD);
		} else {
			dlyr.elm.style.visibility = "hidden";
			dlyr.elm.innerHTML = "";
			dlyr.elm.outerHTML = "";
			if (is.ie5 && is.platform=="win32" && dlyr.elm.children.length>0) dlyr.elm.removeNode(true);
		}
	}
	dlyr.elm = null;
	dlyr.doc = null;
	dlyr.css = null;
	dlyr.created = false;
};
DynLayer.assignElement=function(dlyr,elm) {
	dlyr.elm=elm;
	if (is.ns4) {			
		dlyr.css=elm;
		dlyr.doc=elm.document;
		dlyr.doc.lyrobj=dlyr;
	}
	else if (is.ie || is.dom) {
		dlyr.css=dlyr.elm.style;
		dlyr.doc=dlyr.parent.doc;
	}
	dlyr.elm.lyrobj=dlyr;

	if (is.ns4) { 
		for (var i in dlyr.doc.images) dlyr.doc.images[i].lyrobj=dlyr; 
		for (i=0;i<dlyr.doc.links.length;i++) dlyr.doc.links[i].lyrobj=dlyr;
	}
	if (is.ns5) for (i in dlyr.doc.images) dlyr.doc.images[i].lyrobj=dlyr.elm;
};
DynLayer.assignChildren=function(dlyr) {
	for (var i=0; i<dlyr.children.length; i++) {
		var child=dlyr.children[i];
		if (is.ns4) var elm=dlyr.doc.layers[child.id];
		else if (is.ns5) var elm=dlyr.doc.getElementById(child.id);
		else if (is.ie) var elm=dlyr.elm.all[child.id];
		DynLayer.assignElement(child,elm);
		DynLayer.assignChildren(child);
		if (child.html!=null && (child.w==null || child.h==null)) {
			if (child.w==null && child.getContentWidth()>0) child.setWidth(child.getContentWidth(),false);
			if (child.h==null && child.getContentHeight()>0) child.setHeight(child.getContentHeight(),false);
		}
		child.invokeEvent("resize");
		child.created=true;
		if (child.hasEventListeners) child.captureMouseEvents();
		if (child.z!=null) child.css.zIndex=child.z;
		child.invokeEvent('create');
	}
};
DynLayer.flagPrecreate=function(dlyr) {
	for (var i=0; i<dlyr.children.length;  i++) {
		DynLayer.flagPrecreate(dlyr.children[i]);
	}
	dlyr.invokeEvent('precreate');
}
DynLayer.flagDeleteChildren=function(dlyr) { 
	for (var i=0; i<dlyr.children.length; i++) {
		if (dlyr.children[i].created) DynLayer.flagDeleteChildren(dlyr.children[i]);
	}
	dlyr.invokeEvent('predelete');
};
// End Static Properties/Methods

DynLayer.prototype.isDynLayer = true;
DynLayer.prototype.getClass=function() {
	return this.constructor;
};
DynLayer.prototype.toString=function () {
	return (this.created)?'DynAPI.getDocument("'+this.dyndoc.id+'").all["'+this.id+'"]':'DynLayer.unassigned["'+this.id+'"]';
};
DynLayer.prototype.addChildID=function() {
	for (var a=0;a<arguments.length;a++) {
		var child=arguments[a];
		child.dyndoc=this.dyndoc;
		if (this.dyndoc.all[child.id]) {
			alert('Attempt to add "'+child.id+'" to the document "'+this.dyndoc.id+'" failed.\n\nThe DynLayer already exists in that DynDocument.\n\nYou must remove the dynlayer from its parent first.');
			return;
		}
		for (var i=0;i<child.children.length;i++) child.addChildID(child.children[i]);
		DynAPI.removeFromArray(DynLayer.unassigned,child,true);
		this.dyndoc.all[child.id]=child;
	}
};
DynLayer.prototype.addChild=function() {
    for (var a=0;a<arguments.length;a++) {
        var child=arguments[a];
        if (child.created) child.removeFromParent();
        child.parent=this;
	child.isChild=true;
        if (this.dyndoc) this.addChildID(child);
        this.children[this.children.length]=child;
        if (this.created) DynLayer.createElement(child); 
    }
    return arguments[arguments.length-1];
};
DynLayer.prototype.removeChild=function() {
    for (var a=0;a<arguments.length;a++) {
        var child=arguments[a];	
        if (child.parent==this) {
	    DynAPI.removeFromArray(this.children,child);
            DynAPI.removeFromArray(this.dyndoc.all,child,true);
            DynLayer.unassigned[child.id]=child;
            DynLayer.deleteElement(child);
            child.parent=null;
            child.isChild=false;
        }
    }
	return arguments[arguments.length-1];
};
DynLayer.prototype.removeFromParent=function() {
	if (this.parent!=null) this.parent.removeChild(this);
};
DynLayer.prototype.deleteChild=function() {
    for (var a=0;a<arguments.length;a++) {
        var child=arguments[a];	
        if (child.parent == this) {
			child.deleteAllChildren();
			child.invokeEvent('delete');
            DynAPI.removeFromArray(this.children,child);
            DynAPI.removeFromArray(this.dyndoc.all,child,true);
            if (is.ns && child.elm) {
                if (!this.doc.recycled) this.doc.recycled=[];
                this.doc.recycled[this.doc.recycled.length]=child.elm;
            }
            DynLayer.deleteElement(child);
            child.parent=null;
            child.isChild=false;
        }
    }
};
DynLayer.prototype.deleteAllChildren=function() {
    for (var i in this.children) {
		this.children[i].deleteAllChildren();
		this.children[i].invokeEvent('delete');
        DynAPI.removeFromArray(this.dyndoc.all,this.children[i],true);
		if (is.ns && this.children[i].elm) {
			if (!this.doc.recycled) this.doc.recycled=[];
			this.doc.recycled[this.doc.recycled.length]=this.children[i].elm;
		}
        DynLayer.deleteElement(this.children[i]);
		this.children[i].parent=null;
		this.children[i].isChild=false;
    }
    this.children=[];
};
DynLayer.prototype.deleteFromParent=function() {
	this.parent.deleteChild(this)
};
DynLayer.prototype.getInnerHTML=function() {
	var s="";
	if (this.html!=null) s+=this.html;
	for (var i=0;i<this.children.length;i++) s+=this.getOuterHTML(this.children[i]);
	return s;
};
if (is.ns4) {
	DynLayer.prototype.getOuterHTML=function(dlyr) {
		var s='\n<layer id="'+dlyr.id+'"';
		if (dlyr.visible==false) s+=' visibility="hide"';
		if (dlyr.x!=null) s+=' left='+dlyr.x;
		if (dlyr.y!=null) s+=' top='+dlyr.y;
		if (dlyr.w!=null) s+=' width='+dlyr.w;
		if (dlyr.h!=null) s+=' height='+dlyr.h;
		if (dlyr.clip) s+=' clip="'+dlyr.clip[3]+','+dlyr.clip[0]+','+dlyr.clip[1]+','+dlyr.clip[2]+'"';
		else if (dlyr.w!=null && dlyr.h!=null);
			s+=' clip="0,0,'+dlyr.w+','+dlyr.h+'"';

		if (dlyr.bgImage!=null)	s+=' background="'+dlyr.bgImage+'"';
		if (dlyr.bgColor!=null)	s+=' bgcolor="'+dlyr.bgColor+'"';
		s+='>';
		if (dlyr.html!=null) s+=dlyr.html;
		for (var i=0; i<dlyr.children.length; i++) s+=this.getOuterHTML(dlyr.children[i]);
		s+='</layer>';
		return s;
	};
} else {
	DynLayer.prototype.getOuterHTML=function(dlyr) {
		var s='<div id="'+dlyr.id+'" style="';
		if (dlyr.visible==false) s+=' visibility:hidden;';
		if (dlyr.x!=null) s+=' left:'+dlyr.x+'px;';
		if (dlyr.y!=null) s+=' top:'+dlyr.y+'px;';
		if (dlyr.w!=null) s+=' width:'+dlyr.w+'px;';
		if (dlyr.h!=null) s+=' height:'+dlyr.h+'px;';
		if (dlyr.clip) s+=' clip:rect('+dlyr.clip[0]+'px '+dlyr.clip[1]+'px '+dlyr.clip[2]+'px '+dlyr.clip[3]+'px);';
		else if (dlyr.w!=null && dlyr.h!=null);
			s+=' clip:rect(0px '+dlyr.w+'px '+dlyr.h+'px 0px);';
		if (dlyr.bgImage!=null)	s+=' background-image:url('+dlyr.bgImage+');' 
		if (dlyr.bgColor!=null)	s+=' background:'+dlyr.bgColor+';'
		if (is.ie55 && dlyr.bgImage==null && dlyr.html==null) s+=' background-image:url(javascript:null);' 
		s+=' position:absolute;">';
		if (dlyr.html!=null) s+=dlyr.html;
		for (var i=0; i<dlyr.children.length; i++) s+=this.getOuterHTML(dlyr.children[i]);
		s+='</div>';
		return s;
	};
};
DynLayer.prototype.setStyle=function (s) {
	var id=s.id,x=s.left,y=s.top,w=s.width,h=s.height,i=s.backgroundImage,c=s.backgroundColor,v=s.visibility,z=s.zIndex;
	if (!this.id && !this.created) this.id=id||"JSDynLayer"+(DynLayer.nullCount++);
	if (x!=null||y!=null) this.moveTo(x,y);
	if (w!=null||h!=null) this.setSize(w,h);;
	if (i!=null) this.setBgImage(i);
	if (c!=null) this.setBgColor(c);
	if (z!=null) this.setZIndex(z);
	this.setVisible(v!='hidden');
};
DynLayer.prototype.moveTo=function(x,y) {
	this.x=x!=null ? x : this.x;
	this.y=y!=null ? y : this.y;
	if (this.css==null) return;
	if (is.ns) {
		this.css.left=this.x;
		this.css.top=this.y;
	}
	else {
		this.css.pixelLeft=this.x;
		this.css.pixelTop=this.y;
	}
	this.invokeEvent('move');
};
DynLayer.prototype.moveBy=function(x,y) {
	this.moveTo(this.x+x,this.y+y);
};
DynLayer.prototype.setX=function(x) {
	this.moveTo(x,null);
};
DynLayer.prototype.setY=function(y) {
	this.moveTo(null,y);
};
DynLayer.prototype.getX=function() {
	return this.x
};
DynLayer.prototype.getY=function() {
	return this.y
};
DynLayer.prototype.getPageX=function() {
	if (this.css==null) return;
	if (is.ns4) return this.css.pageX;
	else return (this.isChild)? this.parent.getPageX()+this.x : this.x;
};
DynLayer.prototype.getPageY=function() {
	if (this.css==null) return;
	if (is.ns4) return this.css.pageY;
	else return (this.isChild)? this.parent.getPageY()+this.y : this.y;
};
DynLayer.prototype.setPageX=function(x) {
	if (this.css==null) return;
	if (is.ns4) this.css.pageX=x;
	if (is.ie) {
		if (this.isChild) this.setX(this.parent.getPageX()-x);
		else this.setX(x);
	}
	this.getX();
	this.invokeEvent('move');
};
DynLayer.prototype.setPageY=function(y) {
	if (this.css==null) return;
	if (is.ns4) this.css.pageY=y;
	if (is.ie) {
		if (this.isChild) this.setY(this.parent.getPageY()-y);
		else this.setY(y);
	}
	this.getY();
	this.invokeEvent('move');
};
DynLayer.prototype.setVisible=function(b) {
	this.visible=b;
	if (this.css==null) return;
	this.css.visibility = b? "inherit" : (is.ns4?"hide":"hidden");
};
DynLayer.prototype.getVisible=function() {
	return this.visible;
};
DynLayer.prototype.setZIndex=function(z) {
	this.z=z;
	if (this.css==null) return;
	this.css.zIndex=z;
};
DynLayer.prototype.getZIndex=function() {
	return this.z;
};
DynLayer.prototype.setBgImage=function(path) {
	this.bgImage=path;
	if (this.css==null) return;
	if (is.ns4) {
		this.elm.background.src=path;
		if (!path) this.setBgColor(this.getBgColor());
	}
	else this.css.backgroundImage='url('+path+')';
};
DynLayer.prototype.getBgImage=function() {
	return this.bgImage;
};
DynLayer.prototype.setBgColor=function(color) {
	if (color==null && !is.ns4) color='transparent';
	this.bgColor=color;
	if (this.css==null) return;
	if (is.ns4) this.doc.bgColor=color;
	else this.css.backgroundColor=color;
};
DynLayer.prototype.getBgColor=function() {
	return this.bgColor;
};
DynLayer.prototype.setHTML=function(html,noevt) {
	this.html=html?html:'';
	if (this.css==null) return;
	this.invokeEvent("beforeload");
	this.elm.innerHTML=html;
	if (is.ns4) {
		this.doc.open();
		this.doc.write(html);
		this.doc.close();
        	for (var i in this.doc.images) this.doc.images[i].lyrobj=this;
        	for (i=0;i<this.doc.links.length;i++) this.doc.links[i].lyrobj=this;
	}
	else if (is.ns5) {
		while (this.elm.hasChildNodes()) this.elm.removeChild(this.elm.firstChild);
		var r=this.elm.ownerDocument.createRange();
		r.selectNodeContents(this.elm);
		r.collapse(true);
		var df=r.createContextualFragment(html);
		this.elm.appendChild(df);
        	for (var i in this.doc.images) this.doc.images[i].lyrobj=this.elm;
	}
	else { }
	if (noevt!=false) this.invokeEvent("load");
};
DynLayer.prototype.getHTML=function() {
	return this.html;
};
DynLayer.prototype.setSize = function(w,h,noevt) {
	this.setWidth(w,false);
	this.setHeight(h,false);
	if (noevt!=false) this.invokeEvent('resize');
};
DynLayer.prototype.setWidth=function(w,noevt) {
	this.w=(w==null)?this.w:w<0?0:w;
	if (this.w==null) return;
	if (this.css!=null) {
		if (is.ns4) this.css.clip.width = this.w;
		else {
			this.css.width = this.w;
			this.css.clip = 'rect(0px '+(this.w||0)+'px '+(this.h||0)+'px 0px)';
		}
	}
    if (noevt!=false) this.invokeEvent('resize');
};
DynLayer.prototype.setHeight=function(h,noevt) {
	this.h=(h==null)?this.h:h<0?0:h;
	if (this.h==null) return;
	if (this.css!=null) {
		if (is.ns4) this.css.clip.height = this.h;
		else {
			this.css.height = this.h;
			this.css.clip = 'rect(0px '+(this.w||0)+'px '+(this.h||0)+'px 0px)';
		}
	}
	if (noevt!=false) this.invokeEvent('resize');
};
DynLayer.prototype.getWidth=function() {
	return this.w;
};
DynLayer.prototype.getHeight=function() {
	return this.h;
};
DynLayer.prototype.getContentWidth=function() {
	if (this.elm==null) return 0;
	else if (is.ns4) return this.doc.width;
	else if (is.ns5) return this.elm.offsetWidth;
	else if (is.ie) return parseInt(this.elm.scrollWidth);
	else return 0;
};
DynLayer.prototype.getContentHeight=function() {
	if (this.elm==null) return 0;
	else if (is.ns4) return this.doc.height;
	else if (is.ns5) return this.elm.offsetHeight;
	else if (is.ie) return parseInt(this.elm.scrollHeight);
	else return 0;
};
DynLayer.prototype.setClip=function(clip) {
    var cc=this.getClip();
    for (var i in clip) if (clip[i]==null) clip[i]=cc[i];
    this.clip=clip;
	if (this.css==null) return;
    var c=this.css.clip;
    if (is.ns4) c.top=clip[0], c.right=clip[1], c.bottom=clip[2], c.left=clip[3];
    else if (is.ie || is.ns5) this.css.clip="rect("+clip[0]+"px "+clip[1]+"px "+clip[2]+"px "+clip[3]+"px)";
};
DynLayer.prototype.getClip=function() {
	if (this.css==null || !this.css.clip) return [0,0,0,0];
	var c = this.css.clip;
	if (c) {
		if (is.ns4) return [c.top,c.right,c.bottom,c.left];
		else {
			if (c.indexOf("rect(")>-1) {
				c=c.split("rect(")[1].split(")")[0].split("px");
				for (var i in c) c[i]=parseInt(c[i]);
				return [c[0],c[1],c[2],c[3]];
			} else return [0,this.w,this.h,0];
		}
	}
};
DynLayer.prototype.invokeEvent=function() {};