//<script>

OSLMSAPI=function() 
{
    
	  this.Version        = '2.0';
    this.Vendor         = 'OutStart, Inc';
    this.debug          = false;
    
    this.LastErrorCode  = '0';
    
    // predefined error codes
    this.ErrorCodeList          = new Array();
    this.ErrorCodeList['0']     = "No Error";
    this.ErrorCodeList['101']   = "General Exception";
    this.ErrorCodeList['201']   = "Invalid argument error";
    this.ErrorCodeList['202']   = "Element can not have children";
    this.ErrorCodeList['203']   = "Element not an array.  Cannot have count.";
    this.ErrorCodeList['301']   = "Not initialized";
    this.ErrorCodeList['401']   = "Not implemented error";
    this.ErrorCodeList['402']   = "Invalid set value, element is a keyword";
    this.ErrorCodeList['403']   = "Element is read only.";
    this.ErrorCodeList['404']   = "Element is write only";
    this.ErrorCodeList['405']   = "Incorrect Data Type";

}

OSLMSAPI.prototype.LMSInitialize=function(parameter)
{
    if (this.debug) {
      alert("LMSInitialize");
    }
    return true;
}

OSLMSAPI.prototype.LMSFinish=function(parameter)
{
    if (this.debug) {
      alert("LMSFinish");
    }
    return true;
}

OSLMSAPI.prototype.LMSGetLastError=function(parameter)
{
    if (this.debug) {
      alert("LMSGetLastError");
    }
    return this.LastErrorCode;
}

OSLMSAPI.prototype.LMSGetErrorString=function(errorCode)
{
    if (this.debug) {
      alert("LMSGetErrorString:"+errorCode);
    }
    return this.ErrorCodeList[errorCode];
}

OSLMSAPI.prototype.LMSGetDiagnostic=function(errorCode)
{
    if (this.debug) {
      alert("LMSGetDiagnostic:"+errorCode);
    }
    return true;
}

OSLMSAPI.prototype.LMSGetValue=function(element)
{
    if (this.debug) {
      alert("LMSGetValue:"+element);
    }
    return true;
}

OSLMSAPI.prototype.LMSSetValue=function(element,value)
{
    if (this.debug) {
      alert("LMSSetValue:"+element+":"+value);
    }
    return true;
}

OSLMSAPI.prototype.LMSCommit=function(parameter)
{
    if (this.debug) {
      alert("LMSCommit");
    }
    return true;
}

top.API = new OSLMSAPI();