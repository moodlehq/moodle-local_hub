/*
   DynAPI Distribution
   Thread Class

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	dynapi.api [dynlayer, dyndocument, browser, events]
*/


function Thread(dlyr) {
	this.setDynLayer(dlyr);
	Thread.setUniqueKey(this);
}
Thread.prototype.active = false;
Thread.prototype.interval = 50;
Thread.prototype.cancelThread = false;
Thread.prototype.sleep = function (ms) {
	this.interval = Math.abs(parseInt(ms));
	if (this.active) {
		this.stop();
		setTimeout(this._key+'.start()',this.interval+1);
	}
};
Thread.prototype.setFPS = function (fps) {
	this.sleep(Math.floor(1000/fps));
};
Thread.prototype.cancel = function () {
	this.cancelThread = true;
	this.stop();
};
Thread.prototype.start = function () {
	if (!this.active) {
		this.active = true;
		if (!this.cancelThread) this.timer = setInterval(this._key+'.run()',this.interval);
	}
};
Thread.prototype.run = function () {}; // overwrite run
Thread.prototype.stop = function () {
	this.active = false;
	if (!this.cancelThread) {
		clearInterval(this.timer);
		delete this.timer;
	}
};
Thread.prototype.setDynLayer = function (dlyr) {
	this.dlyr = dlyr;
};
Thread.prototype.getDynLayer = function () {
	return this.dlyr;
};

// Functions

// makes a unique ._key global reference to the object
// I recommend this becomes DynAPI.setUniqueKey so that any object can use the same system
// DynLayer.toString() cannot be used because it relies on layer ID's which arent determined
// until after insertion - ie. you cannot get a reference to a DynLayer until you've added it
Thread.setUniqueKey = function(obj) {
	if (!DynAPI.uniqueKey) DynAPI.uniqueKey=0;
	var key = "key_"+DynAPI.uniqueKey++;
	if (typeof(obj)=="object") {
		obj._key = key;
		window[key] = obj;
	}
	return key;
};
Thread.getUniqueKey = function(obj) {
	return obj._key;
};
