/*
   DynAPI Distribution
   Debug Extensions

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser]
*/ 
DynLayer.prototype.debug = function() {
	var str ="";
	str += "DynLayer: "+this.id+"\n";
	str += "-----------------------\n";
	str += "     Size: "+this.getWidth()+"x"+this.getHeight()+"\n";
	str += " Position: "+this.getX()+","+this.getY()+"\n";
	str += "  BgImage: "+this.getBgImage()+"\n";
	str += "  BgColor: "+this.getBgColor()+"\n";
	str += "  Content: "+this.getHTML()+"\n";
	str += "   zIndex: "+this.getZIndex()+"\n";
	str += "  Visible: "+this.getVisible()+"\n";
	str += "----- Number of Children: "+this.children.length+"\n";
	return str;
}
DynLayer.prototype.tree = function(space) {
	var ret ='';
	space = space||" ";
	ret += space +"* "+this.id+"\n";
	for(i in this.children)
		ret += this.children[i].tree(space+"   ");
	return ret;
}
DynDocument.prototype.tree = DynLayer.prototype.tree;
