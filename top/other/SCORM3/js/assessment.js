//<script>
//************************************************************
//  Assessment Client functions
//************************************************************
//  Copyright (C) 1999 OutStart, Inc
//************************************************************
//  Modification History
//************************************************************
//
// E Alonso 08/09/00    - Initial coding
// ABC      10/12/00    - Changed select menus to checkboxes for matching (105)
//  EA      11/07/00    - Added Drag Drop interaction as assessment item.
// DPV      01/30/01    - Added SectionID to the argument list of function toggleRadioButtons.
// ABC      02/20/01    - Fixed logic error for Drag Drop distractors 
// ABC      02/22/01    - removed alert() debug message
// ABC      03/09/01    - Modified scoring on drag drops to judge based on center of drag
// ABC      04/13/01    - Added DynAPI2 support for drag drop
// ABC      04/19/01    - Fixed problem with incorrect weighting on shuffled Drag Drops
// ABC		10/15/01	- modified code to suppress double submit


/*

ns4 = (document.layers)? true:false
ie 	= (document.all)? true:false
ns6	= (document.getElementById&&!document.all)? true:false
*/


function JudgeAssessment(assess_id) {
	
	var assessPrefix = getPrefix( "assessment" );
    eval(assessPrefix+"document.forms.assessment.Grade.disabled=true;");
	
	//var pgCnt  = 17;
	//var lastForm = "Question00000000000000000000000A08A0011C_00000000000000000000000108A01A22Form";
	//var loadTest = eval("document.layers["+pgCnt+"].document."+lastForm);
	//for (i=0; i<pgCnt; i++)  {
	//	var divTest = eval("document.layers["+pgCnt+"]");
	//	alert( divTest );
	//}
	//alert( loadTest );
	var ls_response = '';
    var i = 0;
    
    // loop thru questions
    for (i = 0;i< numQuestions;i++) {

        SectionID = ''; // Initialize.
        // DPV 1/30/1 Change made, because the QuestionArray can now include SectionID
        var SectionContentID = eval( "QuestionArray["+i+"]");
        tempArray = SectionContentID.split("-");
        if ( tempArray.length > 1 ) {
            SectionID = tempArray[0] + '_';
            ContentID = tempArray[1];
        }
        else {
            ContentID = tempArray[0];
        }
		
		var docPrefix = getPrefix( "Question"+SectionID+ContentID+"Form" );
		if (docPrefix==null)  {			
			alert( "Not loaded!  Close window and reload." );
			return false;			
		}
        
		eval("ContentType = QuestionTypeArray["+i+"]");
        
        var is_Shuffled = eval("lb_Shuffled"+ContentID);
                                         
        var ls_answers = '';
        var numAnswers = eval("NumAnswers"+ContentID);
        var AnswerArray = new Array();
                
        // loop thru answers for this question
        for (var j = 0;j<numAnswers;j++) {
		
			// get the form object
			
			//for (x in form)  { alert(x); }
			
            if (is_Shuffled) {
                OrigOrder = eval("Shuffle"+ContentID+"Array["+j+"]");
            } else {
                OrigOrder = j;
            }
            //OrigOrder --;  // start array at position 0 instead of 1
            theType = parseInt(ContentType);
			//alert( theType );
            switch(theType) {
			
	              case 23:
				  		
						checked = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+ContentID+"["+j+"].checked");
						//alert(checked);
                        if (checked) {   // the answer was checked
                            AnswerArray[OrigOrder] = 'Y';  // put the answer in the slot of the original order
                        } else {
                            AnswerArray[OrigOrder] = 'N';
                        }
						
						
						//if (j == (numAnswers-1))  {
						//	AnswerArray[OrigOrder+1] = type;
						//}
				  		
						
				  break;
				  
				  case 24:
				  		
						text = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+ContentID+".value;");
						//alert(text);
						AnswerArray[OrigOrder] = text;
						
						//if (j == (numAnswers-1))  {
						//	AnswerArray[OrigOrder+1] = type;
						//}
				  		
						
				  break;
				
                  
				  case 27:  // drag drop  
                  		
						var drag        = eval("dragdrop"+ContentID+".drags["+(j+1)+"].dragObj");
                        
                        var midX        = ((drag.x)+((drag.w)/2));
                        var midY        = ((drag.y)+((drag.h)/2));
                    
                        var topX        = drag.targetX;
                        var topY        = drag.targetY;
                        var botX        = ((drag.targetX) + (drag.targetW));
                        var botY        = ((drag.targetY) + (drag.targetH));
                        
                        if (drag.isDistractor) {
                            AnswerArray[OrigOrder] = "D";
                        } 
                        else {
                            if ((midX > topX) && (midX < botX) && (midY > topY) && (midY < botY)) {
                                AnswerArray[OrigOrder] = 1;
                            }
							else  {
								AnswerArray[OrigOrder] = 0;
							}
                        }
						
                        break;
                        
                  case 31:
                  	
						answerValue = eval( docPrefix + "document.Question"+SectionID+ContentID+"Form.Answer"+(j+1)+ContentID+".value;");
                        
                        if (answerValue == '') {    answerValue = '-1,-1'; }
                        
                        AnswerArray[OrigOrder] = answerValue;
                        
                        break;

                  case 103:               // fill in the blank
        
                        answerValue = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+j+ContentID+".value;");
                        if ( RTrim(answerValue) == "" )  {
							answerValue = " ";
                        }
						else  {
							answerValue=RTrim(answerValue);
							answerValue=LTrim(answerValue);
						}
						//alert( answerValue );
                        AnswerArray[OrigOrder] = answerValue;    
                        
                        break;
                    
                  case 104:               // ordering
				  
  						selectedIndex = eval( docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+j+ContentID+".selectedIndex" );    
  						//alert( selectedIndex );
  						answerValue = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+j+ContentID+".options["+ selectedIndex +"].value;");
                        //alert( answerValue );
						AnswerArray[OrigOrder] = answerValue;
						
                        break;
                        
                  case 105:              // matching      
                            
                        var numChoices = eval("NumChoices"+ContentID);
                        var theAnswer = '';
                        var theMatch = '';
						var theMatches = '';
                            
                        for (c=0; c<numChoices; c++) {
                                
                            if (eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+j+ContentID+"["+c+"].checked==true;")) {
								// this is the ans that they chose - need to get it's original placement
								theNum = eval(docPrefix+"document.Question"+SectionID +ContentID+"Form.Answer"+j+ContentID+"["+c+"].value;")
		                		if (is_Shuffled) 
								{
									// if it's shuffled, grab it's original spot from the shuffle array
									theAnswer = eval("Shuffle"+ContentID+"Array["+(theNum-1)+"]");
									theMatch = (j+1) + "_" + theAnswer;	                                
                                }
                                else
								{
									// if it's not shuffled, then it is in it's original spot
								 	theAnswer = theNum;
									theMatch = (j+1) + "_" + theAnswer;	
                                }
								if (theMatches > '') { theMatches += '~'; }
								// add the Match to the string of matches
                                theMatches += theMatch;
								
                            }
                        }
                        
						// add to the AnswerArray (order does not reall matter here.  what matters is the matches
						// they will be compared to actuall matches later.  if you answer too many, then you will be penalized
                        AnswerArray[OrigOrder] = theMatches;
                        
                        break;
                        
                  default:                // T/F, MCMA, MCSA
    					
						if (numAnswers==1)  {
							checked = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+ContentID+".checked");
						} else  {
							checked = eval(docPrefix+"document.Question"+SectionID+ContentID+"Form.Answer"+ContentID+"["+j+"].checked");
						}
						//alert(checked);
                        if (checked) {   // the answer was checked
                            AnswerArray[OrigOrder] = 'Y';  // put the answer in the slot of the original order
                        } else {
                            AnswerArray[OrigOrder] = 'N';
                        }

                        
                        break;
                            
            }  // end switch
    
        }  // end for - next answer
        
        ls_answers = AnswerArray.join("~");
        
        if (ls_response > '') { ls_response += '^'; }
        ls_response += SectionID + ContentID+':'+ls_answers;


        if (is_Shuffled) {
            ls_shuffle = eval("Shuffle"+ContentID+"Array.toString();");
            ls_response += ";ShuffleOrder:"+ls_shuffle;
        }
    
    }  // end for - next question

    //alert(ls_response);
	
	var assessPrefix = getPrefix( "assessment" );
    eval(assessPrefix+"document.assessment.answers.value = ls_response;");
    
	return true;
	//return false;
	
}


function getPrefix( formname )  {
	
	if (document.layers)  {
		//alert("checking " + formname);
		var layerName = "";
		for (k=0; k<document.layers.length; k++)  {	
			var form = eval("document.layers["+k+"].document."+formname);
			if ( form != null )
				layerName = document.layers[k].name;
			
		}
		if (layerName=="")
			return null;
		//alert( " found in " + layerName )
		var pre = "document."+layerName+".";
	}
	else {
		var pre = "";		
	}
	
	//alert(pre);
	return pre;
}

/*
// DPV 1/30/1 Added SectionID to the argument list.
function toggleRadioButtons(selButton,ContentID,SectionID) {

    var ls_sectionID = ''; // Initialize
    
    if ( arguments.length == 3 ) {
        ls_sectionID = SectionID+'_';
    }
    
	var numAnswers = eval("NumAnswers"+ContentID);
    for (var i = 1;i<=numAnswers;i++) {
       
        aButton = eval("document.forms.Question"+ls_sectionID + ContentID+"Form.Answer"+i+ContentID); 
        if (aButton.value != selButton.value) {
            aButton.checked = false;
        }
    
    }    
}
*/
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

function page(num) {
	
	updatePageNumber(num);
	currentPage = num;
	if (document.layers) {
        for (var i=1;i<=pageCount;i++) {
            eval("document.layers['Page"+i+"'].visibility = 'hide';");
        }
        eval("document.layers['Page"+num+"'].visibility = 'show';");
    }
    else {
        for (var i=1;i<=pageCount;i++) {
            eval("document.getElementById(\'Page"+i+"\').style.visibility = \'hidden\';");
        }
        eval("document.getElementById(\'Page"+num+"\').style.visibility = \'visible\';");
    }
}

function previousPage(){
	if ( currentPage == 1 ) {
		// They are already on the first page
		return false;
	}
	else {
		page(currentPage-1);
	}
	
	
}

function nextPage(){
	if ( currentPage == pageCount ) {
		// They are already on the last page
		return false;
	}
	else {
		page(currentPage+1);
	}
}

function updatePageNumber(currentPage) {
	
	var activePageClass = "ba-activePage";
	var pageNumberClass = "ba-pagenumberstyle";
	
	if (mode=="survey") {
		activePageClass = "bs-activePage";
		pageNumberClass = "bs-pagenumberstyle";
	}
	
	var newHTML = "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"1\">\n<tr>\n<td class=\""+pageNumberClass+"\">\n" + eval("pageNumberLabel")+ "</td><td class=\""+pageNumberClass+"\" width=\"99%\">\n";
	for (var i=0;i<pageCount;i++) {
		var page = i+1;
		if (currentPage==page) {
			newHTML += "<span class=\""+activePageClass+"\">"+page+"</span>&nbsp;\n";
		}
		else {
			newHTML += "<a class=\""+pageNumberClass+"\" href=\"#\" onclick=\"page("+page+");return false;\">"+page+"</a>&nbsp;\n";
		}
	}
	newHTML+="</td>\n</tr>\n</table>\n";
	
	if (document.layers) {
		
		nshtml = "<span class=\"nspadding\">"+newHTML+"</span>";

		document.layers['outerpagenavdiv'].document.layers[0].document.open();
		//alert( nshtml );
		document.layers['outerpagenavdiv'].document.layers[0].document.write(nshtml);
		document.layers['outerpagenavdiv'].document.layers[0].document.close();
	}
	else {
		var pagediv = document.getElementById("pagenavdiv");
			pagediv.innerHTML = newHTML;
	}
}

function score() {
    document.forms['assessment'].submit();
}
