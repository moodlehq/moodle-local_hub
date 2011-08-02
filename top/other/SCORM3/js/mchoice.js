// ************************************************************
//  Multiple Choice Question CLIENT functions
// ************************************************************
//  Copyright (C) 1999 OutStart, Inc
// ************************************************************
//  Modification History
// ************************************************************
// E Alonso 7/24/00    - Initial coding
//      EA  10/20/00    - Added support for presentation builds

ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false

var BuildNeeded = true;   // to tell presentation that this content type does a build

function TestMe()  {
    alert('Hello Kirby');
}

function JudgeQuestion(ContentID,type) {

    // increment attempts
	eval("Attempts"+ContentID+"++");
    var attemptcount = eval("Attempts"+ContentID);
    
	var trycount = eval("Tries"+ContentID);
	var numAnswers = eval("NumAnswers"+ContentID);
    var numIncorrect = 0;
    var numFeedback = 0;
    var feedback_html ='';
    var ls_html = '';
    
    ls_html += addPopupSyles("Answer Feedback");   
    ls_html += addPopupHeader("Answer Feedback");
    
    ls_html += '<table width="100%" class="yellowbox" border="0" cellpadding="5" cellspacing="0"><tr><td colspan="2">&nbsp</td></tr>';  
    
    if ( attemptcount <= trycount ) {  
        for (var i = 1;i<=numAnswers;i++) {
            
            eval("answerObj = answer"+ContentID+"Array["+(i-1)+"]");
            checked = eval("document.Question"+ContentID+"Form.Answer"+ContentID+"["+(i-1)+"].checked") 
               
            if (checked) {   // the answer was checked
			
				//alert ( "ans feedback = " + answerObj.feedbackText );
                
                // john said to display feedback for  anything that was checked, right or wrong!
                if (answerObj.feedbackText != "") {
                    feedback_html += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</font></td><td width="100%">'+unescape(answerObj.feedbackText)+'</td></tr>';
                    numFeedback++;
                }
                
                if (answerObj.correctFlag) {  // correctly checked
                    // show checkmark
                    if(navigator.appName=="Netscape") {
                          eval("document.images['Img"+i+ContentID+"'].src = CkMark" +ContentID+ ".src");
                    } else{
                          eval("document.images.Img"+i+ContentID+".src = CkMark"+ContentID+".src");
                    }
                } else {                    // checked, but shouldn't have been
                    numIncorrect ++;
                    if(navigator.appName=="Netscape") {
                          eval("document.images['Img"+i+ContentID+"'].src = XMark" +ContentID+ ".src");
                    } else{
                          eval("document.images.Img"+i+ContentID+".src = XMark"+ContentID+".src");
                    }
                }

            } else {  //  answer not checked
                if (answerObj.correctFlag) { // should have been checked
                    numIncorrect ++;
                } else {
                    if(navigator.appName=="Netscape") {   // correctly left unchecked
                          eval("document.images['Img"+i+ContentID+"'].src = Blank" +ContentID+ ".src");
                    } else{
                          eval("document.images.Img"+i+ContentID+".src = Blank"+ContentID+".src");
                    }
                }
            }
                    
        }
 
        // that was the last try
        if (attemptcount >= trycount) {

            // some wrong
            if (numIncorrect > 0) {
                var feedback = eval('Incorrectfdbk'+ContentID);
                ls_html += '<tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
                if (numFeedback>0) { ls_html+='<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_html += feedback_html;
                ls_html += getFeedbackLinks(ContentID,3);
                numFeedback ++;
                
                if (parent.LEARNING_OBJECT != null) {
                  var assessment = parent.LEARNING_OBJECT.getAssessment();
                  assessment.addItem(ContentID,false);
                }
            
            // all correct
            } else {
                var feedback = eval('Correctfdbk'+ContentID);
                ls_html += '<tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
                if (numFeedback>0) { ls_html+='<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_html += feedback_html;
                ls_html += getFeedbackLinks(ContentID,2);
                
                if (parent.LEARNING_OBJECT != null) {
                  var assessment = parent.LEARNING_OBJECT.getAssessment();
                  assessment.addItem(ContentID,true);
                }
                
            }
            numFeedback ++;
            showAnswers(ContentID,numAnswers,type);
            
        // not the last try
      } else {

            // got it correct     
            if (numIncorrect <= 0) {
                var feedback = eval('Correctfdbk'+ContentID);
                ls_html += '<tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
                if (numFeedback>0) { ls_html+='<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_html += feedback_html;
                ls_html += getFeedbackLinks(ContentID,2);
                numFeedback ++;
                
                if (parent.LEARNING_OBJECT != null) {
                  var assessment = parent.LEARNING_OBJECT.getAssessment();
                  assessment.addItem(ContentID,true);
                }
                
            // not correct
            } else {
                ls_html += '<tr><td colspan="2"><b>Please try again.</b></td></tr>';
                ls_html += feedback_html;                
                //if (numFeedback>0) { ls_html+='<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                numFeedback ++;
                
                if (parent.LEARNING_OBJECT != null) {
                  var assessment = parent.LEARNING_OBJECT.getAssessment();
                  assessment.addItem(ContentID,false);
                }
                
            }
        }
        
        // update try counter
        triesLeft = (trycount-attemptcount);
        var trytext = 'Tries Remaining: ' + triesLeft;
        layerWrite('Tries'+ContentID, null ,'<span class="trycounter">'+trytext+'</span>');
    
    } else { // no more tries
        
        ls_html += '<tr><td colspan="2"><b>Sorry, you have exceeded the allowed number of tries.</b></td></tr>';
        numFeedback ++;
    }
    
    if (numFeedback > 0) {  // There is feedback to show
        ls_html += '</table><br></br><center><a href="javascript:self.close()">Close</a></center>';
	    // display feedback
        ShowFeedback(ls_html);
    }
    
    return false;
	
}
function showAnswers(ContentID,numAnswers) {

    for (var i = 1;i<=numAnswers;i++) {
        eval("answerObj = answer"+ContentID+"Array["+(i-1)+"]");

        if (answerObj.correctFlag) {  // a correct answer
            // show checkmark and check ckbox
            if(navigator.appName=="Netscape") {
                 eval("document.images['Img"+i+ContentID+"'].src = CkMark" +ContentID+ ".src");
                 eval("document.Question"+ContentID+"Form.Answer"+ContentID+"["+(i-1)+"].checked = true");
            } else {
                  eval("document.images.Img"+i+ContentID+".src = CkMark"+ContentID+".src");
                  eval("document.all.Question"+ContentID+"Form.Answer"+ContentID+"["+(i-1)+"].checked = true");
            }
        } else {
            // hide chmark or xmark
            if(navigator.appName=="Netscape") {
                  eval("document.images['Img"+i+ContentID+"'].src = Blank" +ContentID+ ".src");
                  eval("document.Question"+ContentID+"Form.Answer"+ContentID+"["+(i-1)+"].checked = false");
            } else {
                  eval("document.images.Img"+i+ContentID+".src = Blank"+ContentID+".src");
                  eval("document.all.Question"+ContentID+"Form.Answer"+ContentID+"["+(i-1)+"].checked = false");
            }
        }      
    }
    
}

function getFeedbackLinks(ContentID,displayType) {
    
    ls_link='';
	var numLinks = eval("NumLinks"+ContentID);

        var linkCnt=0;
        for (var i = 1;i<=numLinks;i++) {
            eval("linkObj = link"+ContentID+"Array["+(i-+1)+"]");
            if ((linkObj.linkDisplay == 1) || (linkObj.linkDisplay == displayType))  {
                theLink=unescape(linkObj.theLink);
                if (theLink.length>34) {
                    if (linkCnt==0) { ls_link = '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>'; }
                    ls_link += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theLink+'</td></tr>';
                    linkCnt++;
                }
            }
        }
        return ls_link;
   
}

