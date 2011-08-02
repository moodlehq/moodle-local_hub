// status widget by richard from www.richardinfo.com
// for DynAPI Copyright (CriStatus) 2000 Dan Steinman
// Redistributable under the terms of the GNU Library General Public License

riStatus = function(x,y,w,h,bgcolor,fgcolor,stepn,caption) {

	this.DynLayer = DynLayer
	this.DynLayer()
	this.id = "riStatus"+(riStatus.Count++)

	this.caption=caption
	this.stepN=stepn||100;
	this.stepSize=w/this.stepN

	var l=new EventListener(this)
	l.oncreate=function(e) {
		var o=e.getTarget()
		o.setSize(w,h)
		o.moveTo(x,y)
		o.setBgColor(bgcolor)
		o.slider=new DynLayer (null,-w,0,w,h,fgcolor)
		o.addChild(o.slider)
		o.cover=new DynLayer (null,0,1,w,h)
		o.cover.setHTML('<center><span class="mystatuscaption">0% '+caption+'</span></center>')
		o.addChild(o.cover)
	}
	this.addEventListener(l)
	return this
}
riStatus.prototype = new DynLayer
riStatus.prototype.getSubClass=function() { return riStatus }
riStatus.prototype.increaseBy = function(steps) {
	if(this.slider.x<0){
	this.slider.moveBy(steps*this.stepSize)
	this.cap=Math.round((this.slider.x+this.w)*100/this.w)
	this.cover.setHTML('<center><span class="mystatuscaption">'+this.cap+'% '+this.caption+'</span></center>')
	}
}
riStatus.prototype.decreaseBy = function(steps) {
	status=this.slider.x+' '+this.slider.w
	if(this.slider.x>-this.slider.w){
	this.slider.moveBy(-steps*this.stepSize)
	this.cap=Math.round((this.slider.x+this.w)*100/this.w)
	this.cover.setHTML('<center><span class="mystatuscaption">'+this.cap+'% '+this.caption+'</span></center>')
	}
}
riStatus.prototype.increaseTo = function(steps) {
	if(this.slider.x<0){
	this.slider.moveTo(steps*this.stepSize-this.w)
	this.cap=Math.round((this.slider.x+this.w)*100/this.w)
	this.cover.setHTML('<center><span class="mystatuscaption">'+this.cap+'% '+this.caption+'</span></center>')
	}
}
riStatus.prototype.reset = function() {
	this.slider.moveTo(-this.w,0);
	this.cap=Math.round((this.slider.x+this.w)*100/this.w)
	this.cover.setHTML('<center><span class="mystatuscaption">'+this.cap+'% '+this.caption+'</span></center>')
}
riStatus.Count = 0