<?php
namespace OmniFlow

{

class DashboardView extends Views
{
    
public function Show($data)
{
	$actions['process.start']='Start';
    
        $this->listEvents($data['events'],$actions);
        $this->listTasks($data['tasks']);
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
                $title=str_replace(".bpmn","",$title);
                $row['title']=$title;
                $row['id']=$event['processName'];

                foreach($actions as $action=>$desc)
                {
		$link=Helper::getUrl(array('action'=>$action,'file'=>$event['processName'])); 
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
        "width:800px;min-height:100px;height=60%");
}

private function listTasks($drows)
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
                
            $rows[]=$row;
	}
//        print_r($rows);

        $cols=array();
        $titles=array();
        $cols[]='linkCase,linkExecute,userName,userGroup';
        $titles[]='CaseId,Title,User,User Group';
        $types[]='link,link,ro,ro';
        $widths[]='40,100,100,100';

        $this->displayGrid("tasksGrid",$rows,$cols,$titles,$types,$widths,
        "width:800px;min-height:300px;height=60%");
        
}

}	// end of class

}	// end of namespace

?>