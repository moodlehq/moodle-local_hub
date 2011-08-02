function nextLoButton( ) {

	this.clicked = goNextLo;
}

var NextLOBtn = new nextLoButton();

function goNextLo() {
	
	if (nextLoID == "0")  {
		return 0;
	}
	
	loUrl = "RloPage?curID="+curID+"&courseID="+courseID+"&loID="+nextLoID+"&studentID="+studentID+"&themeID="+themeID+"&exportInProgress="+bExported;
    
	return 1;

}
