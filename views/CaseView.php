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
                $row['name']=$case['processName'];
                
		$link=Helper::getUrl(array('action'=>'case.view','caseId'=>$case['caseId'])); 
                $row['action']="View^".$link.'^_self';
            
            $rows[]=$row;
	}

        $cols=array();
        $titles=array();
        $cols[]='id,name,action';
        $titles[]='CaseId,Title,Action';
        $types[]='ro,ro,link';

        $this->displayGrid("casesGrid",$rows,$cols,$titles,$types);
        
        
}

function ShowCase($case,$imageFile,$showItems=true)
{
        $json=$json=json_encode($this->getCaseData($case,$showItems));
	?>

<script>
	jQuery( document ).ready(function() {
	BuildCasePage();
        displayCaseData();
	});		
    caseJson=<?php echo $json; ?>

</script>
	<div id="MainLayout" style="position: relative; width: 100%; height: 800px;">
	<!-- js will embed layout here -->
	</div>
	<!-- Diagram here -->
	<div id='diagramContents'>
	<?php 
	$this->getDiagram($case);
	?>
	</div>	
	<!-- end of diagram -->
	<div id="proessItems">
		<table><tr><td width="25%">
			<div id="ItemsList">
			</div> <!-- end of Items list -->
		  </td>
		  <td>
				<div id="itemDetails">
				</div>
		  </td>
		  </tr></table>
	</div>

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

                $actionName="Launch $item->type";
                if ($task!=null)
                {
                if ($task->isTask())
                        $actionName="Launch $item->label";

                if ($task->isEvent())
                        $actionName="Signal Event $item->label";
                }

                $link=Helper::getUrl(array('action'=>'task.execute','file'=>$fileName,'caseId'=>$case->caseId,'id'=>$item->id)); 

                $msg="$actionName^$link^_self";

                return $msg;
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
            // add actions here
            // Stephen King^http://www.stephenking.com/the_author.html
            $action=$this->getItemAction($item);
            $arr['action']=$action;
            
            $arr['rowNo']=$i;
            $items[]=$arr;
            $i++;
        }
        
        $data['items']=$items;
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
	SVGHandler::displayDiagram($case->proc,$decorations);

}
}	// end of class

}	// end of namespace

?>