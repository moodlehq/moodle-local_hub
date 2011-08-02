//************************************************************
//  Fill in Blank functions
//************************************************************
//  Copyright (C) 1999 OutStart, Inc
//************************************************************
//  Modification History
//************************************************************
// KB         07/25/00    - Initial coding
// c.alsop    08/30/00    - Fixed CheckFIB for "begins with"
//                          and "ends with" option.
//  EA        10/20/00    - Added support for presentation build
//TNS         02/08/01    - Added Unordered functionality.  
//TNS         02/20/01    - took out of functions to have correct responses
//TNS         02/27/01    - added multiple answers to unordered arrays
//TNS         03/08/01    - added netscape 6 bullets
//TNS         03/22/01    - changed ordered 4 contains to indexOf matching
//ABC         05/07/01    - Fixed problem with Remediation Links
//TNS	      12/10/01    - added unescape to ordered text


ns4 = (document.layers)? true:false
ie4 = (document.all)? true:false
ns6=document.getElementById&&!document.all?1:0


var BuildNeeded = true;   // to tell presentation that this content type does a build
var add_HTML='';

   


function JudgeFIBQuestion(isOrdered,ContentID,linkScripts) {
  
  	eval("Attempts"+ContentID+"++;");
    var attemptcount = eval("Attempts"+ContentID);
    var trycount = eval("Tries"+ContentID);
    var numFeedback=0;
    var ls_HTML='';
    ls_HTML += addPopupSyles("Answer Feedback");   
    ls_HTML += addPopupHeader("Answer Feedback");
    ls_HTML += '<table width="100%" class="yellowbox" border=0 cellpadding="5" cellspacing="0"><tr><td colspan="2">&nbsp</td></tr>';
    if (attemptcount > trycount) {
        
        ls_HTML += '<tr><td colspan = "2"><b>Sorry, you have exceeded the allowed number of tries.</b></td></tr>';
        numFeedback ++;
        
    } else {    
    
        var numAnswers = eval("NumAnswers"+ContentID);
        var numLinks = eval("NumLinks"+ContentID);
        var numIncorrect = 0;
        var ls_incorrectHTML='';
        var add_HTML='';
       
        var ansArray=new Array();
        var theAnswer='';
            
        var theTextArray=new Array();
     
     
          //TNS 2/5/01  - added unordered
        
        if (isOrdered == 0) {
     
          //Must check for any ordered, case sensitive, possible multiple choices, multiple format answers.
          //The user input is used as one array that is compared to each format that has been selected.
          //In each format the users array is compared to a correct answer array specific to that format.
          //The answers are checked case sensitive first and non case if permited by the case array specific to 
          //its format.  Then the user text array is set to '' so that at the end of the loops any left over
          //answer is counted as a wrong answer.  The answer is set to a value so that it will not match more
          //than one answer.
        
        
          //Divide answer object into arrays based on format type, because only want answers to match once in the correct format since it loops all answers
          var usedAnswerArray = new Array();
          var theTextArray=new Array();
          var ansArray1=new Array();
          var ansArray2=new Array();
          var ansArray3=new Array();
          var ansArray4=new Array();
          var feedbackTextArray1 = new Array();
          var feedbackTextArray2 = new Array();
          var feedbackTextArray3 = new Array();
          var feedbackTextArray4 = new Array();
          var caseArray1 = new Array();
          var caseArray2 = new Array();
          var caseArray3 = new Array();
          var caseArray4 = new Array();
         
          
          //type of format array FIB matches
          var a = 0;    //Exact match 
          var b = 0;    //Starts with match
          var c = 0;    //Ends with match
          var d = 0;    //Contains match 
          
          //create arrays
          for (i=0; i<numAnswers; i++) {
            eval("answerObj = answer"+ContentID+"Array["+i+"]");
            
            theText = eval("escape(document.Question"+ContentID+"Form.Answer"+i+ContentID+".value);");
			//alert( theText );
			theText=RTrim(theText);
            theText=LTrim(theText);
			theText = unescape( theText );
            
            answerGroup=answerObj.answerText;
             
            if (answerGroup.length>0) { 
                answerGroup=unescape(answerObj.answerText);
                answerGroupArray=answerGroup.split(",");
                numChoices=answerGroupArray.length;
            } else {
                numChoices=0;
            }  
            //set empty text to null, we count '' as a correct answer          
            if (theText == '') {theText='null';}
            
            //Main text array of user input, loops in all 4 formats
            theTextArray[i] = theText;
            
            //used to give final answers into textboxes
            theAnswer=LTrim(answerGroupArray[0]);
            theAnswer=RTrim(answerGroupArray[0]);
            ansArray[i]= escape(theAnswer);
            
            //Create the Answer arrays, case arrays, and feedback text arrays.
            if (answerObj.ansFormat == 1 ) {
                if (answerGroup.length>0) { 
                  answerGroup=unescape(answerObj.answerText);
                  answerGroupArray=answerGroup.split(",");
                  var group = answerGroupArray[0];
                  
                  for (z=1; z < answerGroupArray.length; z++) {
                       group += "||" + answerGroupArray[z];
                   }
                }else{
                   var group = answerGroupArray[0];
                }
                
                ansArray1[a]=group;
                caseArray1[a] =answerObj.caseSensitive;
                feedbackTextArray1[a] = answerObj.feedbackText;
                a++;
            }
                      
            if (answerObj.ansFormat == 2 ) {
              if (answerGroup.length>0) { 
                answerGroup=unescape(answerObj.answerText);
                answerGroupArray=answerGroup.split(",");
                var group = answerGroupArray[0];
                  for (z=1; z < answerGroupArray.length; z++) {
                    group += "||" + answerGroupArray[z];
                  }
                }else{
                   var group = answerGroupArray[0];
                }
                
                ansArray2[b]=group;
                caseArray2[b] =answerObj.caseSensitive;
                feedbackTextArray2[b] = answerObj.feedbackText;
                b++;
            }
            if (answerObj.ansFormat == 3 ) {
              if (answerGroup.length>0) { 
                answerGroup=unescape(answerObj.answerText);
                answerGroupArray=answerGroup.split(",");
                var group = answerGroupArray[0];
                  for (z=1; z < answerGroupArray.length; z++) {
                    group += "||" + answerGroupArray[z];
                  }
                }else{
                   var group = answerGroupArray[0];
                }
                ansArray3[c]=group;
                caseArray3[c] =answerObj.caseSensitive;
                feedbackTextArray3[c] = answerObj.feedbackText;
                c++;
            } 
            if (answerObj.ansFormat == 4 ) {
               if (answerGroup.length>0) { 
                answerGroup=unescape(answerObj.answerText);
                answerGroupArray=answerGroup.split(",");
                var group = answerGroupArray[0];
                  for (z=1; z < answerGroupArray.length; z++) {
                    group += "||" + answerGroupArray[z];
                  }
                }else{
                   var group = answerGroupArray[0];
                }
                ansArray4[d]=group;
                caseArray4[d] =answerObj.caseSensitive;
                feedbackTextArray4[d] = answerObj.feedbackText;
                d++;
            }
          }
      
        //_____________________________________________________________________    
        
         if (a > 0 ) { var y=0;
         // document.write(ansArray1);
         var usedTextArray1 = new Array();
         
              ///////CHECK IF MATCH IN CASE SENSITIVE
            for (i=0; i<theTextArray.length; i++){      //User Input TEXT loop
              for(x=0; x<ansArray1.length; x++){        //Correct ANSWER loop
               //___________________________________________________
                   //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray1[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray1[x].split("||");}
                   else{                myAnswers = ansArray1[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   while ( noMatch == true && z<myAnswersLength) {//For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
               //____________________________________________________   
                  if ( theTextArray[i] == myAnswer ) { //MATCH
                     noMatch=false;  
                   if (feedbackTextArray1[x] > ''){
                       
                    if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray1[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray1[x])+'</td></tr>';
                    } 
                                  
                   }
                  usedTextArray1[y] = theTextArray[i];   //If match, add users input match to usedtextarray
                   
                    ansArray1[x]='!||@'; //set the correct answer array to a value that will not match, didnt use null or '' incase user enter
                    y++;                //increment used text count
                  
                  }//END MATCH
                 
                 z++;
                 
                }//END For Match
                
               
                
              }//END ANSWER loop
            }//END TEXT LOOP 
            
             ///////CHECK IF MATCH NOT IN CASE SENSITIVE   
            for (i=0; i<theTextArray.length; i++){          //TEXT loop
             for(x=0; x<ansArray1.length; x++){              //ANSWER loop
                 
               var myAnswers = new Array();            //Multiple answers were split with ||, create a new array with these answers   
               var isGroup = ansArray1[x].indexOf("||");
               if (isGroup != -1) { myAnswers = ansArray1[x].split("||");}
               else{                myAnswers = ansArray1[x];            }
               if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
               else{                                var myAnswersLength = 1;                }
               z=0;
               var noMatch=true;
               while ( noMatch == true && z<myAnswersLength) {//For Match
                  if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                  else                              { myAnswer = myAnswers; }
                  
               if (caseArray1[x] != "1" ) {                  //if not case sensitive:
                if ( theTextArray[i].toUpperCase() == myAnswer.toUpperCase()) { //MATCH
                     
                   if (feedbackTextArray1[x] > ''){
                   
                   
                    if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray1[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray1[x])+'</td></tr>';
                    } 
                   
                    }
                    usedTextArray1[y] = theTextArray[i];
                    
                    ansArray1[x]='!||@';
                    y++;
                    
                  }//END MATCH
                }//end CaseArray
                
                 z++;
                 
                }//END For Match
              }//END ANSWER loop
             
            }//END TEXT LOOP 
                            
             
             //delete the choice from the array
             var counter=0;
             for (i=0;  i<usedTextArray1.length;i++){
               for (j=0;  j<theTextArray.length;j++){
                 if ((usedTextArray1[i] == theTextArray[j]) && (counter < usedTextArray1.length)){ //if 
                   theTextArray[j] = ''; 
                   usedTextArray1[i] == "@||!";
                   counter++;
                 }
               }
             }                     
                      
    }//END A
    
    //_____________BEGINS WITH__________________________________________     
            
    if (b > 0 ) {       
     var y=0;
     var usedTextArray2 = new Array();
     
     
     
        ///////CHECK IF MATCH IN CASE SENSITIVE
        for (i=0; i<theTextArray.length; i++){  //TEXT loop
          for(x=0; x<ansArray2.length; x++){         //ANSWER loop
              //___________________________________________________
                   //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray2[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray2[x].split("||");}
                   else{                myAnswers = ansArray2[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
               textArray=new Array();
               textArray=theTextArray[i].split(" ");
               firstWord=textArray[0];
            if ( firstWord == myAnswer ) { //MATCH
                 
                noMatch=false;  

                if (feedbackTextArray2[x] > ''){
                    if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray2[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray2[x])+'</td></tr>';
                    }
                }
                usedTextArray2[y] = theTextArray[i];
                ansArray2[x]='!||@';
                y++;
                     
            }//END MATCH
                   z++; // increment while loop
                 
                }//END For Match
          }//END ANSWER loop
        }//END TEXT LOOP 
        
       
         ///////CHECK IF MATCH NOT IN CASE SENSITIVE   
        for (i=0; i<theTextArray.length; i++){  //TEXT loop
          for(x=0; x<ansArray2.length; x++){ //ANSWER loop
            //___________________________________________________
            //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray2[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray2[x].split("||");}
                   else{                myAnswers = ansArray2[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
            if (caseArray2[x] != "1" ) {
               textArray=new Array();
               textArray=theTextArray[i].split(" ");
               firstWord=textArray[0];
             
             if ( theTextArray[i].toUpperCase() == myAnswer.toUpperCase() || firstWord.toUpperCase() == myAnswer.toUpperCase())  { //MATCH
                 
                noMatch=false;  

                if (feedbackTextArray2[x] > ''){
                 if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray2[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray2[x])+'</td></tr>';
                    } 
                }
                usedTextArray2[y] = theTextArray[i];
                ansArray2[x]='!||@';
                y++;
              
             }//END MATCH
            
           
           }//end case loop
          
            z++; // increment while loop
                        
          }//END For Match     
         }//END ANSWER loop
        }//END TEXT LOOP 
         
                
         
         //delete the choice from the array
         var counter=0;
         for (i=0;  i<usedTextArray2.length;i++){
           for (j=0;  j<theTextArray.length;j++){
             if ((usedTextArray2[i] == theTextArray[j]) && (counter < usedTextArray2.length)){ //if 
               theTextArray[j] = ''; 
               usedTextArray2[i] == "@||!";
               counter++;
             }
           }
         }  
                 
             
                       
    }//end B
    
    
    //____ENDS WITH__________________________________________________________     
        
    if (c > 0 ) { 
               
     var y=0;
     var usedTextArray3 = new Array();
     
        ///////CHECK IF MATCH IN CASE SENSITIVE
        for (i=0; i<theTextArray.length; i++){  //User Input TEXT loop
          for(x=0; x<ansArray3.length; x++){         //Correct ANSWER loop
            //___________________________________________________
            //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray3[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray3[x].split("||");}
                   else{                myAnswers = ansArray3[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
              
              textArray=new Array();            //Split the text to find last word
              textArray=theTextArray[i].split(" ");
              theLength=eval(textArray.length-1);
              lastWord =textArray[theLength];
              
            if ( lastWord == myAnswer) { //MATCH
               
               noMatch=false;
               
               if (feedbackTextArray3[x] > ''){
                    if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray3[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray3[x])+'</td></tr>';
                    } 
               }
                usedTextArray3[y] = theTextArray[i];
                ansArray3[x]='!||@';
               y++;
                         
            }//END MATCH
  
            z++; // increment while loop
                
            }//END For Match
          }//END ANSWER loop
        }//END TEXT LOOP 
         ///////CHECK IF MATCH NOT IN CASE SENSITIVE   
        
        for (i=0; i<theTextArray.length; i++){  //TEXT loop
          for(x=0; x<ansArray3.length; x++){ //ANSWER loop
            //___________________________________________________
            //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray3[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray3[x].split("||");}
                   else{                myAnswers = ansArray3[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
            if (caseArray3[x] != "1" ) {
              textArray=new Array();            //Split the text to find last word
              textArray=theTextArray[i].split(" ");
              theLength=eval(textArray.length-1);
              lastWord =textArray[theLength];
             
             if ( theTextArray[i].toUpperCase() == myAnswer.toUpperCase() || lastWord.toUpperCase() == myAnswer.toUpperCase()) { //MATCH
                
                noMatch = false;
                
                if (feedbackTextArray3[x] > ''){
                 if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray3[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray3[x])+'</td></tr>';
                    } 
                }
                usedTextArray3[y] = theTextArray[i];
                ansArray3[x]='!||@';
                y++;
              
             }//END MATCH
            } //end casearray
                      
            z++; // increment while loop
           
            }//END For Match
          }//END ANSWER loop
         }//END TEXT LOOP 
               
         //delete the choice from the array
         var counter=0;
         for (i=0;  i<usedTextArray3.length;i++){
           for (j=0;  j<theTextArray.length;j++){
             if ((usedTextArray3[i] == theTextArray[j]) && (counter < usedTextArray3.length)){ //if 
               theTextArray[j] = ''; 
               usedTextArray3[i] == "@||!";
               counter++;
             }
           }
         }  
                        
    } //END C
    
    
    
    //_Contains____________________________________________________________________     
           
    if (d > 0 ) {  
  
    var y =0;
    var usedTextArray4 = new Array();
    
        for (i=0; i<theTextArray.length; i++){  //TEXT loop
             var theText  =theTextArray[i];
         for(x=0; x<ansArray4.length; x++){ //ANSWER loop
             //___________________________________________________
            //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray4[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray4[x].split("||");}
                   else{                myAnswers = ansArray4[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
            var theAnswer4=myAnswer;
           
            if ( theText.indexOf(theAnswer4) != -1 ) { //MATCH
                
               noMatch = false;
                
               if (feedbackTextArray4[x] > ''){
                if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray4[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray4[x])+'</td></tr>';
                    } 
               }
                usedTextArray4[y] = theTextArray[i];
                ansArray4[x]='!||@';
                y++;
               
            }//END MATCH
            
            z++; // increment while loop
                
            }//END For Match
          }//END ANSWER loop
        }//END TEXT LOOP 
        
       
        for (i=0; i<theTextArray.length; i++){  //TEXT loop
           var theText  =theTextArray[i];
               theText = theText.toUpperCase();
         for(x=0; x<ansArray4.length; x++){ //ANSWER loop
            //___________________________________________________
            //Multiple answers arrive in one answer cell seperated by ||. Create a new array with these answers  
                   var myAnswers = new Array();             
                   var isGroup = ansArray4[x].indexOf("||");
                   if (isGroup != -1) { myAnswers = ansArray4[x].split("||");}
                   else{                myAnswers = ansArray4[x];            }
                   if (typeof(myAnswers) == "object"){  var myAnswersLength = myAnswers.length; }
                   else{                                var myAnswersLength = 1;                }
                   z=0;
                   var noMatch=true;
                   
                   while ( noMatch == true && z<myAnswersLength) {                    //For Match
                      if (typeof(myAnswers) == "object"){ myAnswer = myAnswers[z]; }
                      else                              { myAnswer = myAnswers; }
            //____________________________________________________   
          if (caseArray4[i] != "1" ) {
            var theAnswer4=myAnswer;
                theAnswer4 = theAnswer4.toUpperCase();
            if ( theText.indexOf(theAnswer4) != -1 ) { //MATCH
               noMatch = false;
               if (feedbackTextArray4[x] > ''){
                if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(feedbackTextArray4[x])+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(feedbackTextArray4[x])+'</td></tr>';
                    } 
               }
                usedTextArray4[y] = theTextArray[i];
                ansArray4[x]='!||@';
                y++;
               
            }//END MATCH
            
           } //end caseloop
           
            z++; // increment while loop
                
            }//END For Match
          }//END ANSWER loop
         }//END TEXT LOOP 
       
            
                  //delete the choice from the array
         var counter=0;
         for (i=0;  i<usedTextArray4.length;i++){
           for (j=0;  j<theTextArray.length;j++){
             if ((usedTextArray4[i] == theTextArray[j]) && (counter < usedTextArray4.length)){ //if 
               theTextArray[j] = ''; 
               usedTextArray4[i] == "@||!";
               counter++;
             }
           }
         }  
                 
                 
    }//end D
        
        
// _______Count number incorrect_____________________________________________________________________________
            
          numIncorrect=0;
          for (i=0;i<theTextArray.length;i++){
              if (theTextArray[i]!=''){
                   numIncorrect++;
              }
          }
//___________________________________________________________________________________________________________
          
          
/////////////////// ORDERED ANSWERS ////////////////////////////////////////////////////////////////////////////         
          
     
 
       }else{
        for (i=0; i<numAnswers; i++) {
            
            eval("answerObj = answer"+ContentID+"Array["+i+"]");
            
            theText = eval("escape(document.Question"+ContentID+"Form.Answer"+i+ContentID+".value);");
            theText=RTrim(theText);
            theText=LTrim(theText);
            
            answerGroup=answerObj.answerText;
             
            if (answerGroup.length>0) { 
                answerGroup=unescape(answerObj.answerText);
                answerGroupArray=answerGroup.split(",");
                numChoices=answerGroupArray.length;
            } else {
                numChoices=0;
            }
            
            theCheck=0;
             
            for (j=0; j<numChoices; j++)  {
                theAnswer=answerGroupArray[j];
                if (answerObj.caseSensitive==0) {
                    theAnswer=theAnswer.toUpperCase();
                    theText=theText.toUpperCase();
                }
                
                theCheck+=CheckFIB(theText,theAnswer,answerObj.ansFormat);
            }
            if (theCheck>=1) {
                var theFeedback=answerObj.feedbackText;
                if (theFeedback.length>0) {
                     if (ns6) {
                          add_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+unescape(answerObj.feedbackText)+'</td></tr>';
                    }else{
                          add_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+unescape(answerObj.feedbackText)+'</td></tr>';
                    } 
                    numFeedback++;
                }
            } else {
                numIncorrect++;
            }
            theAnswer=LTrim(answerGroupArray[0]);
            theAnswer=RTrim(answerGroupArray[0]);
            ansArray[i]=theAnswer;
            theTextArray[i]=theText;
        }} //END ORDER/NONORDERED



////////////////////////////////////////////////////////////////////////////
// END ORDERED/UNORDERED ANSWERS

////////////////////////////////////////////////////////////////////////////
        if (numIncorrect > 0) {
          
            if (parent.LEARNING_OBJECT != null) {
              var assessment = parent.LEARNING_OBJECT.getAssessment();
              assessment.addItem(ContentID,false);
            }
           
            if (attemptcount >= trycount) {
                
                var theFeedback = eval('Incorrectfdbk'+ContentID);
                
                ls_HTML += '<tr><td colspan="2"><b>'+unescape(theFeedback)+'</b></td></tr>';
                if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
                ls_HTML += add_HTML;
                numFeedback ++;
               
               
                for (i=0; i<numAnswers; i++) {
					var theAns = unescape(ansArray[i]);               
                    eval("document.Question"+ContentID+"Form.Answer"+i+""+ContentID+".value=theAns;");
					eval("document.Question"+ContentID+"Form.Answer"+i+""+ContentID+".readOnly=true;");
                }

                for (i=0; i<numLinks; i++) {
                    eval("linkObj = link"+ContentID+"Array["+i+"]");
                    if ((linkObj.linkDisplay==1) || (linkObj.linkDisplay==3)) {
                        theLink=unescape(linkObj.theLink);
                        if (theLink.length>34) {
                            if (i==0) {
                                ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>';}
                            
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
          
            if (parent.LEARNING_OBJECT != null) {
              var assessment = parent.LEARNING_OBJECT.getAssessment();
              assessment.addItem(ContentID,true);
            }
            
            var theFeedback = eval('Correctfdbk'+ContentID);
            ls_HTML += '<tr><td colspan="2"><b>'+unescape(theFeedback)+'</b></td></tr>';
            if (numFeedback>0) { ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr>'; }
            ls_HTML += add_HTML;
            numFeedback ++;

            for (i=0; i<numLinks; i++) {
                eval("linkObj = link"+ContentID+"Array["+i+"]");
                if ((linkObj.linkDisplay==1) || (linkObj.linkDisplay==2)) {
                    theLink=unescape(linkObj.theLink);
                    if (theLink.length>34) {
                        if (i==0) {
                                ls_HTML += '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>';}
                            
                                if (ns6) {
                                    ls_HTML += '<tr><td><table height=2 width=2 bgcolor=#cc3300 border=0 bordercolor=#cc3300><tr><td style="font-size: 4pt;" bgcolor="#cc3300">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td width="100%">'+theLink+'</td></tr>';
                                }else{
                                    ls_HTML += '<tr><td><font color="#cc3300" font face="WingDings" size="0">n</FONT></td><td width="100%">'+theLink+'</td></tr>';
                                } 
                                                                                 
                           numFeedback ++;
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




function CheckFIB (theText,theAnswer,theFormat) {
    
theText = unescape(theText);
theAnswer = unescape(theAnswer);

    theReturn=0;
    
    theAnswer=LTrim(theAnswer);
    theAnswer=RTrim(theAnswer);
    
    if (theFormat==1) {
        if (theText==theAnswer) {   
            theReturn=1;
        }
    } else if (theFormat==2) {
        textArray=new Array();
        textArray=theText.split(" ");
        firstWord=textArray[0];
        if(theText==theAnswer || firstWord==theAnswer) {
            theReturn=1;
        }
        
    } else if (theFormat==3) {
        textArray=new Array();
        textArray=theText.split(" ");
        theLength=textArray.length-1;
        lastWord =textArray[theLength];
        if(theText == theAnswer || lastWord==theAnswer) {
            theReturn=1;
        }
    } else if (theFormat==4) {
                 
        if ( theText.indexOf(theAnswer) != -1 ) { //MATCH
         theReturn = 1;
        }
            
    }
 
    return theReturn;
    
}




