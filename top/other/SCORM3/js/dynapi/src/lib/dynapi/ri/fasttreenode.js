/*	Fast skinable treeview widget by richard from www.richardinfo.com
 	Based on CoreSkinTreenode by Pascal Bestebroer
 	for DynAPI Copyright (CriStatus) 2000 Dan Steinman
 	Redistributable under the terms of the GNU Library General Public License

	Changes added:
   	* Large speed improvement by layer resizing
   	* Eliminated a few for loops doing nothing.
   	* Node in same level close onnodeopen
   	* Is_selected color for content layer
   	* You can hide selector on all content by setting color to ''
   	* One click selection
   	* Is_selected color also on image select
   	* Images in content nodes, if no image is selected, empty.gif is NOT used (resources),
   	  but links are aligned with &nbsp;'s
   	* Option "Step" sets tree style from step=0 to step="way off the screen".
   	* You can mix links and HTML content at will, selector only shows on links
   	* You can call a node to open, simulating clicks (for bookmarking)


	Requires:
	var theme = "images/themes/iceBlue/"
	document.write('<script language="Javascript" src="'+theme+'style.js"><'+'/'+'script>');
*/
var myHeight=0
function RiFastSkinTreenode(x,y,w,pcontents,theme,step,url) { // DPV Added new URL
	
	this.DynLayer = DynLayer
	this.DynLayer()
	//alert ("x="+x+"y="+y);
	this.id = "RiFastSkinTreenode"+(RiFastSkinTreenode.Count++)
	// DPV --- Added new URL
	this.url = null
	if ( arguments.length == 7 ) this.url = url
	
	this.caption=pcontents||''
	this.theme=theme||''
	this.items=new Array
	
	this.moveTo(x||0,y||0)
	this.setSize(w||128,18)
	this.setVisible(false)
	this.offset=0
	this.parent=null
	
	this.step=step||0
	this.subNodeId=0
	this.rootNode=this
	this.isBaseNode=true
	this.contentNodeContent=''

	var l=new EventListener(this)
	
	l.oncreate=function(e) {
		var o=e.getTarget()
		
		var nodecont=''
		if (o.items.length>0) {
			nodecont='<img src="'+o.theme+o.imgExpand+'" width=15 height=15 name="'+o.id+'sw";>'
		o.layerimg=new DynLayer(null,0,0,o.w,18);//vg added another layer to which added the image 
		o.layerimg.setHTML(nodecont)  //set the html for layerimg to nodecont instead of o.setHTML 
		
		o.canvas=new DynLayer(null,21,2,o.w-18,16)
		//alert ("nodecanvassize"+o.canvas)
		
		
// DPV		o.canvas.setHTML('<span class="OutstartTreeNode">'+o.caption+'</span>')
		//alert("o.caption"+o.caption)
		
		if ( o.url != null ) o.canvas.setHTML('<a class="OutstartTreeNode" href="' + o.url + '">'+o.caption+'</a>')
		else o.canvas.setHTML('<span class="OutstartTreeNode">'+o.caption+'</span>')
// DPV
//		o.levents=new DynLayer(null,0,0,o.w,18)
		o.levents=new DynLayer(null,0,0,16,18)
		o.levents.addEventListener(o.events)
		o.layerimg.addEventListener(o.events)
		for (var i=0; i<o.items.length;i++) o.addChild(o.items[i])
		o.addChild(o.layerimg)
		o.addChild(o.canvas)
		//o.addChild(o.levents)
		o.repaint(1)
		}else{
		//nodecont='<img src="'+o.theme+o.imgEmpty+'" width=16 height=16 name="'+o.id+'sw">'

		o.setHTML(nodecont)												//<add> this sets height for content nodes
		o.canvas=new DynLayer(null,0,0,o.w-(o.step-5),18)
		
		// DPV
		//o.canvas.setHTML('<span class="OutstartTreeNode">'+o.caption+'</span>')
		if ( o.url != null ) o.canvas.setHTML('<a class="OutstartTreeNode" href="' + o.url + '">'+o.caption+'</a>')
		else	o.canvas.setHTML('<span class="OutstartTreeNode">'+o.caption+'</span>')
		// DPV
		o.selectLayer=new DynLayer(null,20,-20,o.w-(o.step+15),16)	//is selectorheight-2
		o.canvas.addEventListener(o.selEvents)
		o.addChild(o.selectLayer)
		o.addChild(o.canvas)

		o.offsetHeight=o.canvas.getContentHeight()
		//alert("offsetHeight="+o.offsetHeight);
		o.canvas.setHeight(o.offsetHeight)
		o.repaint(0)														//</add>
		}
	}
	this.addEventListener(l)

	this.selEvents=new EventListener(this)	//<add>
	
	
		this.selEvents.onmousedown=function(e) {
			o=e.getTarget()
			var Text, scrollOffset
			   	if (is.ns) {Text = e.pageY;scrollOffset=window.pageYOffset;
			   	}else{ Text=event.y;scrollOffset=document.body.scrollTop;}
				  	var steps=Math.floor(((Text-2)-(o.getPageY()-scrollOffset))/18)	//was text-2
					var dist=steps*18
						o.selectLayer.moveTo(o.selectLayer.x,dist)	//was dist-2
						if(o.showSelect)o.selectLayer.setBgColor(o.selectedColor)	//to do call fadein()

	}																	//</add>+(steps*1.5)


	this.events=new EventListener(this)
			
	this.events.onmousedown=function(e) {
		o=e.getTarget()
		//alert("source="+e.getId());
		//alert("pagex="+e.pageX );
		//alert("pagey="+e.pageY);
		//alert("layerx="+o.layerX);
		//alert("layery="+o.layerY);
		
		if (o.items.length>0) {
			if (o.expanded) o.collapse()
			else o.expand()
			o.repaint(0)
		o.privOnSelect()
		e.setBubble(true)
		}
	}
	/*  DPV
	this.events.onmouseover=function(e) {
		o=e.getTarget()
		o.canvas.setHTML('<span class="OutstartTreeNodeOver">'+o.caption+'</span>')
		o.onMouseOver()
		e.setBubble(false)
	}
	this.events.onmouseout=function(e) {
		o=e.getTarget()
		o.canvas.setHTML('<span class="OutstartTreeNode">'+o.caption+'</span>')
		o.onMouseOut()
		e.setBubble(false)
	}
	*/
	return this
}
RiFastSkinTreenode.Count=0
RiFastSkinTreenode.prototype = new DynLayer()
RiFastSkinTreenode.prototype.expanded=false
RiFastSkinTreenode.prototype.rootNode=null
RiFastSkinTreenode.prototype.isBaseNode=true
RiFastSkinTreenode.prototype.selected=null
//RiFastSkinTreenode.prototype.contentNodeContent=''
RiFastSkinTreenode.prototype.selectedColor=''	//'#0000c0'
RiFastSkinTreenode.prototype.imgExpand='treenode-expand.gif'
RiFastSkinTreenode.prototype.imgCollapse='treenode-collapse.gif'
RiFastSkinTreenode.prototype.imgEmpty='empty.gif'
RiFastSkinTreenode.prototype.getSubClass=function() { return RiFastSkinTreenode }
RiFastSkinTreenode.prototype.onDeselect=function() {(" I am not slected anymore "+this);}
RiFastSkinTreenode.prototype.onSelect=function() {}
RiFastSkinTreenode.prototype.onMouseOver=function() { alert ("mouse is over me")}
RiFastSkinTreenode.prototype.onMouseOut=function() {}
RiFastSkinTreenode.prototype.onRepaint=function() {}
RiFastSkinTreenode.prototype.setColor=function(col) { this.selectedColor=col }
RiFastSkinTreenode.prototype.addNodeContent=function(cap,fn,img, guid){			//added
	//var myTarget = (target)? 'target="'+target+'"' : ''
	if(img)myImg='<img src="'+img+'" width=16 height=16 border=0>'
	else myImg='&nbsp;&nbsp;&nbsp;&nbsp;'

	if(is.ns){
		if(!img)myImg='<img src="'+img+'" width=1 height=16 border=0>&nbsp;&nbsp;&nbsp;&nbsp;'
		id=this.id+this.subNodeId++;
		this.contentNodeContent+='<a class="OutstartTreeContent" name="' + guid +  '" href="'+fn+'" onmousedown'
		+'="javascript: '+id+'.setBubble(true);">'+myImg+'&nbsp;'+cap+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><br>';
	}else{
		this.contentNodeContent+='<a class="OutstartTreeContent" name="' + guid + '" style="width: '+(this.w-this.step+5)+';" href="'+fn+'">'+myImg+'&nbsp;'+cap+'</a><br>'
	}
}
RiFastSkinTreenode.prototype.closeContent=function(){		//added
	this.add(this.contentNodeContent,true)
}
RiFastSkinTreenode.prototype.resetContent=function(){		//added
	this.contentNodeContent=''
}
RiFastSkinTreenode.prototype.add=function(caption,showSelect, url){	//added showSelector
	var i=this.items.length
	//this.items[i]=new RiFastSkinTreenode(this.step,18+(i*18),this.w-16,caption,this.theme,this.step,this.select)
	this.items[i]=new RiFastSkinTreenode(this.step,18+(i*18),this.w-16,caption,this.theme,this.step, url)
	this.items[i].index=i
	this.items[i].setSize(this.w,18)
	this.items[i].setVisible(true)
	this.items[i].isBaseNode=false
	this.items[i].parent=this
	this.items[i].rootNode=this.rootNode
	this.items[i].setColor(this.selectedColor)
	this.items[i].showSelect=showSelect||false				//added
	return this.items[i]
}
RiFastSkinTreenode.prototype.expand=function() {
	var tmph=18
	var tmpw=1
	if(this!=this.rootNode){
		for (var i=0;i<this.parent.items.length;i++){		//<add>	added this to close open nodes in same level
			if (this.parent.items[i].expanded==true){
				tmpw=0
				this.parent.items[i].expanded=false;
				this.parent.items[i].setSize(this.w,18)
				this.parent.items[i].doc.images[this.parent.items[i].id+"sw"].src = this.theme+this.imgExpand
			}
		}
	}														//</add>
	this.expanded=true
	this.setSize(this.w,tmph)
	this.doc.images[this.id+"sw"].src = this.theme+this.imgCollapse
	this.rootNode.repaint(tmpw)
}
RiFastSkinTreenode.prototype.collapse=function() {
	this.expanded=false
	this.setSize(this.w,18)
	this.doc.images[this.id+"sw"].src = this.theme+this.imgExpand
	this.rootNode.repaint(-1)
}
RiFastSkinTreenode.prototype.repaint=function(exp) {
	var tmph=18
	var tmpw=this.w+(exp*this.step)
	var y=18
	if(this.offsetHeight!=null)tmph=this.offsetHeight+3	//added to resize
	for (var i=0; i<this.items.length; i++) {
		if (this.expanded) {
			this.items[i].moveTo(null,y)
			this.items[i].repaint(exp)
			tmph+=this.items[i].h
		}
		y+=this.items[i].h
	}

	this.setSize(tmpw,tmph)
	//alert("this.setSize="+tmpw+"\t"+tmph); 
	myHeight+=tmph
	this.offset=myHeight
	//alert("myheight="+myHeight)
	this.onRepaint()
}
RiFastSkinTreenode.prototype.privOnSelect=function() {
	var oldnode=null
	if (this.rootNode.selected!=null) {
		oldnode=this.rootNode.selected
		this.rootNode.selected.canvas.setBgColor(null)
//	
//		this.rootNode.selected.canvas.setHTML('<span class="OutstartTreeNode">'+this.rootNode.selected.caption+'</span>')
		if ( this.rootNode.selected.url != null ) o.canvas.setHTML('<a class="OutstartTreeNode" href="' + this.rootNode.selected.url + '">'+o.caption+'</a>')
		else this.rootNode.selected.canvas.setHTML('<span class="OutstartTreeNode">'+this.rootNode.selected.caption+'</span>')

		this.rootNode.selected.onDeselect(this)
	}
//  DPV
//	this.canvas.setHTML('<span class="OutstartTreeNodeSelected">'+this.caption+'</span>')
	if ( this.url != null ) this.canvas.setHTML('<a class="OutstartTreeNode" href="' + this.url + '">'+this.caption+'</a>')
	else this.canvas.setHTML('<span class="OutstartTreeNode">'+this.caption+'</span>')

	this.canvas.setBgColor(this.selectedColor)
	this.onSelect(oldnode)
	this.rootNode.selected=this
}
RiFastSkinTreenode.prototype.openNode=function() {	//added to call a node which then opens, closing it's child nodes.
	var node = this.rootNode
	node.expand()
		if(!arguments.length>0){
			node.privOnSelect()
			for (var i=0;i<node.items.length;i++){
				if (node.items[i].expanded==true){
					node.items[i].collapse()
				}
			}
		}
		for(var i=0;i<arguments.length;i++){
			if(node.items[arguments[i]].items.length>0){ 	//check if node exists and has children
				node=node.items[arguments[i]]
				node.expand()
			}else{
				node.privOnSelect()
					for (var i=0;i<node.items.length;i++){
						if (node.items[i].expanded==true){
							node.items[i].collapse()
						}
					}
				alert('You have tried to access a tree-node \nwhich does not exist anymore.\nThis is the closest match we could find.')
				break;
			}
			if(i==arguments.length-1){
				node.privOnSelect()
					for (var i=0;i<node.items.length;i++){
						if (node.items[i].expanded==true){
							node.items[i].collapse()
						}
					}
			}

		}
}
function fadein(color){
	//to do (maybe)
}