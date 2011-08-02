var alerts = false;

function CommandCheck( contentID ) {

	/*
	   var noFinalCorrect = true;
	   var noFinalIncorrect = true;
	           
	   if (FinalCorrect == null  || FinalCorrect <= '' ){  FinalCorrect = "Correct!"; }
	          
	   if (FinalIncorrect == null  || FinalIncorrect <= '' ){  FinalIncorrect = "Incorrect!"; }
	*/
    var numCommands = eval( "numCommands"+contentID );
	var numTries	= eval( "numTries"+contentID );
	var stepNum   	= eval( "stepNum"+contentID );
	var tryNum   	= eval( "tryNum"+contentID );
	
	
	var theCommand 	 = eval("commandArray"+contentID+"["+stepNum+"];");
	theCommand = unescape(theCommand);
	var theFormValue = eval("document.commandForm"+contentID+".commandText.value;");
    
	if (alerts)  {
		//alert( "num commands = " + numCommands );
		alert( "step num = " + stepNum );
		//alert( "num tries = " + numTries );
		alert( "command = " + theCommand );
		alert( "form val = " + theFormValue );
	}
	
	if ( stepNum > (numCommands-1) ) {
	
		eval("alert(commandComplete"+contentID+");");
		
   	} else {
		
		var commandCheck    = CorrectCheck("check",theCommand,theFormValue);
      			
		if (commandCheck) { //correct
			
			if (stepNum==(numCommands-1)) {
       			alertFinalCorrect( contentID ); //Final Correct Response
      		}
			updateForm( contentID, stepNum );			
			// increment step count
			incrementStep( contentID );
			// set tries per step to 0
			initializeTries( contentID );
			

      	} else { //incorrect
			
			if (stepNum==(numCommands-1) && tryNum==(numTries-1)) {
				alertFinalIncorrect( contentID );
				
				updateForm( contentID, stepNum );
				incrementStep( contentID );

            } else {
			
				alertRemediation( contentID, tryNum );
                
				if (tryNum==(numTries-1)) {
				
					updateForm( contentID, stepNum );
					incrementStep( contentID );
					initializeTries( contentID );
				}
				else {
				
					decrementTries( contentID );
				}
				
            }
			
      	}
		
   	}    
	
	cleanupTry( contentID );
	
	return true;

}

function CorrectCheck(type,theCommand,formValue) {

	var theReturn   = false;
    var commLength  = theCommand.length;
    var commLast    = theCommand.charAt(commLength-1);
	
    while (commLast == " ") {
    	var theCommand  = theCommand.substring(0,commLength-1);
        commLength      = theCommand.length;
        commLast        = theCommand.charAt(commLength-1);
    }

	var commArray       = theCommand.split(" ");
	var commArrayLength = commArray.length;
    
	if (type=="check") {
	
		var formLength = formValue.length;
        var formLast   = formValue.charAt(formLength-1);
        if (formLast == " ") {
			while (formLast == " ") {
				formValue=formValue.substring(0,formLength-1);
				formLength=formValue.length;
				formLast=formValue.charAt(formLength-1);
			}
		}
		
      	var formArray=formValue.split(" ");
		formArrayLength=formArray.length;

            if (formArrayLength==commArrayLength) {
            	cnt=0;
            	for (i=0 ; i<formArrayLength ; i++) {
				if (WordCheck(commArray[i],formArray[i])) {
					cnt++;
				}
			}
			if (cnt==formArrayLength) 	{ theReturn=true; }
			else 						{ theReturn=false; }
		}

	} else {

		theReturn="";
        for (i=0 ; i<commArrayLength ; i++) {
			//theReturn += CommandFix(commArray[i]) + "%20";
			theReturn += CommandFix(commArray[i]) + " ";
		}

	}

	return theReturn;        

}

function WordCheck(theCommand,formValue) {


      var theReturn   = false;    
      var firstParen  = theCommand.indexOf("(");
      var lastParen   = theCommand.indexOf(")");

      if (firstParen>0) {
      	var shortString = theCommand.substring(0,firstParen);
      } else {
      	var shortString = theCommand;
      }
      var shortStringLength = shortString.length;
      var formValueLength   = formValue.length;
	  
	  if (formValueLength>=shortStringLength) {
            var theDiff             = formValueLength-shortStringLength;
            var startChar           = firstParen+1;
            var addString           = theCommand.substring(startChar,startChar+theDiff);
            var compareString       = shortString+addString;
            var compareStringLength = compareString.length;
            if (formValueLength>compareStringLength) 	{ theReturn=false; }
      		else if (formValue==compareString) 			{ theReturn=true; }
      }
	
	return theReturn;        

}
            
function CommandFix(theCommand) {

	var firstParen = theCommand.indexOf("(");
      var lastParen  = theCommand.indexOf(")");
      if (firstParen>0) {
      	var shortString  = theCommand.substring(0,firstParen);
            var addString    = theCommand.substring(firstParen+1,lastParen);
            var returnString = shortString+addString;
      } else {
		var returnString = theCommand;
      }
      
	return returnString;

}

function ResetCommand(contentID, initialPrompt) {
	
	var initialPrompt = unescape(initialPrompt);
	eval( "document.commandForm" + contentID + ".commandTerminal.value = initialPrompt;" );
	eval( "tryNum"+contentID+" = 0;" );
	eval( "stepNum"+contentID+" = 0;" );
	cleanupTry( contentID );
	return true;
	
}

function cleanupTry( contentID )  {
	
	eval( "document.commandForm"+contentID+".commandText.value = '';" );
	eval( "document.commandForm"+contentID+".commandText.focus();" );
	return true;
	
}

function alertFinalCorrect( contentID )  {
	eval( "alert( unescape(finalCorrect"+contentID+") );" );
}

function alertFinalIncorrect( contentID )  {
	eval( "alert( unescape(finalIncorrect"+contentID+") );" );
}

function alertRemediation( contentID, tryNum )  {
	eval( "alert( unescape(remedArray"+contentID+"["+tryNum+"]) );" );
}

function updateForm( contentID, stepNum )  {
	
	var output 		= eval( "outputArray"+contentID+"["+stepNum+"]" );
	var prompt 		= eval( "promptArray"+contentID+"["+stepNum+"]" );
	var theCommand 	= eval( "commandArray"+contentID+"["+stepNum+"];" );
	
	var displayCommand  = CorrectCheck("fix",theCommand);
    
	var oldString 		= eval("document.commandForm" + contentID + ".commandTerminal.value");
	var appendString 	= " " + displayCommand + "\n" + output + "\n" + prompt;//"help me";// = document.commandForm" + contentID + ".commandTerminal.value + appendString");
	var newString 		= oldString + unescape(appendString);
	
	if (alerts)  {		
		//alert( "old string = " + oldString );
		//alert( "append string = " + appendString );
		//alert( "new string = " + newString );
	}
    
	eval("document.commandForm" + contentID + ".commandTerminal.value = newString;");
	
	return true;
			
}

function incrementStep(contentID)  {
	eval( "stepNum"+contentID+"++;" );
}

function decrementTries(contentID)  {
	eval( "tryNum"+contentID+"++;" );
}

function initializeTries(contentID)  {
	eval( "tryNum"+contentID+" = 0;" );
}