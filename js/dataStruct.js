/*
 *  DataStructure is a class to hold Json data
 */
function DataStruct(data)
{
    var self=this;
    var data;
    
    self.data=data;
    
    self.notify=notifier;
    

//-------------------------------------------------------------------------------
self.getObject= function(path,objectId)
{
    if (objectId!==null)
        path=path+'.['+objectId+']';
    return getJsonValue(path);
}

self.getNode= function (path,createFlag)
{
        var nodes=path.split('.');
        var data=jsonData;
        var parent=null;
        var tree=Array();
        var node;
        for(var i=0;i<nodes.length;i++)
        {
            if (data!==null)
                tree.push(data);
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
                            data=obj;
                        }
                        else
                        {
                            data=null;
                        return {data:data,parent:parent,node:node,tree: tree ,status:'NotFound'};
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
                if(node in data) {
                    data=data[node];
                }
                else {
                    if (createFlag)
                    {   // create new object or new array we need to find out
                        if ((i<nodes.length-1) && (nodes[i+1].indexOf('[')==0)) {
                            data[node]=new Array();
                        } else {
                            data[node]=new Object();
                        }
                        data=data[node];
                                
                    } else {
                        data=null;
                        break;
                    }
                }
            }
        }
        
    return {data:data,parent:parent,tree: tree ,node:node};
}
self.getValue= function (path)
{
    var res=self.getNode(path,false);
    return res['data'];
}

self.setValue=function (path,value)
{
    var res=self.getNode(path,true);
    var pnode=res['parent'];
    var node = res['node'];
    var tree = res['tree'];
    for(var i=0;i<tree.length;i++)
    {
        tree[i]['_modified']=true;
    }
    pnode[node]=value;
    
}
self.getItemValue=function (path,itemId,field)
{
        return self.getValue(path+'.['+itemId+'].'+field);
}


}
/////

function DXGrid(data,path)
{
    var self=this;
    var grid;
    var data;
    var path;
    var monitorSelect;
    var monitorChange;
    
    self.data=data;
    self.path=path;
    
self.attach=function (parent)    
{
    self.grid=parent.attachGrid();
    self.grid.setIconsPath(dxImgPath);

}
self.build= function (headers,cols,types,widths,align,sorts)
{
    	self.grid.setHeader(headers);
    	self.grid.setColumnIds(headers);
	self.grid.setColTypes(types);
        
	self.grid.setInitWidths(widths);
	self.grid.setColSorting(sorts);
        
        if (align!==null)
            self.grid.setColAlign(align);
        self.grid.init();

	self.grid.attachEvent("onRowSelect",function(rowId,cellIndex){
		self.monitorSelect(rowid,cellIndex);	
	});	
}

self.populate = function ()
{
	var objs=self.data.getObject(self.path);
	if (objs==null)
            return null

	var rows=Array();
	for(var i=0;i<objs.length;i++)
	{
		var obj=objs[i];
                
                var data=Array();
                for(var c=0;c<self.grid.getColumnsNum();c++)
                {
                    var colId=self.grid.getColumnId(c);
                    var val=obj[colId];
                    if (val==null)
                        val="";
                    data.push(val);
                }
		var row= { id: obj.id , data: data};
		rows.push(row);

	}
	var data = {rows: rows};
	self.grid.clearAll();
	self.grid.parse(data,"json");

	self.grid.selectRow(0);
	var rowId=self.grid.getSelectedRowId();
    
        return rowId;
}

