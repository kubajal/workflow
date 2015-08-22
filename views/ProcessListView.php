<?php
namespace OmniFlow

{

class ProcessListView extends Views
{
    public function ProcessList($list,$actions)
    {
    $rows=array();
        foreach($list as $file)
        {
            $row=array();
                $title=str_replace(".bpmn","",$file);
                $row['title']=$title;
                $row['id']=$file;

                foreach($actions as $action=>$desc)
                {
		$link=Helper::getUrl(array('action'=>$action,'file'=>$file)); 
                $row[$action]=$desc.'^'.$link.'^_self';
                }
            $rows[]=$row;

        }
        $cols=array();
        $titles=array();
        $cols[]='title';
        $titles[]='Title';
        $types[]='ro';
            foreach($actions as $action=>$desc)
            {
            $cols[]=$action;
            $titles[]=$desc;
            $types[]='link';
            }
        $this->displayGrid("processGrid",$rows,$cols,$titles,$types);
}
public function ListProcesses($list,$actions)
{
        $rows=array();
	foreach($list as $row)
	{
//		$procId=$row['processId'];
		$name=$row['processName'];
		$file=$name;
		$title=str_replace(".bpmn","",$file);
                $row['title']=$title;
                $row['id']=$row['id'];
		
                foreach($actions as $action=>$desc)
                {
		$link=Helper::getUrl(array('action'=>$action,'file'=>$file)); 
                $row[$action]=$desc.'^'.$link.'^_self';
                }
            $rows[]=$row;

	}
        $cols=array();
        $titles=array();
        $cols[]='title';
        $titles[]='Title';
        $types[]='ro';
            foreach($actions as $action=>$desc)
            {
            $cols[]=$action;
            $titles[]=$desc;
            $types[]='link';
            }
        $this->displayGrid("processGrid",$rows,$cols,$titles,$types);
}

}	// end of class

}	// end of namespace
?>