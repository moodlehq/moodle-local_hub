/*
   DynAPI Distribution
   ButtonImage Class

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser, events]
*/

function ButtonImage() {
	this.DynLayer = DynLayer;
	this.DynLayer();
	
	this.events = new EventListener(this);
	this.events.onmousedown = function (e) {
		var o = e.getTarget();
		if (o.checkbox) o.setSelected(!o.selected);
		else o.change(o.selectedImage);
		e.setBubble(false);
	};
	this.events.onmouseup = function (e) {
		var o = e.getTarget();
		if (!o.checkbox) o.change(o.defaultImage);
		e.setBubble(false);
	};
	this.events.onmouseover = function (e) {
		var o = e.getTarget();
		if (o.selected) o.change(o.selectedRoll);
		else o.change(o.defaultRoll);
		e.setBubble(false);
	};
	this.events.onmouseout = function (e) {
		var o = e.getTarget();
		if (o.selected) o.change(o.selectedImage);
		else o.change(o.defaultImage);
		e.setBubble(false);
	}
	this.addEventListener(this.events);
};

ButtonImage.prototype = new DynLayer;

ButtonImage.prototype.checkbox = false;
ButtonImage.prototype.setImages = function(defaultImage,defaultRoll,selectedImage,selectedRoll) {
	if (arguments.length==4) this.checkbox = true;
	this.defaultImage = defaultImage;
	this.defaultRoll = defaultRoll;
	this.selectedImage = selectedImage;
	this.selectedRoll = selectedRoll;
	this.setHTML('<img name="'+this.id+'Image" src="'+this.defaultImage.src+'" width='+this.defaultImage.width+' height='+this.defaultImage.height+'>');
	this.setSize(this.defaultImage.width,this.defaultImage.height);
};
ButtonImage.prototype.setSelected = function(b) {
    this.selected=b;
	if (this.selected) {
		this.change(this.selectedImage);
		this.invokeEvent("select");
	}
	else {
		this.change(this.defaultImage);
		this.invokeEvent("deselect");
	}
};
ButtonImage.prototype.change = function(img) {
	if (img) this.doc.images[this.id+"Image"].src = img.src;
};
