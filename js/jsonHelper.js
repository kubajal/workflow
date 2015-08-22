jQuery( document ).ajaxError(function( event, request, settings , thrownError ,response ) {
  alert("Error requesting page " + settings.url + thrownError  +request +response);
});
var jsonData;
var statusBar;
function displayStatus(msg)
{
    statusBar.setText(msg);
}

function getTreeData(data,field)
{
	
	var text="<ul>";
	for (var i=0;i<data.length;i++)
	{
		var object=data[i];
	   	var val=object[field];
		text+='<li>'+val+'</li>';
		var children=object['children'];
		text+=getTreeData(children,field);
	}
	text+="</ul>";

	return text;	
	
}

function getObject(path,objectId)
{
    return getJsonValue(path+'.['+objectId+']');
    
        if (typeof(jsonRoot)==='undefined') jsonRoot=procJson;
     
	var item=null;
        var nodes=path.split('.');
       	
        data=jsonRoot;
        for(var i=0;i<nodes.length;i++)
        {
            var node=nodes[i];
            data=data[node];
        }
 	jQuery.each(data, function(indx, object){  

       		   	var id=object.id
       		   	if (id==objectId)
       		   	{
           		   	item=object;
           		   	return false;
       		   	}
       	   });
   	   return item;
}

function getJsonNode(path,createFlag)
{
        //var jsonData;
        var nodes=path.split('.');
        var data=jsonData;
        var parent=null;
        var node;
        for(var i=0;i<nodes.length;i++)
        {
            parent=data;
            node=nodes[i];

            if (node.indexOf('[')==0)
            {
                var keyName='id';
                var ki=node.indexOf('=');
                if (ki > 0)
                    keyName=node.substring(1,ki);
                else
                    ki=0;
                var index=node.substring(ki+1,node.length-1);
                
                if (data instanceof Array)
                {
                    var found=false;
                    for(var indx=0;indx<data.length;indx++)
                    {
                        var rec=data[indx];
                        if (rec[keyName]==index)
                        {
                            data=rec;
                            found=true;
                            break;
                        }
                    }
                    // record not found -- may be need to be created
                    if (!found)
                    {
                        if (createFlag)
                        {
                           var obj=new Object();
                            obj[keyName]=index;
                            data.push(obj);
                        }
                        else
                        {
                        return {data:data,parent:parent,node:node,status:'NotFound'};
                        }
                    }
                }
                else
                {
                    node=index;
                    data=data[node];
                }
            }
            else
            {
                data=data[node];
            }
        }
        
    return {data:data,parent:parent,node:node};
}
function getJsonValue(path)
{
    var res=getJsonNode(path,false);
    return res['data'];
        //var jsonData;
        var nodes=path.split('.');
        var data=jsonData;
        for(var i=0;i<nodes.length;i++)
        {
            var node=nodes[i];

            if (node.indexOf('[')==0) 
            {
                var index=node.substring(1,node.length-1);
                
                if (data.isArray())
                {
                    for(var indx=0;indx<data.length;indx++)
                    {
                        var rec=data[indx];
                        if (rec['id']=index)
                        {
                            data=rec;
                            break;
                        }
                    }
                }
               else
               {
                node=index;
                data=data[node];
               }
            }
            else
            {
                data=data[node];
            }
        }
    return data;
}

function setJsonValue(path,value)
{
    var res=getJsonNode(path,true);
    var pnode=res['parent'];
    var node = res['node'];
    pnode['_modified']=true;
    pnode[node]=value;
    
}
function getItemValue(path,itemId,field)
{
        return getJsonValue(path+'.['+itemId+'].'+field);
	var value=null;
	data=procJson[path];
 	jQuery.each(data, function(indx, object){  

       		   	var id=object.id
       		   	if (id==itemId)
       		   	{
           		   	var val=object[field];
           		   	value=val;
           		   	return false;
       		   	}
       	   });
   	   return value;
}

function populateField(path,itemId,field)
{
	var val=getItemValue(path,itemId,field);
        fieldName='form_input_'+field;
    
    	var fld = jQuery('[name='+fieldName+']');
	fld.val(fieldVal);

}
function processItemClicked(evt,itemId)
{
	displayItemDetails(itemId);
	
}

function populateSelect(data,selectControl,keyName,valName)
{
	var sel=jQuery('#'+selectControl);

	sel.on('change',(function(evt){

		var itemId=jQuery(this).val();

	    processItemClicked(evt,itemId);
	})); 
	
	
	var text="";
	   jQuery.each(data, function(indx, object){  

		   	var key=object[keyName];
		   	var val=object[valName];
		   	var opt='<option value="'+key+'">'+val+'</optiion';
	        sel.append(opt);
	   
	   });
}
function populateCombo(combo,data,idProperty,valueProperty)
{
	for(var i=0;i<data.length;i++)
	{
            var obj=data[i];
            var id=obj[idProperty];
            var val=obj[valueProperty];
            combo.put(id,val);                
        }
}

function populateGrid(grid,data)
{
	var objs=data;
	if (objs==null)
            return null

	var rows=Array();
	for(var i=0;i<objs.length;i++)
	{
		var obj=objs[i];
                
                var data=Array();
                for(var c=0;c<grid.getColumnsNum();c++)
                {
                    var colId=grid.getColumnId(c);
                    var val=obj[colId];
                    if (val==null)
                        val="";
                    data.push(val);
                }
		var row= { id: obj.id , data: data};
		rows.push(row);

	}
	var data = {rows: rows};
	grid.clearAll();
	grid.parse(data,"json");

	grid.selectRow(0);
	var rowId=grid.getSelectedRowId();
    
        return rowId;
}

function confirm(message,text)
{
			dhtmlx.confirm({
				title:message,
				ok:"Yes", cancel:"No",
				text:text,
				callback:function(result){
				return result;
			}});
}	