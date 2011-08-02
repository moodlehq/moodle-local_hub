//<script language="javascript">

function osDragDrop(contentid,dragbase,trycount,aryDrags,intTries,mediaWidth) {
    
    this.dragbase           = dragbase;
    this.contentid          = contentid;
    this.state              = false;
    this.safeX              = mediaWidth;
    this.tries              = intTries;
    this.attempts           = 0;
    this.finished           = false;
    this.ztop               = 1;
    
    this.reset              = resetDragDrop;
    this.check              = checkDragDrop;
    this.updateState        = updateDragDropState;
    this.solve              = solveDragDrop;
    this.feedback           = showDragDropFeedback;
    this.links              = getDragDropLinks;
    this.hint               = showDragDropHint;
    this.trycount           = trycount;
    this.updateTryCount     = updateDragDropTries;
    
    if (aryDrags != '') {
        this.drags = new Array(aryDrags.length-1);
        for (var i=1; i<aryDrags.length; i++) {
            var aryParms = aryDrags[i].split(",");
            eval("this.drags["+i+"] = new osDrag('"+this.contentid+"',"+dragbase+","+i+","+aryParms[0]+","+aryParms[1]+","+aryParms[2]+","+aryParms[3]+","+aryParms[4]+","+aryParms[5]+","+aryParms[6]+","+aryParms[7]+","+aryParms[8]+","+aryParms[9]+","+aryParms[10]+","+aryParms[11]+");");
        }
    }
    
    return this;
}

function updateDragDropState() {
    for(var i=1; i<this.drags.length; i++) {
        if (this.drags[i].dragObj.state == false) {
            this.state = false;
            return false;
        }
    }
    this.state = true;
    
    return;
}

function checkDragDrop() {
    
    if (!this.drags) { alert('there are no drag images to check'); return; }
    
    if (this.finished) {
        if (confirm('Click OK to Reset')) { this.reset(); }
        return;
    }
    
    this.attempts++;                                                            // increment try counter
    
    if (this.attempts > this.tries) {
        this.feedback();
        return;
    }
    
    if (this.attempts >= this.tries) {                                          // last try
        for(var i=1; i<this.drags.length; i++) {                                // loop through the drag objects
            var drag = this.drags[i].dragObj;
            if ((drag.state) && (!drag.isDistractor)) {
                drag.show();
            }
            drag.solve();                                                       // snap
            drag.lock();
            this.finished = true;                                               // set the finished flag
        }
    }
    else {                                                                      // more tries left
        for(var i=1; i<this.drags.length; i++) {                                // loop through the drag objects
            var drag = this.drags[i].dragObj;
            if (!drag.safe) {                                                   // moved the drag out of the safe zone
                if (drag.state) {                                               // correct
                    drag.solve();                                               // snap
                    drag.lock();                                                // lock all correctly placed non distractors
                }
                else {                                                          // incorrect
                    drag.reset();                                               // move incorrects back
                    drag.show();
                }
                if (drag.isDistractor) drag.state == true;
            }
            else {
                drag.reset();
            }
        }
    }
    
    if (this.state) { this.finished = true; }                                   // set the finished flag
    
    if (parent.LEARNING_OBJECT != null) {
      var assessment = parent.LEARNING_OBJECT.getAssessment();
      assessment.addItem(this.contentid,this.state);
    }
    
    this.updateTryCount();
    this.feedback();
    
    return;

}

function resetDragDrop() {
    if (!this.drags) { return; }
    this.attempts = 0;                                                          // set attempts to zero
    this.updateTryCount();                                                      // update the try counter
    this.state    = false;                                                      // set the drag drop state to false
    this.finished = false;
    for(var i=1; i<this.drags.length; i++) {                                        
        var drag = this.drags[i].dragObj;
        drag.unlock();                                                          // make the drag 'draggable' again
        if (drag.isDistractor) { drag.state = true; }                           // distractors initialize as true
        else {                   drag.state = false; }                          // valid drag images initialize as false
        drag.reset();                                                           // move the drag to start position
    }
    
    return;
}

function solveDragDrop() {
    for(var i=1; i<this.drags.length; i++) {
        var drag = this.drags[i].dragObj;
        drag.solve();
        drag.lock();
    }
    
    return;
}

function showDragDropFeedback() {
    
    numFeedback     = 0;
    ls_html         = '';
    feedback_html   = '';
    
    ls_html += addPopupSyles("Answer Feedback");   
    ls_html += addPopupHeader("Answer Feedback");
    ls_html += '<table width="100%" class="yellowbox" border="0" cellpadding="5" cellspacing="0">\n';
    ls_html += '  <tr>\n';
    ls_html += '    <td colspan="2">&nbsp;</td>\n';
    ls_html += '  </tr>\n';
    
    if (this.state) {
        var feedback = eval('Correctfdbk'+this.contentid); 
    }
    else {                      
        if (this.attempts >= this.tries) {
            var feedback = eval('Incorrectfdbk'+this.contentid); 
        }
        else {
            var feedback = "Please Try Again";
        }
    }
    
    if (this.attempts > this.tries) {
        ls_html += '<tr><td colspan="2">Sorry, you have exceeded the allowed number of tries.</td></tr>\n';
        ls_html += '<tr><td colspan="2">You may reset the interaction and try again.</td></tr>\n';
    }
    else {
        ls_html += '  <tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
        
        for(var i=1; i<this.drags.length; i++) {
            var drag = this.drags[i].dragObj;
            if ((drag.state == true) && (!drag.isDistractor) && (!drag.feedbackShown)) {
                if ((drag.feedback != 'null') && (drag.feedback != '') && (drag.feedback != ' ')) {
                    ls_html += '  <tr>\n';
                    ls_html += '    <td><table><tr><td bgcolor="#cc3300"><img src="../themes/thumb_clear.gif" width="3" height="3"></td></tr></table></td>\n';
                    ls_html += '    <td width="100%">'+unescape(drag.feedback)+'</td>\n';
                    ls_html += '  </tr>\n';
                }
            }
            drag.feedbackShown = true;
        }
        
        if (this.state) {   ls_html += this.links(2); }
        else {
            if (this.finished == true) {
                ls_html += this.links(3); }
        }
    }
    
    ls_html += '</table><br></br><center><a href="javascript:window.close(); window.opener.focus();">Close</a></center>\n';
    
    ShowFeedback(ls_html);
    
    return;
}

function getDragDropLinks(displayType) {
    
    ls_link='';
	var numLinks = eval("NumLinks"+this.contentid);

    var linkCnt=0;
    for (var i=1; i<=numLinks; i++) {
        eval("linkObj = link"+this.contentid+"Array["+(i-+1)+"]");
        if ((linkObj.linkDisplay == 1) || (linkObj.linkDisplay == displayType))  {
            theLink=unescape(linkObj.theLink);
            if (theLink.length>34) {
                if (linkCnt==0) { ls_link = '<tr><td colspan="2"><hr width="80%"></hr></td></tr><tr><td colspan="2">For more information:</td></tr>'; }
                ls_link += '<tr><td><table><tr><td bgcolor="#cc3300"><img src="../themes/thumb_clear.gif" width="3" height="3"></td></tr></table></td><td width="100%">'+theLink+'</td></tr>';
                linkCnt++;
            }
        }
    }
    return ls_link;
}

function showDragDropHint() {
    ShowHint(this.contentid);
}

function osDrag(contentid,dragbase,dragid,startX,startY,width,height,targetX,targetY,targetW,targetH,feedback,isDistractor,imgpath,border) {

    this.dragObj                = new DynLayer(null,startX,startY,(width+2),(height+2));    // drag object
    
    this.dragObj.mark           = new DynLayer(null,0,0,width,height);                        // check image
    
    this.dragObj.contentid      = contentid;
    this.dragObj.dragbase       = dragbase;
    this.dragObj.dragid         = dragid;
    this.dragObj.imgpath        = imgpath;
    
    this.dragObj.safe           = true;
    this.dragObj.startX         = parseInt(startX);
    this.dragObj.startY         = parseInt(startY);
    this.dragObj.width          = parseInt(width)+2;
    this.dragObj.height         = parseInt(height)+2;
    this.dragObj.targetX        = parseInt(targetX);
    this.dragObj.targetY        = parseInt(targetY);
    this.dragObj.targetW        = parseInt(targetW);
    this.dragObj.targetH        = parseInt(targetH);
    
    this.dragObj.realTargetX    = parseInt(targetX) + (parseInt(parseInt(targetW) /2) - parseInt(parseInt(width) /2));
    this.dragObj.realTargetY    = parseInt(targetY) + (parseInt(parseInt(targetH) /2) - parseInt(parseInt(height) /2));
    
    this.dragObj.isDistractor   = eval(isDistractor);
    
    if (isDistractor) {           this.dragObj.state = true; }
    else {                        this.dragObj.state = false; }

    this.dragObj.feedback       = feedback;
    this.dragObj.feedbackShown  = false;

    this.dragObj.check          = checkDragTarget;
    this.dragObj.reset          = resetDrag;
    this.dragObj.solve          = solveDrag;
    this.dragObj.show           = showDragState;
    this.dragObj.clear          = clearDrag;
    this.dragObj.top            = topDrag;
	
	this.border					= border;

    this.dragObj.lock=function() {
        DragEvent.disableDragEvents(this)
    }
    
    this.dragObj.unlock=function() {
        DragEvent.enableDragEvents(this)
    }
      
    this.dragListener=new EventListener(this.dragObj)

        this.dragListener.onmousedown=function(e) {
            target=e.getTarget();
             if (!target.state) { target.clear(); }
             target.top();
             //if (DynAPI.browser.ns5) { target.setBgImage(''); }
        }
        
        this.dragListener.ondragend=function(e) {
            target=e.getTarget();
            target.check();
        }

    this.dragObj.addEventListener(this.dragListener)

    var imgsrc = this.dragObj.imgpath;
		
    var alttext = 'drag'+this.dragObj.contentid+'_'+this.dragObj.dragid;
    
    //if (DynAPI.browser.ns5) {   this.dragObj.setBgImage(imgsrc);  }
    
	//removed alt text
    //this.dragObj.setHTML('<img src="'+imgsrc+'" border="1" alt="'+alttext+'">');
	this.dragObj.setHTML('<img src="'+imgsrc+'" border="'+ border +'">');
	
    DragEvent.setDragBoundary(this.dragObj)
    DragEvent.enableDragEvents(this.dragObj)
    
    this.dragObj.dragbase.addChild(this.dragObj);
    
    this.dragObj.addChild(this.dragObj.mark);
    
    return this;
}

function resetDrag() {
    if (DynAPI.browser.ns5) {   this.moveTo(this.startX,this.startY); }
    else {                      this.slideTo(this.startX,this.startY); }
    this.clear();
    this.safe = true;
    this.feedbackShown = false;
    return;
}

function solveDrag() {
    if (this.isDistractor) {
        if (this.x < this.startX) {
            this.moveTo(this.startX,this.startY);
            this.show();
        }
    }
    else {
        this.moveTo(this.realTargetX,this.realTargetY);
        this.state = true;
        this.show();
    }
    return;
}

function showDragState() {
    var imgsrc = CkMark.src;
    if (!this.state) imgsrc = XMark.src;
    this.mark.setHTML('<img src="'+imgsrc+'">');
    this.mark.setZIndex(this.mark.getZIndex()+1);
    return;
}

function topDrag() {
    var z = eval("dragdrop"+this.contentid+".ztop++")
    this.setZIndex(z)
    return;
}

function clearDrag() {
    this.mark.setHTML('');
    return;
}

function checkDragTarget() {
    
    var midX        = ((this.x)+((this.w)/2));
    var midY        = ((this.y)+((this.h)/2));

    var topX        = this.targetX;
    var topY        = this.targetY;
    var botX        = ((this.targetX) + (this.targetW))
    var botY        = ((this.targetY) + (this.targetH))
    
    var safeX    = eval("dragdrop"+this.contentid+".safeX");
    
    if (this.x < safeX) { this.safe = false; }
    else {                this.safe = true; }
    
    if (this.isDistractor) {
        if (this.safe) {        this.state = true; }
        else {                  this.state = false; }
    }
    else {
        if ((midX > topX) && (midX < botX) && (midY > topY) && (midY < botY)) {
            this.state = true;
        }
        else {
            this.state = false;
        }
    }
    
    eval("dragdrop"+this.contentid+".updateState()");           // update DragDrop state
    
    return;
}

function updateDragDropTries() {
    var count = (this.tries - this.attempts);
    this.trycount.setHTML('<span class="trycounter">Tries Remaining: '+count+'</span>');
    return;
}