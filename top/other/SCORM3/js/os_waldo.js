/*
 * Copyright (c) 2000, 2001 OutStart, Inc. All rights reserved.
 *
 * $Id: os_waldo.js,v 1.5.8.4 2002/07/10 23:32:58 vaishnavi Exp $
 */
 
function findWaldo (contentid,base,tryCnt,aryAreas,tries,ordered) {
    
    this.base           = base;
    this.contentid      = contentid;
    this.attempts       = 0;
    this.tries          = tries;
    this.tryCnt         = tryCnt;
    this.ordered        = new Boolean(ordered);
    this.state          = false;
    this.finished       = false;
    this.matches        = 0;
    this.areasFound     = 0;
    this.orderArray     = new Array();
    this.clickArray     = new Array();
    
    this.reset          = resetWaldo;
    this.feedback       = showWaldoFeedback;
    this.links          = getWaldoLinks;
    this.hint           = showWaldoHint;
    
    this.judge          = judgeWaldoClick;
    this.getArea        = getWaldoArea;
    this.getNextArea    = getNextWaldoArea;
    this.updateTries    = updateWaldoTries;
    this.showClick      = showWaldoClick;
    this.hideClicks     = hideWaldoClicks;
    this.checkState     = checkWaldoState;
    this.solve          = solveWaldo;
        
    if (aryAreas != '') {
        this.areasArray = new Array(aryAreas.length);
        for (var i=1; i<aryAreas.length; i++) {
            var parms = aryAreas[i].split(',');
            this.areasArray[i] = new findWaldoArea(this,i,parms[0],parms[1],parms[2],parms[3],parms[4],parms[5],parms[6]);
            if (parms[6] == true) {             
                this.orderArray[this.matches] = parms[5];
                this.matches++;
            }
        }
        this.orderArray = this.orderArray.sort(waldoCompare);
    }
    
    this.loaded = true;
    return this;
}

function findWaldoArea (parent,id,x,y,w,h,feedback,order,correct) {
    
    this.parent         = parent;
    this.base           = parent.base;
    this.id             = id;
    this.x              = parseInt(x);
    this.y              = parseInt(y);
    this.w              = parseInt(w);
    this.h              = parseInt(h);
    this.bx             = (this.x + this.w);
    this.by             = (this.y + this.h);
    this.feedback       = feedback;
    this.order          = order;
    this.correct        = correct;
    this.found          = false;
    this.state          = false;
    
    this.show           = showWaldoArea;
    
    if (this.correct == 1) { 
        var borderstyle = "waldocorrect"; 
        var image = CkMark;
    }
    else {                   
        var borderstyle = "waldoincorrect";
        var image = XMark;
    }

    this.area = new DynLayer(null,this.x,this.y);
    this.area.setHTML('<div class="'+borderstyle+'"><img src="'+Blank.src+'" width="'+(this.w-4)+'" height="'+(this.h-4)+'"></div>');
    this.area.setVisible(false);
    this.base.addChild(this.area);
    
    this.mark = new DynLayer(null,1,1,image.width,image.width);
    this.mark.setHTML('<img src="'+image.src+'" width="'+image.width+'" height="'+image.width+'">');
    this.mark.setVisible(true);
    this.area.addChild(this.mark);
        
    return;
}

function judgeWaldoClick (e) {
    
    this.attempts++;
    
    if (!this.areasArray) { alert('Sorry, there are no correct areas'); return; }
    
    if ((this.state == true) || (this.finished == true)) {
        if (confirm('Click OK to reset')) { 
            this.reset();
        }
        return;
    }

    this.click  = new waldoClick(e);
    var area    = this.getArea();
    
    
    if (this.ordered == true) {
        
        var nextArea = this.getNextArea();
        var lastOrder = this.orderArray[(this.orderArray.length-1)];
        
        if (this.attempts < this.tries) {
            if (area == false) {
                this.showClick(false);
            }
            else {
                if (area.correct == 1) {
                    if (area.order == nextArea.order) {
                        this.hideClicks();
                        area.show();
                        //this.showClick(true);
                        area.found = true;
                        area.state = true;
                        this.areasFound++;
                        if (area.order < lastOrder) {
                            this.attempts = 0;
                        }
                    }
                    else {
                        this.showClick(false);
                    }
                }
                else {
                    area.show();
                }
            }
        }
        else {
            this.hideClicks();
            if (area == false) {
                if (nextArea.order < lastOrder) {
                    this.attempts = 0;
                }
                nextArea.found = true;
                nextArea.show(true);
                this.areasFound++;
            }
            else {
                if (area.correct == 1) {
                    if (area.order == nextArea.order) {
                        area.show();
                        //this.showClick(true);
                        area.found = true;
                        area.state = true;
                        this.areasFound++;
                    }
                    else {
                        nextArea.show();
                        nextArea.found = true;
                        this.areasFound++;
                    }
                    if (nextArea.order < lastOrder) {
                        this.attempts = 0;
                    }
                }
                else {
                    area.show();
                    nextArea.show();
                    nextArea.found = true;
                    this.areasFound++;
                    if (nextArea.order < lastOrder) {
                        this.attempts = 0;
                    }
                }
            }
        }
    }
    else {
        if (this.attempts < this.tries) {
            if (area == false) {
                this.showClick(false);
            }
            else {
                area.show();
                area.found = true;
                if (area.correct == 1) {
                    area.state = true;
                    this.areasFound ++;
                }
            }
        }
        else {
            if (area == false) {
                this.solve();
            }
            else {
                area.show();
                area.found = true;
                if (area.correct == 1) {
                    area.state = true;
                    this.areasFound ++;
                }
                this.solve();
            }
        }
    }
    
    this.state = this.checkState();
    
    if ((this.attempts >= this.tries) || (this.areasFound == this.matches)) {
        this.finished = true;
    }
    
    if (parent.LEARNING_OBJECT != null) {
      var assessment = parent.LEARNING_OBJECT.getAssessment();
      assessment.addItem(this.contentid,this.state);
    }
    
    this.feedback(area);
    this.updateTries();

    return;
}

function checkWaldoState() {
    for (var i=1;i<this.areasArray.length;i++) {
        if (this.areasArray[i].correct == 1) {
            if (this.areasArray[i].state == false) { 
                return false;
            }
        }
    }
    return true;
}

function getWaldoArea () {
    var click = this.click;
    if (!this.areasArray) { return false; }
    for (var i=1;i<this.areasArray.length;i++) {
        var area = this.areasArray[i];
        if ((!area.found) && (click.x >= area.x) && (click.x <= area.bx) && (click.y >= area.y) && (click.y <= area.by)) {
            return area;
        }
    }
    return false;
}

function getNextWaldoArea() {
    if (this.areasFound < this.matches) {
        var nextOrder = this.orderArray[this.areasFound];
        for (var i=1;i<this.areasArray.length;i++) {
            if (nextOrder == this.areasArray[i].order) {
                return this.areasArray[i];
            }
        }
    }
    return false;
}

function showWaldoArea (solve) {
    this.area.setVisible(true);
    return;
}

function showWaldoClick (state) {

    if (!state) { var image = XMark; }
    else {        var image = CkMark; }
    var sx = this.click.x-(image.width/2);
    var sy = this.click.y-(image.height/2);
    var i = this.clickArray.length;
    this.clickArray[i] = new DynLayer(null,sx,sy,image.width,image.height);
    this.clickArray[i].setHTML('<img src="'+image.src+'" width="'+image.width+'" height="'+image.height+'">');
    this.base.addChild(this.clickArray[i]);
    return;

}

function hideWaldoClicks () {
    for(var el in this.clickArray) {
        this.clickArray[el].setVisible(false);
    }
    return;
}

function resetWaldo () {
    this.attempts  = 0;
    this.areasFound = 0;
    this.finished = false;
    this.state = false;
    for (var i=1;i<this.areasArray.length;i++) {
        this.areasArray[i].found = false;
        this.areasArray[i].state = false;
        this.areasArray[i].area.setVisible(false);
    }
    this.updateTries();
    this.hideClicks();
    return;
}

function updateWaldoTries() {
    var currentCount = this.tries - this.attempts;
    if (currentCount >= 0) {
        this.tryCnt.setHTML('<span class="trycounter">Tries Remaining: '+currentCount+'</span>');
    }
    return;
}

function waldoCompare (y,z) { return y - z; }

function waldoClick (e) {
    if (document.all) { this.x = e.offsetX; this.y = e.offsetY; }
    else {              this.x = e.layerX; this.y = e.layerY; }
    return this;
}

function solveWaldo() {
    for (var i=1;i<this.areasArray.length;i++) {
        var area = this.areasArray[i];
        if ((area.correct == 1) && (area.found == false)) { 
            area.show(true); 
        }
    }
    return;
}

function showWaldoFeedback(area) {
    
    var numFeedback     = 0;
    var ls_html         = '';
    var feedback_html   = '';
    
    ls_html += addPopupSyles("Answer Feedback");   
    ls_html += addPopupHeader("Answer Feedback");
    ls_html += '<table width="100%" class="yellowbox" border="0" cellpadding="5" cellspacing="0">\n';
    ls_html += '  <tr>\n';
    ls_html += '    <td colspan="2">&nbsp;</td>\n';
    ls_html += '  </tr>\n';
    
    if (this.areasFound == this.matches) {
        var feedback = eval('Correctfdbk' + this.contentid);
        ls_html += '  <tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
    } else if (this.attempts >= this.tries) {
    	if (this.areasFound < this.matches) {
            var feedback = eval('Incorrectfdbk' + this.contentid);
        } else {
            var feedback = eval('Correctfdbk' + this.contentid);
        }
        ls_html += '  <tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
    } else {
    	
        if (!area.state) {           
            var feedback = "Please Try Again";
        } else {                       
            var feedback = "Correct!"; 
        }
        ls_html += '  <tr><td colspan="2"><b>'+unescape(feedback)+'</b></td></tr>';
        if (area) {
            if ((area.state) || (area.correct == 0)) {
                if ((area.feedback != '') && (area.feedback != 'null') && (area.feedback != null)) {
                    ls_html += '  <tr>\n';
                    ls_html += '    <td><table><tr><td bgcolor="#cc3300"><img src="../themes/thumb_clear.gif" width="3" height="3"></td></tr></table></td>\n';
                    ls_html += '    <td width="100%">'+unescape(area.feedback)+'</td>\n';
                    ls_html += '  </tr>\n';
                }
            }
        }
        if (this.state == true) { ls_html += this.links(2); }
        else {                       
            if (this.finished == true) {
                ls_html += this.links(3);   // incorrect
            }
        }
    }
    
    ls_html += '</table>\n';
    
    ls_html += '<p align="center"><a href="" onClick="javascript: window.opener.focus(); window.close(); return false;">close</a></p>\n';
    
    ShowFeedback(ls_html);
    
    return;
}

function showWaldoHint() {
    ShowHint(this.contentid);
    return;
}

function getWaldoLinks(displayType) {
    
    ls_link='';
	var numLinks = eval("NumLinks"+this.contentid);

    var linkCnt=0;
    for (var i = 1;i<=numLinks;i++) {
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

function waldoAssessment (contentid,base,tries,tryLayer,sectionid) {
	
    this.base           = base;
    this.contentid      = contentid;
    this.clickArray     = new Array();
	this.tryLayer 		= tryLayer;
	this.sectionid		= sectionid;
    
    return this;
}

waldoAssessment.prototype.assess = function(e) {
	
    eval("currentClick"+this.contentid+"++");
    
    var numClicks       = eval("numClicks"+this.contentid);
    var currentClick    = eval("currentClick"+this.contentid);
	var fName			= "Question"+this.sectionid+"_"+this.contentid+"Form";
	var prefix			= getPrefix( fName );
    
    if (document.all) {
        var x           = e.offsetX;
        var y           = e.offsetY;
    }
    else {
        var x           = e.layerX;
        var y           = e.layerY;
    }
    
    if (document.layers) { var trycounter  = eval("document.layers.tries"+this.contentid); }
    else {                 var trycounter  = eval("document.getElementById('tries"+this.contentid+"')"); }
	
	
    
    if (currentClick > numClicks) {
        if (confirm('Click OK to reset this question')) {
        	for (var i=0; i<this.clickArray.length; i++) {
        		this.clickArray[i].setVisible(false);
            }
            eval("currentClick"+this.contentid+" = 0");
			this.tryLayer.setHTML( "<span class=\"trycounter\">Clicks Remaining: "+numClicks+"</span>" );
            for(var i=1;i<=numClicks;i++) {
                eval(prefix+"document.Question"+this.sectionid+"_"+this.contentid+"Form.Answer"+i+this.contentid+".value = ''");
            }
        }
    }
    else {
        var nextClick = this.clickArray.length;
        this.clickArray[nextClick] = new DynLayer(null,(x-10),(y-10),null,null);
        this.clickArray[nextClick].setHTML('<div class="waldoAssessClick">'+currentClick+'</div>');
        this.base.addChild(this.clickArray[nextClick]);
        this.tryLayer.setHTML( "<span class=\"trycounter\"><font face=\"arial\" size=\"2\">Clicks Remaining: "+(numClicks - currentClick)+"</font></span>" );
        eval(prefix+"document.Question"+this.sectionid+"_"+this.contentid+"Form.Answer"+currentClick+this.contentid+".value = '"+x+","+y+"'");
    }
    return;
}

function waldoWrite(layer,text) {
    return;
}
