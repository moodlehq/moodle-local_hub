//
// Client functions for LO forum
//

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// User defined variables:	
// Use these variables to control the appearance of the tree.  ** Need change to use themes to set them.  **

structureStyle = 0;                                   // 0 for light background, 1 for dark background

backgroundColor = '#FFFFFF';                          // sets the bgColor of the menu
    textColor = '#000000';                          // sets the color of the text used in the menu
    linkColor = '#0000AA';                          // sets the color of any text links (usually defined in additional HTML sources)
   aLinkColor = '#FF0000';                          // sets the active link color (when you click on the link)
   vLinkColor = '#880088';                          // sets the visited link color

backgroundImage = '';                                 // give the complete path to a gif or jpeg to use as a background image

defaultTargetFrame = 'pageFrame';                     // the name of the frame that links will load into by default 
//defaultImageURL = '../../themes/';                       // the URL or path where the Tree images are located

defaultLinkIcon = 'newsgroup.gif';                    // the default icon image used for links

TreeFont = 'Verdana,Arial,MS Sans Serif,Helvetica';   // the font used for the menu

TreeFontSize = 1;                                     // its size - don't make it too big!

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// start() - GENERAL FUNCTION - called by the HTML document once loaded - starts Tree by
//					  first loading the user data, and then drawing the tree.
function start() {

    if (top.menu.gb_treeState) {
        lb_treeState = top.menu.gb_treeState;
    } else {
        lb_treeState = 0;
    }
	loadData(lb_treeState);
	drawTree();
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// drawTree() - GENERAL FUNCTION - starts the recursive tree drawing process by first writing
//					     the root node, and then all subordinate branches.

function drawTree() {
	outputFrame = top.topics.treeFrame.window.document;	// If you really must, you can change the name of the treeFrame here to match your new name defined at the bottom.
	outputFrame.open("text/html");
	outputFrame.write('<HTML>\n');
    outputFrame.write('<HEAD>\n');
    outputFrame.write('<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">\n');
    outputFrame.write('<META HTTP-EQUIV="Pragma" CONTENT="no-cache">\n');
    outputFrame.write('<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">\n');
    outputFrame.write('</HEAD>\n');
    outputFrame.write("<BODY BGCOLOR='" + backgroundColor + "' BACKGROUND='" + backgroundImage + "' LINK='" + linkColor + "' ALINK='" + aLinkColor + "' VLINK='" + vLinkColor + "'>\n");
	outputFrame.write("<FONT FACE='" + TreeFont + "' SIZE=" + TreeFontSize + " COLOR='" + textColor + "'>\n");
	outputFrame.write(prefixHTML + "\n<NOBR>\n");
	if (treeData[1].target == "") {var targetFrame = defaultTargetFrame} else {var targetFrame = treeData[1].target}
	if (treeData[1].icon == "") {var imageString = defaultImageURL + 'img-folder-open-' + structureStyle + '.gif'} else {imageString = defaultImageURL + treeData[1].icon}
	outputFrame.write("<A HREF='" + treeData[1].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + treeData[1].url + "'; return true\"><IMG SRC='" + imageString + "' WIDTH=16 HEIGHT=16 ALIGN=TEXTTOP BORDER=0 ALT='" + treeData[1].url + "'>&nbsp;<B>" + treeData[1].name + "</B></A><BR>\n");
	drawBranch("root","");
	outputFrame.write("</NOBR>\n" + suffixHTML + "\n");
	outputFrame.write("</FONT>\n</BODY>\n</HTML>");
	outputFrame.close();
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// drawBranch() - GENERAL FUNCTION - used by the drawTree() function to recursively draw all
//						 visable nodes in the tree structure.

function drawBranch(startNode,structureString) {
	var children = extractChildrenOf(startNode);
	var currentIndex = 1;
	while (currentIndex <= children.length) {
	
		outputFrame.write(structureString);
		if (children[currentIndex].type == 'link') {
			if (children[currentIndex].icon == "") {
				var imageString = defaultImageURL + defaultLinkIcon;
			} else {
				var imageString = defaultImageURL + children[currentIndex].icon
			}
          if (children[currentIndex].target == "") {
				var targetFrame = defaultTargetFrame;
			} else {
				var targetFrame = children[currentIndex].target
			}
            
			if (currentIndex != children.length) {
				outputFrame.write("<img src='" + defaultImageURL + "img-branch-cont-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALIGN=TEXTTOP>")
			} else {
				outputFrame.write("<img src='" + defaultImageURL + "img-branch-end-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALIGN=TEXTTOP>")
			}
			outputFrame.write("<img src='" + imageString + "' WIDTH=16 HEIGHT=16 ALIGN=TEXTTOP BORDER=0 ALT='" + children[currentIndex].url + "'>");
          outputFrame.write("<a href='" + children[currentIndex].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + children[currentIndex].url + "'; return true\">&nbsp;" + children[currentIndex].name + "</a><br>\n")	
        }	else {
			var newStructure = structureString;
          if (children[currentIndex].target == "") {
				var targetFrame = defaultTargetFrame;
			} else {
				var targetFrame = children[currentIndex].target
			}
			if (children[currentIndex].iconClosed == "") {var iconClosed = "newsgroup.gif"} else {var iconClosed = children[currentIndex].iconClosed}
			if (children[currentIndex].iconOpen == "") {var iconOpen = "newsgroup.gif"} else {var iconOpen = children[currentIndex].iconOpen}
			if (currentIndex != children.length) {
				if (children[currentIndex].open == 0) {
					outputFrame.write("<A HREF=\"javascript:parent.toggleFolder('" + children[currentIndex].id + "',1)\" onMouseOver=\"window.status='Click to open this folder'; return true\"><IMG SRC='" + defaultImageURL + "img-plus-cont-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALT='Click to open this folder' ALIGN=TEXTTOP BORDER=0>")
					outputFrame.write("<IMG SRC='" + defaultImageURL + iconClosed + "' WIDTH=16 HEIGHT=16 ALT='Click to open this folder' ALIGN=TEXTTOP BORDER=0></A>&nbsp<A HREF='" + children[currentIndex].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + children[currentIndex].url + "'; return true\">" + children[currentIndex].name + "</A><BR>\n")
				} else {
					outputFrame.write("<A HREF=\"javascript:parent.toggleFolder('" + children[currentIndex].id + "',0)\" onMouseOver=\"window.status='Click to close this folder'; return true\"><IMG SRC='" + defaultImageURL + "img-minus-cont-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALT='Click to close this folder' ALIGN=TEXTTOP BORDER=0>");
					outputFrame.write("<IMG SRC='" + defaultImageURL + iconOpen + "' WIDTH=16 HEIGHT=16 ALT='Click to close this folder' ALIGN=TEXTTOP BORDER=0></A>&nbsp;<A HREF='" + children[currentIndex].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + children[currentIndex].url + "'; return true\">" + children[currentIndex].name + "</A><BR>\n");
					newStructure = newStructure + "<IMG SRC='" + defaultImageURL + "img-vert-line-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALIGN=TEXTTOP>";
					drawBranch(children[currentIndex].id,newStructure); 
				}
			} else {
				if (children[currentIndex].open == 0) {
					outputFrame.write("<A HREF=\"javascript:parent.toggleFolder('" + children[currentIndex].id + "',1)\" onMouseOver=\"window.status='Click to open this folder'; return true\"><IMG SRC='" + defaultImageURL + "img-plus-end-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALT='Click to open this folder' ALIGN=TEXTTOP BORDER=0>")
					outputFrame.write("<IMG SRC='" + defaultImageURL + iconClosed + "' WIDTH=16 HEIGHT=16 ALT='Click to open this folder' ALIGN=TEXTTOP BORDER=0></A>&nbsp;");
                if (children[currentIndex].url == '') {
                    outputFrame.write(children[currentIndex].name + "</A><BR>\n")
                } else {
                    outputFrame.write("<A HREF='" + children[currentIndex].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + children[currentIndex].url + "'; return true\">" + children[currentIndex].name + "</A><BR>\n")
                }
				} else {
					outputFrame.write("<A HREF=\"javascript:parent.toggleFolder('" + children[currentIndex].id + "',0)\" onMouseOver=\"window.status='Click to close this folder'; return true\"><IMG SRC='" + defaultImageURL + "img-minus-end-" + structureStyle + ".gif' WIDTH=19 HEIGHT=16 ALT='Click to close this folder' ALIGN=TEXTTOP BORDER=0>");
					outputFrame.write("<IMG SRC='" + defaultImageURL + iconOpen + "' WIDTH=16 HEIGHT=16 ALT='Click to close this folder' ALIGN=TEXTTOP BORDER=0></A>&nbsp;<A HREF='" + children[currentIndex].url + "' TARGET='" + targetFrame + "' onMouseOver=\"window.status='" + children[currentIndex].url + "'; return true\">" + children[currentIndex].name + "</A><BR>\n");
					newStructure = newStructure + "<IMG SRC='" + defaultImageURL + "img-blank.gif' WIDTH=19 HEIGHT=16 ALIGN=TEXTTOP>";
					drawBranch(children[currentIndex].id,newStructure); 
				}
			}
		}
		currentIndex++;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// toggleFolder() - GENERAL FUNCTION - opens/closes folder nodes.

function toggleFolder(id,status) {
	var nodeIndex = indexOfNode(id); 
	treeData[nodeIndex].open = status; 
	timeOutId = setTimeout("drawTree()",100);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// closeAllFolders() - GENERAL FUNCTION - closes All folder nodes.

function closeAllFolders() {
    for (var i=1;i<=treeData.length;i++)  {
        if (treeData[i].type == 'folder') treeData[i].open = 0;
    }
	drawTree();
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// openAllFolders() - GENERAL FUNCTION - opens all folder nodes.

function openAllFolders() {
    for (var i=1; i<=treeData.length; i++) {
		if (treeData[i].type == 'folder') treeData[i].open = 1;
    }
	drawTree();
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// indexOfNode() - GENERAL FUNCTION - finds the index in the treeData Collection of the node
//						  with the given id.

function indexOfNode(id) {
	var currentIndex = 1;
	while (currentIndex <= treeData.length) {
		if ((treeData[currentIndex].type == 'root') || (treeData[currentIndex].type == 'folder')) {
			if (treeData[currentIndex].id == id) {return currentIndex}} 
		currentIndex++} 
	return -1}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// extractChildrenOf() - GENERAL FUNCTION - extracts and returns a Collection containing all
//							  of the node's immediate children nodes.

function extractChildrenOf(node) {
	var children = new Collection();
	var currentIndex = 1; 
	while (currentIndex <= treeData.length) {
		if ((treeData[currentIndex].type == 'folder') || (treeData[currentIndex].type == 'link')) {
			if (treeData[currentIndex].parent == node) {
				children.add(treeData[currentIndex])}}
		currentIndex++} 
	return children}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Collection() - OBJECT - a dynamic storage structure similar to an Array.

function Collection() {
	this.length = 0; 
	this.add = add; 
	return this
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// add() - METHOD of Collection - adds an object to a Collection.

function add(object) {
	this.length++; 
	this[this.length] = object
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// RootNode() - OBJECT - represents the top-most node of the hierarchial tree.

function RootNode(id,name,url,target,icon) {
	this.id = id;
	this.name = name;
	this.url = url;
	this.target = target;
	this.icon = icon;
	this.type = 'root';
	return this;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// FolderNode() - OBJECT - represents a node which branches to contain other nodes.

function FolderNode(id,parent,name,url,target,iconClosed,iconOpen,folderstate) {
	this.id = id;
	this.parent = parent;
	this.name = name;
	this.url = url;
	this.target = target;
	this.iconClosed = iconClosed;
	this.iconOpen = iconOpen;
	this.type = 'folder';
    this.open = folderstate;

	return this;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// LinkNode() - OBJECT - a node that represents a link using a URL.

function LinkNode(id,parent,name,url,target,icon) {
	this.parent = parent;
	this.id = id;
	this.name = name;
	this.url = url;
	this.target = target;
	this.icon = icon;
	this.type = 'link';
	return this;
}

