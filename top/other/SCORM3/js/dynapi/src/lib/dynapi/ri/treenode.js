function TreeNode(id,x,y,w,h,caption) {
	this.DynLayer=DynLayer
	this.DynLayer(id,x||0,y||0,w||128,h||18)
	
	this.items=new Array

	this._caption=caption||''
	this._rootNode=this
	this._isBaseNode=true

	this.setVisible(false)

	this.addEventListener(TreeNode.listener)
	return this
}
TreeNode.prototype=new DynLayer()
TreeNode.prototype.getSubClass=function() { return TreeNode }
TreeNode.prototype._rootNode=null
TreeNode.prototype._isBaseNode=true
TreeNode.prototype.expanded=false
TreeNode.prototype.selected=null
TreeNode.imgExpand=DynAPI.librarypath+'dynacore/img/gui.treenode.expand.gif'
TreeNode.imgCollapse=DynAPI.librarypath+'dynacore/img/gui.treenode.collapse.gif'
TreeNode.imgEmpty=DynAPI.librarypath+'dynacore/img/empty.gif'
TreeNode.listener=new EventListener()
TreeNode.listener.onprecreate=function(e) {
	var o=e.getSource()
	o.events=new EventListener(o)
	o.events.onmousedown=function(e) {
		var o=e.getTarget()
		if (e.x<17 && o.items.length>0) {
			if (o.expanded) o.invokeEvent('collapse',null,false)
			else o.invokeEvent('expand',null,false)
			o.invokeEvent('repaint')
		} else o.privOnSelect()
		e.setBubble(true)
	}
	o.events.onmouseover=function(e) {
		var o=e.getTarget()
		if (o.onMouseOver) o.onMouseOver()
		e.setBubble(false)
	}
	o.events.onmouseout=function(e) {
		var o=e.getTarget()
		if (o.onMouseOut) o.onMouseOut()
		e.setBubble(false)
	}
	o.events.ondblclick=function(e) {
		var o=e.getTarget()
		if (e.x>16 && o.items.length>0) {
			if (o.expanded) o.invokeEvent('collapse',null,false)
			else o.invokeEvent('expand',null,false)
		}
		e.setBubble(false)
	}

	var nodecont=''
	if (o.items.length>0) nodecont='<img src=\''+TreeNode.imgExpand+'\' valign=bottom name=\''+o.id+'img\'>'
	else nodecont='&nbsp;'
	
	o.setHTML(nodecont)
	o.canvas=new DynLayer(null,16,0,o.w-16,18)
	o.canvas.setHTML(DynAPI.theme.write(o._caption))
	o._events=new DynLayer(null,0,0,o.w,18)
	o._events.addEventListener(o.events)
	o.addChild(o.canvas)
	o.addChild(o._events)
	o.setVisible(true)
	o.invokeEvent('repaint')
}
TreeNode.listener.onrepaint=function(e) {
	var o=e.getSource()
	var tmph=18
	var tmpw=o.w
	var y=18
	for (var i=0; i<o.items.length; i++) {
		if (o.expanded) {
			o.items[i].moveTo(null,y)
			o.items[i].invokeEvent('repaint')
			tmph+=o.items[i].h
			if (o.items[i].getPageX()+o.items[i].w>tmpw) tmpw=o.items[i].getPageX()+o.items[i].w
		}
		y+=o.items[i].h
	}
	o.setSize(tmpw,tmph)
}
TreeNode.listener.onexpand=function(e,recursive) {
	var o=e.getSource()
	var tmph=18
	if (o.children.length==2) for (var i=0; i<o.items.length;i++) o.addChild(o.items[i])
	for (var i=0;i<o.items.length;i++){
		if (recursive) o.items[i].invokeEvent('recursive',null,true)
		tmph+=o.items[i].h
	}
	o.expanded=true
	o.setSize(o.w,tmph)
	o.doc.images[o.id+"img"].src=TreeNode.imgCollapse
	o._rootNode.invokeEvent('repaint')
}
TreeNode.listener.oncollapse=function(e,recursive) {
	var o=e.getSource()
	for (var i=0;i<o.items.length;i++) if (recursive) o.items[i].invokeEvent('collapse',null,true)
	o.expanded=false
	o.setSize(o.w,18)
	o.doc.images[o.id+"img"].src=TreeNode.imgExpand
	o._rootNode.invokeEvent('repaint')
}
TreeNode.prototype.setCaption=function(cap) {
	this._caption=cap
	if (this.created) this.canvas.setHTML(cap)
}
TreeNode.prototype.add=function(caption){
	var i=this.items.length
	this.items[i]=new TreeNode(this.id+'node'+parseInt(i),18,18+(i*18),this.w-16,18,caption)
	this.items[i]._isBaseNode=false
	this.items[i]._rootNode=this._rootNode
	return this.items[i]
}
TreeNode.prototype.privOnSelect=function() {
	var oldnode=null
	if (this._rootNode.selected!=null) {
		oldnode=this._rootNode.selected
		this._rootNode.selected.canvas.setBgColor(null)
		this._rootNode.selected.canvas.setHTML(DynAPI.theme.write(this._rootNode.selected._caption))
		if (this._rootNode.selected.onDeselect) this._rootNode.selected.onDeselect(this)
	}
	this.canvas.setHTML(DynAPI.theme.write(this._caption,'selected'))
	this.canvas.setBgColor(DynAPI.theme.scheme.bgSelected)
	if (this.onSelect) this.onSelect(oldnode)
	this._rootNode.selected=this
}	