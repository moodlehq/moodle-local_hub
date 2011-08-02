function GenPDAButton( ) {

	this.clicked = goGenPDA;
}

var GenPDABtn = new GenPDAButton();

function goGenPDA() {

	doGenPDA();
	
	return 0;

}

function doGenPDA() {

	var pdaURL = "LaunchCachedZip?objectID="+loID+"&objectTypeID=3&typeID=5&startCache=yes&userID="+studentID+"&requestType=export&requestSubType=pda&themeID="+themeID;

    var popup = window.open(pdaURL,loID,"height="+popupHeight+",width="+popupWidth+",location=no,menubar=no,scrollbars=yes,left=0,top=0,toolbar=no,status=no,resizable=yes");

    if (popup.opener == null)  {
		popup.opener = window;
    }
    popup.focus();
	
}
