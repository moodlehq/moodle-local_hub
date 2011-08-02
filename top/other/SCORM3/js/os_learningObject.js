function LearningObject(id) 
{
  this.id         = id;
  this.topicList  = new Array();
  this.score      = 0;
  this.assessment = new Assessment();

  return this;
}

LearningObject.prototype.getID = function() {
  return this.id;
}

LearningObject.prototype.getAssessment = function() {
  return this.assessment;
}

LearningObject.prototype.addTopic = function(id) {

	var slot = this.topicList.length;

	for (var i=0; i<this.topicList.length; i++) {
		if (this.topicList[i].id == id) {
			slot = i;
		}
	}

  this.topicList[slot] = new Topic(id);
  
}

function Topic() 
{
  this.id         = id;
  this.groupList  = new Array();
  this.accessed   = false;
  this.active     = false;
  
  return this;
}

Topic.prototype.getID = function() {
  return this.id;
}

Topic.prototype.addGroup = function(id) {

	var slot = this.groupList.length;

	for (var i=0; i<this.groupList.length; i++) {
		if (this.groupList[i].id == id) {
			slot = i;
		}
	}

  this.groupList[slot] = new Group(id);
  
  return true;
}

function Group(id) 
{
  this.id = id;
  this.elementList = new Array();
  
  return this;
}

Group.prototype.getID = function() {
  return this.id;
}

Topic.prototype.addElement = function(id) {

	var slot = this.elementList.length;

	for (var i=0; i<this.elementList.length; i++) {
		if (this.elementList[i].id == id) {
			slot = i;
		}
	}

  this.elementList[slot] = new Element(id);
  
  return true;
}

function Element(id) 
{
  this.id = id;
  
  return this;
}

Element.prototype.getID = function() {
  return this.id;
}

function Assessment() {

    this.itemList  = new Array();
    this.score     = 0;

    return this;
}

Assessment.prototype.addItem = function(elementID,correct) {
  
  var slot = this.itemList.length;

	for (var i=0; i<this.itemList.length; i++) {
		if (this.itemList[i].id == elementID) {
			slot = i;
		}
	}

  this.itemList[slot] = new AssessmentItem(elementID, correct);
  
  //alert(this.getScore());
  
  return true;
}

Assessment.prototype.getScore = function() {
  
  this.score = 0;
  
  for (var i=0; i<this.itemList.length; i++) {
    if (this.itemList[i].correct == true) {
      this.score++;
    }
  }

  return this.score;
}

function AssessmentItem(elementID,correct) {
	
    this.id 		  = elementID;
    this.correct  = correct;
    
    return this;

}