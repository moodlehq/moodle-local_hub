/*
   Dynacore Widget Pack
   Adapted by richard of www.richardinfo.com
   SkinButton
*/
function CoreSkinButton(x,y,w,h,caption,theme) {
	this.DynLayer = DynLayer
	this.DynLayer()
	this.id = "CoreSkinButton"+(CoreSkinButton.Count++)

	this.moveTo(x||0,y||0)
	this.setSize(w||128,h||36)

	this.theme=theme||''
	this.lcaption=new DynLayer()
	this.caption=caption||''
	this.lcaption.setVisible(true)
	this.levents=new DynLayer()
	this.levents.setVisible(true)

	this.setVisible(false)

	var l=new EventListener(this)
	l.oncreate=function(e) {
		if (e.getSource()!=e.getTarget()) return
		o=e.getTarget()
		o.setHTML('<img src="'+o.theme+o.buttonup+'" name="'+o.id+'btn"width="'+o.w+'" height="'+o.h+'">')
		o.lcaption.moveTo(2,2)
		o.lcaption.setSize(o.w-4,o.h-4)
		o.levents.moveTo(0,0)
		o.levents.setSize(o.w,o.h)

		o.lcaption.setHTML('<span class="default">'+o.caption+'</span>')

		o.levents.addEventListener(l)
		o.addChild(o.lcaption)
		o.addChild(o.levents)
	}
	l.onmousedown=function(e) {
		o=e.getTarget()
		o.doc.images[o.id+"btn"].src=o.theme+o.buttondown
		o.onClick()
		e.setBubble(false)
	}
	l.onmouseup=function(e) {
		o=e.getTarget()
		o.doc.images[o.id+"btn"].src=o.theme+o.buttonup
		o.onRelease()
		e.setBubble(false)
	}
	l.onmouseover=function(e) {
		o=e.getTarget()
		o.doc.images[o.id+"btn"].src=o.theme+o.buttonselected
		o.lcaption.setHTML('<span class="over">'+o.caption+'</span>')
		o.onMouseOver()
		e.setBubble(false)
	}
	l.onmouseout=function(e) {
		o=e.getTarget()
		o.doc.images[o.id+"btn"].src=o.theme+o.buttonup
		o.lcaption.setHTML('<span class="default">'+o.caption+'</span>')
		o.onMouseOut()
		e.setBubble(false)
	}
	this.addEventListener(l)

	return this
}
CoreSkinButton.Count=0
CoreSkinButton.prototype = new DynLayer()
CoreSkinButton.prototype.getSubClass=function() { return CoreSkinButton }
CoreSkinButton.prototype.onClick=function() {}
CoreSkinButton.prototype.onMouseOver=function() {}
CoreSkinButton.prototype.onMouseOut=function() {}
CoreSkinButton.prototype.onRelease=function() {}
CoreSkinButton.prototype.buttonup='button-up.gif'
CoreSkinButton.prototype.buttondown='button-down.gif'
CoreSkinButton.prototype.buttonselected='button-selected.gif'
CoreSkinButton.prototype.setCaption=function(caption) {
	this.caption=caption
	this.lcaption.setHTML('<span class="default">'+this.caption+'</span>')
}
