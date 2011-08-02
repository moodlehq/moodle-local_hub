/*
   DynAPI Distribution
   DragDrop Extension

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements: 
	dynapi.api.*
*/ 
DynLayer.prototype.DragDrop=function(s){
	if (!this.children.length>0) return;
	for (var i in this.children) {
		var ch=this.children[i];
		if (ch.x<s.x && ch.x+ch.w>s.x && ch.y<s.y && ch.y+ch.h>s.y) ch.invokeEvent("drop");
	}
};
DynDocument.prototype.DragDrop=DynLayer.prototype.DragDrop;