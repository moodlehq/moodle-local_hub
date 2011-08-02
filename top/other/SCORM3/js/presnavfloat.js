/* -------------------------------------------------------------------------
 * Floating image II (up down)- Bruce Anderson (http://appletlib.tripod.com)
 * Submitted to Dynamicdrive.com to feature script in archive
 * Modified by DD for script to function in NS6
 * For 100's of FREE DHTML scripts, Visit http://www.dynamicdrive.com
 * -------------------------------------------------------------------------
 * Modification History
 * -------------------------------------------------------------------------
 * ABC      03.01.01        - Modified x coordinate to push nav to abs left
 *                          - Moved onLoad event to pres_body onLoad
 * -------------------------------------------------------------------------
 */

var XX=0; // X position of the scrolling objects
var xstep=0; // SPEED of up down by pix
var delay_time=60;

//Begin of the unchangable area, please do not modify this area
var YY=0;  
var ch=0;
var oh=0;
var yon=0;
//var winWidth=0;

var ns4=document.layers?1:0
var ie=document.all?1:0
var ns6=document.getElementById&&!document.all?1:0

function init()  {

if(ie){
YY=document.body.clientHeight;
//winWidth=document.body.clientWidth;
point1.style.top=YY;
}
else if (ns4){
YY=window.innerHeight;
//winWidth=window.innerWidth;
document.point1.pageY=YY;
document.point1.visibility="hidden";
}
else if (ns6){
YY=window.innerHeight
//winWidth=window.innerWidth;
document.getElementById('point1').style.top=YY
}

}


function reloc1()
{

if(yon==0){YY=YY-xstep;}
else{YY=YY+xstep;}
if (ie){
ch=document.body.clientHeight;
oh=point1.offsetHeight;
}
else if (ns4){
ch=window.innerHeight;
oh=document.point1.clip.height;
}
else if (ns6){
ch=window.innerHeight
oh=document.getElementById("point1").offsetHeight
}
		
if(YY<0){yon=1;YY=0;}
if(YY>=(ch-oh)){yon=0;YY=(ch-oh);}
if(ie){
point1.style.left=XX;
point1.style.top=YY+document.body.scrollTop;
}
else if (ns4){
document.point1.pageX=XX;
document.point1.pageY=YY+window.pageYOffset;
}
else if (ns6){
document.getElementById("point1").style.left=XX
document.getElementById("point1").style.top=YY+window.pageYOffset
}

}

function onad()
{

init();
window.focus();

if(ns4)
document.point1.visibility="visible";
loopfunc();
}
function loopfunc()
{
reloc1();
setTimeout('loopfunc()',delay_time);
}

/* moved this to onload event
if (ie||ns4||ns6)
window.onload=onad
*/

function centerImage( width, divID )
{

//alert(width);
//alert(divID);
eval(divID+".style.left=10;");
	
if(ie){
eval(divID+".style.left=10;");
}
else if (ns4){
eval("document."+divID+".pageX=10;");
}
else if (ns6){
eval("document.getElementById('"+point1+"').style.left=10;");
}	

	

}


document.onkeypress = keygrabber;

function keygrabber(e) {
    
    var k    = 0;
    var ctrl = false;
    var alt  = false;
    
    if (document.layers) {
        k = e.which;
    }
    else if (document.all) {
        e    = window.event;
        k    = e.keyCode;
        ctrl = e.ctrlKey;
        alt  = e.altKey;
    }
    else {
        k    = e.charCode;
        ctrl = e.ctrlKey;
        alt  = e.altKey;
    }
    
    if (k != 0) {
        keyhandler(k,ctrl,alt);
    }
}

function keyhandler(k,ctrl,alt) {
	//alert( k );
    switch (k) {
        case 13:
            //alert( "forward to slide " + nextSlide );
			if (nextSlide>0)  { parent.pres_footer.NewSlide( nextSlide ); }
            break;
        
			/*
			trying to hide button bar
	    case 104:
			
			if(ie){
				if (showNavBar)  {
					//alert( " hide bar " );
					showNavBar=false;
					point1.style.visibility="hidden";
				} else  {
					//alert( " show bar " );
					showNavBar=true;
					point1.style.visbility="visible";
				}
			}
			/*
			else if (ns4){
				document.point1.visibility="hidden";
			}
			else if (ns6){
				document.getElementById('point1').visibility="hidden";
			}
			*/	           
        default:
            return true;
    }
}





