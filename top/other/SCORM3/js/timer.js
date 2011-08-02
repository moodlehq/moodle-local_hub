//<script 

function Timer(duration,base) {
    
    this.duration = (duration * 60);
    
    this.remaining = (duration * 60);
    
    
    this.layer = new DynLayer(null,2,2,200);
    
    if (base != null) {
        this.parent = base;
        //DragEvent.enableDragEvents(this.parent);
    }
    else {
        this.parent = DynAPI.document;
        //DragEvent.enableDragEvents(this.layer);
    }

    this.layer.setHTML(this.label+this.duration);

    this.parent.addChild(this.layer);

    this.label = "Time remaining:";
    this.timertextstyle = "counter" ;
    
    return this;

}

Timer.prototype.start=function() {
    this.update();
}

Timer.prototype.onexpire=function() {
}

Timer.prototype.onwarning=function() {
    alert("warning");
}

Timer.prototype.decrement=function() {
    this.remaining--;
}

Timer.prototype.setLabel=function(text) {
    this.label = text;
}

Timer.prototype.setWarning=function(time) {
    this.warning = time * 60;
}

Timer.prototype.update=function() {
    
    if (this.remaining == this.warning) this.onwarning();
    if (this.remaining == 0) {
        this.onexpire();
        return;
    }
    
    this.layer.setHTML("<span class=\"" + this.timertextstyle + "\">"+this.label+" "+this.getDisplayTime()+"</span>");
    
    this.decrement();
    
    setTimeout("myTimer.update()",1000);

}

Timer.prototype.setVisible=function(visible) {
    this.layer.setVisible(visible);
}

Timer.prototype.setBgColor=function(color) {
    this.layer.setBgColor(color);
}

Timer.prototype.moveTo=function(x,y) {
    this.layer.moveTo(x,y);
}

Timer.prototype.setDuration=function(duration) {
    this.duration = duration;
}

Timer.prototype.setTimerTextStyle=function(textstyle) {
    this.timertextstyle = textstyle;
}

Timer.prototype.setLabel=function(textlabel) {
    this.label = textlabel;
}

Timer.prototype.getDisplayTime=function() {
    
    var displayString = "";
    
	var hours = 0;
	var minutes = 0;
	var seconds = 0;
	
	if ( this.remaining < 60 ) {
		seconds = this.remaining;
	}
	else if ( this.remaining < 3600 ) {
		minutes = Math.floor(this.remaining / 60);
		seconds = (this.remaining - (minutes * 60));
	}
	else {
		hours = Math.floor( this.remaining / 3600 );
		minutes = Math.floor(( this.remaining - (hours * 3600)) / 60 );
		seconds = (this.remaining - ( hours * 3600 ) - ( minutes * 60 ));
	}
	
	if (minutes < 10) minutes = "0"+minutes;
	if (seconds < 10) seconds = "0"+seconds;
    
    displayString = hours+":"+minutes+":"+seconds;
    
    return displayString;

}