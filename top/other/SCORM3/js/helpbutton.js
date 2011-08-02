function helpButton( ) {

	this.clicked = goHelp;
}

var HelpBtn = new helpButton();

function goHelp() {

	doHelp();
	
	return 0;

}

function doHelp() {

    switch(helpType) {
			
	    case 'lo':
		
			var objectTypeID = 3;
			break;
			
	    case 'topic':
		
			var objectTypeID = 2;
			break;
			
	    default:
		
			break;
    }	
	
    if (helpID == "0") {      
    
    	alert("No Help currently available");
    
	} else  {

		if (helpType == "url")  {
		
			var popup = window.open(helpID);
			
	        if (popup.opener == null)   {  
				popup.opener = window;
	        }
	        popup.focus();
			
		} else  {

		    var helpURL = "HelpMain?objectID="+helpID+"&objectTypeID="+objectTypeID+"&studentID="+studentID+"&themeID="+themeID;
		    var popup = window.open(helpURL,helpID,"height="+popupHeight+",width="+popupWidth+",location=no,menubar=no,scrollbars=yes,left=0,top=0,toolbar=no,status=no,resizable=yes");

		    if (popup.opener == null)  {
				popup.opener = window;
		    }
		    popup.focus();
		}
	}
}
