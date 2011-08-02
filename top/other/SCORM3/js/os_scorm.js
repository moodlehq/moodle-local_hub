var findAPITries = 0;
var apiAdapter = null;
var apiHandle;

function getAPIHandle()
{
   //if (apiHandle == null)
   //{
      apiHandle = getAPI();
   //}

   return apiHandle;
}

function doLMSInitialize()
{
   var api = getAPIHandle();
   if (api == null)
   {
      alert("Unable to locate the LMS's API Implementation.\nLMSInitialize was not successful.");
      return "false";
   }

   var result = api.LMSInitialize("");

   if (result.toString() != "true")
   {
      var err = ErrorHandler();
   }

   return result.toString();
}

function doLMSSetValue(parameter,value) 
{
  
   var api = getAPIHandle();
   if (api == null)
   {
      alert("Unable to locate the LMS's API Implementation.\nLMSSetValue was not successful.");
      return "false";
   }
   else
   {
      // call the LMSSetValue function that should be implemented by the API
      
      var strValue = new String( value );
      
      var result = api.LMSSetValue(parameter,strValue);
      
      if (result.toString() != "true")
      {
         var err = ErrorHandler();
      }

    }
}

function doLMSFinish()
{
  
  if (self.LEARNING_OBJECT != null) {
    var assessment = self.LEARNING_OBJECT.getAssessment();
    doLMSSetValue("cmi.core.score.raw",assessment.getScore());
  }
  
  var api = getAPIHandle();
  if (api == null)
  {
    alert("Unable to locate the LMS's API Implementation.\nLMSFinish was not successful.");
    return "false";
  }
  else
  {
    
    var result = api.LMSFinish("");
    if (result.toString() != "true")
    {
       var err = ErrorHandler();
    }
  
  }
  
  return result.toString();
}

function findAPI(win)
{
   // Check to see if the window (win) contains the API
   // if the window (win) does not contain the API and
   // the window (win) has a parent window and the parent window  
   // is not the same as the window (win)
   
   while ( (win.API == null) && 
           (win.parent != null) && 
           (win.parent != win)) 
   {	
      // increment the number of findAPITries
      findAPITries++;

      // Note: 7 is an arbitrary number, but should be more than sufficient
      if (findAPITries > 7) 
      {
         alert("Error finding API -- too deeply nested.");
         return null;
      }
      
      // set the variable that represents the window being 
      // being searched to be the parent of the current window
      // then search for the API again
      win = win.parent;
   }
   return win.API;
}

function getTop(win) {
	
	while((win.parent!=null) && (win.parent != win)) {
		win=win.parent;
	}

	return win;

}

function getAPI()
{
   // start by looking for the API in the current window
   var theAPI = findAPI(window);
   
   //if (theAPI == "undefined") theAPI = null;

   // if the API is null (could not be found in the current window)
   // and the current window has an opener window
   
   var win = getTop(window);
   
   if ( (theAPI == null) && 
        (win.opener != null) && 
        (typeof(win.opener) != "undefined") )
   {
      // try to find the API in the current window's opener
      theAPI = findAPI(win.opener);
   }

   // if the API has not been found
   if (theAPI == null)
   {
      // Alert the user that the API Adapter could not be found
      alert("Unable to find an API adapter");
   }
   return theAPI;
}



function scormOnLoad() {
    
    alert("searching for adapter");
    
    alert( "adapter = " + apiAdapter );
    
    apiAdapter = getAPI();
    
    if ( exists(apiAdapter) ) {
         alert( "adapter = no good" );
    } else {
        alert( "uh, oh" );
    }
}