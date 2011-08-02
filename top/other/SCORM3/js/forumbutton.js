function discussionButton( ) {

	this.clicked = goDiscussion;
}

var DiscussionBtn = new discussionButton();

function goDiscussion() {

	var forumurl = "DiscussionForum?page=main&loID="+loID+"&studentID="+studentID+"&themeID="+themeID; 
	popup = window.open(forumurl,"LOForum","scrollbars=yes,width=850,height=600,top=70,left=220,resizable=yes");
	popup.focus();

	return 0;

}
