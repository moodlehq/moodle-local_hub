/*
 * Copyright (c) 2000, 2001 OutStart, Inc. All rights reserved.
 *
 * $Id: questions.js,v 1.2.16.1 2002/05/14 14:56:11 dvardhan Exp $
 */
 
ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false


// object to store attributes of answers
function answerObj(id,ContentID,answerText,feedbackText,weightValue,correctFlag,orderedFlag,ansLength,ansFormat,caseSensitive) {
    
	this.name = id;
	this.ContentID = ContentID;
	this.answerText = answerText;
	this.feedbackText = feedbackText;
	this.weightValue = weightValue;
	this.correctFlag = correctFlag;
    this.orderedFlag = orderedFlag;
    this.ansLength = ansLength;
    this.ansFormat = ansFormat;
    this.caseSensitive = caseSensitive;
    //this.answerGroup = answerGroup;
    
    return false;
}

function linkObj(id,ContentID,theLink,linkDisplay) {
	this.name = id;					
	this.ContentID = ContentID;
	this.theLink = theLink;
	this.linkDisplay = linkDisplay;
}


function ShowFeedback(ls_html) {
   feedbackWindow=window.open("","feedbackWindow","scrollbars=yes,status=no,width=400,height=300,resizable=yes");
   if (feedbackWindow.opener == null) feedbackWindow.opener = window;
   feedbackWindow.document.open();
   feedbackWindow.document.write(ls_html);
   feedbackWindow.document.close();
   feedbackWindow.focus();
   return false;
}

function ShowHint(ContentID) {
   
   
   nextHint = eval('CurHint'+ContentID);
   numHints = eval('Hints'+ContentID);
   //alert( "num hints = " + numHints );
   //alert( "curHInt = " + nextHint );
   
   ls_hint = '';
   ls_hint += addPopupSyles("Hint");   
   ls_hint += addPopupHeader("Hint");
   
   ls_hint += '<table width="100%" class="yellowbox" border=0 cellpadding="5" cellspacing="0"><tr><td colspan"2"></td></tr>'; 

   if (numHints > 0) {
       hintText = eval("hintArray"+ContentID+"["+nextHint+"]");
       hintText = unescape(hintText);
       if (hintText.length>0) {
        ls_hint += '<tr><td colspan="2" width="100%">' + hintText + '</td></tr>';
       } else {
        ls_hint += '<tr><td colspan="2" width="100%">Sorry, there are no hints!</td></tr>';
       }
       eval("CurHint"+ContentID+"++");
   } else {
       ls_hint += '<tr><td colspan="2" width="100%">Sorry, there are no hints!</td></tr>';
   }
   ls_hint += '</table><br></br><center><a href="javascript:self.close()">Close</a></center>';

   if (nextHint==(numHints-1)) {
       eval("CurHint"+ContentID+"=0");
   }

   hintWindow=window.open("","HintWindow","scrollbars=yes,status=no,width=400,height=300,resizable=yes");
   if (hintWindow.opener == null) hintWindow.opener = window;
   hintWindow.document.open();
   hintWindow.document.write(ls_hint);
   hintWindow.document.close();
   hintWindow.focus();
           
   return true;
   
}


function LTrim(passString) {
    passString=" "+passString;
    while(passString.charAt(0)==' ') {
        passString=passString.substring(1,passString.length);
    }
    return passString;
}

function RTrim(passString) {
    passString+=" ";
    while(passString.charAt(passString.length-1)==' ') {
        passString=passString.substring(0,passString.length-1);
    }
    return passString;
}

function addPopupSyles(title) {
    
    ls_style = '<head><title>'+title+'</title>\n';
    ls_style += '<STYLE type="text/css">\n  BODY	    { background: #ffffff; font:10pt arial;} \n';
    ls_style += 'P { font:10pt arial;}\n UL { font:10pt arial;} \n OL { font:10pt arial;} \n';
    ls_style += 'STRONG { font-weight: bold;} \n TD { font:10pt arial; } ';
    ls_style += '.Title { color: black; text-decoration: none; font:14pt arial; font-weight: bold; background-color: transparent; vertical-align: middle; text-align: left; }\n';
    ls_style += '.yellowbox  { background: #ffffbe; } \n </STYLE></head> \n';
    
    return ls_style;

}
function addPopupHeader(title) {
    
    ls_header =  '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#000000" height="6" colspan="2">\n';
    ls_header += '<SPACER type = "block" width = "1" height = "4"></td></tr></table>\n';
    ls_header += '<table width="100%" cellpadding="0" cellspacing="2" border="0"><tr><td><font face="Arial,Helvetica">\n';
    ls_header += '<b>'+title+'</b></font></td></tr></table>\n';
    ls_header += '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#000000" height="6" colspan="2">\n';
    ls_header += '<SPACER type = "block" width = "1" height = "4"></td></tr></table>\n ';   

    return ls_header;
}

function layerWrite(id,nestref,text) {  // pass the id of the <div> you want to write into, null if no parent layer, and text  to write
	
	//alert( "id = " + id + " text = " + text );
	var browser = navigator.appName;	
	
	if(browser=="Netscape") {
        //alert(id + " " + text + " " + nestref);
	var version = navigator.appVersion;
		if (version.charAt(0)=='4'){
			var lyr = (nestref)? eval('document.'+nestref+'.document.'+id+'.document') : document.layers[id].document;
        	var lyr = document.layers[id].document;
			lyr.open();
			lyr.write(text);
			lyr.close();
		}
		
		if (version.charAt(0)=='5'){		//  the version netscape 6.x has version number 5
			document.getElementById(id).innerHTML=text;
		
		}
	}
	else {
		if(browser=="Microsoft Internet Explorer"){
			document.all[id].innerHTML = text;
		}	
	}
}	
