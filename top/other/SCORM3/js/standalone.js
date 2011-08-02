
var taskOptions = new Array();
taskOptions[0] = "external_content";
taskOptions[1] = "media";
taskOptions[2] = "content_version";

// mirror only
var mirrorOptions = new Array();
mirrorOptions[0] = "content_version";

var expType="move";

// when page loads, disable content check, external, radio, and media radio
function optionsLoad( theForm ) 
{
	// always disable content checkbox
	theForm.options[0].disabled=true;
	theForm.options[0].checked=true;
	
	// if content checkout then disable move/checkout for all options.  they can be enabled later
	if (theForm.options[0].value=="content")
	{
		setToDisabled( theForm, "0" );
		setToDisabled( theForm, "1" );
	}
	
	if (saType == "template") 
	{	
		for (j=0; j<taskOptions.length; j++)
		{
			//var name = taskOptions[j];
			var theRadio = eval( "theForm."+taskOptions[j] );
			if (theRadio!=null)
				theRadio[1].disabled=true;
		}
		if (theForm.content[1]!=null)
			theForm.content[1].disabled=true;
	}
	
}

function setToDisabled( theForm, index )
{
	for (i=0; i<theForm.options.length; i++)
	{
		var theRadio = eval( "theForm." + taskOptions[i] );
		if (theRadio!=null)
			theRadio[index].disabled=true;
		
	}
}

function toggleRadio( theForm ) 
{
	// if the content move is checked then disable the other's checkout
	// we cannot checkout other options without checking out content
	if ( theForm.content!=null && theForm.content[0].checked ) 
	{
		var cnt=0;
		// loop thru options, unchecking checkout if necessary
		for (i in taskOptions)
		{
			var theRadio = eval( "theForm."+taskOptions[cnt] );
			if (theRadio!=null)
			{
				theRadio[1].disabled=true;
				if (theForm.options[cnt+1].checked)
				{
					// option was checked so toggle radio
					eval( "theForm."+taskOptions[cnt]+"[1].checked=false;" );
					eval( "theForm."+taskOptions[cnt]+"[0].checked=true;" );
				}
			}
			cnt++;
		}
		// set exptype var to "checkout"
		expType="move";
	}
	else if (theForm.content!=null)
	{	
		// content checkout is selected so enable other option radios if they have been checked
		var cnt=0;
		for (i in taskOptions)
		{
			if (theForm.options[cnt+1]!=null && theForm.options[cnt+1].checked)
			{	
				var name = taskOptions[cnt];
				if (name!="content_version")
					eval( "theForm."+name+"[1].disabled=false;" );
			}
			cnt++;	
		}
		// set exptype var to "checkout"
		expType="checkout";
	}
	else if (theForm.external_content!=null && theForm.external_content[1].checked)
	{
		expType="checkout";
	}
	else if (theForm.media!=null && theForm.media[1].checked)
	{
		expType="checkout";
	}
	else 
	{
		expType="move";
	}
}


function toggleCheckBox( theForm ) 
{
	var cnt=0;
	
	for (i in taskOptions)
	{
		if (theForm.options[cnt+1].checked==true) {
			eval( "theForm."+taskOptions[cnt]+"[0].disabled=false" );
			eval( "theForm."+taskOptions[cnt]+"[0].checked=true" );
			if (theForm.content[1].checked && taskOptions[i]!="content_version") {
				eval( "theForm."+taskOptions[cnt]+"[1].disabled=false" );
			}
		} else {
			eval( "theForm."+taskOptions[cnt]+"[0].checked=false" );
			eval( "theForm."+taskOptions[cnt]+"[1].checked=false" );
			eval( "theForm."+taskOptions[cnt]+"[0].disabled=true" );
			eval( "theForm."+taskOptions[cnt]+"[1].disabled=true" );
		}
		cnt++;
	}
	
}

function getContentOptionsString( theForm ) 
{
	var xml = "";
	var roOption = "";
	var option;
	var optionLength = theForm.options.length;
	
	xml += "<options>";
	
	for (i=0; i<optionLength-1; i++)
	{
		if (theForm.options[i].checked)
		{
			option = theForm.options[i].value;
			xml += "<" + option +	">" + getReadOnlyOption(theForm,option) + "</" + option + ">";
		}
		
	}
	
	xml += "<encrypt>false</encrypt></options>";
	
	return xml;	
}

function getAdminOptionsString( theForm ) 
{
	var strOption = "admin_info";
	if (theForm.type.value=="20")
	{
		strOption = "metadata_dictionary";
	}
	else if (theForm.type.value=="8")
	{
		strOption = "media";
	}
	else if (theForm.type.value=="12")
	{
		strOption = "external_content";
	}
	var xml = "<options><"+ strOption +"/><encrypt>false</encrypt></options>";
	return xml;	
}

function getReadOnlyOption(theForm,itemName) 
{
	var retVal = "readonly";
	var isChecked = eval( "theForm."+itemName+"[1].checked" );
	if (isChecked) 
	{
		retVal = "checkout";
	}
	return retVal;
}

function requestContentCheckout( theForm )
{
	var taskServer = "TaskServer?" + 
					 "userID=" + theForm.userID.value +
					 "&objectID=" + theForm.objectID.value +
					 "&objectTypeID=" + theForm.objectTypeID.value +
					 "&requestType=" + expType +
					 "&requestSubType=content" + 
					 "&notifyUser=0" + 
					 "&taskOptions=" + getContentOptionsString(theForm) +
					 "&redirect=" + theForm.redirect.value;
	
	
					 
	//alert( "URL = " + taskServer );
	
	parent.tasks.location.href = taskServer;
	
}

function requestAdminCheckout( theForm )
{
	// default to admin
	var subType = "admin";
	// if metadata object type
	if (theForm.type.value=="20")
	{
		subType="metadata%20dictionary";
	}
	else if (theForm.type.value=="12")
	{
		subType="external%20content";
	}
	else if (theForm.type.value=="8")
	{
		subType="media";
	}
		
	var taskServer = "TaskServer?" + 
					 "userID=" + theForm.userID.value +
					 "&requestType=export" + 
					 "&requestSubType=" + subType +
					 "&notifyUser=0" + 
					 "&taskOptions=" + getAdminOptionsString(theForm) +
					 "&redirect=" + theForm.redirect.value;
	
	
					 
	//alert( "URL = " + taskServer );
	
	parent.tasks.location.href = taskServer;
	
	
	
}








