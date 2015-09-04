        var caseJson;
//	--- Layout ---
	var main_layout;
	var workAreaTabbar;
	var itemTabbar;

// --- Item Forms -----
	var ProcessItemForm;
	var ActionForm;
	var ConditionForm;
	var ItemDocForm;

	var gridItems;
	var gridItemData;

	var currentItemId;


	function getCaseJson()
	{
		var el=jQuery('#targetBlock');
	 	el.html("loading item details ...");
		var url=getAjaxUrl();

	 	var caseId=getParameterByName('caseId');
	 	var data = {
	 				'action': 'omni_ajax_call',
	 				'command': 'case.getJson',
	 				'caseId': caseId
	 			};
//	 	alert(url+"data :"+data.action+" "+data.command+" "+data.file);
		jQuery.post(url, data, function(response) {
	//		alert(response);
			caseJson=response;
//			displayCase(caseJson);
			}).error(function() {
                            alert("Error requesting data " + settings.url + thrownError  +request+"response:" +response);
                            waiting("");
                        }); 
	}
//	------------------------------- Build Page
	function BuildCasePage()
	{
		BuildCaseMainLayout();
		BuildCaseWorkArea();
	}

	var dxImgPath;
	
	function BuildCaseMainLayout()
	{
	dxImgPath=omni_base_url+'/dhtmlx/codebase/imgs/';
	window.skin = "skyblue"; // for tree image_path
			dhtmlx.image_path=dxImgPath;

	main_layout = new dhtmlXLayoutObject('MainLayout', '2E');

	var diagram = main_layout.cells('a');
	diagram.setText('Diagram');
	diagram.setCollapsedText('Diagram');
	
//	diagram.hideHeader();
	diagram.attachObject("diagramContents");
}
	

function BuildCaseWorkArea()
{
	workArea = main_layout.cells('b');
	workArea.setText('Details');
        
	workAreaTabbar = workArea.attachTabbar();

	workAreaTabbar.addTab('CaseItems','Case Items');
	var CaseItems = workAreaTabbar.cells('CaseItems');
	CaseItems.setActive();

	BuildCaseItems();

	workAreaTabbar.addTab('CaseDetails','Case Details');
	var CaseDetails = workAreaTabbar.cells('CaseDetails');


	statusBar = workArea.attachStatusBar();
	displayStatus('Starting');
	
}

function displayCaseItemData(force,fromTab)
{
		if (typeof(fromTab)==='undefined') fromTab=false;
		if (force)
			ItemDataChanges=true;
		if (!ItemDataChanges)
			return;
		if  ((itemTabbar.getActiveTab()=='itemData') || fromTab)
		{
		dhtmlx.message("display item data");
		ItemDataChanges=false;
		}
		
	var item = getObject('items',currentItemId);		
	
	if (item.dataElements==null)
		{
		item['dataElements']=Array();
		}
	var des=item.dataElements;
	
	var objs=caseJson['dataElements'];
	if (objs!=null)
	{
		var rows=Array();
		for(var i=0;i<objs.length;i++)
		{
			var processDataElement=objs[i];
			var visible="";
			var input="";
			var fldName="";
			
			for(var d=0;d<des.length;d++)
			{
				var de=des[d];
				if (de.refId==processDataElement.id)
				{
					visible=de.visible;
					input=de.input;
					fldName=de.field;
				}
			}
			var row= { id: processDataElement.id , data: [ processDataElement.name, visible,input,fldName]};
			rows.push(row)
		}

		var data = {rows: rows};
		gridItemData.clearAll();
		gridItemData.parse(data,"json");

		gridItemData.selectRow(0);
		var rowId=gridItemData.getSelectedRowId();
		
	}
    displayItemDescription(currentItemId);
		
}
function displayCaseItemDescription(itemId)
{
    var item = getObject('descriptions.items',currentItemId);	
    if (item==null)
    {
        jQuery("#itemDescription").html("Not Defined");
        return;
    }
    var dOptions="<ul>";
    for(var i=0;i<item.designOptions.length;i++)
    {
        var opt=item.designOptions[i];
        dOptions+="<li>"+opt+"</li>";
    }
    dOptions+="</ul>";
    
    var mOptions="<ul>";
    for(var i=0;i<item.modelOptions.length;i++)
    {
        var opt=item.modelOptions[i];
        mOptions+="<li>"+opt+"</li>";
    }
    mOptions+="</ul>";


    var html="<table><tr><td>Title:</td><td>"+item.title+
            "</tr><tr><td>Description:</td><td>"+item.desc+
            "</tr><tr><td>start:</td><td>"+item.start+
            "</tr><tr><td>Completion:</td><td>"+item.completion+
            "</tr><tr><td>Design Options:</td><td>"+dOptions+
            "</tr><tr><td>Model Options:</td><td>"+mOptions+
            
            "</tr></table>";
            
    jQuery("#itemDescription").html(html);

}
function BuildCaseItems()
{
	var CaseItems = workAreaTabbar.cells('CaseItems');

	
	var itemsMenu = workAreaTabbar.attachMenu();
		
	var layout_2 = CaseItems.attachLayout('2U');
	var itemsList = layout_2.cells('a');
	itemsList.setWidth(760);
	itemsList.hideHeader();
//	itemsList.attachObject("proessItems");	// adding the list select to this cell
//  replace by grid 

	gridItems = itemsList.attachGrid();
	gridItems.setIconsPath(dxImgPath);
        
	gridItems.setHeader("No,Type,Label,Action,Status,Started,Completed,Actor");
	gridItems.setColTypes("ro,ro,ro,link,ro,ro,ro,ro");
	gridItems.setColumnIds("rowNo,type,label,action,status,started,completed,actor");
	
	gridItems.setInitWidths("24,100,100,70,120,120,70,150");
//	gridItems.setColAlign("left,left");
	
//	gridItems.setColSorting('str,str');
	gridItems.init();
	
	gridItems.attachEvent("onRowSelect",function(rowId,cellIndex){
		displayCaseItemDetails(rowId);
	});	
	
// end of grid

	var ItemDetails = layout_2.cells('b');
	ItemDetails.hideHeader();
/*	
	itemsMenu.addNewSibling(null, "run", "Simulate Process", false); 
	itemsMenu.addNewSibling(null, "debug", "View Model", false); 
	itemsMenu.addNewSibling(null, "cancel", "Cancel", false); 
	itemsMenu.addNewSibling(null, "save", "Save", false); 
	
	itemsMenu.attachEvent('onClick', function(id,zoneId,cas)
	{
		if (id=='run')
		{
	 	var url=window.location.href.split('?')[0];
	 	var file=getParameterByName('file');
                alert(url+" file "+file);
                url=url+"?action=process.test&file="+file;
                 window.location=url;
		}
		if (id=='save')
		{
		saveJson();
		}
		if (id=='debug')
		{
		debugWindow(caseJson);
		}
		if (id=='cancel')
		{
		confirm("Cancelling","this will cancel all the changes");
		}
	});

	*/
       
	itemTabbar = ItemDetails.attachTabbar();
	
/*       
	var tab=addTab(itemTabbar,'itemDetails',"Item Details","itemDetails");
	tab.setActive();
	
var str1 = 
[{"type":"fieldset","name":"General","label":"General","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_type","label":"Type","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_subType","label":"subType","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_actor","label":"Actor","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_name","label":"Name","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_id","label":"Id","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_subProcess","label":"Sub-Process","labelWidth":105,"inputWidth":150}
		]},
	{"type":"fieldset","name":"Navigation","label":"Navigation","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_inflowsLabels","label":"In-Flows","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_outflowsLabels","label":"Out-Flows","labelWidth":105,"inputWidth":150}
	]},
	{"type":"fieldset","name":"Flow Navigation","label":"Flow Navigation","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_fromNodeLabel","label":"From","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},{"type":"input","name":"form_input_toNodeLabel","label":"To","labelWidth":105,"inputWidth":150}
	]},
	{"type":"fieldset","name":"Timer","label":"Timer","inputWidth":"auto","list":[
		{"type":"select","name":"form_input_timerType","label":"Timer Type","labelWidth":105,"inputWidth":150,
			"options":[{"text":"","value":""},
			{"text":"DateTime","value":"DateTime"},
			{"text":"duration","value":"duration"}]},
		{"type":"input","name":"form_input_timer","label":"Timer value","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_timerRepeat","label":"Repeat","labelWidth":105,"inputWidth":150}
	]},
	{"type":"fieldset","name":"Gateway","label":"Gateway","inputWidth":"auto","list":[
		{"type":"select","name":"form_input_direction","label":"Direction?","labelWidth":105,"inputWidth":150,
			"options":[{"text":"","value":""},{"text":"Converging","value":"Converging"},
				{"text":"Diverging","value":"Diverging"}]},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_defaultFlow","label":"Default Flow","labelWidth":105,"inputWidth":150}
	]},
	{"type":"fieldset","name":"Message","label":"Message","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_message","label":"Message","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_messageRepeat","label":"Message is Repeated","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_messageFinalCondition","label":"Final Message Condition","labelWidth":105,"inputWidth":150}
	]}
] 
*/

var str2 = [{"type":"fieldset","name":"Action","label":"Action","inputWidth":"auto","list":[{"type":"select","name":"form_input_actionType","label":"Action Type","labelWidth":105,"inputWidth":150,"options":[{"text":"None","value":"None"},{"text":"Form","value":"Form"},{"text":"Script","value":"Script"},{"text":"Function","value":"Function"},{"text":"Email","value":"Email"},{"text":"Web Service","value":"Web Service"}]},{"type":"input","name":"form_input_actionScript","rows":5,"label":"Action Script","labelWidth":105,"inputWidth":425},{"type":"input","name":"form_input_actionParameters","label":"Action Parameters","labelWidth":105,"inputWidth":150}]}]
var str3 = [{"type":"fieldset","name":"Condition","label":"Condition","inputWidth":"auto","list":[
	{"type":"input","name":"form_input_condition","rows":5,"label":"Condition","labelWidth":105,"inputWidth":425}]}] 
	
//	ProcessItemForm = tab.attachForm(str1);

	
	jQuery(ProcessItemForm).attr('id', 'ProcessItemForm');

	itemTabbar.addTab('history','History');
	 tab = itemTabbar.cells('history');
	 ConditionForm = tab.attachForm(str3);
	
	  
	gridItemData = tab.attachGrid();
	gridItemData.setIconsPath(dxImgPath);
	
	gridItemData.setHeader(["Property Name","Visible/Read","Input","Field Name"]);
	gridItemData.setColTypes("ro,ch,ch,ed");
	gridItemData.setColumnIds("name,read,input,field");
	
	gridItemData.setInitWidths("270,50,50,200");
	gridItemData.setColAlign("left,left,left,left");
	gridItemData.setColSorting('str,str');
	gridItemData.init();
//	gridItemData.sync(dataElementsStore);

        
	itemTabbar.addTab('itemDescription','Description');
         tab = itemTabbar.cells('itemDescription');
         tab.attachHTMLString("<div id='itemDescription'>Item Description is here</div>");
        
}
function displayCaseData()
{
        var firstRow;
        firstRow=populateGrid(gridItems,caseJson['items']);
        
	displayCaseItemDetails(firstRow);

}
function displayCaseItemDetails(rowId)
{
    
}


