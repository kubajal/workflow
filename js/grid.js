var $SlickImages="lib/SlickGrid/images";

function Grid(gridId,columns,options,notifier)
{
    var self=this;
    var gridId;
    var options;
    var grid=null;
    var dataView=null;
    var data=null;
    var columns;
    var currentRow; 
    var notify;
    var maxId;

    var hasTree;
    var hasSequence;
    
    self.notify=notifier;
    
    self.gridId=gridId;
    self.columns=columns;
    self.options=options;

//-------------------------------------------------------------------------------
self.getDataRow= function (rowNo)
{
    return self.dataView.getItem(rowNo);
}
//-------------------------------------------------------------------------------
self.getCurrentRow= function()
{
    var indx=self.getCurrentRowNo();
    if (indx!==null)
           return self.getDataRow(indx);
    else
        return null;
}
//-------------------------------------------------------------------------------
self.getCurrentRowNo= function()
{
    var selectedrows = self.grid.getSelectedRows();  
    var len = selectedrows.length;
    if (len>0)
           {
           return selectedrows[0];
           }
    else
        return null;
}
//-------------------------------------------------------------------------------
self.init =function()
{
    
  self.groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider();
  self.dataView = new Slick.Data.DataView({
//    groupItemMetadataProvider: groupItemMetadataProvider,
    inlineFilters: true
  });
  self.grid = new Slick.Grid(self.gridId, self.dataView, self.columns, self.options);

    self.hasTree=false;
    self.hasSequence=false;

    var treeField=self.options.treeField;

    
    if (treeField === null || treeField === undefined || !treeField) 
        ;
    else
        self.hasTree=true;

    if (self.options.sequence === null || self.options.sequence === undefined || self.options.sequence=='') 
        ;
    else
        self.hasSequence=true;
    
    if (self.hasTree)
    {
//        self.options.enableDragAndDrop=false; // just for now to avoid a bug
        self.dataView.setFilterArgs({
                gridObject: self
                });        
        self.dataView.setFilter(treeFilter);
    }
    if (self.hasTree || self.hasSequence)
    {
        for(var i=0;i<self.columns.length;i++)
        {
            var col=columns[i];
            if (col.id===self.options.treeField)
            {
                 col.formatter=self.TreeFormatter;
            }
            col.sortable=false;
        }
    }
    
    
        $(".grid-header .ui-icon")
            .addClass("ui-state-default ui-corner-all")
            .mouseover(function (e) 
            {
                $(e.target).addClass("ui-state-hover")
            })
            .mouseout(function (e) 
            {
                $(e.target).removeClass("ui-state-hover")
            });

  // register the group item metadata provider to add expand/collapse group handlers
//  self.grid.registerPlugin(groupItemMetadataProvider);
  self.grid.setSelectionModel(new Slick.RowSelectionModel());

//  var pager = new Slick.Controls.Pager(self.dataView, self.grid, $("#pager"));
  var columnpicker = new Slick.Controls.ColumnPicker(self.columns, self.grid, self.options);
  
  self.grid.onClick.subscribe(function (e, args) {
    if ($(e.target).hasClass("toggle")) {
        
      debug("onclick with a toggle");
      var item = self.dataView.getItem(args.row);
      if (item) {
        if (!item._collapsed) {
          item._collapsed = true;
        } else {
          item._collapsed = false;
        }

        self.dataView.updateItem(item.id, item);
      }
      e.stopImmediatePropagation();
    }
  });

  
  
  self.grid.onSelectedRowsChanged.subscribe(function (e, args) {
  
  	 var selectedrows = self.grid.getSelectedRows();  
	 var len = selectedrows.length;
	 if (len>0)
		{
		indx=selectedrows[0];
		if (indx !== self.selectedRow)
			{
                        args['row']=self.getDataRow(indx);
                        args['gridObject']=self;
			self.selectedRow=indx;
                        if (self.notify!==null)
                            self.notify('onSelectedRowsChanged',e,args);
			}
		}
	});
 
  self.grid.onCellChange.subscribe(function (e, args) {
        var cell=args.cell;
        var dataRow=self.getDataRow(args.row);
        var field=args.grid.getColumns()[args.cell].field;
        var value = dataRow[field];        
        args['value']=value;
        args['field']=field;
        args['rowId']=dataRow.id;
        args['row']=dataRow;
        args['gridObject']=self;
        
        if (self.notify!==null)
            self.notify('onCellChange',e,args);
	});

  self.grid.onSort.subscribe(function (e, args) {
    sortdir = args.sortAsc ? 1 : -1;
    sortcol = args.sortCol.field;

     {
      // using native sort with comparer
      // preferred method but can be very slow in IE with huge datasets
      self.dataView.sort(self.comparer, args.sortAsc);
    }
  });

  // wire up model events to drive the grid
  self.dataView.onRowCountChanged.subscribe(function (e, args) {
    self.grid.updateRowCount();
    self.grid.render();
  });

  self.dataView.onRowsChanged.subscribe(function (e, args) {
    self.grid.invalidateRows(args.rows);
    self.grid.render();
  });

  if (self.options.enableDragAndDrop)
  {
      self.enableDragAndDrop();
  }

// ------------------ end of init()
}
//-------------------------------------------------------------------------------
self.setCurrentRowValue=function(property,value)
{
    var indx=self.getCurrentRowNo();
    var item = self.getDataRow(indx);
    var id=item.id;
    item[property] = value;
    self.dataView.updateItem(id, item);
}

//-------------------------------------------------------------------------------
self.setData =function(data) 
{

    if (self.options.sequence!=='')
    {
        //data.sort()
    }
    var seq=10;
    var delta=10;
    if (self.hasTree)
    {
        var parents=[];
        for(var i=0;i<data.length;i++)
        {
            var node=data[i];
            var id=node.id;
            var parentId=node['parent'];
            if (parentId=='')
            {
                node['indent']=0;
            }
            else
            {
                var parentNode=parents[parentId];
                node['indent']=parentNode['indent']+1;
            }
            parents[id]=node;
            if (self.options.sequence!=='')
            {
                node[self.options.sequence]=seq;
                seq+=delta;
            }
            

        }
    }

  self.maxId = 100;
  self.dataView.beginUpdate();
  
  self.dataView.setItems(data);
  self.dataView.setGrouping([]);
  
  self.dataView.endUpdate();
  
  var selectedRows = Array();
  selectedRows.push(0);
  self.grid.setSelectedRows(selectedRows);

}
//-------------------------------------------------------------------------------
self.comparer =function(a,b) //function comparer(a, b) 
{
  var x = a[sortcol], y = b[sortcol];
  return (x == y ? 0 : (x > y ? 1 : -1));
}
//-------------------------------------------------------------------------------
self.addRow =function() //  function addRow(gridObject)
  {
	 var selectedrows = self.grid.getSelectedRows();  
	 var len = selectedrows.length;
	 var indx=0;
	 if (len>0)
		{
		indx=selectedrows[0];
		}
	
		
//	gridObject.dataView.addItem({'id': '1l5', 'lang': 'CoffeeScript'});
        self.maxId = self.maxId +1;
	var item = {
      "id": self.maxId , "title": "testing"
            };
            
    if (self.options.sequence)
    {
        var startSeq=0;
        var endSeq;
        var newSeq;
        
        var data1 = self.grid.getData().getItem(indx);
        if (data1)
            startSeq=data1[self.options.sequence];
            
        var data2 = self.grid.getData().getItem(indx+1);
        if (data2)
        {
            endSeq=data2[self.options.sequence];
           newSeq =(endSeq+startSeq) / 2;
        }
        else
            newSeq=startSeq+10;
        
        item[self.options.sequence]=newSeq;
    }

        indx++;
        var args=Array();
        args['index']=indx;
        args['rowId']=item.id;
        
	self.dataView.insertItem(indx, item);  

        args['row']=self.getDataRow(indx);

        var selectedRows = Array();
        selectedRows.push(indx);
        self.grid.setSelectedRows(selectedRows);

	
	self.dataView.endUpdate();
        args['gridObject']=self;
        if (self.notify!==null)
            self.notify('addRow',null,args);

  }
//-------------------------------------------------------------------------------
self.deleteRow=function() //  function deleteRow(gridObject)
  {
	 var selectedrows = self.grid.getSelectedRows();  
	 var len = selectedrows.length;
	 var sure= confirm("Are You Sure You Want to Delete?");   
	 if(sure){
	  for(var i=0;i<len;i++){                        
	   var data = self.grid.getData().getItem(selectedrows[i]);                         
	   self.dataView.deleteItem(data.id)                                                 
            var args=Array();
           args['rowId']=data.id;
           args['row']=data;
           args['gridObject']=self;

         if (self.notify!==null)
            self.notify('deleteRow',null,args);
	  }
	 }  
  }
//-------------------------------------------------------------------------------
self.TreeFormatter=function(row, cell, value, columnDef, dataContext) //function TreeFormatter(row, cell, value, columnDef, dataContext) 
{
  if ((typeof value) =='undefined')
     value='';
  if ((typeof value) !='string')
      value=value.toString();
  value = value.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
  var spacer = "<span style='display:inline-block;height:1px;width:" + (15 * dataContext["indent"]) + "px'></span>";
  var idx = self.dataView.getIdxById(dataContext.id);
  var row1=self.dataView.getItem(row);
  var row2=self.dataView.getItem(row+1);

    //if (gridObject1.data[idx + 1] && gridObject1.data[idx + 1].indent > gridObject1.data[idx].indent) {
  
        if (dataContext._collapsed) 
        {
          return spacer + " <span class='toggle expand'></span>&nbsp;" + value;
        } 
        else 
        {
          if (row2 && row2.indent > row1.indent) 
            {
              return spacer + " <span class='toggle collapse'></span>&nbsp;" + value;
            }
            else
            {
              return spacer + " <span class='toggle'></span>&nbsp;" + value;
            }
        }
}

//-------------------------------------------------------------------------------
 
self.treeFilter=function(item) //  function treeFilter(item) 
{

  if (item.parent != null) {
    var parent = self.dataView.getItemById(item.parent);

    while (parent) {
      if ( parent._collapsed ) {
        return false;
      }

      parent = self.dataView.getItemById(parent.parent);
    }
  }

  return true;
}

self.enableDragAndDrop= function()  
{
  var grid=self.grid;
  var moveRowsPlugin = new Slick.RowMoveManager({
    cancelEditOnDrag: true
  });

  moveRowsPlugin.onBeforeMoveRows.subscribe(function (e, data) {
    for (var i = 0; i < data.rows.length; i++) {
      // no point in moving before or after itself
      if (data.rows[i] == data.insertBefore || data.rows[i] == data.insertBefore - 1) {
        e.stopPropagation();
        return false;
      }
    }
    return true;
  });

  moveRowsPlugin.onMoveRows.subscribe(function (e, args) {
   var data=self.dataView.getItems();
    var extractedRows = [], left, right;
    var rows =args.rows;
    var insertBefore = args.insertBefore;
    
    left = data.slice(0, insertBefore);
    right = data.slice(insertBefore, data.length);

    rows.sort(function(a,b) { return a-b; });

    for (var i = 0; i < rows.length; i++) {
      extractedRows.push(data[rows[i]]);
    }

    if (self.options.sequence)
        {
        var startSeq;
        var endSeq;
        startSeq=data[insertBefore-1][self.options.sequence];
        endSeq=data[insertBefore][self.options.sequence];
        var delta =(endSeq-startSeq) / (rows.length+1);
            
        for (var i = 0; i < extractedRows.length; i++)
            {
              extractedRows[i][self.options.sequence]=startSeq+(delta*(i+1));
            }
        }

    rows.reverse();

    for (var i = 0; i < rows.length; i++) {
      var row = rows[i];
      if (row < insertBefore) {
        left.splice(row, 1);
      } else {
        right.splice(row - insertBefore, 1);
      }
    }

    data=left.concat(extractedRows.concat(right));
    
    self.dataView.setItems(data);

    var selectedRows = [];
    for (var i = 0; i < rows.length; i++)
      selectedRows.push(left.length + i);

    grid.resetActiveCell();
//    grid.setData(data);
    grid.setSelectedRows(selectedRows);
//    grid.invalidate();
    grid.render();
  });

  if (self.hasSequence)
    grid.registerPlugin(moveRowsPlugin);

  grid.onDragInit.subscribe(function (e, dd) {
    // prevent the grid from cancelling drag'n'drop by default
    e.stopImmediatePropagation();
  });

  grid.onDragStart.subscribe(function (e, dd) {
    var data=self.dataView.getItems();
    var cell = grid.getCellFromEvent(e);
    if (!cell) {
      return;
    }

    dd.row = cell.row;
    if (!data[dd.row]) {
      return;
    }

    if (Slick.GlobalEditorLock.isActive()) {
      return;
    }

    e.stopImmediatePropagation();
    dd.mode = "recycle";

    var selectedRows = grid.getSelectedRows();

    if (!selectedRows.length || $.inArray(dd.row, selectedRows) == -1) {
      selectedRows = [dd.row];
      grid.setSelectedRows(selectedRows);
    }

    dd.rows = selectedRows;
    dd.count = selectedRows.length;

    var proxy = $("<span></span>")
        .css({
          position: "absolute",
          display: "inline-block",
          padding: "4px 10px",
          background: "#e0e0e0",
          border: "1px solid gray",
          "z-index": 99999,
          "-moz-border-radius": "8px",
          "-moz-box-shadow": "2px 2px 6px silver"
        })
        .text("Drag to Recycle Bin to delete " + dd.count + " selected row(s)")
        .appendTo("body");

    dd.helper = proxy;

    $(dd.available).css("background", "pink");

    return proxy;
  });

  grid.onDrag.subscribe(function (e, dd) {
    if (dd.mode != "recycle") {
      return;
    }
    dd.helper.css({top: e.pageY + 5, left: e.pageX + 5});
  });

  grid.onDragEnd.subscribe(function (e, dd) {
    if (dd.mode != "recycle") {
      return;
    }
    dd.helper.remove();
    $(dd.available).css("background", "beige");
  });

//  $.drop({mode: "mouse"});
  var dropZone=self.options.dropZone;
  $("#dropzone")
      .bind("dropstart", function (e, dd) {
        if (dd.mode != "recycle") {
          return;
        }
        $(this).css("background", "yellow");
      })
      .bind("dropend", function (e, dd) {
        if (dd.mode != "recycle") {
          return;
        }
        $(dd.available).css("background", "pink");
      })
      .bind("drop", function (e, dd) {
        if (dd.mode != "recycle") {
          return;
        }
        var rowsToDelete = dd.rows.sort().reverse();
        for (var i = 0; i < rowsToDelete.length; i++) {
          data.splice(rowsToDelete[i], 1);
        }
        
        self.dataView.setItems(data);
        grid.invalidate();
        grid.setSelectedRows([]);
      });
}

    self.init();

//--------------------------------------------------------------------------------

}


//=================================================================================
function SelectCellEditor(args) {
        var $select;
        var defaultValue;
        var scope = this;

        this.init = function() {

            if(args.column.options){
              opt_values = args.column.options.split(',');
            }else{
              opt_values ="yes,no".split(',');
            }
            option_str = ""
            for( i in opt_values ){
              v = opt_values[i];
              option_str += "<OPTION value='"+v+"'>"+v+"</OPTION>";
            }
            $select = $("<SELECT tabIndex='0' class='editor-select'>"+ option_str +"</SELECT>");
            $select.appendTo(args.container);
            $select.focus();
        };

        this.destroy = function() {
            $select.remove();
        };

        this.focus = function() {
            $select.focus();
        };

        this.loadValue = function(item) {
            defaultValue = item[args.column.field];
            $select.val(defaultValue);
        };

        this.serializeValue = function() {
            if(args.column.options){
              return $select.val();
            }else{
              return ($select.val() == "yes");
            }
        };

        this.applyValue = function(item,state) {
            item[args.column.field] = state;
        };

        this.isValueChanged = function() {
            return ($select.val() != defaultValue);
        };

        this.validate = function() {
            return {
                valid: true,
                msg: null
            };
        };

        this.init();
    }
  function requiredFieldValidator(value) {
    if (value == null || value == undefined || !value.length) {
      return {valid: false, msg: "This is a required field"};
    } else {
      return {valid: true, msg: null};
    }
  }

function treeFilter(item,args) 
{
  var gridObject = args.gridObject;
  
  if (item.parent !== null) {
      debug("treeFilter: item.parent: "+item.parent);
    var parent = gridObject.dataView.getItemById(item.parent);
      debug("treeFilter: parent: "+parent);

    while (parent) {
      debug("treeFilter: parent "+parent.id+" collapsed:"+parent._collapsed);
      if ( parent._collapsed ) {
       debug("treeFilter: false"+item.id);
        return false;
      }

      debug("parent: "+parent.parent);
      parent = gridObject.dataView.getItemById(parent.parent);
    }
  }

  debug("treeFilter: true"+item.id+" p:"+item.parent);
  return true;
}
