<?php
namespace OmniFlow

{

class CaseView extends Views
{
public function ListCases($cases)
{
        $rows=array();
	foreach($cases as $case)
	{
            $row=array();

                $row['id']=$case['caseId'];
                $row['process']=$case['processName'];
                $row['status']=$case['caseStatus'];
                $row['created']=$case['created'];
                $row['updated']=$case['updated'];
                
		$link=Helper::getUrl(array('action'=>'case.view','caseId'=>$case['caseId'])); 
                $row['action']="View^".$link.'^_self';
            
            $rows[]=$row;
	}

        $cols=array();
        $titles=array();
        $cols[]='id,process,action,status,created,updated';
        $titles[]='CaseId,Process,Action,Status,Created,Updated';
        $types[]='ro,ro,link,ro,ro,ro';

        $this->displayGrid("casesGrid",$rows,$cols,$titles,$types);
        
        
}

function ShowCase($case,$imageFile,$showItems=true)
{
        $arr=$this->getCaseData($case,$showItems);
        $arr['itemsDescription']=$case->proc->Describe();        
        $json=$json=json_encode($arr);
	?>

<script>
        inDesignMode=false;
        inViewMode=true;
        var caseTitle='<?php echo $case->proc->processName;?>';
        var caseId=<?php echo $case->caseId; ?>;
	jQuery( document ).ready(function() {
            BuildCasePage();
            displayCaseData();
            main_layout.cells('a').setHeight(200);
            main_layout.cells('b').setHeight(300);
            jQuery('#diagramContents').parent().css('overflow-y','auto');            
	});		
        jsonData=<?php echo $json; ?>
    

</script>
	<div id="MainLayout" class='mainLayout'>
	<!-- js will embed layout here -->
	</div>
	<!-- Diagram here -->
	<div id='diagramContents'>
	<?php 
	$this->getDiagram($case);
	?>
	</div>	
	<!-- end of diagram -->
<?php
}
function getItemAction(WFCase\WFCaseItem $item)
{
    $case=$item->case;
    $fileName=$case->proc->processName;
    
        if ($item->status!= \OmniFlow\enum\StatusTypes::Completed && $item->status!= \OmniFlow\enum\StatusTypes::Terminated )
        {
                $taskId=$item->processNodeId;
                $task=$case->proc->getItemById($taskId);

                $link=Helper::getUrl(array('action'=>'task.execute','file'=>$fileName,'caseId'=>$case->caseId,'id'=>$item->id)); 
                
                if ($task!=null)
                {
                    $privileges=  WFCase\Assignment::getPrivileges($task, $item); 

                    if ($task->isTask())
                    {

                        if (in_array(WFCase\Assignment::Perform , $privileges))
                            $actionName="Launch $item->label";
                        elseif (in_array(WFCase\Assignment::View , $privileges)) {
                            $actionName="Launch $item->label";
                        }
                    }

                    if ($task->isEvent())
                    {
                        if (in_array(WFCase\Assignment::Perform , $privileges)) {
                            $actionName="Signal Event $item->label";
                        }
                    }
                }
                if ($actionName!=='')
                    return "$actionName^$link^_self";

        }
        else {
                return "";
            }

}
public function getCaseData($case,$showItems)
{
	if ($showItems)
	{
	$data=array();
        $data['case']=$case->__toArray();
        $items=array();
        $i=1;
        foreach($case->items as $item)
        {
            $arr=$item->__toArray();
            
            $assignTo=  WFCase\Assignment::getAssignmentForCaseItem($item);
            
            // add actions here
            // Stephen King^http://www.stephenking.com/the_author.html
            $action=$this->getItemAction($item);
            $arr['action']=$action;
            $arr['actor']=$assignTo;
            
            $arr['rowNo']=$i;
            $items[]=$arr;
            $i++;
        }
        
        $data['items']=$items;
        $values=array();
        $i=1;
        foreach($case->values as $k=>$v)
        {
            $values[]=array("id"=>$i++,"field"=>$k,"value"=>$v);
        }
        $data['data']=$values;
        return $data;
	}
}
public function getDiagram($case)
{
    	$decorations=Array();
	$i=0;
	foreach ($case->items as $item)
	{
		$i++;
		$pitem=$case->proc->getItemById($item->processNodeId);
		if ($item->status== \OmniFlow\enum\StatusTypes::Completed||$item->status== \OmniFlow\enum\StatusTypes::Terminated)
		{
				
			$decorations[]=array($pitem,$i,'black');
		}
		else
		{
				
			$decorations[]=array($pitem,$i,'red');
		}
	}
        
        echo "<div>";// style='overflow-y: scroll;height:400px'>";

        	SVGHandler::displayDiagram($case->proc,$decorations);

        echo '</div>';
        

}
}	// end of class

}	// end of namespace

?>