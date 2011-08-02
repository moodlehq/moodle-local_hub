function DisplayThumbnail(ImageName,ImgWidth,ImgHeight,imagefileID,ImgCaption,ln_AutoStart) {
 
	if (ImgCaption == null)  {
		ImgCaption = "";
	}
	if (ln_AutoStart == null)  {
		ln_AutoStart = "TRUE";
	}

//	var url = "DisplayThumbnail?ImageName="+ImageName+"&ImageWidth="+ImgWidth+"&ImageHeight="+ImgHeight+"&imagefileID="+imagefileID+"&ln_AutoStart="+ln_AutoStart+"&ImgCaption="+ImgCaption;

	// I'm I'm going to leave the method args the same for this so I don't break anything that is calling this
	// the DisplayThumbnail servlet doesn't take these parameters so I'm not passing them  
	var url = "DisplayThumbnail?ImageName="+ImageName+"&ln_AutoStart="+ln_AutoStart+"&ImgCaption="+ImgCaption;
	if ( ImgWidth < 1 ) { 
		ln_width = 310; 
		ln_height = 210;
	} else {
		ln_width = ImgWidth + 20; 
		ln_height = ImgHeight + 120; 
	} 
	popup = window.open(url,"ThumbnailPage","\'left=1,top=1,resizable=yes,width=" + ln_width + ",height=" + ln_height + ",scrollbars=no\'"); 
}
