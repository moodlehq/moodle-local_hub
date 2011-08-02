// Simulation Functions
// Copyright (C) 1999 OutStart, LLC
//
// TNS 4/5/01  - Recoded to use new dynapi2, ns6 compatable
//<script>

function Simulation_CheckAnswer(ContentID,NumOfTries,NumOfAnt,NumOfUnAnt,CheckCase) {
  var NumOfUnAnt = NumOfUnAnt - 1; //minus final
  var ContentID = ContentID.toString();
  var UnAntCount = eval("UnAntCount"+ContentID);
  var Tries = eval("Tries"+ContentID);
     
  if (Tries >= NumOfTries){
      DisplayLastSlide(ContentID);
  }else{
     
    
    if (ns4)    { var answer = eval("document.simform"+ContentID+".simanswer"+ContentID+".value");}
	else if (ie){ var answer = eval("document.all.simform"+ContentID+".simanswer"+ContentID+".value");}
    else        { var answer = eval("document.getElementById(\'simAnswerID"+ContentID+"\').value");   }
    
    answer = escape(answer);  
    
    var match = false;  
    for(i=0; i<NumOfAnt; i++){
      eval("antRes = AntResponse"+ContentID+"[i];")
        if (CheckCase ==0){
          antRes = antRes.toUpperCase();
          answer = answer.toUpperCase();
        }
        if (antRes == answer) { 
          if (NumOfAnt > 0){
              eval("currentSlide"+ContentID+".setVisible(false);")
              eval("AntLayer"+i+ContentID+".setVisible(true);")
              eval("currentSlide"+ContentID+" = AntLayer"+i+ContentID );//set global current slide in use var
              eval('alert(unescape(AntFeedback'+ContentID+'[i]));')
          }
          match=true;
          eval("Tries"+ContentID+"=0");
        } 
    }
   
    if (match == false){ 
        
        if (NumOfUnAnt > 0) { //must have at least one remediation slide or leave base img.
            eval("currentSlide"+ContentID +".setVisible(false);")
            eval("UnAntLayer"+UnAntCount+ContentID+".setVisible(true);")
            eval("currentSlide"+ContentID+" = UnAntLayer"+UnAntCount+ContentID );//set global current slide in use var
        }
        eval("alert(unescape(UnFeedback"+ContentID+"[UnAntCount]));")
        if (UnAntCount < NumOfUnAnt){ //Increment until last unanticipated slide using this slide until tries run out.
           eval("UnAntCount"+ContentID+"++;") 
        }         
       eval("Tries"+ContentID+"++");
     }
  }//end : if (Tries >= NumOfTries)
  
  return false;
}

function DisplayLastSlide(ContentID){
    
  eval("currentSlide"+ContentID+".setVisible(false);")
  eval( "LastLayer" +ContentID+ ".setVisible(true)");
  eval("alert(unescape(LastFeedback"+ContentID+"));")
  
}    
    