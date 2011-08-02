function homeButton( ) {

	this.clicked = goHome;
}

var HomeBtn = new homeButton();

function goHome()  { 

	if (docMode == 'PREVIEW')  {
		return 0;
	}
	
	if (parent.opener) {
		parent.opener.focus();
		parent.opener.location.reload();
	}
	parent.close();
 	return 0; 
	
} 
