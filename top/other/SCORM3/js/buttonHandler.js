function buttonHandler(button) {

	if (button) {

		// call the clicked event of the button 
		// then exectute system action depending on response
		// the object definition and the implementation for the clicked 
		// event are in a .js file that is included in the page when the
		// button is added by the servlet generating the page
		resp = button.clicked();

	}

	switch (resp) {

		// do nothing
		case 0:

			return true;

		// load LO
		case 1:

			eval('parent.location.href=\"'+loUrl+'\"');

		default:
		
			return true;
		
		
	}
}
