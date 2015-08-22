<?php
namespace OmniFlow
{


/*
 * defines public interface to
 */
Interface iProcessItemExecutable
{
	function willWait();
	function checkWait();
	function getExecutingOutflows();
	function do_start();
	function do_execute();
	function do_complete();
	
}
/*
	Class responsibilities:
	
		all execute runtime functions
		
		managing interface with CaseItem
		
		dataElement
		
		notification
		
		
*/
class ProcesssExecutor
{
	/* can be called by API and custom code to force a start of step
	 *
	 */
	private $process;
	private $processItem;
	private $case;
	private $caseItem;
	//	======================= pulbic functions are all static ==============================

	public static function StartProcess(Process $process)
	{
		// to do create new case

		$startItem = $process->getStartNode();
		self::RunProcessItem($startItem, $newCase);

	}
	public static function RunProcessItem(ProcessItem $item,WFCase\WFCase $case)
	{
		$exec=new self();
		$exec->process=$item->proc;
		$exec->case=$case;
		$exec->initialize();

	}
	public static function ContinueProcessItem(WFCase\WFCaseItem $caseItem,$data,StatusTypes $newStatus)
	{

	}
	public static function TerminateProcessItem(WFCase\WFCaseItem $caseItem,$data)
	{

	}
	public static function CancelCase(WFCase\WFCase $case,string $reason)
	{

	}

	// ------------------------------------------------------------

	private function initialize()
	{

		$this->start();
	}

	private function start()
	{
		// set the caseItem
		$caseItem= WFCase\WFCase::createItemHandler($case,$this->proc, $this);
			
		Context::Log(LOG,"**ProcessIterm Executing: $this->type - $this->label - $this->id -input=$input" );

		$this->processItem->do_start();
		
		
		if ($this->processItem->willWait())
		{
			return;
		}
		else
		{
			$this->execute();
		}
	}

	private function resume()
	{


		if ($this->processItem->checkWait())
		{
			return;
		}
		else
		{
			$this->execute();
		}

	}
	private function execute()
	{
		$this->processItem->do_execute();
		
		$this->complete();
	}

	private function complete()
	{
		$this->processItem->do_complete();

		$outs=$this->processItem->getExecutingOutflows();
		foreach($outs as $flow)
		{
			self::RunProcessItem($flow, $case);
		}
	}
	private function notify()
	{
	}
	private function setStatus()
	{
	}
}
class ActionManager
{
	public static function ExecuteAction($_script,WFCase\WFCaseItem $_caseItem)
	{
		$_ret=null;
                $_case=$_caseItem->case;
                
		Context::Log(INFO,"ExecutingCondition script:'.$_script");
		
		foreach($_case->values as $_var=>$_value)
		{
			$$_var=$_value;
			Context::Log(INFO,'variable:'.$$_var.'='.$_value);
		}
	
		try
		{
                    $_ret=eval($_script);
                        
                    foreach($_case->values as $_var=>$_value)
                    {
                            $_case->values[$_var]=$$_var;
                            Context::Log(INFO,'variable:'.$_var.'='.$$_var);
                    }
                        
		}
		catch(Execption $_exc)
		{
			Context::Log(ERROR,$_exc->message);
		}
		if ($_ret!=null)
		{
			Context::Log(INFO,'var_export _ret:'.var_export($_ret,true));
		}
		Context::Log(INFO,'Condition Ret'.$_ret.' is true '.($_ret==true));
		if ($_ret==true)
			return true;
		else
			return false;
	//	return $_ret;
		
	}

	public static function ExecuteCondition(WFCase\WFCase $_case,$_script)
	{
		$_ret=null;
		Context::Log(INFO,"ExecutingCondition script:'.$_script");
		
		try
		{
                    $eng=ScriptEngine::Evaluate($_script, $_case);
                    $_ret=$eng->result;
                    
//			$_ret=eval($_script);
		}
		catch(\Execption $_exc)
		{
			Context::Log(ERROR,$_exc->message);
		}

		if ($_ret==true)
                {
        		Context::Log(INFO,'Condition is TRUE'.$_ret );
			return true;
                    
                }
		else
                {
        		Context::Log(INFO,'Condition is FALSE'.$_ret );
			return false;
                }
	}
	public static function saveForm($post)
	{
		Context::Log(INFO,' saveForm: '.print_r($post,true));
		$caseId=$post['_caseId'];
		$id=$post['_itemId'];
		$status=\OmniFlow\enum\StatusTypes::Started;
		if (isset($post['_complete']))
			{
			$action=$post['_complete'];
			$status=\OmniFlow\enum\StatusTypes::Completed;
			}
		$item=CaseSvc::LoadCaseItem($caseId, $id);
		$case=$item->case;
		$task=$item->getProcessItem();
		
		TaskSvc::SetStatus($caseId, $id, $status, $post);
		
		/*
		foreach($post as $var=>$val)
		{
			if (($var=='_caseId') || ($var=='_itemId'))
			{
				
			}
			else
			{
				foreach($task->variables as $v)
				{
					if ($v->field==$var)
					{
						echo 'value for '.$var. ' ='.$val;
						$case->setValue($v->name,$val);
					}
				}
			}
			
		} */
                
                return $case;
	}
	public static function defaultForm(WFCase\WFCaseItem $item)
	{
            FormView::defaultForm($item);
	}	
	public static function getActionView(WFCase\WFCaseItem $caseItem,$postForm)
	{
		
		$task=$caseItem->getProcessItem();
                if ($task==null)
                {
                    Context::Error("Case Item is not consistent with process. can not locate task $caseItem->processNodeId");
                    return false;
                }

		Context::Log(INFO,'getActionView'.$task->name.' type:'.$task->actionType." postForm $postForm");
                
		if ($postForm==true)
			return "";
		
		Context::Log(INFO,'Action View action type:'.$task->actionType);
		if ($task->actionType=='Form')
		{
			$formParams=explode(";",$task->actionScript);
			if (count($formParams)>1)
			{
				$formType=$formParams[0];
				$formId=$formParams[1];
		
				if ($formType=="nf")
				{
					if ( function_exists( 'ninja_forms_display_form' ) )
					{
						include_once ("NinjaForm.php");
						$form=NinjaForms::displayForm($formId);
						return true;
					}
					else
					{
						return "Ninja Form goes here".$formType.' '.$formId;
					}
				}
		
			}
			else	// default form 
			{
                		Context::Log(INFO,'defaultForm');
				return "defaultForm";
			}
		}
                else
                {
		Context::Log(ERROR,'No Action type specified:'.$task->actionType);
                }
	} 
}

}
