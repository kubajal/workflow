<?php
namespace OmniFlow

{

class TaskView extends Views
{
    
    /*
     * *
     * 
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

     */
public function ListTasks($drows)
{
	$i=0;
        $rows=array();
	for ($i=0;$i<count($drows);$i++)
	{

		$row=$drows[$i];

		$id=$row['id'];
		$pid=$row['processNodeId'];
		$cid=$row['caseId'];
		$type=$row['type'];
		$label=$row['label'];
		$actor=$row['actor'];
                
		$linkCase=Helper::getUrl(array('action'=>'case.view','caseId'=>$cid));

		$link=Helper::getUrl(array('action'=>'task.execute','caseId'=>$cid,'id'=>$id));

                $row['linkCase']=$cid.'^'.$linkCase.'^_self';
                $row['linkExecute']=$label.'^'.$link.'^_self';
                
            $rows[]=$row;
	}
//        print_r($rows);

        $cols=array();
        $titles=array();
        $cols[]='linkCase,type,linkExecute,actor';
        $titles[]='CaseId,Type,Title,Actor';
        $types[]='link,ro,link,ro';

        $this->displayGrid("tasksGrid",$rows,$cols,$titles,$types);
        
}

}	// end of class

}	// end of namespace

?>