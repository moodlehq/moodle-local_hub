/*
   DynAPI Distribution
   Miscellaneous Functions

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   Requirements:
	none
*/ 
DynAPI.functions = {
	DecToHex : function(val){
		lo=val%16;
		val-=lo;
		lo+=48;
		if (lo>57) lo+=7;
		hi=val/16;
		hi+=48;
		if (hi>57) hi+=7;
		return String.fromCharCode(hi,lo);
	},
	getColor : function(r,g,b) { 
		return '#'+DynAPI.functions.DecToHex(r)+DynAPI.functions.DecToHex(g)+DynAPI.functions.DecToHex(b);
	},
	createRedPal : function(pal) {
		var r=g=b=0;
		for (var i=0; i<256; i++){
			pal[i]=DynAPI.functions.getColor(r,g,b);
			r+=8;
			if (r>255) { r=255; g+=6; b+=2; }
			if (g>255) { g=255; b+=2; }
			if (b>255) { b=255; }
		}
	},
	createGrayPal : function(pal) {
		var r=0;
		for (var i=0; i<256; i++){
			pal[i]=DynAPI.functions.getColor(r,r,r);
			r+=4;
			if (r>255) { r=255; }
		}
	},
	createBluePal : function(pal){
		var r=g=b=0;
		for (var i=0; i<256; i++){
			pal[i]=DynAPI.functions.getColor(r,g,b);
			b+=6;
			if (b>255) { b=255; g+=2; }
			if (g>255) { g=255; r+=2; }
		}
	},
	createGreenPal : function(pal) {
		var r=g=b=0;
		for (var i=0; i<256; i++){
			pal[i]=DynAPI.functions.getColor(r,g,b);
			g+=6;
			if (g>255) { g=255; b+=2; }
			if (b>255) { b=255; r+=2; }
		}
	},
	tpoint : function() { 
		this.x=0;
		this.y=0;
		this.z=0;
		return this;
	},
	sintable : function(lsin) {
		for (var i=0; i<361; i+=1) lsin[i]=Math.sin((i/180)*Math.PI);
	},
	costable : function(lcos) {
		for (var i=0; i<361; i+=1) lcos[i]=Math.cos((i/180)*Math.PI);
	}
};