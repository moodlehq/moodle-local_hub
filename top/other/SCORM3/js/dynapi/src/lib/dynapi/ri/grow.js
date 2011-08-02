	/*
	   DynAPI Widget by richardinfo.com
	   GrowAnimation Class
	   Modified: 2000.12.06
	   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.
	*/
var q=1

function GrowAnimation(x,y,w,h,bgColor,bgImage,foreImage,comp,mouseact,stepn,stepsize,clickn,clicksize,speed) {

	this.superClass = DynLayer
	this.superClass()
	this.id = 'GrowAnimation'+(GrowAnimation.Count++);

	this.singleInTimeout=null
	this.singleOutTimeout=null
	this.singleI=0
	this.clickInTimeout=null
	this.clickOutTimeout=null
	this.clickI=0

	this.mouseActive=mouseact||false
	this.compensate=comp||false
	this.stepN=stepn||8;
	this.stepSizeW = this.stepSizeH = this.stepSize = stepsize*2||16;
	this.stepNClick= clickn||3;
	this.stepSizeClick = clicksize||6;
	this.speed=speed||20;

	var l=new EventListener(this)
	l.oncreate=function(e) {
		var o=e.getTarget()
		o.moveTo(x||0,y||0)
		o.setSize(w||128,h||36)
		//o.setBgImage(bgImage||null)
		o.setBgColor(bgColor||'gray')

			var pcent=w*100/(w+h)
			o.stepSizeW=(o.stepSize*pcent)/100
			o.stepSizeH=o.stepSize-o.stepSizeW

		if(foreImage){
			if(is.ie){
				o.setHTML('<img src='+foreImage+' width=100%>'||'');
				//o.setBgImage(null)
				o.setBgColor('')
				}else{
				o.setBgImage(bgImage)
			}
		}
	}
	l.onmousedown=function(e) {
			o=e.getTarget()
			if(o.mouseActive){
				this.startClickW=this.w
				this.startClickH=this.h
				o.clickInsingle ()
			}
	}
	l.onmouseup=function(e) {
			o=e.getTarget()
			if(o.mouseActive)o.clickOutsingle ()
	}
	l.onmouseover=function(e) {
			o=e.getTarget()
			if(o.mouseActive)o.fadeInsingle ()
	}
	l.onmouseout=function(e) {
			o=e.getTarget()
			if(o.mouseActive)o.fadeOutsingle ()
	}
	this.addEventListener(l)
	return this
};

GrowAnimation.Count=0
GrowAnimation.prototype = new DynLayer()
GrowAnimation.prototype.timerid=null
GrowAnimation.prototype.running=false
GrowAnimation.prototype.itemcount=0
GrowAnimation.prototype.getSubClass = function() { return GrowAnimation }
GrowAnimation.prototype.setCompensateXY = function (comp) {
	this.compensate = comp||true;
};
GrowAnimation.prototype.setMouseActive = function (comp) {
	this.mouseActive = comp||false;
};
GrowAnimation.prototype.setGrowSleep = function (slp) {
	this.speed = slp||20;
};
GrowAnimation.prototype.setSteps = function (v) {
	this.stepN= v||8;
};
GrowAnimation.prototype.setStepSize = function (v) {
	this.stepSizeW = this.stepSizeH = this.stepSize = v*2||16;
};
GrowAnimation.prototype.setClickSteps = function (v) {
	this.stepNClick= v||3;
};
GrowAnimation.prototype.setClickStepSize = function (v) {
	this.stepSizeClick = v||6;
};
GrowAnimation.prototype.playAnimation = function () {
	if (this.dlyr!=null) this.dlyr.invokeEvent("growstart");
	this.startW=this.w
	this.startH=this.h
	this.fadeInsingle();
};
GrowAnimation.prototype.growUp = function () {
	if (this.dlyr!=null) this.dlyr.invokeEvent("growstart");
	this.startW=this.w
	this.startH=this.h
	this.fadeInsingle();
};
GrowAnimation.prototype.shrinkBack = function () {
	if (this.dlyr!=null) this.dlyr.invokeEvent("shrinkstart");
	this.fadeOutsingle();
};
GrowAnimation.prototype.fadeInsingle = function () {
	 if (this.singleI < this.stepN) {
      	if (this.singleI < this.stepN-1) {
         	this.setSize(this.w+this.stepSizeW,this.h+this.stepSizeH)
         	if(this.compensate)this.moveBy(-this.stepSizeW/2,-this.stepSizeH/2)
  			++this.singleI
        	this.singleInTimeout=setTimeout(this.toString()+'.fadeInsingle()',this.speed);
         	clearTimeout(this.singleOutTimeout);
         }else{
		 }
     }
 }
GrowAnimation.prototype.fadeOutsingle = function () {
     if (this.singleI > 0) {
      	if (this.singleI > 1) {
         	this.setSize(this.w-this.stepSizeW,this.h-this.stepSizeH)
         	if(this.compensate)this.moveBy(+this.stepSizeW/2,+this.stepSizeH/2)
         	this.singleI--
        	this.singleOutTimeout =setTimeout(this.toString()+'.fadeOutsingle()',this.speed);
         	clearTimeout(this.singleInTimeout);
         }else{
			this.setSize(this.startW,this.startH)
       }
     }
 }
 GrowAnimation.prototype.clickInsingle = function () {
      if (this.clickI < this.stepNClick) {
       	if (this.clickI < this.stepNClick-1) {
          	this.setSize(this.w-this.stepSizeClick,this.h-this.stepSizeClick)
          	if(this.compensate)this.moveBy(+this.stepSizeClick/2,+this.stepSizeClick/2)
           	++this.clickI
         	this.clickInTimeout=setTimeout(this.toString()+'.clickInsingle()',this.speed);
          	clearTimeout(this.clickOutTimeout);
          }else{
 		 }
      }
  }
 GrowAnimation.prototype.clickOutsingle = function () {
      if (this.clickI > 0) {
       	if (this.clickI > 0) {
          	this.setSize(this.w+this.stepSizeClick,this.h+this.stepSizeClick)
          	if(this.compensate)this.moveBy(-this.stepSizeClick/2,-this.stepSizeClick/2)
          	this.clickI--
         	this.clickOutTimeout =setTimeout(this.toString()+'.clickOutsingle()',this.speed);
          	clearTimeout(this.clickInTimeout);
          }else{
			this.setSize(this.startClickW,this.startClickH)
          }
      }
 }
 GrowAnimation.prototype.remove=function() { this.deleteFromParent() }

