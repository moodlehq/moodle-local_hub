/*
   Dynacore Widget Pack
   SkinWindow
*/
function CoreSkinWindow(x,y,w,h,caption,theme) {
	this.DynLayer = DynLayer
	this.DynLayer()
	this.id = "CoreSkinWindow"+(CoreSkinWindow.Count++)

	this.moveTo(x||0,y||0)
	this.setSize(w||128,h||36)

	this.caption=caption||''
	this.theme=theme||''
	this.setVisible(false)

	this.capbar=new DynLayer()
	this.capbarCover=new DynLayer()
	this.lLeft=new DynLayer()
	this.lTop=new DynLayer()
	this.lRight=new DynLayer()
	this.lBottom=new DynLayer()
	this.canvas=new DynLayer()
	this.closeButton=new DynLayer()
	this.minimizeButton=new DynLayer()
	this.maximizeButton=new DynLayer()

	this.leftItems=new Array()
	this.topItems=new Array()
	this.rightItems=new Array()
	this.bottomItems=new Array()

	var l=new EventListener(this)
	l.oncreate=function(e) {
		var o=e.getTarget()
		o.capbar.moveTo(o.left,0)
		o.capbarCover.moveTo(o.left,0)

		o.resize=new DynLayer(null,o.w-o.right,o.h-o.bottom,o.right,o.bottom)

		o.capbar.addEventListener(o.moveEvent)
		o.capbar.addEventListener(o.dblClickEvent)

		if (o.resizeable) o.resize.addEventListener(o.resizeEvent)

		o.closeButton.addEventListener(o.closeEvent)
		o.minimizeButton.addEventListener(o.minimizeEvent)
		o.maximizeButton.addEventListener(o.maximizeEvent)

		o.canvas.moveTo(o.left>>1,o.top>>1)

		o.lLeft.moveTo(0,0)
		o.lLeft.setVisible(true)
		o.lTop.setVisible(true)
		o.lRight.setVisible(true)
		o.lBottom.setVisible(true)

		o.CoreSkinwindowaddChild(o.canvas)
		o.CoreSkinwindowaddChild(o.lLeft)
		o.CoreSkinwindowaddChild(o.lTop)
		o.CoreSkinwindowaddChild(o.lRight)
		o.CoreSkinwindowaddChild(o.lBottom)
		o.CoreSkinwindowaddChild(o.capbarCover)
		o.CoreSkinwindowaddChild(o.capbar)
		o.CoreSkinwindowaddChild(o.resize)
		o.CoreSkinwindowaddChild(o.closeButton)
		o.CoreSkinwindowaddChild(o.minimizeButton)
		o.CoreSkinwindowaddChild(o.maximizeButton)

		o.repaint()

		DragEvent.enableDragEvents(o.resize)
		if (o.moveable) DragEvent.enableDragEvents(o.capbar)
	}
	this.addEventListener(l)

	this.minimizeEvent=new EventListener(this)
	this.minimizeEvent.onmouseup=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		if (!o.restoreX) o.minimize()
		else o.restore()
		o.onMinimize()
	}

	this.maximizeEvent=new EventListener(this)
	this.maximizeEvent.onmouseup=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		if (!o.restoreX) o.maximize()
		else o.restore()
	}

	this.resizeEvent=new EventListener(this)
	this.resizeEvent.ondragstart=function(e) {
		o=e.getTarget()
		if(is.ns)o.setBgColor('#c0c0c0')
		o.css.borderWidth="2px"
		o.css.borderColor="black"
	    o.css.borderStyle="solid"
		}
	this.resizeEvent.ondragmove=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		o.w=e.pageX-o.x
		o.h=e.pageY-o.y
		if (o.w<o.minW) o.w=o.minW
		if (o.h<o.minH) o.h=o.minH
		o.setSize(o.w,o.h)
		o.resize.moveTo(o.w-o.right,o.h-o.bottom)
	}
	this.resizeEvent.ondragend=function(e) {
		var o=e.getTarget()
		o.setBgColor(null)
		o.css.borderWidth="0px"
		o.repaint()
	}

	this.moveEvent=new EventListener(this)
	this.moveEvent.ondragmove=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		o.setZIndex(0)
		o.moveTo(e.pageX-e.x,e.pageY-e.y)
		o.capbar.moveTo(o.left,0)
	}

	this.closeEvent=new EventListener(this)
	this.closeEvent.onmouseup=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		o.setVisible(false)
		o.onClose()
	}
	this.dblClickEvent=new EventListener(this)
	this.dblClickEvent.ondblclick=function(e) {
		e.setBubble(false)
		var o=e.getTarget()
		if (!o.restoreX) o.maximize()
		else o.restore()
	}
	return this
}
CoreSkinWindow.Count=0
CoreSkinWindow.prototype=new DynLayer()
CoreSkinWindow.prototype.getSubClass=function() { return CoreSkinWindow }
CoreSkinWindow.prototype.left=2
CoreSkinWindow.prototype.top=2
CoreSkinWindow.prototype.right=2
CoreSkinWindow.prototype.bottom=2
CoreSkinWindow.prototype.minW=100
CoreSkinWindow.prototype.minH=100
CoreSkinWindow.prototype.caption=''
CoreSkinWindow.prototype.resizeable=false
CoreSkinWindow.prototype.moveable=true
CoreSkinWindow.prototype.cbx=0
CoreSkinWindow.prototype.cby=0
CoreSkinWindow.prototype.mibx=0
CoreSkinWindow.prototype.miby=0
CoreSkinWindow.prototype.mabx=0
CoreSkinWindow.prototype.maby=0
CoreSkinWindow.prototype.restoreX=null
CoreSkinWindow.prototype.restoreY=null
CoreSkinWindow.prototype.restoreW=null
CoreSkinWindow.prototype.restoreH=null
CoreSkinWindow.prototype.onMinimize=function() { }
CoreSkinWindow.prototype.onMaximize=function() { }
CoreSkinWindow.prototype.onClose=function() { }
CoreSkinWindow.prototype.setCaption=function(caption){
	this.caption=caption
	if (this.capbarCover.created) this.capbarCover.setHTML('<span class="default">'+this.caption+'</span>')
}
CoreSkinWindow.prototype.setCloseButton=function(x,y,w,h){
	this.cbx=x||0
	this.cby=y||0
	this.closeButton.setSize(w||16,h||16)
	this.closeButton.moveTo(this.cbx,this.cby)
	this.closeButton.setHTML('<img src="'+this.theme+'window-close-a.gif" width="'+(w||16)+'" height="'+(h||16)+'" alt="Close window">')
	this.closeButton.setVisible(true)
}
CoreSkinWindow.prototype.setMinimizeButton=function(x,y,w,h){
	this.mibx=x||16
	this.miby=y||0
	this.minimizeButton.setSize(w||16,h||16)
	this.minimizeButton.moveTo((this.w-this.mibx),this.miby)
	this.minimizeButton.setHTML('<img src="'+this.theme+'window-minimize-a.gif" width="'+(w||16)+'" height="'+(h||16)+'" alt="Minimize">')
	this.minimizeButton.setVisible(true)
}
CoreSkinWindow.prototype.setMaximizeButton=function(x,y,w,h){
	this.mabx=x||16
	this.maby=y||0
	this.maximizeButton.setSize(w||16,h||16)
	this.maximizeButton.moveTo((this.w-this.mabx),this.maby)
	this.maximizeButton.setHTML('<img src="'+this.theme+'window-maximize-a.gif" width="'+(w||16)+'" height="'+(h||16)+'" alt="Maximize">')
	this.maximizeButton.setVisible(true)
}
CoreSkinWindow.prototype.setResizeable=function(b) {
	if (b==null) this.resizeable=true
	else this.resizeable=b
	if (this.created) {
		if (b) this.resize.addEventListener(this.resizeEvent)
		else this.resize.removeEventListener(this.resizeEvent)
	}
}
CoreSkinWindow.prototype.setMoveable=function(b) {
	this.moveable=b
	if (this.created) {
		if (b) DragEvent.enableDragEvents(this.capbar)
		else DragEvent.disableDragEvents(this.capbar)
	}
}
CoreSkinWindow.prototype.restore=function() {
	if (!this.restoreX) return
	this.setSize(this.restoreW,this.restoreH)
	this.moveTo(this.restoreX,this.restoreY)
	this.restoreX=null
	this.setVisible(true)
	this.repaint()
}
CoreSkinWindow.prototype.minimize=function() {
	this.restoreX=this.x
	this.restoreY=this.y
	this.restoreW=this.w
	this.restoreH=this.h
	this.setVisible(false)
	this.repaint()
	this.onMinimize()
}
CoreSkinWindow.prototype.maximize=function() {
	this.restoreX=this.x
	this.restoreY=this.y
	this.restoreW=this.w
	this.restoreH=this.h
	this.setSize(this.parent.w,this.parent.h)
	this.moveTo(0,0)
	this.repaint()
	this.onMaximize()
}
CoreSkinWindow.prototype.close=function() {
	this.setVisible(false)
	this.onClose()
}
CoreSkinWindow.prototype.CoreSkinwindowaddChild=DynLayer.prototype.addChild
CoreSkinWindow.prototype.addChild=function(wdg) { this.canvas.addChild(wdg) }
CoreSkinWindow.prototype.addLeftImage=function(w,h,stretch) {
	i=this.leftItems.length
	this.leftItems[i]=new Array()
	this.leftItems[i].stretch=stretch
	this.leftItems[i].w=w||0
	this.leftItems[i].h=h||0
	if (w>this.left) this.left=w
}
CoreSkinWindow.prototype.addTopImage=function(w,h,stretch) {
	i=this.topItems.length
	this.topItems[i]=new Array()
	this.topItems[i].w=w||0
	this.topItems[i].h=h||0
	this.topItems[i].stretch=stretch
	if (h>this.top) this.top=h
}
CoreSkinWindow.prototype.addRightImage=function(w,h,stretch) {
	i=this.rightItems.length
	this.rightItems[i]=new Array()
	this.rightItems[i].w=w||0
	this.rightItems[i].h=h||0
	this.rightItems[i].stretch=stretch
	if (w>this.right) this.right=w
}
CoreSkinWindow.prototype.addBottomImage=function(w,h,stretch) {
	i=this.bottomItems.length
	this.bottomItems[i]=new Array()
	this.bottomItems[i].w=w||0
	this.bottomItems[i].h=h||0
	this.bottomItems[i].stretch=stretch
	if (h>this.bottom) this.bottom=h
}
CoreSkinWindow.prototype.getImageHeight=function(ar,el,total) {
	if (ar=='left') {
		var newH=total
		for (var i=0; i<this.leftItems.length; i++) if (i!=el) newH-=this.leftItems[i].h
		this.leftItems[el].h=newH
	} else {
		var newH=total
		for (var i=0; i<this.rightItems.length; i++) if (i!=el) newH-=this.rightItems[i].h
		this.rightItems[el].h=newH
	}
}
CoreSkinWindow.prototype.getImageWidth=function(ar,el,total) {
	if (ar=='top') {
		var newW=total
		for (var i=0; i<this.topItems.length; i++) if (i!=el) newW-=this.topItems[i].w
		this.topItems[el].w=newW
	} else {
		var newW=total
		for (var i=0; i<this.bottomItems.length; i++) if (i!=el) newW-=this.bottomItems[i].w
		this.bottomItems[el].w=newW
	}
}
CoreSkinWindow.prototype.repaint=function() {
	this.lLeft.setSize(this.left,this.h)
	this.lTop.setSize(this.w-(this.left+this.right),this.top)
	this.lRight.setSize(this.right,this.h)
	this.lBottom.setSize(this.w-(this.left+this.right),this.bottom)
	this.lBottom.moveTo(this.left,this.h-this.bottom)
	this.lRight.moveTo(this.w-this.right,0)
	this.lTop.moveTo(this.left,0)
	this.canvas.setSize(this.w-((this.left+this.right)>>1),this.h-((this.top+this.bottom)>>1))
	this.capbar.setSize(this.w-(this.left+this.right),this.top)
	this.capbarCover.setSize(this.w-(this.left+this.right),this.top)
	this.capbarCover.setHTML('<span class="default">'+this.caption+'</span>')

	this.closeButton.moveTo(this.mbx,this.mby)
	this.minimizeButton.moveTo((this.w-this.mibx),this.miby)
	this.maximizeButton.moveTo((this.w-this.mabx),this.maby)

	tmp=''
	for (var i=0;i<this.leftItems.length;i++) {
		if (this.leftItems[i].stretch) this.getImageHeight('left',i,this.lLeft.h)
		tmp+='<img src="'+this.theme+'window-left-'+parseInt(i+1)+'.gif" width="'+this.leftItems[i].w+'" height="'+this.leftItems[i].h+'"><br>'
	}
	this.lLeft.setHTML(tmp)

	tmp='<table border=0 cellpadding=0 cellspacing=0 width=100%><td nowrap>'
	for (var i=0;i<this.topItems.length;i++) {
		if (this.topItems[i].stretch) this.getImageWidth('top',i,this.lTop.w)
		tmp+='<img src="'+this.theme+'window-top-'+parseInt(i+1)+'.gif" width="'+this.topItems[i].w+'" height="'+this.topItems[i].h+'">'
	}
	this.lTop.setHTML(tmp+'</td></table>')

	tmp=''
	for (var i=0;i<this.rightItems.length;i++) {
		if (this.rightItems[i].stretch) this.getImageHeight('right',i,this.lRight.h)
		tmp+='<img src="'+this.theme+'window-right-'+parseInt(i+1)+'.gif" width="'+this.rightItems[i].w+'" height="'+this.rightItems[i].h+'"><br>'
	}
	this.lRight.setHTML(tmp)

	tmp='<table border=0 cellpadding=0 cellspacing=0 width=100%><td nowrap>'
	for (var i=0;i<this.bottomItems.length;i++) {
		if (this.bottomItems[i].stretch) this.getImageWidth('bottom',i,this.lBottom.w)
		tmp+='<img src="'+this.theme+'window-bottom-'+parseInt(i+1)+'.gif" width="'+this.bottomItems[i].w+'" height="'+this.bottomItems[i].h+'">'
	}
	this.lBottom.setHTML(tmp+'</td></table>')
	this.invokeEvent("repaint");
}