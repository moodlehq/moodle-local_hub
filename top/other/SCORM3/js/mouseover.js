// Mouseover Interaction Functions
// 19990928
// Copyright (C) 1999 OutStart, LLC
//
//  EA  11/30/99    Corrected problem causing mouseover tips to be chopped off.
//
ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false

function MouseOverInit(ContentID,numobjects) {
    // parent layer
	eval("parlayer"+ContentID+" = new DynLayer('parlayer"+ContentID+"Div')");
    
	// base image layer
	eval("mousebase"+ContentID+" = new DynLayer('mousebase"+ContentID+"Div')" );

    // the hot areas
	for (var i = 1;i<=numobjects;i++) {
		eval( "mousearea"+i+ContentID+" = new DynLayer('"+"mousearea"+i+ContentID+"Div')" );
	}
    // the tooptip layer
	eval("tooltip"+ContentID+" = new DynLayer('tooltip"+ContentID+"Div')" );	
}
// object to store attributes of mouseover
function MouseArea(id,ContentID,feedback) {
	this.name = id;
	this.ContentID = ContentID;
	this.feedback = feedback;
    this.showTip = MouseOver_WriteTip;
    this.hideTip = MouseOver_HideTip;
}
function mouseOver(ContentID,area) {
	areaObj = eval('mouse'+ContentID+'Array['+area+']');
    areaObj.showTip(area);
}
function mouseOff(ContentID,area) {
	areaObj = eval("mouse"+ContentID+"Array["+area+"]");
    areaObj.hideTip();
}
function MouseOver_WriteTip(area) {
//	  eval('MouseOver_layerWrite("tooltip'+this.ContentID+'Div","parlayer'+this.ContentID+'Div","<SPAN CLASS='+"'"+'mouseovertext'+"'"+'>'+this.feedback+'</SPAN>")' );
	  eval('MouseOver_layerWrite("tooltip'+this.ContentID+'Div",null,"<SPAN CLASS='+"'"+'mouseovertext'+"'"+'>'+this.feedback+'</SPAN>")' );
      parX = eval("parlayer"+this.ContentID+".x");
      parY = eval("parlayer"+this.ContentID+".y");
      baseX = eval("mousebase"+this.ContentID+".x");
      baseY = eval("mousebase"+this.ContentID+".y");
      newX = eval( "mousearea"+area+this.ContentID+".x");
      newY = eval( "mousearea"+area+this.ContentID+".y");
      targetX = parseInt(parX) + parseInt(baseX) + parseInt(newX);
      targetY = parseInt(parY) + parseInt(baseY) + parseInt(newY);
      eval("tooltip"+this.ContentID+".moveTo((parseInt(targetX)-0),(parseInt(targetY)+40))");
//      eval("tooltip"+this.ContentID+".moveTo((parseInt(newX)-0),(parseInt(newY)+40))");
      eval("tooltip"+this.ContentID+".show()");
}
function MouseOver_layerWrite(id,nestref,text) {
	if (is.ns) {
		var lyr = (nestref)? eval('document.'+nestref+'.document.'+id+'.document') : document.layers[id].document;
		lyr.open();
		lyr.write(text);
		lyr.close();
	}
	else {
		document.all[id].innerHTML = text;
	}
}
function MouseOver_HideTip() {
      eval("tooltip"+this.ContentID+".hide();");
}


