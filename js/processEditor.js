 jQuery( document ).ajaxError(function( event, request, settings , thrownError ,response ) {
  alert("Error requesting data " + settings.url + thrownError  +request +"Response:"+response);
waiting("");
}); 

jQuery( window ).resize(function() {
    
    if (typeof main_layout !== 'undefined') {
       main_layout.setSizes();
    }
});

/*
	BuildPage and its parts
	
	Display first time data 
	
	Synchronize	 data into Json var procJson
	
	Items:
		grid (read only), several forms
			focus is sync using bind between the two
			displayItemDetails

*/
//	--- Layout ---
	var main_layout;
	var workAreaTabbar;
	var itemTabbar;

// --- Item Forms -----
	var ProcessItemForm;
	var ActionForm;
        var MessageForm;
	var ConditionForm;
	var ItemDocForm;

	var gridItems;
	var gridItemData;

        var gridSubProcesses;
        var gridActors;

	var currentItemId;

//	----------------- Data Elements 

	var form_prop;
	var gridDataElements;

        var gridAccessRules;
        var form_access;
//	var dataElementsStore;

        var gridNotificationRules;
        
	var currentDataElementId;
// Modeler

	function diagramChanged()
	{
            omniMenusLocal.setItemEnabled("local.saveModel");
	}
	
	
	function saveDiagram()
	{
	waiting("Saving..");
//            displayStatus("Saving Data.");
            var url=getAjaxUrl(); 

/*	
	
$.ajax({
   url             :    url+"?command=saveDiagram",
   type            :   'POST',
   processData     :   false,
   contentType     :   'text/xml',
   data            :   xmlDocument,
   sucess          :   function( data ) {
      alert('success'+data);
   },
   error           :   function() {
      alert('failed to send ajax request');
   },
   complete        :   function( response) {
      alert('ajax request completed'+response.responseText);
   }
});

return;


	xmlDocument = encodeURIComponent(bpmn);

*/			
            var data = {
        			'action': 'omni_ajax_call',
                                'command': 'modeler.saveDiagram',
                                'file': file ,
                                'bpmn':  OmniXML,
				'svg': OmniSVG
                            };

//                alert(url);
			jQuery( document ).ajaxError(function( event, request, settings , thrownError ,response ) {
                            
				alert("Error requesting data " + settings.url + thrownError  +request+"response:" +response);
                                waiting("");
                                
			});
							
            jQuery.post(url, data, function(response) {
        	waiting("");
//				alert(response);
                }); 	 	

	}


// end of Modeler
        
        function newModel()
        {
//            alert("new");
			formData = [
				{type: "settings", position: "label-left", labelWidth: 100, inputWidth: 120},
				{type: "block", inputWidth: "auto", offsetTop: 12, list: [
					{type: "input", name:"modelName", label: "New Model Name", value: "new model"},
					{type: "button", value: "Proceed", offsetLeft: 70, offsetTop: 14}
				]}
			];
			dhxWins = new dhtmlXWindows();
//			dhxWins.attachViewportTo("vp");
			w1 = dhxWins.createWindow("w1", 40, 40, 300, 200);
                        w1.setText("New Model");
                    myForm = w1.attachForm(formData, true);
                    
                    myForm.attachEvent("onButtonClick", function(name)
                        {
                        var value = myForm.getItemValue("modelName");
//                        alert(value);
                        dhxWins.unload();
                        invokeAction('modeler.new','&file='+value+'.bpmn');
                        });                    

        }
        
        function invokeAction(action,params)
        {
            var inAdminMode=false;
            var seperator='?';

                if (typeof omni_admin_page !== 'undefined') {
                    inAdminMode=omni_admin_page;
                    }
                if (inAdminMode)
                    seperator='&';

                var url=window.location.href.split(seperator)[0];
                url=url+seperator+"action="+action+params;
                 window.location=url;
            return;

        }
	function getAjaxUrl()
	{
	 	var baseUrl=window.location.href.split('?')[0];

	 	var url= baseUrl;
	 	if (ajax_object!=null)
	 		{
	 		url=ajax_object.ajax_url;
	 		}
		return url;
	}
        function buildJsonForSave()
        {
            var saveData=new Object();
            var items=procJson['items'];
            var mods=Array();

            for(var i=0;i<items.length;i++)
            {
                var item=items[i];
                if (item['_modified']==true)
			mods.push(item);
            }
            saveData["items"]=procJson['items'];
            saveData["dataElements"]=procJson['dataElements'];
            saveData["actors"]=procJson['actors'];
            saveData["accessRules"]=procJson['accessRules'];
            saveData["notificationRules"]=procJson['notificationRules'];
            saveData["subprocesses"]=procJson['subprocesses'];

            var jsonStr = JSON.stringify(saveData);
            
            return jsonStr;
            
        }
	function saveJson()
	{
       	waiting("Saving..");
            
            displayStatus("Saving Data.");
            var url=getAjaxUrl();
            var jsonStr = buildJsonForSave();
/*            alert(jsonStr);
            
            jsonStr.replace(/\n/g,"~~n");
            alert(jsonStr); */

            var file=getParameterByName('file');
            var data = {
        			'action': 'omni_ajax_call',
                                'command': 'process.saveJson',
                                'file': file ,
                                'json':  jsonStr
                            };

            jQuery.post(url, data, function(response) {
        	waiting("");
                displayStatus("Data Saved.");
                
                    }); 	 	

	}
	function getJson()
	{
            waiting("Loading Data");
            displayStatus("Loading data...");
		var el=jQuery('#targetBlock');
	 	el.html("loading item details ...");
		var url=getAjaxUrl();

	 	var file=getParameterByName('file');
	 	var data = {
	 				'action': 'omni_ajax_call',
	 				'command': 'process.getJson',
	 				'file': file
	 			};
//	 	alert(url+"data :"+data.action+" "+data.command+" "+data.file);
		jQuery.post(url, data, function(response) {
//			alert(response);
            displayStatus("Data Loaded.");
        
            waiting("");
			procJson=response;
                        jsonData=procJson;
			displayData(procJson);
			}); 	 
	}
        function validationError(item,tab)
        {
            displayItemDetails(item);

        }
	function validate()
	{
            displayStatus("validating...");
		var el=jQuery('#targetBlock');
	 	el.html("loading item details ...");
		var url=getAjaxUrl();
                
                
                var jsonStr = buildJsonForSave();

	 	var file=getParameterByName('file');
	 	var data = {
	 				'action': 'omni_ajax_call',
	 				'command': 'process.validate',
                                        'file': file ,
                                        'json':  jsonStr
	 			};
//	 	alert(url+"data :"+data.action+" "+data.command+" "+data.file);
		jQuery.post(url, data, function(response) {
            displayStatus("Validated.");
                
                dhxWins = new dhtmlXWindows();
                var w1 = dhxWins.createWindow("w1", 200, 200, 320, 450);
                w1.setText("Validation Results");
                w1.attachHTMLString('<div style="overflow-y: scroll;height:400px">'+response+'</div>');
		}); 	 
	}

        
        
        
//	------------------------------- Build Page
	function BuildPage()
	{
		BuildMainLayout();
		BuildWorkArea();
	}

	var dxImgPath;
	
	function BuildMainLayout()
	{
	dxImgPath=omni_base_url+'/dhtmlx/codebase/imgs/';
	window.skin = "skyblue"; // for tree image_path
			dhtmlx.image_path=dxImgPath;

	main_layout = new dhtmlXLayoutObject('MainLayout', '2E');
        main_layout.setAutoSize("a", "a;b");
	var diagram = main_layout.cells('a');
	diagram.setText('Diagram');
	diagram.setCollapsedText('Diagram');
	
//	diagram.hideHeader();
	diagram.attachObject("diagramContents");
        
}
	

function BuildWorkArea()
{
	workArea = main_layout.cells('b');
	workArea.setText('Design Details');
//        workArea.hideHeader();
        
	workAreaTabbar = workArea.attachTabbar();

	workAreaTabbar.addTab('ProcessItems','Process Items');
	var ProcessItems = workAreaTabbar.cells('ProcessItems');
	ProcessItems.setActive();

	BuildDataModel(workAreaTabbar);

    	BuildAccessRules(workAreaTabbar);

    	BuildNotificationRules(workAreaTabbar);

	BuildProcessItems();

/*	workAreaTabbar.addTab('data','Data Model');
	var dataTab = workAreaTabbar.cells('data');
	dataTab.attachObject("DataModel"); */

	workAreaTabbar.addTab('ProcessDetails','Process Setting');
	var ProcessDetails = workAreaTabbar.cells('ProcessDetails');
        
       	gridSubProcesses = ProcessDetails.attachGrid();
	gridSubProcesses.setIconsPath(dxImgPath);
	
	gridSubProcesses.setHeader(["Sub-Process Name","Implementation"]);
	gridSubProcesses.setColTypes("ro,co");
        
        combo = gridSubProcesses.getCombo(1);
            combo.put("","Undefined");
            combo.put("all","Full Implementation");
            combo.put("no","No Implementation");
            combo.put("msgs","Receive Messages Only"); 
        
	gridSubProcesses.setColumnIds("name,implementation");
	
	gridSubProcesses.setInitWidths("270,150");
	gridSubProcesses.setColAlign("left,left");
	gridSubProcesses.init();


	gridSubProcesses.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){

		var cId=gridSubProcesses.getColumnId(cInd);
		
		if (gridSubProcesses.getColType(cInd)=='ch')
			{
			nValue=gridItemData.cells(rId,cInd).isChecked();
			}
		debug("onChange! "+stage+" row"+rId+" cell:"+cInd+":"+cId+ " value:" + nValue+" from "+oValue+" type:"+gridItemData.getColType(cInd));
		
                
//        	var item = getObject('subprocesses',rId);		
//                item[cId]=nValue;
                setJsonValue('subprocesses.['+rId+'].'+cId,nValue);
                
		return true;
	});


	workAreaTabbar.addTab('docs','Documentation');
	var docs = workAreaTabbar.cells('docs');
	var str = [
		{ type:"settings" , labelWidth:80, inputWidth:250, position:"absolute"  },
		{ type:"editor" , name:"form_editor_1", label:"Editor", labelWidth:900, inputWidth:900, inputHeight:120, labelLeft:5, labelTop:5, inputLeft:5, inputTop:21  }
	];
	var form_3 = docs.attachForm(str);

	statusBar = workArea.attachStatusBar();
	displayStatus("starting");
	
}
function addTab(tabbar,id,title,obj)
{
	tabbar.addTab(id,title);
	var tab = tabbar.cells(id);
	//tab.setActive();
	
	if (obj!=null)
		tab.attachObject(obj);
	return tab;
}
function displayItemData(force,fromTab)
{
		if (typeof(fromTab)==='undefined') fromTab=false;
		if (force)
			ItemDataChanges=true;
		if (!ItemDataChanges)
			return;
		if  ((itemTabbar.getActiveTab()=='itemData') || fromTab)
		{
		debug("display item data");
		ItemDataChanges=false;
		}
		
	var item = getObject('items',currentItemId);		
	
	if (item.dataElements==null)
		{
		item['dataElements']=Array();
		}
	var des=item.dataElements;
	
	var objs=procJson['dataElements'];
	if (objs!=null)
	{
		var rows=Array();
		for(var i=0;i<objs.length;i++)
		{
			var processDataElement=objs[i];
			var view="";
			var edit="";
			var fldName="";
			
			for(var d=0;d<des.length;d++)
			{
				var de=des[d];
				if (de.refId==processDataElement.id)
				{
                                    if ((de.view==="true") || (de.view==true))
					view=true;
                                    if ((de.edit==="true") || (de.edit==true))
					edit=true;
					fldName=de.field;
				}
			}
			var row= { id: processDataElement.id , data: [ processDataElement.name, view,edit,fldName]};
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
function displayItemDescription(itemId)
{
    var item = getObject('items',itemId);	
    
    var iType=item.type;
    
    item = getObject('descriptions',iType);	
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
function updateItemData(rowId,field,value)
{
	debug("updating item data");

        setJsonValue('items.['+currentItemId+'].dataElements.[refId='+rowId+'].'+field,value);
	
}

var ItemDataChanges=true;
//	----------------------------------
function BuildMonitorForItemData()
{
	workAreaTabbar.attachEvent("onSelect", function(id, lastId){
	
		displayItemData(false);
		
		return true;
	});
	
	itemTabbar.attachEvent("onSelect", function(id, lastId){
		if (id=='itemData')
			displayItemData(false,true);
		return true;
	});
	
	gridItems.attachEvent("onRowSelect",function(rowId,cellIndex){
		displayItemData(true);	// force a display
	});	



}
function cancelChanges()
{
                   dhtmlx.confirm({
				type:"confirm-warning",
				text:"this will cancel all the changes",
				callback:function(ret){

                                if (ret)
                                getJson();
  
                                }
                                });

}
function BuildProcessItems()
{
	var ProcessItems = workAreaTabbar.cells('ProcessItems');

	
//	var itemsMenu = workAreaTabbar.attachMenu();
		
	var layout_2 = ProcessItems.attachLayout('2U');
	var itemsList = layout_2.cells('a');
	itemsList.setWidth(360);
	itemsList.hideHeader();
//	itemsList.attachObject("proessItems");	// adding the list select to this cell
//  replace by grid 

	gridItems = itemsList.attachGrid();
	gridItems.setIconsPath(dxImgPath);
	
	gridItems.setHeader(["Type","Name"]);
	gridItems.setColTypes("ro,ro");
	gridItems.setColumnIds("type,name");
	
	gridItems.setInitWidths("150,250");
	gridItems.setColAlign("left,left");
	
	gridItems.setColSorting('str,str');
	gridItems.init();
	
	gridItems.attachEvent("onRowSelect",function(rowId,cellIndex){
		displayItemDetails(rowId);
	});	
	
// end of grid

	var ItemDetails = layout_2.cells('b');
	ItemDetails.hideHeader();
	/*
	itemsMenu.addNewSibling(null, "run", "Simulate Process", false); 
	itemsMenu.addNewSibling(null, "validate", "Validate", false); 
	itemsMenu.addNewSibling(null, "debug", "View Model", false); 
	itemsMenu.addNewSibling(null, "cancel", "Cancel", false); 
	itemsMenu.addNewSibling(null, "save", "Save", false); 
	
	itemsMenu.attachEvent('onClick', function(id,zoneId,cas)
	{
		if (id=='validate')
                {
                    validate();
                }
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
		debugWindow(procJson);
		}
		if (id=='cancel')
		{
                    cancelChanges();
		}
	});

	*/
	itemTabbar = ItemDetails.attachTabbar();
	
	var tab=addTab(itemTabbar,'itemDetails',"Item Details","itemDetails");
	tab.setActive();
	
var str1 = 
[{"type":"fieldset","name":"General","label":"General","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_type","label":"Type","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_subType","label":"subType","labelWidth":105,"inputWidth":150},
/*		{"type":"input","name":"form_input_actor","label":"Actor","labelWidth":105,"inputWidth":150}, */
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
		{"type":"input","name":"form_input_caseStatus","label":"Case Status","labelWidth":105,"inputWidth":150},
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
	]}
] 
var strMsg=[{"type":"fieldset","name":"Message","label":"Message","inputWidth":"auto","list":[
		{"type":"input","name":"form_input_message","label":"Message","labelWidth":105,"inputWidth":150},
		{"type":"input","name":"form_input_messageRepeat","label":"Message is Repeated","labelWidth":105,"inputWidth":150},
		{"type":"newcolumn"},
		{"type":"input","name":"form_input_messageFinalCondition","label":"Final Message Condition","labelWidth":105,"inputWidth":150}
	]}]
var str2 = [{"type":"fieldset","name":"Action","label":"Action","inputWidth":"auto","list":[{"type":"select","name":"form_input_actionType","label":"Action Type","labelWidth":105,"inputWidth":150,"options":[{"text":"None","value":"None"},{"text":"Form","value":"Form"},{"text":"Script","value":"Script"},{"text":"Function","value":"Function"},{"text":"Email","value":"Email"},{"text":"Web Service","value":"Web Service"}]},{"type":"input","name":"form_input_actionScript","rows":5,"label":"Action Script","labelWidth":105,"inputWidth":425},{"type":"input","name":"form_input_actionParameters","label":"Action Parameters","labelWidth":105,"inputWidth":150}]}]
var str3 = [{"type":"fieldset","name":"Condition","label":"Condition","inputWidth":"auto","list":[
	{"type":"input","name":"form_input_condition","rows":5,"label":"Condition","labelWidth":105,"inputWidth":425}]}] 
	
	ProcessItemForm = tab.attachForm(str1);

	
	jQuery(ProcessItemForm).attr('id', 'ProcessItemForm');

	itemTabbar.addTab('condition','Condition');
	 tab = itemTabbar.cells('condition');
	 ConditionForm = tab.attachForm(str3);
	
	itemTabbar.addTab('action','Action');
	  tab = itemTabbar.cells('action');
	  ActionForm = tab.attachForm(str2);

	itemTabbar.addTab('message','Message');
	  tab = itemTabbar.cells('message');
	  MessageForm = tab.attachForm(strMsg);


	itemTabbar.addTab('itemData','Data Usage');
	  tab = itemTabbar.cells('itemData');
	  
	gridItemData = tab.attachGrid();
	gridItemData.setIconsPath(dxImgPath);
	
	gridItemData.setHeader(["Property Name","Visible/View","Edit","Field Name"]);
	gridItemData.setColTypes("ro,ch,ch,ed");
	gridItemData.setColumnIds("name,view,edit,field");
	
	gridItemData.setInitWidths("270,50,50,200");
	gridItemData.setColAlign("left,left,left,left");
	gridItemData.setColSorting('str,str');
        gridItemData.enableDragAndDrop(true);
	gridItemData.init();
//	gridItemData.sync(dataElementsStore);
	gridItemData.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){

		var cId=gridItemData.getColumnId(cInd);
		
		if (gridItemData.getColType(cInd)=='ch')
			{
			nValue=gridItemData.cells(rId,cInd).isChecked();
			}
		debug("onChange! "+stage+" row"+rId+" cell:"+cInd+":"+cId+ " value:" + nValue+" from "+oValue+" type:"+gridItemData.getColType(cInd));
		updateItemData(rId,cId,nValue);
		return true;
	});

	itemTabbar.addTab('itemDoc','Documentation');
	  tab = itemTabbar.cells('itemDoc');

	var str = [
		{ type:"settings" , labelWidth:80, inputWidth:250, position:"absolute"  },
		{ type:"editor" , name:"form_editor_1", label:"Editor", labelWidth:900, inputWidth:900, inputHeight:120, labelLeft:5, labelTop:5, inputLeft:5, inputTop:21  }
		];
	  
	ItemDocForm = tab.attachForm(str);	

	ProcessItemForm.attachEvent("onChange", function (status){
		itemFieldUpdated(ProcessItemForm,status);
	});
	ActionForm.attachEvent("onChange", function (status){
		itemFieldUpdated(ActionForm,status);
	});
	MessageForm.attachEvent("onChange", function (status){
		itemFieldUpdated(MessageForm,status);
	});
	ConditionForm.attachEvent("onChange", function (status){
		itemFieldUpdated(ConditionForm,status);
	});
	ItemDocForm.attachEvent("onChange", function (status){
		itemFieldUpdated(ItemDocForm,status);
	});
        
        
	itemTabbar.addTab('itemDescription','Description');
         tab = itemTabbar.cells('itemDescription');
         tab.attachHTMLString("<div id='itemDescription'>Item Description is here</div>");
        
	BuildMonitorForItemData();
		
}
function BuildDataModel(tabbar)
{
	tabbar.addTab('dataModel','Data Model');
	var dataModel = tabbar.cells('dataModel');
	var layout_3 = dataModel.attachLayout('2U');

	/* --------------------- Properties List -------------*/

//	dataElementsStore = new dhtmlXDataStore()

	
	var cell_9 = layout_3.cells('a');
	cell_9.setText('Properties');

	var grid_4 = cell_9.attachGrid();
	grid_4.setIconsPath(dxImgPath);
	
	grid_4.setHeader(["Name","Title","Type"]);
	grid_4.setColTypes("ro,ro,ro");
	grid_4.setColumnIds("name,title,dataType");
	
	grid_4.setInitWidths("270,250,100");
	grid_4.setColAlign("left,left,left");
//	grid_4.setColTypes("ed,ed");

	
	grid_4.setColSorting('str,str');
	grid_4.init();
	
	gridDataElements=grid_4;
	gridDataElements.attachEvent("onRowSelect",function(rowId,cellIndex){
		displayProperty(rowId);
	});	
	
	/* --------------------- Property -------------*/
	var cell_6 = layout_3.cells('b');
	cell_6.setText('Property Details');
	
	var str = [
	{ type:"settings" , labelWidth:80, inputWidth:250, position:"absolute"  },
	{ type:"button" , name:"form_button_prop", label:"New Property", value:"New Property", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 20},
	{ type:"button" , name:"form_button_del", label:"Delete Property", value:"Delete Property", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 140},
	{"type":"fieldset","name":"Property","label":"Property","inputWidth":"auto",
			offsetLeft:10, offsetTop: 40,"list":[
		{type:"settings", position:"label-left" },
		{ type:"input" , name:"form_input_name", label:"Name", labelWidth:100, labelAlign:"left"   },
		{ type:"input" , name:"form_input_title", label:"Title", labelWidth:100, labelAlign:"left"  },
		{ type:"input" , name:"form_input_description", label:"Description", labelWidth:100, labelAlign:"left"  },
		{ type:"select"  ,name:"form_input_dataType", label:"Data Type", labelWidth:100, labelAlign:"left" ,
			"options":[{"text":"String","value":"String"},
					{"text":"Number","value":"Number"},
					{"text":"Text","value":"Text"},
					{"text":"Select","value":"Select"},
					{"text":"Date","value":"Date"},
					{"text":"Boolean","value":"Boolean"},
					{"text":"Email","value":"Email"},
					{"text":"File","value":"File"}]},
		{ type:"input" , name:"form_input_validValues", label:"Valid Values", rows:3, labelWidth:100, labelAlign:"left"   },
		{ type:"input" , name:"form_input_options", label:"Options", rows:3, labelWidth:100, labelAlign:"left"   },
		{ type:"checkbox" , name:"form_input_req", label:"Required", labelWidth:100, labelAlign:"left"  }
	]}
	];
	form_prop = cell_6.attachForm(str);
	form_prop.attachEvent("onChange", function (fieldName){
		var field=getPropertyFromField(fieldName);
		var value = form_prop.getItemValue(fieldName);
		
		ItemDataChanges=true;
		
		debug("onChange!"+fieldName+" val:"+value+ " for de:" + currentDataElementId);
		if (field=='name')
			gridDataElements.cells(currentDataElementId,0).setValue(value);
		if (field=='title')
			gridDataElements.cells(currentDataElementId,1).setValue(value);
		if (field=='dataType')
			gridDataElements.cells(currentDataElementId,2).setValue(value);
			
                setJsonValue("dataElements.["+currentDataElementId+"]."+field,value)
//		var de = getObject('dataElements',currentDataElementId);
//		de[field]=value;
	});

	
//	form_prop.sync(dataElementsStore);
//	gridDataElements.sync(dataElementsStore);
	
	form_prop.bind(gridDataElements);
	form_prop.attachEvent("onButtonClick", function(name){

		var type;
		type="Property";
		
		if (name=='form_button_prop')
		{
			ItemDataChanges=true;
			var defaults={name:"new Property", type:type};
			newObject(gridDataElements,'dataElements',defaults);
/*			var newId=(new Date()).valueOf();
			var rowId=gridDataElements.getSelectedRowId();
			var indx=gridDataElements.getRowIndex(rowId);
			
			gridDataElements.addRow(newId,"new Property,"+type,indx+1);
			indx=gridDataElements.getRowIndex(newId);
			gridDataElements.selectRow(indx,true,false,true);
*/			
		}
		if (name=='form_button_del')
		{
			ItemDataChanges=true;
			deleteObject(gridDataElements,'dataElements');
			displayProperty(gridDataElements.getSelectedRowId());
		}

	});
}

var arActorCombo1;
var arActorCombo2;
var nrActorCombo;

function populateActorCombo()
{
    populateArActorCombo();
    populateCombo(nrActorCombo,procJson['actors'],'actor','actor');
}
function populateArActorCombo()
{
    populateCombo(arActorCombo1,procJson['actors'],'actor','actor');
    populateCombo(arActorCombo2,procJson['actors'],'actor','actor');

return;
    var data=procJson['accessRules'];
    for(var i=0;i<data.length;i++)
	{
            var ar=data[i];
            var val=ar['asActor'];
            if ((val !== '') && (val !==null))
            {
                arActorCombo.put(val,val);
            }
        }    
}
function BuildAccessRules(tabbar)
{
	tabbar.addTab('access','Access Rules');
	var dataModel = tabbar.cells('access');
	var layout_3 = dataModel.attachLayout('3L');

	var cell_9 = layout_3.cells('a');
	cell_9.setText('Access Rules');

	gridAccessRules = cell_9.attachGrid();
	gridAccessRules.setIconsPath(dxImgPath);
	
	gridAccessRules.setHeader(["User Goup","Work Scope Type","WorkScope Variable","Actor","Privilege","On","As Actor","Condition"]);
	gridAccessRules.setColTypes("co,co,co,co,co,co,ed,txt");
	gridAccessRules.setColumnIds("userGroup,workScopeType,workScopeVariable,actor,privilege,nodeId,asActor,condition");
	
	gridAccessRules.setInitWidths("100,80,80,40,40,150,80,300");
	gridAccessRules.setColAlign("center,center,center,center,left,center");

        arActorCombo1= gridAccessRules.getCombo(3);
        arActorCombo2= gridAccessRules.getCombo(6);
        
        combo = gridAccessRules.getCombo(4);
            combo.put("V","View");
            combo.put("S","Start");
            combo.put("P","Perform");
            combo.put("A","Assign"); 
        
        combo = gridAccessRules.getCombo(5);
            combo.put("__Process__","Process");

	gridAccessRules.init();
	
	gridAccessRules.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){

		var cId=gridAccessRules.getColumnId(cInd);
		
		if (gridAccessRules.getColType(cInd)=='ch')
			{
			nValue=gridItemData.cells(rId,cInd).isChecked();
			}
                if (cId=='asActor')
                {
//                    populateArActorCombo();
                }
		debug("onChange! "+stage+" row"+rId+" cell:"+cInd+":"+cId+ " value:" + nValue+" from "+oValue+" type:"+gridItemData.getColType(cInd));
		
                
        	setJsonValue('accessRules.['+rId+'].'+cId,nValue)
	
		return true;
	});
	
	/* --------------------- Property -------------*/
	var cell_6 = layout_3.cells('b');
	cell_6.setText('');
	
	var str = [
	{ type:"settings" , labelWidth:80, inputWidth:250, position:"absolute"  },
	{ type:"button" , name:"form_button_add", label:"Add Rule", value:"Add Rule", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 20},
	{ type:"button" , name:"form_button_del", label:"Delete Rule", value:"Delete Rule", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 140},
	{ type:"button" , name:"form_button_addA", label:"Add Actor", value:"Add Actor", 
				width:"75", inputWidth:75 , inputTop:55 , inputLeft: 20},
	{ type:"button" , name:"form_button_delA", label:"Delete Adtor", value:"Delete Actor", 
				width:"75", inputWidth:75 , inputTop:55 , inputLeft: 140}
	];
        
        cell_6.setHeight(120);
	form_access = cell_6.attachForm(str);
	form_access.attachEvent("onChange", function (fieldName){

            var field=getPropertyFromField(fieldName);
		var value = form_prop.getItemValue(fieldName);
		
		debug("onChange!"+fieldName+" val:"+value+ " for de:" + currentDataElementId);
	});


	form_access.attachEvent("onButtonClick", function(name){
		var type;
		type="Property";
		
		if (name=='form_button_add')
		{
			var defaults={};
			newObject(gridAccessRules,'accessRules',defaults);
		}
		if (name=='form_button_del')
		{
			deleteObject(gridAccessRules,'accessRules');
		}
		if (name=='form_button_addA')
		{
			var defaults={};
			newObject(gridActors,'actors',defaults);
                        populateActorCombo();
		}
		if (name=='form_button_delA')
		{
			deleteObject(gridActors,'actors');
                        populateActorCombo();
		}


	});
        
    // Actors
	var cell_7 = layout_3.cells('c');
	cell_7.setText('Actors');

	gridActors = cell_7.attachGrid();
	gridActors.setIconsPath(dxImgPath);
	
	gridActors.setHeader(["Actor","Description"]);
	gridActors.setColTypes("ed,ed");
	gridActors.setColumnIds("actor,description");
	
	gridActors.setInitWidths("100,300");
	gridActors.setColAlign("center,center");
	gridActors.init();
	
	gridActors.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){

		var cId=gridActors.getColumnId(cInd);
		
		if (gridActors.getColType(cInd)=='ch')
			{
			nValue=gridItemData.cells(rId,cInd).isChecked();
			}
		debug("onChange! "+stage+" row"+rId+" cell:"+cInd+":"+cId+ " value:" + nValue+" from "+oValue+" type:"+gridItemData.getColType(cInd));
		
        	setJsonValue('actors.['+rId+'].'+cId,nValue)
                populateActorCombo();
		return true;
	});
	
    
}
function BuildNotificationRules(tabbar)
{
	tabbar.addTab('notification','Notifications');
	var dataModel = tabbar.cells('notification');
	var layout_3 = dataModel.attachLayout('2U');

	var cell_9 = layout_3.cells('a');
	cell_9.setText('Notifications');

	gridNotificationRules = cell_9.attachGrid();
	gridNotificationRules.setIconsPath(dxImgPath);
	
	gridNotificationRules.setHeader(["User Goup","When","Condition"]);
	gridNotificationRules.setColTypes("co,co,txt");
	gridNotificationRules.setColumnIds("userGroup,nodeId,condition");
	
	gridNotificationRules.setInitWidths("100,150,300");
	gridNotificationRules.setColAlign("center,center,center");

        nrActorCombo= gridNotificationRules.getCombo(0);
        
        nrTasksCombo = gridNotificationRules.getCombo(1);
          nrTasksCombo.put("__Process__","Process");

	gridNotificationRules.init();
	
	gridNotificationRules.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){

		var cId=gridNotificationRules.getColumnId(cInd);
		
		debug("onChange! "+stage+" row"+rId+" cell:"+cInd+":"+cId+ " value:" + nValue+" from "+oValue+" type:"+gridItemData.getColType(cInd));
		
        	setJsonValue('notificationRules.['+rId+'].'+cId,nValue)
	
		return true;
	});
	
	/* --------------------- Property -------------*/
	var cell_6 = layout_3.cells('b');
	cell_6.setText('');
	
	var str = [
	{ type:"settings" , labelWidth:80, inputWidth:250, position:"absolute"  },
	{ type:"button" , name:"form_button_add", label:"Add Notification", value:"Add Notification", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 20},
	{ type:"button" , name:"form_button_del", label:"Delete Notification", value:"Delete Notification", 
				width:"75", inputWidth:75 , inputTop:5 , inputLeft: 140}
	];
        
        cell_6.setHeight(120);
	form_access = cell_6.attachForm(str);

	form_access.attachEvent("onButtonClick", function(name){
		var type;
		type="Property";
		
		if (name=='form_button_add')
		{
			var defaults={};
			newObject(gridNotificationRules,'notificationRules',defaults);
		}
		if (name=='form_button_del')
		{
			deleteObject(gridNotificationRules,'notificationRules');
		}
	});
    
}

function deleteObject(grid,path)
{
			var rowId=grid.getSelectedRowId();
			var indx=grid.getRowIndex(rowId);
			var arr=procJson[path];
			var newArr=arr.splice(indx, 1);
			grid.deleteRow(rowId);
				
			if (indx==grid.getRowsNum())
				indx--;
			grid.selectRow(indx);

}
function newObject(grid,path,defaultValues)
{
			var newId=(new Date()).valueOf();
			var rowId=grid.getSelectedRowId();
			var indx=grid.getRowIndex(rowId);
			var beforeObj=getObject(path,rowId);
			
			var values=Array();
			for (var k in defaultValues){
				values.push(defaultValues[k]);
				}
			
			grid.addRow(newId,values,indx+1);
				
			indx=grid.getRowIndex(newId);
			var object=defaultValues;
			object['id']=newId;
			var arr=procJson[path];
			var newArr=arr.splice(indx, 0, object);
			grid.selectRow(indx,true,false,true);
}
function itemFieldUpdated(form,fieldName)
{
		var value = form.getItemValue(fieldName);
		debug("onChange!"+fieldName+" val:"+value+ " for row:" + currentItemId);
		var field=getPropertyFromField(fieldName);
                setJsonValue('items.['+currentItemId+'].'+field,value);
}
function initFields()
{
	ProcessItemForm.setReadonly('form_input_id',true);

	ProcessItemForm.setReadonly('form_input_type',true);
	ProcessItemForm.setReadonly('form_input_subType',true);

	ProcessItemForm.setReadonly('form_input_subProcess',true);
	ProcessItemForm.setReadonly('form_input_inflowsLabels',true);
	ProcessItemForm.setReadonly('form_input_outflowsLabels',true);
	ProcessItemForm.setReadonly('form_input_fromNodeLabel',true);
	ProcessItemForm.setReadonly('form_input_toNodeLabel',true);
}
function displayObject(forms,path,objectId)
{
	var obj=getObject(path,objectId);


	forms.forEach(function(form) {
		form.forEachItem(function(name){
			var type=form.getItemType(name);
			if ((type=='input')||(type=='select')||(type=='checkbox'))
				{
				var fieldName=getPropertyFromField(name);
				if (obj==null)
					{
					form.setItemValue(name,'');
					}
					else
					{	if (fieldName in obj)
						{
						var val=obj[fieldName];
						form.setItemValue(name,val);
						}
						else
						obj[fieldName]=null;
					}
				}
		});
	});
}
function displayProperty(rowId)
{
	currentDataElementId=rowId;
	displayObject([form_prop],'dataElements',rowId);
}
function getPropertyFromField(fieldName)
{
	if (fieldName.indexOf("form_input")==0)
		{
		var l="form_input_".length;
		return fieldName.substring(l);
		}
	return fieldName;
}
function displayItemDetails(itemId)
{
	currentItemId=itemId;
	
	var forms=[ProcessItemForm,ActionForm,ConditionForm,ItemDocForm];

	displayObject(forms,'items',itemId);
	checkItemFields(itemId);
        
	var rowId=gridItems.getSelectedRowId();
        if (rowId!=itemId)
        {
            gridItems.selectRowById(itemId);
            displayItemData(true);
        }

}
function checkItemFields(itemId)
{
	var type=getItemValue('items',itemId,"type");
	var subtype=getItemValue('items',itemId,"subType");
	var hasMessage=getItemValue('items',itemId,"hasMessage");
	var hasTimer=getItemValue('items',itemId,"hasTimer");
	var isFlow=false;
	var isTask=false;

	if ((type.indexOf('Task')>=0)|| (type.indexOf('task')>=0))
		isTask=true;
	
	if ((type=='sequenceFlow')||(type=='messageFlow'))
		isFlow=true;

	if (isFlow)
		{
		itemTabbar.cells('itemDoc').hide();
		itemTabbar.cells('itemData').hide();
		itemTabbar.cells('condition').show();
		ProcessItemForm.showItem("Flow Navigation");
		ProcessItemForm.hideItem("Navigation");
		}
	else
		{
		itemTabbar.cells('itemDoc').show();
		itemTabbar.cells('itemData').show();
		itemTabbar.cells('condition').hide();
		ProcessItemForm.hideItem("Flow Navigation");
		ProcessItemForm.showItem("Navigation");
		}
	
	if (isTask)
		itemTabbar.cells('action').show();
	else
		itemTabbar.cells('action').hide();

		
	if (type.indexOf("Gateway")==-1)
		ProcessItemForm.hideItem("Gateway");
	else
		ProcessItemForm.showItem("Gateway");

	if (hasMessage==true)
		itemTabbar.cells('message').show();
	else
		itemTabbar.cells('message').hide();

	if (hasTimer==true)
		ProcessItemForm.showItem("Timer");
	else
		ProcessItemForm.hideItem("Timer");

}

function displayData()
{
	//  display gridItems

        // Process details
        populateActorCombo();

        
        var combo=gridAccessRules.getCombo(5);
        var procItems=procJson["items"];
        
        for(var i=0;i<procItems.length;i++)
        {
            var item=procItems[i];
            if ((item['type']=='userTask') || (item['type']=='startEvent'))
            {
            combo.put(item['id'],item['name']);                
            }
        } 
        
        for(var i=0;i<procItems.length;i++)
        {
            var item=procItems[i];
            if ((item['superType']=='Task') || (item['superType']=='Event') )
            {
                var label=item['type']+':'+item['name'];
            nrTasksCombo.put(item['id'],label);                
            }
        } 
        
        populateGrid(gridActors,procJson['actors']);
        
        populateGrid(gridAccessRules,procJson['accessRules']);
	
        populateGrid(gridNotificationRules,procJson['notificationRules']);
        
        populateGrid(gridSubProcesses,procJson['subprocesses']);

    
	var objs=procJson['items'];
	if (objs!=null)
	{

	var rows=Array();
	for(var i=0;i<objs.length;i++)
	{
		var obj=objs[i];
		var row= { id: obj.id , data: [ obj.type, obj.name]};
		rows.push(row);

	}
	var data = {rows: rows};
	gridItems.clearAll();
	gridItems.parse(data,"json");

	gridItems.selectRow(0);
	var rowId=gridItems.getSelectedRowId();
	displayItemDetails(rowId);

	}

	// display gridObjects
	var objs=procJson['dataElements'];
	if (objs!=null)
	{

	var rows=Array();
	for(var i=0;i<objs.length;i++)
	{
		var obj=objs[i];
		if (obj.id ==null)
			obj.id ='dataElement'+i;
		var row= { id: obj.id , data: [ obj.name, obj.title,obj.dataType]};
		rows.push(row)
	}
	var data = {rows: rows};
	gridDataElements.clearAll();
	gridDataElements.parse(data,"json");

	gridDataElements.selectRow(0);
	rowId=gridDataElements.getSelectedRowId();
	displayProperty(rowId);
	
	}
        
}
var treeIndx=0;

function debugWindow(jsonRoot)
{
	treeIndx=0;
	dhxWins = new dhtmlXWindows();
	var w1 = dhxWins.createWindow("w1", 20, 30, 320, 400);
        w1.setText("Process Data");

	var tree=w1.attachTree();
	tree.setImagePath(dxImgPath+'dhxtree_'+window.skin+'/');
	tree.deleteChildItems(0);
//		tree.insertNewChild(0,1,"Root");

		debugObject(tree,jsonRoot,0);
		
	tree.closeAllItems(0);
}

function debugObject(tree,object,parent)
{
		if (object instanceof Array)
		{
		for (var i=0;i<object.length;i++)
			{
				var obj=object[i];
				treeIndx++;
				if ((obj instanceof Array)||(obj instanceof Object))
				{
					tree.insertNewChild(parent,treeIndx,i);
					debugObject(tree,obj,treeIndx);
				}
				else
					tree.insertNewChild(parent,treeIndx,i+" : "+obj);
			}
		}
		else
		{
			if (object instanceof Object)
			{
			for(var propertyName in object) {
				treeIndx++;
				var obj=object[propertyName];
				if ((obj instanceof Array)||(obj instanceof Object))
					{
						tree.insertNewChild(parent,treeIndx,propertyName);
						debugObject(tree,obj,treeIndx);
					}
				else
					tree.insertNewChild(parent,treeIndx,propertyName+" : "+obj);
				}			
			}
			else
				tree.insertNewChild(parent,treeIndx++,object);
		}
}
function waiting(msg)
{
    if (msg=='')
    {
        jQuery( "#omni_page" ).isLoading( "hide" );
        
    }
    else
    {
        jQuery( "#omni_page" )
            .isLoading({
                            text:       msg,
                            position:   "overlay"
                        });        
    }
    /*
    if (msg=='')
    {
         dhxWins.unload();
         return;
    }
    else
    {
        $( "selector" ).isLoading();    
    }
    
     dhxWins = new dhtmlXWindows();
     var w1 = dhxWins.createWindow("w1", 200, 200, 120, 120);
     w1.setText("waiting");
     w1.attachHTMLString(msg);
*/
}
function debug(msg)
{
//    dhtmlx.message(msg);
}