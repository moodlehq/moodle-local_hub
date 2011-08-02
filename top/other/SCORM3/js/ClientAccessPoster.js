//************************************************************
//  Client Access Poster
//  Copyright (C) 1999 OutStart, Inc
//
//  Public methods:
//     add            add items to client buffer
//     flush          flushes client buffer to server
//  Public Mutators
//     setWindowName  Window to use for sending information to server
//     setMaxCount    Maximum item count allowed in client buffer
//  Public Accessors
//     getMaxCount    Maximum item count allowed in client buffer
//     getCount       current item count in buffer
//   
//  Modification History
//
//  M Mader, M Draper - Initial coding
//************************************************************

function ClientAccessPoster()
{
    // instance variables
    this.sBuf = new String();
    this.count = 0;
    this.hdnWindow = "hdnWindow";
    this.maxCount = 5;
	this.maxBufSize = 1000

    // methods
    this.add = _add;
    this.flush = _flush;
    this.setWindowName = _setWindowName;
    this.setMaxCount = _setMaxCount;
    this.getMaxCount = _getMaxCount;
    this.getCount = _getCount;
}

//
// add log entry to buffer. If max count reached
// then flush buffer to server
//
function _add(sCategory, sSubCat, sData)
{
	var sTemp = "";

	sTemp += "cat=" + escape(sCategory);
	sTemp += "&subcat=" + escape(sSubCat);
	sTemp += "&data=" + escape(sData);

	// If the length of the new string plus the old buffer is
	// going to be larger then the maximum buffer size, then
	// we want to flush the information out before we continue
	// because there is a maximum buffer size that an address
	// can be.
	if ((sTemp.length + this.sBuf.length) > this.maxBufSize) this.flush();

    if (this.sBuf.length > 0) this.sBuf += "&";

    this.sBuf += sTemp;

	// if the current count is greater then the maximum count 
	// automatically flush the buffer to the servelet.
    if (this.count++ > this.maxCount) this.flush();
}

//
// flushes log buffer to server.
//
function _flush()
{
    if (this.sBuf.length > 0) {
        // This is where we will hit the hidden window to load the
        // contents of the buffer to the DB.
        open("/servlet/com.outstart.common.AccessPoster?" + this.sBuf, this.hdnWindow);
        this.sBuf = new String();
        this.count = 0;
    }
}

//
// Allows caller to set the window that the logging URL should
// be invoked in. Default will be used if not overridden.
//
function _setWindowName(shdnWindow)
{
    this.hdnWindow = shdnWindow;
}

//
// Accessors
//
function _getCount() { return this.count; }
function _getMaxCount() { return this.maxCount; }

// Mutators
function _setMaxCount(cnt)
{
    if (!isNaN(cnt)) this.maxCount = new Number(cnt);

    alert(this.maxCount);
}
