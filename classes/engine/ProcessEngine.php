<?php
namespace OmniFlow
{


class ActionManager
{
	public static function ExecuteAction($_script,WFCase\WFCaseItem $_caseItem)
	{
		$_ret=null;
                $_case=$_caseItem->case;
                
		Context::Log(INFO,"Executing Action script:'.$_script");
		
	
		try
		{
                    $eng=ScriptEngine::Evaluate($_script, $_case,$_caseItem);
                    $_ret=$eng->result;
                    foreach($eng->vars as $k=>$v)
                    {
                        if (isset($_case->values[$k])) {
                            $_case->values[$k]=$v;
                        }
                        
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
		$item=CaseSvc::LoadCaseItem($caseId, $id);
                
                if ($item->status===\OmniFlow\enum\StatusTypes::Completed)
                {
                  throw new \Exception("Task is already Completed.");
                }
		if (isset($post['_complete']))
			{
	   		$newStatus= enum\StatusTypes::Completed;
			}
                else {
	   		$newStatus= enum\StatusTypes::Updated;
                }
                    
                TaskSvc::SaveData($item, $post,$newStatus);
	
                return $case;
	}
	public static function defaultForm(WFCase\WFCaseItem $item,$edit)
	{
            FormView::defaultForm($item,$edit);
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
		//Context::Log(ERROR,'No Action type specified:'.$task->actionType);
                }
	} 
}

}
