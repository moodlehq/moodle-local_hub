//************************************************************
//  Ordering Client functions
//************************************************************
//  Copyright (C) 1999 OutStart, Inc
//************************************************************
//  Modification History
//************************************************************
// KB   07/26/00    - Initial coding
//
// EA   10/20/00    - Added support for presentation build
// TNS  3/7/01      - added netscape 6 bullets



ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false
ns6=document.getElementById&&!document.all?1:0

var BuildNeeded = true;   // to tell presentation that this content type does a build

function JudgeOrderQuestion(ContentID) {
    
	eval("Attempts"+ContentID+"++");
    
    var attemptcount = eval("Attempts"+ContentID);
    var trycount = eval("Tries"+ContentID);
    var numLinks = eval("NumLinks"+ContentID);
	var numAnswers = eval("NumAnswers"+ContentID);
    var numFeedback=0;
    var numIncorrect = 0;
    var ls_incorrectHTML='';
    var add_HTML='';
    var ls_HTML='';
    ls_HTML += addPopupSyles("Answer Feedback");   
    ls_HTML += addPopupHeader("Answer Feedback");
    ls_HTML += '<table width="100%" class="yellowbox" border=0 cellpadding="5" cellspacing="0"><tr><td colspan="2">&nbsp</td></tr>';
    
	if (attemptcount > trycount) { // no more tries!
        
        ls_HTML += '<tr><td colspan = "2"><b>Sorry, you have exceeded the allowed number of tries.</b></td></tr>';
        numFeedback ++;
        
    } else { 
    	
		
		// start creating feedback for each options selected correctly.
		// must assume only correct feedback in ordering
        for (i=0; i<numAnswers; i++) {
            
            eval("answerObj = answer"+ContentID+"Array["+i+"]");
            
            theChoice = eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+".options[document.Question"+ContentID+"Form.Answer"+i+ContentID+".selectedIndex].value;");
            theAnswer=answerObj.orderedFlag;
			
            if (theChoice==theAnswer) {   
			
			
                var theFeedback=answerObj.feedbackText;
                if (theFeedback.length>0) {
                    
                    if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(theFeedback)+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(theFeedback)+'</td></tr>';
                    } 
                    
                    numFeedback++;
                }
                eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+".disabled=true;");
                eval("document.images['Img"+i+ContentID+"'].src = CkMark" +ContentID+ ".src");
            } else {
               numIncorrect++;
			
            }
  
        }
        
        if (numIncorrect > 0) {
            
            if (attemptcount >= trycount) {
				//alert( "final incorrect!" );
                var theFeedback = eval('Incorrectfdbk'+ContentID);
                ls_HTML += '<tr><td colspan="2"><b>'+unescape(theFeedback)+'</b></td></tr>';
                if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_HTML += add_HTML;
                numFeedback ++;
                for (i=0; i<numAnswers; i++) {
                    eval("answerObj = answer"+ContentID+"Array["+i+"]");
                    theSelected=(answerObj.orderedFlag/10)-1;
                    eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+".options["+theSelected+"].selected=true;");
                    eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+".disabled=true;");
                    if(ns4) {
                          //eval("document.layers.Answer"+i+ContentID+"Img.visibility='show'");
                          //eval("document.images['Img"+i+ContentID+"'].src = CkMark"+ContentID);
                    } else{
                          //eval("document.images.Img"+i+ContentID+".src ='../themes/thumb_clear.gif'");
                    }
                }
                    
                for (i=0; i<numLinks; i++) {
					//alert("looping through links");
                    eval("linkObj = link"+ContentID+"Array["+i+"]");
                    if (linkObj.linkDisplay==1 || linkObj.linkDisplay==3) {
                        theLink=unescape(linkObj.theLink);
                        if (theLink.length>34) {
                            if (i==0) {ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>';}
                            
                             if (ns6) {
                                      ls_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+theLink+'</td></tr>';
                             }else{
                                      ls_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theLink+'</td></tr>';
                             } 
                            numFeedback ++;
                        }
                    }
                }

            } else {
                ls_HTML += '<tr><td colspan="2"><b>Please Try Again</b></td></tr>';
                ls_HTML += add_HTML;
                numFeedback ++;
            }
                       
        } else {

            var theFeedback = eval('Correctfdbk'+ContentID);
            ls_HTML += '<tr><td colspan="2"><b>'+unescape(theFeedback)+'</b></td></tr>';
            if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
            ls_HTML += add_HTML;
            numFeedback ++;            

            for (i=0; i<numLinks; i++) {
                eval("linkObj = link"+ContentID+"Array["+i+"]");
                if (linkObj.linkDisplay==1 || linkObj.linkDisplay==2) {
                    theLink=unescape(linkObj.theLink);
                    if (theLink.length>34) {
                        if (i==0) {ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>';}
                        if (ns6) {
                                      ls_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+theLink+'</td></tr>';
                        }else{
                                      ls_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theLink+'</td></tr>';
                        } numFeedback ++;
                    }
                }
            }
               
        }
         
        
         // update tries 
		 
		triesLeft = (trycount-attemptcount);
        var trytext = 'Tries Remaining: ' + triesLeft;
        layerWrite('Tries'+ContentID,null,'<span class="trycounter">'+trytext+'</span>');
  
    
    }
    
	if (numFeedback>0) {
        ls_HTML+='</table><br></br><center><a href="javascript:self.close()">Close</a></center>';
        ShowFeedback(ls_HTML);    
    }
    
    return false;
    
}


function showOrderAnswers(theID,NumOfAnswers) {
    
    for (i=0; i<NumOfAnswers; i++) {
        eval("answerObj = answer"+theID+"Array["+i+"]");
        theSelected=(answerObj.orderedFlag/10)-1;
        eval("document.Question"+theID+"Form.Answer"+i+theID+".options["+theSelected+"].selected=true;");
        eval("document.Question"+theID+"Form.Answer"+i+theID+".disabled=true;");
    }
    
}
