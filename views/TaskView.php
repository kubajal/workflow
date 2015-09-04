<?php
namespace OmniFlow

{

class TaskView extends Views
{
    
public function ListTasks($drows)
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

        $this->displayGrid("tasksGrid",$rows,$cols,$titles,$types);
        
}

}	// end of class

}	// end of namespace

?>