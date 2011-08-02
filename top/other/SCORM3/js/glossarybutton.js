function glossaryButton( ) {

	this.clicked = goGlossary;
}

var GlossaryBtn = new glossaryButton();

function goGlossary() {

	glossaryType = glossaryType.toLowerCase();

	if (glossaryType == "embedded")  {

		NewExternalContainer(glossaryUrl);

	} else  {
		
		if (glossaryUrl != "")  {
			popup = window.open(glossaryUrl,"GlossaryWin","scrollbars=yes,width=550,height=500,top=70,left=220,resizable=yes");
		} else  {
			alert("No Glossary currently available"); 
		}
	}
	
	return 0;

}

