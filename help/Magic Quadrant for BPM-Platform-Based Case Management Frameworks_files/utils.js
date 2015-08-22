	function isChrome() {
		return Boolean(window.chrome);
	}

var mouseXPositionOnMove = 0;
var mouseYPositionOnMove = 0;

document.onmouseup = getMousePositionOnMove;

	function getMousePositionOnMove(e) {

		if (navigator.appName.indexOf("Microsoft") >= 0) {	
			mouseXPositionOnMove = event.x;
			mouseYPositionOnMove = event.y;
		} else {
			mouseXPositionOnMove = e.pageX;
			mouseYPositionOnMove = e.pageY;
		}
		
	}

	//This function will be used to hide a layer. This function takes in a div id for the layer to be
	//hidden. It also hides the transparent background div that gave the layer effect.
	function hideGlobalLayer(layerDivIdToBeHidden, arrayOfFormNamesInLayer) {

		//Hide the layer.
		if (layerDivIdToBeHidden == 'globalLayerDiv') {
			document.getElementById(layerDivIdToBeHidden).className = "emptyLayerClass";
			document.getElementById(layerDivIdToBeHidden).innerHTML = '';
		}

	}

	// This function detects the browser type and version -- Specific to IE 6
	function IE6browserCheck() {	
		//return ((navigator.appName == 'Microsoft Internet Explorer' && Math.floor(Number(/MSIE ([^;]*);/.exec(navigator.appVersion)[1])) == 6));
		//Same issue is happning in IE 7 so just checking the browser type for IE
		return (navigator.appName == 'Microsoft Internet Explorer');
	}

	//START --- JavaScript support functions for Image pop-up for doc display
                        
					   
	function showDocDisplayImage(javascriptFunctionString, layerDivIdToBeShown, arrayOfFormNamesInLayer, layerWidth, layerHeight, startX, startY) {

		//If the javascript function string is not empty then call the javascript function string.
		if (javascriptFunctionString != "") {
			eval(javascriptFunctionString);
		}
		
		document.getElementById('globalLayerDiv').className = "globalLayerDiv";
		var gbd = document.getElementById('globalLayerDiv');
		gbd.style.left = startX+"px";
		gbd.style.top = startY-layerHeight+"px";

	}

	function checkZoom(imgSrc)
	{
	var enlarge = imgSrc + 'Enlarge';
	var newImg = new Image();
	newImg.onload=function() {};
	newImg.src = imgSrc;
			if(newImg.width > 650) {
				document.getElementById(enlarge).style.display = 'inline';
			}
	}

	function zoomImg(imgSrc){
	
		var enlarge = imgSrc + 'Enlarge';
		var close = imgSrc + 'Close';
		document.getElementById(enlarge).style.display = "none";
		document.getElementById(close).style.display = "inline";
		var d = document.getElementById(enlarge);
		
		startX = mouseXPositionOnMove-15;
		startY = mouseYPositionOnMove-10;
		
		var newImg = new Image();
		newImg.onload=function() {};
		newImg.src = imgSrc;
			
		showDocDisplayImage('showImageLayer("' + imgSrc + '")', 'globalLayerDiv', '', newImg.width, newImg.height, startX, startY);
	}

	function showImageLayer(imgSrc){
		var imgSrcModified = '"' + imgSrc + '"';
		document.getElementById('globalLayerDiv').innerHTML= "<img src='" + imgSrc + "' onclick='closeImageLayer(" + imgSrcModified + ");' />";
	}

	function closeImageLayer(imgSrc){
		var enlarge = imgSrc + 'Enlarge';
		var close = imgSrc + 'Close';
		document.getElementById(enlarge).style.display = "inline";
		document.getElementById(close).style.display = "none";
		
		hideGlobalLayer('globalLayerDiv', '');
		document.getElementById('globalLayerDiv').innerHTML= "";
	}

	//Generic Function to expand or collapse the doc display components
	function handleXSLCollapsibility(divId)
	{
			
		var showHide = divId + 'ShowHide';
		
		if(divId == 'toc')
		{
			showHide = 'toc';
			divId = 'tocContent';
		}
		
		if(document.getElementById(divId).style.display == "none")
		{
			document.getElementById(divId).style.display = "block";
			document.getElementById(showHide).className = "show";
		} else {
			document.getElementById(divId).style.display = "none";
			document.getElementById(showHide).className = "hide";
		}
	}	

//Generic Function to expand or collapse the doc display components
	function handleCollapsibility(divId)
	{
		var expand = divId + 'Expand';
		var collapse = divId + 'Collapse'
		if(document.getElementById(expand).style.display == "none")
		{
			document.getElementById(expand).style.display = "block";
			document.getElementById(collapse).style.display = "none";
		} else {
			document.getElementById(expand).style.display = "none";
			document.getElementById(collapse).style.display = "block";
		}
	}	


	
	//END --- JavaScript support functions for Image pop-up for doc display

