
/*
   DynAPI Distribution
   PathAnimation wipeTo addon Class by www.richardinfo.com

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser, events]
	dynapi.util [thread, pathanim]
*/

DynLayer.prototype.wipeTo = function(endt,endr,endb,endl,nSteps,ms) {
	if (!this.pathanim) this.pathanim = new PathAnimation(this);
		if (!ms) ms = 20;
		this.pathanim.sleep(ms);
		if (!nSteps) nSteps = 10;

		var curt=this.getClip()[0]
		var curr=this.getClip()[1]
		var curb=this.getClip()[2]
		var curl=this.getClip()[3]

		var distt = (endt!=null)? endt-curt:0;
		var distr = (endr!=null)? endr-curr:0;
		var distb = (endb!=null)? endb-curb:0;
		var distl = (endl!=null)? endl-curl:0;

		var stepSizet = distt/nSteps;
		var stepSizer = distr/nSteps;
		var stepSizeb = distb/nSteps;
		var stepSizel = distl/nSteps;

	this.pathanim.playWipeAnimation(PathAnimation.wipe(curt,curr,curb,curl,stepSizet,stepSizer,stepSizeb,stepSizel,nSteps));
};

PathAnimation.prototype.playWipeAnimation = function () {
	if (this.playing) return;
	this.pathPlaying = null;

		this.pathPlaying = arguments[0];
		this.pathPlaying.loops = arguments[1]||false;
		this.pathPlaying.resets = arguments[2]||false;
		this.pathPlaying.frame = 0;

	this.playing = true;

	if (this.dlyr!=null) this.dlyr.invokeEvent("pathstart");
	this.start();
};

// generates a path between 4 points, stepping nStep steps.
PathAnimation.wipe = function(t1,r1,b1,l1,t2,r2,b2,l2,nSteps) {
	if (nSteps==0) return [];
	var patht = [];
	var pathr = [];
	var pathb = [];
	var pathl = [];
	for (var i=0;i<=nSteps;i++) {
		patht[i]=Math.round(t1+i*t2);
		pathr[i]=Math.round(r1+i*r2);
		pathb[i]=Math.round(b1+i*b2);
		pathl[i]=Math.round(l1+i*l2);
	}
	return [patht, pathr, pathb, pathl];
};