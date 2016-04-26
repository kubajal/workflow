<?php
namespace OmniFlow

{

class Views
{
    
        public static function header($menus=true,$modeler=false,$localMenus=array(),$maximize=true)
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
        Helper::BasicJS();
?>
        <!--- Views::header -->
        <div id="omni_page">
            <div id="omni_header">
        
<?php
        if ($menus)
        {
	MenusView::displayMenus($localMenus,$maximize);
        }
?>
                <!-- end of omni_header -->
            </div> 
<?php
}
    public static function endPage()
    {
    ?>
                
            <!-- end of omni_contents -->
      <div id="omni_footer">
          <hr /></div>
            
<!-- end of omni_page -->
</div>
<?php
    return;
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

		$link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$cid,'id'=>$id));

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
                $link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$event['caseId'],'id'=>$event['id'])); 
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
		
		$link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$cid,'id'=>$id)); 
		
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
    
   function displayGrid($gridname,$data,$cols,$titles,$types,$widths=null,$gridStyle=null,$parent=null)
    {
           $json=json_encode($data);
           
           if ($gridStyle===null)
           {
               $gridStyle="width:800px;min-height:400px;height=60%";
           }

           $headers=join(',',$titles);
           $colIds=join(',',$cols);
           $colTypes=join(',',$types);
           if ($widths!=null)
           $widthList=join(',',$widths);
           $gridnamejson=$gridname.'json';
           
        if ($parent===null) 
        {
            ?>
            <div id='MainLayout' class='mainLayout'></div>
            <script>
            dxImgPath=omni_base_url+'/dhtmlx/codebase/imgs/';
            window.skin = "skyblue"; // for tree image_path
                            dhtmlx.image_path=dxImgPath;

            main_layout = new dhtmlXLayoutObject('MainLayout', '1C');
    //        main_layout.setAutoSize("a", "a;b");
            var grid = main_layout.cells('a');
            grid.setText("<?php echo $gridname; ?>");
            grid.hideHeader();
    //        processGrid.setWidth(2000);

            <?php
            $parent='grid';
        } else {
            echo "<script>";
        }
        echo "
           $gridnamejson=$json;

           var $gridname = $parent.attachGrid();
     
        
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

           var firstRow=populateGrid($gridname,$gridnamejson);
            
	});		
</script>
           

        ";
    
    }
	
}	// end of class

}	// end of namespace
?>