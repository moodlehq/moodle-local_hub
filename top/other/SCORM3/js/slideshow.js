// Slideshow Functions
//TNS recoded  - 3/15/01 new dyn api
//TNS 5/1/01   - added buildslide and removeslide func for animated slide memory leak ns6
//ABC 05/03/01 - added caching support for control buttons
//TNS 5/9/01   - modified buildslide and removeslide funcs for ns6 specific and ie & ns4x specific
//ABC 06/19/01 - enabled stacking of slides
//<script language=javascript>

//AutoPlay Functions
function autoPlaySlides(autoDelay, ContentID){
  
   var numImages    = eval("numImages"+ContentID+";")
   var currentSlide = eval("currentSlide"+ContentID+";")
   var autoPlay     = eval("autoPlay" + ContentID+";")
   
   var newDelay     = autoDelay * 1000;
   var imgCount = parseInt(numImages - 1);
   if ( imgCount <= currentSlide){
      stopAutoSlide(ContentID);
   }
   if (autoPlay){
      autoTimeOut = setTimeout('callNextSlide('+autoDelay+',"'+ContentID+'")',newDelay);  
       eval("autoTimeOut"+ContentID+" = "+autoTimeOut+";")
   }
   
   
}
function callNextSlide(autoDelay, ContentID) {
   var autoPlay = eval("autoPlay" + ContentID+";")
  
   nextSlide(ContentID);
   if (autoPlay){
     autoPlaySlides(autoDelay, ContentID); 
   }
}
function stopAutoSlide(ContentID){
   var autoPlay = eval("autoPlay" + ContentID+";")
   if (autoPlay){
   	// stop after one loop?
     eval("autoPlay" + ContentID +" = 0;")  
     eval("clearTimeout(autoTimeOut"+ContentID+");")
   }
}

//CONTROLS
function firstSlide(ContentID){
    var currentSlide = eval("currentSlide"+ContentID+";")
    var isText = eval("isText"+ContentID+";")
    var stack = eval("stack"+ContentID); 
    var numImages = eval("numImages"+ContentID+";")
    
    if (stack == true) {
        for (var i = 1; i < numImages; i++) {
            hideSlides( isText, ContentID, i, true);
        }
    }
    else {
        hideSlides( isText, ContentID, currentSlide);
    }
  
  currentSlide = 0;                 // set to first slide
  
  buildSlides( isText, ContentID, currentSlide);
}

function previousSlide(ContentID){
    var currentSlide = eval("currentSlide"+ContentID+";")
    var isText = eval("isText"+ContentID+";")
    
    hideSlides( isText, ContentID, currentSlide, true);
    
    if (currentSlide == 0) {              // == first slide
      currentSlide = 0;                 // stay at first slide
    }else{
      currentSlide--;
    }
    
    buildSlides( isText, ContentID, currentSlide);
    
}

function nextSlide(ContentID) {
    
 var currentSlide = eval("currentSlide"+ContentID+";")
 var isText = eval("isText"+ContentID+";")
 var numImages = eval("numImages"+ContentID+";")
 var stack = eval("stack"+ContentID); 
 
 if ((stack == true) && (currentSlide == (numImages - 1))) {
     firstSlide(ContentID);
     return;
 }
 
 hideSlides( isText, ContentID, currentSlide);
  
  if (currentSlide == (numImages - 1) ) { // == last slide
      currentSlide = 0;                  //return to first slide
  }else{
      currentSlide++;
  }
  
  buildSlides( isText, ContentID, currentSlide );
    
}

function lastSlide(ContentID){
  var currentSlide = eval("currentSlide"+ContentID+";")
  var numImages = eval("numImages"+ContentID+";")
  var isText = eval("isText"+ContentID+";")
  var stack = eval("stack"+ContentID); 
   
  hideSlides( isText, ContentID, currentSlide);
  
  currentSlide = numImages - 1;    // set to last slide
  
  if (stack == true) {
      for (var i = 0; i < numImages; i++) {
          buildSlides( isText, ContentID, i);
      }
      for (var i = 0; i < (numImages-1); i++) {
          hideSlides( isText, ContentID, i);
      }
  }
  else {
      buildSlides( isText, ContentID, currentSlide);
  }
      
}
//--END CONTROLS

//Remove Slides
function hideSlides( isText, ContentID, currentSlide, force) {
 
    var isNS6 = eval("ns6"+ContentID+";");
    var stack = eval("stack"+ContentID);
    var total = eval("numImages"+ContentID);
   
    if ((stack == false) || (force == true)) {
        if(isNS6){
            eval('myLayer'+currentSlide+ContentID+'.setHTML("");')
        }
		
		eval('myLayer'+currentSlide+ContentID+'.setVisible(false);\n')
    }
      
    if (isText){
        eval("textLayer"+currentSlide+ContentID+".setVisible(false);")
    }
    
    eval("countLayer"+currentSlide+ContentID+".setVisible(false);")
    
    return true;
}
  
//Build Slides
function buildSlides( isText, ContentID, currentSlide ) {
 var isNS6 = eval("ns6"+ContentID+";")
 //set global  
  eval("currentSlide"+ContentID+" = "+ currentSlide +";")
  if(isNS6){ 
      var slide = eval('slide'+currentSlide+ContentID+';')
      //alert('"<img src=\'../common/getmedia.ssc?id='+slide+'\'>"');
      eval('myLayer'+currentSlide+ContentID+'.setHTML("<img src=\'../common/getmedia.ssc?id='+slide+'\'>");')
  }
 eval('myLayer'+currentSlide+ContentID+'.setVisible(true);\n')       
      
 if (isText){
   eval("textLayer"+currentSlide+ContentID+".setVisible(true);")
 }
 eval("countLayer"+currentSlide+ContentID+".setVisible(true);")    
  
 return true;
  
}
//CONTROL IMAGE SWAP
function swapImage(name,image) {
    document.images[name].src = image.src;
}


