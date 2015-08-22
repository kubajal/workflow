<?php
namespace OmniFlow

{

class Views
{
    
        public static function header($menus=true,$modeler=false,$localMenus=array())
{
	
/*	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); 

<link rel="stylesheet" href="css\workflow.css" type="text/css">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/workflow.js"></script>
<link rel="stylesheet" href="lib/jquery-ui/jquery-ui.css">
<link rel="stylesheet" href="lib/jquery-ui/jquery-ui.theme.css">
<script src="lib/jquery-ui/jquery-ui.min.js"></script>
	
	*/
	Helper::HeaderInclude('',$modeler);
        self::startPage();
        if ($menus)
        {
	MenusView::displayMenus($localMenus);
        }
}
    
    public static function startPage()
    {?>
        <div id="omni_page">
            <div id="omni_contents">
                </di</div>
        
<?php
    }
    public static function endPage()
    {
        return;
    ?>
            <!-- end of omni_contents -->
    </div>    
<!-- end of omni_page -->
</div>
<?php
 
    }
/*	public function ProcessList($list,$actions)
		{
			echo '<table>';
			foreach ($list as $file)
			{
				$title=str_replace(".bpmn","",$file);
				echo "<tr><td>$title</td>";
				foreach($actions as $action=>$desc)
				{
				$link=Helper::getUrl(array('action'=>$action,'file'=>$file)); 
				echo "<td><a href='$link'>$desc</a></td>";
				}
				
				
			}
			echo '</tr></table>';

		}
public function displayProcessItems($proc)
{
?>
	<div id="ItemsList">
		
	<select id="processItems" style="width:350px;" size="15">
<?php
		//$proc->Describe();
		foreach ($proc->items as $item)
		{
			$msg=$item->describe();
			$msg="<option value='$item->id'>$msg</option>";
			//$msg='<option><table><tr><td>'.str_replace(":","</td><td>",$msg).'</tr></table></option';
			echo $msg;
		}
?>
	</select>
	</div> <!-- end of Items list -->
<?php
	
}
public function DescribeProcess(Process $proc,$file)
{
	
	$imageFile = 'processes/'.str_replace(".bpmn", ".svg",$file);
	echo "<div id='diagram' class='ui-widget-content'>";
	SVGHandler::displayDiagram($proc,array());

?>
	</div>	</div>
						
	<div id="workArea" class="ui-widget-content">
	<div id="tabs">
	<ul>
	<li><a href="#Items">Items</a></li>
	<li><a href="#tabs-3">Process Details</a></li>
	</ul>
	<div id="items">
	<table><tr><td width="25%">
		<?php $this->displayProcessItems($proc); ?>		
		</td><td valign="top">

		<div id="itemDetails">
		<p>Please select an item from the list on the left or diagram above to view details.
		</div>
	  </td></tr></table>
	</div> <!-- end of items -->
	<div id="tabs-3">
	<?php $this->processDetails($proc); ?>

	</div>
	</div>
	
	</div></div>
<?php

}
public function processDetails($proc)
{
	?>
	<script>
	jQuery(function() {
		jQuery( "#ptabs" ).tabs();
	});
	</script>
	 
	<div id="ptabs">
		<ul>
			<li><a href="#ptabs-1">General</a></li>
			<li><a href="#ptabs-2">Roles</a></li>
			<li><a href="#ptabs-3">Data Model</a></li>
		</ul>
		<div id='ptabs-1'></div>
		<div id='ptabs-2'></div>
		<div id='ptabs-3'>
		<?php $this->viewProcessData($proc); ?>
		</div>
	</div>	
<?php
	
}
public function displayDataElement($de,$level)
{
	echo "<li>$de->name</li>";
	if (count($de->children) ==0)
		return;
	echo '<ul>';
	foreach($de->children as $child)
	{
		$this->displayDataElement($child, $level+1);
	}
	echo '</ul>';
	
}

public function viewProcessData($proc)
{
	$data=DataManager::createDataObject($proc);
	echo '<ul>';
	
	foreach ($proc->dataElements as $de)
	{
		if ($de->parent==null)
		{
			$this->displayDataElement($de,0);
		}
	}	
	echo '</ul>';
//	var_dump($data);
}
public function ListProcesses($list)
{
	echo "<div><table>";
	foreach($list as $row)
	{
		$procId=$row['processId'];
		$name=$row['processName'];
		$file=$name;
		$title=str_replace(".bpmn","",$file);
		
		$link1=Helper::getUrl(array('action'=>'startProc','file'=>$file)); 
		$link2=Helper::getUrl(array('action'=>'describeProc','file'=>$file)); 
		$link3=Helper::getUrl(array('action'=>'unregisterProcess','file'=>$file));

		echo "<tr><td>$title</td>";
		echo "<td><a href='$link1'>Start</a></td>";
		echo "<td><a href='$link2'>Describe</a></td>";
		echo "<td><a href='$link3'>UnRegister</a></td>";
		
		
		echo "</tr>";
	}
	echo "</table></div>";
}
*/
function listTasks($rows)
{

	echo "<div>
			<table>";
	$i=0;
	for ($i=0;$i<count($rows);$i++)
	{

		$row=$rows[$i];

		$id=$row['id'];
		$pid=$row['processNodeId'];
		$cid=$row['caseId'];
		$type=$row['type'];
		$label=$row['label'];
		$actor=$row['actor'];

		$linkCase=Helper::getUrl(array('action'=>'show','caseId'=>$cid));

		$link=Helper::getUrl(array('action'=>'executeActivity','caseId'=>$cid,'id'=>$id));

		$line= "<tr>
		<td><a href='$linkCase'>$cid</a></td>
		<td>$type</td>
		<td><a href='$link'>$label</a></td><td>$actor</td>
		</tr>";
		echo $line;


	}
	echo "</table></div>";
}
function listMessages($rows)
{

	echo "<div>
			<table>";
	$i=0;
	for ($i=0;$i<count($rows);$i++)
	{

		$row=$rows[$i];

		$id=$row['id'];
		$pid=$row['processNodeId'];
		$cid=$row['caseId'];
		$type=$row['type'];
		$label=$row['label'];
		$actor=$row['actor'];

		$linkCase=Helper::getUrl(array('action'=>'show','caseId'=>$cid));

		$link=Helper::getUrl(array('action'=>'executeActivity','caseId'=>$cid,'id'=>$id));

		$line= "<tr>
		<td><a href='$linkCase'>$cid</a></td>
		<td>$type</td>
		<td><a href='$link'>$label</a></td><td>$actor</td>
		</tr>";
		echo $line;


	}
	echo "</table></div>";
}


function listEvents($events)
{

        $rows=array();
	foreach($events as $event)
	{
            $row=array();

                $row['id']=$event['id'];

                if ($event['source']=='Case Item')
                {
                    $row['caseId']=$event['caseId'];
                }
                else
                {
                    $row['caseId']=$event['processName'];
                    
                }
                $row['type']=$event['type'];
                $row['subType']=$event['subType'];
                $row['label']=$event['label'];
                
                if ($event['subType']=='timer')
                    $row['details']=$event['timer'].'due:'.$event['timerDue'];;
                if ($event['subType']=='message')
                    $row['details']=$event['message'];
                
                if (isset($event['caseId']))
                {
                $link=Helper::getUrl(array('action'=>'executeActivity','caseId'=>$event['caseId'],'id'=>$event['id'])); 
                $row['action']="View^".$link.'^_self';
                }
                
            $rows[]=$row;
	}


        $cols=array();
        $titles=array();
        $cols[]='caseId,type,label,subType,details,action';
        $titles[]='CaseId,Type,Label,Timer/Message/Signal,detail,Action';
        $types[]='ro,ro,ro,ro,ro,link';

        $this->displayGrid("EventsGrid",$rows,$cols,$titles,$types);
        
        return;
    
    
	echo "<div>
			<table>";
	$i=0;
	for ($i=0;$i<count($rows);$i++)
	{

		$row=$rows[$i];

		$linkCase=Helper::getUrl(array('action'=>'show','caseId'=>$cid));
		
		$link=Helper::getUrl(array('action'=>'executeActivity','caseId'=>$cid,'id'=>$id)); 
		
		$line= "<tr>
		<td><a href='$linkCase'>$cid</a></td>
		<td>$type</td>
		<td>$timer</td>
		<td>$timerDue</td>
		</tr>";
		echo $line;


	}
	echo "</table></div>";
}


public function executeActivity($case,$imageFile,$id,$actionView="")
{
        $v=new CaseView();
        
	$v->showCase($case,$imageFile,false);
	
	$proc=$case->proc;
	$item = $case->getItem($id);
	
	$taskId = $item->processNodeId;
	
	$task = $proc->getItemById($taskId);	
	$fileName=$case->proc->processName;
	
	echo "<table>
	<tr><td> </td>
	<td>$item->type</td>
	<td>$item->label</td>
	<td>$item->actor</td>
	<td>$item->result</td>
	<td>$item->status</td>
	<td>$item->started</td>
	<td>$item->completed</td></tr><table>";
	
	
	if ($item->status!= \OmniFlow\enum\StatusTypes::Completed && $item->status!= \OmniFlow\enum\StatusTypes::Terminated )
	{
	
		if ($actionView=="defaultForm")
		{
			ActionManager::defaultForm($item);
		}
		if ($actionView=="")
		{
			echo "<table><tr>";
			$actionName="Launch";
			if ($task!=null)
			{
			
			if ($task->isTask())
				$actionName="Complete the Task '$item->label'";
				
			if ($task->isEvent())
				$actionName="Signal the Event";
			}
		
			$msg="";
			if ($task->type=='userTask'||$task->type=='task')
			{
				if (count($task->validOutput)>0)
				{
					foreach($task->validOutput as $val)
					{
						$link=Helper::getUrl(array('action'=>'completeActivity','file'=>$fileName,'caseId'=>$case->caseId,'id'=>$item->id,'value'=>$val)); 
	
						$msg.="<td><a href='$link'>$val</a></td>";
					}
				}
			}
			
			if ($msg=="")
			{
				$link=Helper::getUrl(array('action'=>'completeActivity','file'=>$fileName,'caseId'=>$case->caseId,'id'=>$item->id)); 
	
				$msg="
				<td><a href='$link'>$actionName</td>";
			}
		
			$msg.="</tr></table>";
			
			echo $msg;
		}
		else
			echo $actionView;
			
	}
	
		
}

public function ShowHelp()
{
	?>
	<br /><a href="help/formal-11-01-03.pdf">BPMN 2.0 Specs</a>
	
	<br /><a href="https://www.bizagi.com/docs/BPMNbyExampleENG.pdf">BPMN 2.0 by example</a>
	
	<br /><a href="http://camunda.org/bpmn/examples/">BPMN 2.0 by examples</a>
	
	<br /><a href="help/10-06-02.pdf">BPMN 2.0 by examples</a>
	
	<br /><a href="http://docs.camunda.org/7.2/api-references/bpmn20/">BPMN 2.0 Implementation Reference</a>
	<?php 
	
}
    function sampleGrid($dataRows)
    {
        $cols=array();
        $cols[]=array("id"=>'name',"property"=>'name',"title"=>'Name',"type"=>"ro","width"=>100,"values"=>'a,b,c');
        $cols[]=array("id"=>'name',"property"=>'name',"title"=>'Name',"type"=>"link","width"=>100,
                "action"=>array('task.view','View'),
                "actionParams"=>array(array("caseId","caseId"),
                                      array("taskId","itemId")));
    }
    function displayGrid2($gridname,$data,$cols)
    {
           $json=json_encode($data);
           $headers=array();
           $colIds=array();
           $colTypes=array();
           $widths=array();
           foreach($cols as $col)
           {
               $headers[]=$col['title'];
               $colIds[]=$col['id'];
               $colTypes[]=$col['type'];
               $widths[]=$col['width'];
           }
           $rows=array();
           foreach($data as $drow)
           {
            $row=array();
            foreach($cols as $col)
            {
                if (isset($col['action']))
                {
                    $action=$col['action'][0];
                    $actionDesc=$col['action'][1];
                    $actionParams=$col['actionParams'];
                    $parms=array();
                    $parms['action']=$action;
                    foreach($actionParams as $param)
                    {
                        $parms[$param[0]]=$drow[$param[1]];
                    }
                    $link=Helper::getUrl($parms); 
                    $row[$col['id']]=$actionDesc.'^'.$link.'^_self';
                }
                else
                {
                    $pname=$col['property'];
                    $row[$col['id']]=$data[$pname];
                }
            }
            $rows[]=$row;
           }
           $json=json_encode($rows);
           
   echo 
        "
        <div id='$gridname' style='width:800px;min-height:400px;height=60%'>
        </div>
<script>
       json=$json;
        var $gridname = new dhtmlXGridObject('$gridname');
        $gridname.setIconsPath(dxImgPath);
        
	$gridname.setHeader('$headers');
	$gridname.setColTypes('$colTypes');
	$gridname.setColumnIds('$colIds');
        ";
        if ($widths!=null)
        {
        echo "  $gridname.setInitWidths('$widthList');";
        }
        echo "
        
	$gridname.init();

	jQuery( document ).ready(function() {

           var firstRow=populateGrid($gridname,json);
            
	});		
</script>
           

        ";
    
    }
    
   function displayGrid($gridname,$data,$cols,$titles,$types,$widths=null)
    {
           $json=json_encode($data);
           $headers=join(',',$titles);
           $colIds=join(',',$cols);
           $colTypes=join(',',$types);
           if ($widths!=null)
           $widthList=join(',',$widths);
           
   echo 
        "
        <div id='$gridname' style='width:800px;min-height:400px;height=60%'>
        </div>
<script>
       json=$json;
//		main_layout = new dhtmlXLayoutObject('$gridname', '2U');
//		var diagram = main_layout.cells('a');
	    var $gridname = new dhtmlXGridObject('$gridname');
//		var $gridname = diagram.attachGrid();
        $gridname.setIconsPath(dxImgPath);
        
	$gridname.setHeader('$headers');
	$gridname.setColTypes('$colTypes');
	$gridname.setColumnIds('$colIds');
        ";
        if ($widths!=null)
        {
        echo "  $gridname.setInitWidths('$widthList');";
        }
        echo "
        
	$gridname.init();

	jQuery( document ).ready(function() {

           var firstRow=populateGrid($gridname,json);
            
	});		
</script>
           

        ";
    
    }
	
}	// end of class

}	// end of namespace
?>