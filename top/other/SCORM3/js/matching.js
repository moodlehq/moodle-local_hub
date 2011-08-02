//************************************************************
//  Matching Client functions
//************************************************************
//  Copyright (C) 1999 OutStart, Inc
//************************************************************
//  Modification History
//************************************************************
// KB   07/28/00    - Initial coding
//
// ABC  10/10/00    - Modified code to use checkboxes instead 
//                    of select menus
// EA   10/20/00    - Modified to support presentation build
// EA   10/20/00    - Fixed problem in Netscape, presentation build feedback layer not showing
// KB   01/29/01    - fixed problem with more than one remediation link.  causing loop.
// TNS  03/07/01    - Added ns6 bullets
// ABC  05/02/01    - fixed feedback bug with links

//<script>


ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false
ns6=document.getElementById&&!document.all?1:0

var BuildNeeded = true;   // to tell presentation that this content type does a build
//var numFeedback = 0;

function JudgeMatchingQuestion(ContentID) {
    
    eval("Attempts"+ContentID+"++;");
    
    var attemptcount        = eval("Attempts"+ContentID);
    var trycount            = eval("Tries"+ContentID);
    var numLinks            = eval("NumLinks"+ContentID);
	var numAnswers          = eval("NumAnswers"+ContentID);
    var numItems            = eval("NumItems"+ContentID);
    var numMatches          = eval("NumMatches"+ContentID);
    var origMatchArray      = eval("origMatchArray"+ContentID);
    var add_HTML            = '';
    var numIncorrect        = 0;
    var numFeedback         	= 0; // initialize
    var ls_incorrectHTML    = '';
    var ls_correctHTML      = '';
    var choiceArray         = new Array();
    var matchArray          = new Array(numMatches);
    var choiceCnt           = 0;
    var theCheck            = 0;
    
    var ls_HTML  = '';
        ls_HTML += addPopupSyles("Answer Feedback");   
        ls_HTML += addPopupHeader("Answer Feedback");
        ls_HTML += '<table width="100%" class="yellowbox" border=0 cellpadding="5" cellspacing="0"><tr><td colspan="2">&nbsp</td></tr>';
    
    if (attemptcount > trycount) {
        ls_HTML += '<tr><td colspan = "2"><b>Sorry, you have exceeded the allowed number of tries.</b></td></tr>';
        numFeedback ++;
        
    } else { 
        for (i=0; i<numItems; i++) {
            for (j=0; j<numAnswers; j++) {
                if (eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+"["+j+"].checked==true;")) {
                    theNum=eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+"["+j+"].value;")
                    theChoice=(i+1)+"-"+(theNum);
                    choiceArray[choiceCnt]=theChoice;
                    choiceCnt++;
                }
            }
        }
        choiceLength=choiceArray.length;
        
        for (j=0; j<numMatches; j++) {
            eval("answerObj = answer"+ContentID+"Array["+j+"]");
            for (i=0; i<choiceLength; i++) {
                if (choiceArray[i]==answerObj.answerText) {
                    theCheck++;
                }
            }
            if (theCheck==0) { numIncorrect++; }
            else {
                var theFeedback=unescape(answerObj.feedbackText);
                if (theFeedback.length>0) {
                     if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+theFeedback+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theFeedback+'</td></tr>';
                    }
                    numFeedback++;
                }
            }
            theCheck=0;
        }
        if (choiceLength>numMatches) { numIncorrect++; }
    
/////////////////////////////////////////////
        if (numIncorrect > 0) {
            if (attemptcount >= trycount) {
                var theFeedback = unescape(eval('Incorrectfdbk'+ContentID));
                ls_HTML += '<tr><td colspan="2"><b>'+theFeedback+'</b></td></tr>';
                if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_HTML += add_HTML;
                numFeedback ++;
                
                //showAnswers(numAnswers,numItems,ContentID);
                for (i=0; i<numItems; i++) {
					// clear check boxes
                    for (x=0; x<numAnswers; x++) {
                        eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+"["+x+"].checked=false;");
                    }
					var theArray=parseMatch(i+1,ContentID,origMatchArray);
					for (x=0; x<theArray.length; x++) {
                        var origAnsIndex = theArray[x];
						eval("document.Question"+ContentID+"Form.Answer"+i+ContentID+"["+(origAnsIndex)+"].checked=true;");
                    }
                }
                
				ls_HTML += getRemedLinks(3,ContentID);
                
				
/////////////////////////////////////////////////                
            } else {
                ls_HTML += '<tr><td colspan="2"><b>Please Try Again</b></td></tr>';
                ls_HTML += add_HTML;
                numFeedback++;
            }
        } else {
            var theFeedback = unescape(eval('Correctfdbk'+ContentID));
            ls_HTML += '<tr><td colspan="2"><b>'+theFeedback+'</b></td></tr>';
            if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
            ls_HTML += add_HTML;
            numFeedback ++;
			
			ls_HTML += getRemedLinks(2,ContentID);
			
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

function getRemedLinks( type, ContentID )  {
	
	var html="";
	var numLinks = eval("NumLinks"+ContentID);
	for (i=0; i<numLinks; i++) {
        eval("linkObj = link"+ContentID+"Array["+i+"]");	
        if (linkObj.linkDisplay==1 || linkObj.linkDisplay==type) {
            theLink=unescape(linkObj.theLink);
            if (theLink.length>34) { //  valid link
                if (i==0) {
					html += '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>';
				}
                if (ns6) {
                	html += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+theLink+'</td></tr>';
                }
				else {
					html += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theLink+'</td></tr>';
                }
           	}
        }
    }
	return html;
}

function parseMatch(passItem,ContentID,origArray) {
    
    var returnArray=new Array();
    var returnCnt=0;
    var numMatches = eval("NumMatches"+ContentID);
	// get all orig ans numbers that match this item
    for (j=0; j<numMatches; j++) {
        eval("answerObj = answer"+ContentID+"Array["+j+"]");
        theMatch=answerObj.answerText;
        theLength=theMatch.length;
        theDash=theMatch.indexOf("-");
        
        theItem=theMatch.substring(0,theDash);

        if (theItem==passItem) {
			var ansNum = theMatch.substring(theDash+1,theLength);
			// find the index in origArray for this answer
			for (k=0; k<origArray.length; k++)  {
				if (ansNum == origArray[k])  { // found it!
					returnArray[returnCnt]=k;
            		returnCnt++;	
				}
			}
            
        }
    }
	return returnArray;

     
}

/*
function showMatchAnswers(NumOfAnswers,NumOfItems,theID) {
    
    var theOrigMatchArray = eval("origMatchArray"+theID);
    for (i=0; i<NumOfItems; i++) {
        for (x=0; x<NumOfAnswers; x++) {
            //eval("document.Question"+theID+"Form.Answer"+i+theID+".options["+x+"].selected=false;");
            eval("document.Question"+theID+"Form.Answer"+i+theID+"["+x+"].checked=false;");
        }
        var theArray='';
        theArray = parseMatch(i+1,theID);
        arrayLength=theArray.length;
        for (x=0; x<arrayLength; x++) {
            var theOrigChoice = theArray[x];
            var theShuffleChoice = theOrigMatchArray[theOrigChoice-1];
            //eval("document.Question"+theID+"Form.Answer"+i+theID+".options["+(theShuffleChoice-1)+"].selected=true;");
            eval("document.Question"+theID+"Form.Answer"+i+theID+"["+(theShuffleChoice-1)+"].checked=true;");
        }
    }
                
}
*/