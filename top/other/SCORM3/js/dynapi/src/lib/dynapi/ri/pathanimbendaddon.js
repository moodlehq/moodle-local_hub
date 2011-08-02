/*
   DynAPI Distribution PathAnimation Class Addon by richardinfo.com
   Modified: 2000.12.08

   The DynAPI Distribution is distributed under the terms of the GNU LGPL license.

   requires pathanim.js
*/


// generates a bend between 2 points in N steps, with offst offset of bend in middle
PathAnimation.bendN = function(x1,y1,x2,y2,N,offst,updown,sag,leftright,lean) {
	if(!lean)lean='*1'
	if(!sag)sag='*1'
	var vertOpp=(updown)?intOpp=(updown=='up')?'Math.round( y1 + i*dy+offset[i]'+sag+');':'Math.round( y1 + i*dy-offset[i]'+sag+');':'Math.round( y1 + i*dy)'
	var horOpp=(leftright)?intOpp=(leftright=='left')?'Math.round( x1 + i*dx+offset[i]'+lean+')':'Math.round( x1 + i*dx-offset[i]'+lean+');':'Math.round(x1 +  i*dx);'
	if (N==0) return [];//
	var dx = x2==x1? 0 : (x2-x1)/N;
	var dy = y2==y1? 0 : (y2-y1)/N;
	var path = new Array();
	var offset=new Array();
	var Nset=offst/(N/2)
	var ii=N/2
	var dist=0
	for(var i=0;i<=N/2;i++) {offset[i+N/2] =Math.round(Math.pow(Nset,Math.log(i)))}
	for(var i=N/2;i<=N;i++) {offset[i-N/2] =Math.round(Math.pow(Nset,Math.log(ii--)))}
	dist=offset[0]
	for(var i=0;i<=N;i++) {offset[i]=offset[i]-dist}
	for (var i=0;i<=N;i++) {
		path[i*2] = eval(horOpp)			//Math.round(x1 +  i*dx)
		path[i*2+1] =eval(vertOpp)
	}
	return path;
}