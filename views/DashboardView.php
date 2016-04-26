<?php
namespace OmniFlow

{

class DashboardView extends Views
{
    
public function Show($data)
{
    ?>
    <div id='MainLayout' class='mainLayout'></div>
    <script>
	dxImgPath=omni_base_url+'/dhtmlx/codebase/imgs/';
	window.skin = "skyblue"; // for tree image_path
			dhtmlx.image_path=dxImgPath;

	main_layout = new dhtmlXLayoutObject('MainLayout', '3L');
//        main_layout.setAutoSize("a", "a;c");
	var tasksGrid = main_layout.cells('a');
        tasksGrid.setWidth(400);
	tasksGrid.setText('Tasks');
	tasksGrid.setCollapsedText('Tasks');
	
	var eventsGrid = main_layout.cells('b');
	eventsGrid.setText('Start New Process');
        eventsGrid.setHeight(400);

	var recentsGrid = main_layout.cells('c');
	recentsGrid.setText('Recent');

	jQuery( document ).ready(function() {

           main_layout.cells('b').setHeight(200);
            
	});
    </script>
<?php

	$actions['process.start']='Start';
    
        $this->listEvents($data['events'],$actions);
        $this->listTasks($data['tasks'],'tasksGrid');
        $this->listRecents($data['recents'],'recentsGrid');
        return;
    ?>
<table>
    <tr>
        <td>To-do</td>
        <td>List of Tasks that are pending for the current user	</td>
        <td>Select Task</td>
    </tr>
    <tr>
        <td>Notifications</td>
        <td>List of all notifications for current user</td>
        <td>Hide Notifications</td>
    </tr>
    <tr>
        <td>Recent</td>
        <td>List of Cases and Tasks that are recently involved the current user</td>
        <td></td>
    </tr>
    <tr>
        <td>Workload</td>
        <td>Summary of the Current Workload for the user scope</td>
        <td>Select Task</td>
    </tr>
</table>
	 
	 
	
	 
<?php
}       
public function listEvents($events,$actions)
    {
    $rows=array();

        foreach($events as $event)
        {
            $row=array();
                $title=$event['processName'];
                $row['title']=$title;
                $row['id']=$event['id'];

                foreach($actions as $action=>$desc)
                {
		$link=Helper::getUrl(array('action'=>$action,'processId'=>$event['processId'])); 
                $row[$action]=$desc.'^'.$link.'^_self';
                }
            $rows[]=$row;

        }
        $cols=array();
        $titles=array();
        $cols[]='title';
        $titles[]='Title';
        $types[]='ro';
        $widths[]='200';
            foreach($actions as $action=>$desc)
            {
            $cols[]=$action;
            $titles[]=$desc;
            $types[]='link';
            $widths[]='100';
            }
        $this->displayGrid("eventsGrid",$rows,$cols,$titles,$types,$widths,
                "width:800px;height:300px;",
                "eventsGrid");
}

private function listRecents($drows,$parent)
{
	$i=0;
        $rows=array();
	for ($i=0;$i<count($drows);$i++)
	{

		$row=$drows[$i];

		$id=$row['id'];
//		$pid=$row['processNodeId'];
		$cid=$row['caseId'];
		$label=$row['label'];
                
                if ($row['userName']=='')
                    $row['user']='Group:'.$row['userGroup'];
                else
                    $row['user']=$row['userName'];
                
                        
		$linkCase=Helper::getUrl(array('action'=>'case.view','caseId'=>$cid));

		$link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$cid,'id'=>$id));

                $row['linkCase']=$cid.'^'.$linkCase.'^_self';
                $row['linkExecute']=$label.'^'.$link.'^_self';
                
            $rows[]=$row;
	}
//        print_r($rows);

        $cols=array();
        $titles=array();
        $cols[]='linkCase,processName,label,user,status,recent';
        $titles[]='CaseId,Process,Title,User,Status,Recent';
        $types[]='link,ro,ro,ro,ro,ro';
        $widths[]='40,60,100,100,100,120';

        $this->displayGrid($parent,$rows,$cols,$titles,$types,$widths,
        "width:800px;min-height:300px;height=60%",
                $parent);
        
}
private function listTasks($drows,$parent)
{
	$i=0;
        $rows=array();
	for ($i=0;$i<count($drows);$i++)
	{

		$row=$drows[$i];

		$id=$row['id'];
//		$pid=$row['processNodeId'];
		$cid=$row['caseId'];
		$label=$row['label'];
                
		$linkCase=Helper::getUrl(array('action'=>'case.view','caseId'=>$cid));

		$link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$cid,'id'=>$id));

                $row['linkCase']=$cid.'^'.$linkCase.'^_self';
                $row['linkExecute']=$label.'^'.$link.'^_self';

                if ($row['userName']=='')
                    $row['user']='Group:'.$row['userGroup'];
                else
                    $row['user']=$row['userName'];                
                
            $rows[]=$row;
	}
//        print_r($rows);

        $cols=array();
        $titles=array();
        $cols[]='linkCase,processName,linkExecute,user,started,priority';
        $titles[]='CaseId,Process,Title,User,Started,Priority';
        $types[]='link,ro,link,ro,ro,ro';
        $widths[]='40,80,100,80,120,80';

        $this->displayGrid($parent,$rows,$cols,$titles,$types,$widths,
        "width:800px;min-height:300px;height=60%",
                $parent);
        
}

}	// end of class

}	// end of namespace

?>