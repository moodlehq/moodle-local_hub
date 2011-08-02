	/*
	   DynAPI WidGrowInAnimationget by richardinfo.com
	   GrowInAnimation Class
	   Modified: 2000.12.06
	   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
	*/
var q=1

function GrowInAnimation(x,y,w,h,bgColor,bgImage,foreImage,stepn,speed,comp) {

	this.superClass = DynLayer
	this.superClass()
	this.id = 'GrowInAnimation'+(GrowInAnimation.Count++);

	this.singleInTimeout=null
	this.singleOutTimeout=null
	this.singleI=0

	this.compensate=comp||false
	this.newX=x
	this.newY=y
	this.newWidth=w
	this.newHeight=h
	this.stepN=stepn||8;
	this.stepSizeW = w/stepn
	this.stepSizeH = h/stepn
	this.speed=speed||20;

	var l=new EventListener(this)
	l.oncreate=function(e) {
		var o=e.getTarget()
		o.moveTo(x+w/2,y+h/2)
		o.setSize(0,0)
		o.setBgColor(bgColor)
		o.setBgImage(bgImage)

		if(foreImage){
			if(is.ie){
				o.setHTML('<img src='+foreImage+' width=100%>'||'');
				o.setBgImage(null)
				o.setBgColor(null)
				}else{
			}
		}
	}
	this.addEventListener(l)
	return this
};

GrowInAnimation.Count=0
GrowInAnimation.prototype = new DynLayer()
GrowInAnimation.prototype.timerid=null
GrowInAnimation.prototype.running=false
GrowInAnimation.prototype.itemcount=0
GrowInAnimation.prototype.getSubClass = function() { return GrowInAnimation }

GrowInAnimation.prototype.growUp = function () {
	if (this.dlyr!=null) this.dlyr.invokeEvent("growstart");
	this.startW=this.w
	this.startH=this.h
	this.fadeInsingle();
};
GrowInAnimation.prototype.shrinkBack = function () {
	if (this.dlyr!=null) this.dlyr.invokeEvent("shrinkstart");
	this.fadeOutsingle();
};
GrowInAnimation.prototype.fadeInsingle = function () {
	 if (this.singleI <= this.stepN) {
      	if (this.singleI <= this.stepN-1) {
         	this.setSize(this.w+this.stepSizeW,this.h+this.stepSizeH)
         	if(this.compensate)this.moveBy(-this.stepSizeW/2,-this.stepSizeH/2)
  			++this.singleI
        	this.singleInTimeout=setTimeout(this.toString()+'.fadeInsingle()',this.speed);
         	clearTimeout(this.singleOutTimeout);
         }else{
			this.setSize(this.newWidth,this.newHeight)
			//if(this.compensate)this.moveTo(this.newX,this.newY)
		 }
     }
 }
GrowInAnimation.prototype.fadeOutsingle = function () {
     if (this.singleI > 0) {
      	if (this.singleI > 1) {
         	this.setSize(this.w-this.stepSizeW,this.h-this.stepSizeH)
         	if(this.compensate)this.moveBy(+this.stepSizeW/2,+this.stepSizeH/2)
         	this.singleI--
        	this.singleOutTimeout =setTimeout(this.toString()+'.fadeOutsingle()',this.speed);
         	clearTimeout(this.singleInTimeout);
         }else{
			this.setSize(0,0)
		}
     }
 }

 GrowInAnimation.prototype.remove=function() { this.deleteFromParent() }

