var isNS = (navigator.appName == "Netscape");
var isDHTML = (parseInt(navigator.appVersion) > 3);

layerObj = (isNS) ? 'document.layers' : 'document.all';
styleObj = (isNS) ? '' : '.style'; 

function topicJumpTo(jumpTopicID) {

	topicNum = findTopic(jumpTopicID);
	if (topicNum != 0)  {
		NextPage(topicNum);
	}
}
function findTopic(topicID)   {

	topicNum = 0;	
	topicCount = topicIDVals.length;
	for (i=0; i<topicCount; i++) {
		tempTopic = topicIDVals[i];
		if (topicID == tempTopic)  {
			topicNum = i+1;
		}
	}
	return topicNum;
}

function ChangeTopic(newTopicValue) {
	
	if (numTopics < 1) return;
    
	// have copy of these arrays in menu now	
    newTopicID  = topicIDVals[newTopicValue -1];
    topicType   = topicTypes[newTopicValue-1];
    
    var objectTypeID = parseInt(topicTypes[newTopicValue -1]);

    if (bExported) {
		  //link objects launch inline
	   	if ((objectTypeID == 7) && (inlineExternal == true)) {
	   		newTopicRef = unescape(parent.frames.topiclist.fileList[newTopicValue -1]);
	   	}
	   	else {
       		newTopicRef = topicIDVals[newTopicValue -1] + ".htm";
		  }
    }
    else {
    	  //link objects launch inline
       	if ((objectTypeID == 7) && (inlineExternal == true)) {
       		newTopicRef = "ExternalContentViewer?objectID="+newTopicID;
		}
		else {
       		newTopicRef = "ViewTopic?loID="+loID+"&topicID="+newTopicID + "&object_type="+topicType+"&studentID="+studentID+"&studentRole="+studentRole+"&loStatus="+loStatus+"&themeID="+themeID+"&browser="+browser+"&docMode="+docMode+"&curID="+curID+"&courseID="+courseID;
		}
    }
    
    // change the content window
    eval('parent.frames.content.location.href="'+newTopicRef+ '"');
}

function cleanUp() { 
  if (window.popup) { 
       popup.close(); 
  } 
} 

function NextPage(pageNumber) {
	
	if (numTopics < 1) return;

	currentTopic = curTopic; 
    prevTopic     = currentTopic;
    
    // increment the chapter counter    
    if (pageNumber > '') {
        currentTopic = pageNumber;
    } else {
    	if (currentTopic == numTopics) { 
            return; 
        } else { 
			currentTopic++; 
        }
    }    
    
    UpdateIndicator(prevTopic,currentTopic);
    
	curTopic = currentTopic;
	
    // update the variable in the nav frame if it exists
	if (parent.frames.topiclist)  {
	   	parent.frames.topiclist.current = currentTopic;
		parent.frames.topiclist.aryTopicHistory[currentTopic - 1]=true; 
		parent.frames.topiclist.updateTopicsVisited(); 	
	}

    ChangeTopic(currentTopic);
    showText(currentTopic+' '+lotopiccounttext+' '+numTopics);
}

function PrevPage(pageNumber) {
	
	if (numTopics < 1) return;

   	currentTopic = curTopic;
    prevTopic     = currentTopic;

    if (pageNumber > '') {
        currentTopic = pageNumber;
    } else {
    	if (currentTopic == 1) { 
			return; 
		} else { 
			currentTopic--; 
		}
    }

    UpdateIndicator(prevTopic,currentTopic)
 
    // update the global variables
	curTopic = currentTopic;
	
	// update the counter in the nav frame if it exists
    if (parent.frames.topiclist)  {
	   	parent.frames.topiclist.current = currentTopic;
	    parent.frames.topiclist.aryTopicHistory[currentTopic - 1]=true; 
	    parent.frames.topiclist.updateTopicsVisited(); 	
    }   
    ChangeTopic(currentTopic);

    showText(currentTopic+' '+lotopiccounttext+' '+numTopics);
}

// Display slide text in the TextLayerBox element, using browser-appropriate code. 
function showText(count) { 
    
  if (document.layers) { 
      document.layers.TopicCount.document.open();
      document.layers.TopicCount.document.writeln("<span class=topicCountText >" + count + " </span>");
      document.layers.TopicCount.document.close();
  } else {
      document.getElementById('TopicCount').innerHTML = count; 
  } 
  
}  

function changeImages() { 
	if (menuLoaded) {		
		if (document.images) { 
	    	for (var i=0; i<changeImages.arguments.length; i+=2) { 
    	  		document[changeImages.arguments[i]].src = eval(changeImages.arguments[i+1] + ".src"); 
				
      		}
		} 
  	} 
} 


function flipImage(layer,imgName,imgObj) {

	if (menuLoaded) {		
		if (document.images) {
    	    if (document.layers && layer!=null)  {
				eval('document.'+layer+'.document.images["'+imgName+'"].src = '+imgObj+'.src');
	       	} else  {
				document.images[imgName].src = eval(imgObj+".src");
    	    }
		}
	}
}
