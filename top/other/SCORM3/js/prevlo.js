function prevLoButton( ) {

	this.clicked = goPrevLo;
}

var PrevLOBtn = new prevLoButton();

function goPrevLo() {
	
	if (prevLoID == "0")  {
		return 0;
	}
	
	loUrl = "RloPage?curID="+curID+"&courseID="+courseID+"&loID="+prevLoID+"&studentID="+studentID+"&themeID="+themeID+"&exportInProgress="+bExported;
    
	return 1;

}
