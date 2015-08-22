<?php
namespace OmniFlow
{

class ValidationRule
{
	var $title;
	var $className;
	var $type;
	var $condition;
	var $message;
        var $tab='';
	static $rules=array();
	public static function AddRule($title,$class,$type,$condition,$message)
	{
            $rule=new ValidationRule();
            $rule->title=$title;
            $rule->className=$class;
            $rule->type=$type;
            $rule->condition=$condition;
            $rule->message=$message;
            self::$rules[]=$rule;
		 
	}
	public static function getRules()
	{
		if (self::$rules==null)
			self::init();
			
		return self::$rules;
	}
	public static function ValidateProcess(BPMN\Process $process)
	{
		foreach(self::getRules() as $rule)
		{
                    if ($rule->className =='Process')
                    {
                        $rule->evaluate($process,null);
                    }

		}
		foreach($process->items as $item)
		{
                    $className=get_class($item);

                    foreach(self::getRules() as $rule)
                    {
                        $ruleClass=__NAMESPACE__.'\\'.$rule->className;

        		if (($ruleClass==$className) || ($rule->className ===''))
                        {
                            if (($rule->type==$item->type)||($rule->type===''))
                            {
                                $rule->evaluate($process,$item);
                            }
                        }

                    }

		}
	}
	public static function ValidateItem(ProcessItem $item)
	{
		return;
			$process=$item->proc;
			$className=get_class($item);

			foreach(self::getRules() as $rule)
			{
				$ruleClass=__NAMESPACE__.'\\'.$rule->className;
					
				if (($ruleClass==$className) || ($rule->className ===''))
				{
					if ($rule->type==$item->type)
						$rule->evaluate($process,$item,false);
				}

			}

	}

        
	public function evaluate($process,$processItem,$showItemLink=true)
	{
            Context::Log(INFO, "validation rule $this->title");
		$condition=$this->condition;
		if (!$condition($process,$processItem,$this))
		{
			if ($this->message=='')
				$msg=$this->title;
			else
				$msg=$this->message;
				
			if (($processItem!=null) && $showItemLink)
			{
                            
                                $link="javascript:validationError(\"$processItem->id\",\"$this->tab\")";
				$msg.=" for <a class='processItem' href='$link'>$processItem->label</a>";
			}
			Context::Log(VALIDATION_ERROR, $msg);
		}
			
	}
	public static function init()
	{
		ValidationRule::AddRule(
				"Must have at least one startEvent"
				,"Process", ""
				,function ($proc,$item) {

					$count=0;
					foreach($proc->items as $item)
					{
						if ($item->type=='startEvent')
							$count++;
					}
					if ($count==0)
						return false;
					else
						return true;
				}
		,"");

		ValidationRule::AddRule(
				"Must have at least one endEvent"
				,"Process", ""
				,function ($proc,$item) {
						
					$count=0;
					foreach($proc->items as $item)
					{
						if ($item->type=='endEvent')
							$count++;
					}
					if ($count==0)
						return false;
					else
						return true;
				}
		,"");


		ValidationRule::AddRule(
				"For Main Process, StartEvent can not have inflows"
				,"Event", "startEvent"
				,function ($proc,$item,$rule) {
                    
                        		$count=count($item->inflows);
					if ($count>0)
						return false;
					else
						return true;
				}
		,"");
			

		ValidationRule::AddRule(
				"For Main Process, EndEvent can not have outflows"
				,"Event", "endEvent"
				,function ($proc,$item) {
					$count=0;
					if (count($item->outflows)>0)
						return false;
					else
						return true;
				}
		,"");

		//
		ValidationRule::AddRule(
				"sequenceFlow that follow an XOR, OR gateway need to have a default or condition"
 				,"Flow", "sequenceFlow"
				,function (Process $proc,$item,$rule) {
				$from=$item->fromNode;
				$source=$proc->getItemById($from->id);
				$rule->message=$rule->title;
				if ($source->type=='exclusiveGateway')
 					{
                                            if ($source->defaultFlowId==$item->id)
						return true;
                                            if ($item->condition!='')
						return true;
                                            return false;
					}
				return true;	
				}
				,"");
					
		ValidationRule::AddRule(
				"Timer Expression is not valid"
 				,"", ""
				,function (Process $proc,$item,$rule) {
                                if ($item->subType==='timer')
                                {
                                    $dueDate=EventEngine::getDueDate($item);
                                    if ($dueDate==null)
                                        return false;
                                }
                                return true;	
				}
				,"");
					
					
				/*
				*
				*
				 Must have at least one startEvent
				  Must have at least one EndEvent
				 	
				 For Main Process, StartEvent can not have inflows
				 	
				 For Main Process, EndEvent can not have outflows
				 	
				 Attributes:
				 Timer need to have a type and expression
				 Message need to have a name and variables
				 Signal need to have a name and variables
				 	
				 sequenceFlow that follow an XOR, OR gateway need to have a default or condition
				 	
				 XOR/OR/AND gateway need to be either split or join but not both
				 split more than one outflow
				 join more than one inflow
				 	
				 EventBasedGateway can only outflow to events
				*
				*/
					
 	}
 }
/*
class Validation1 extends ValidationRule
{
  public function __construct() {
        $this->title="sequenceFlow that follow an XOR, OR gateway need to have a default or condition";
        $this->className="Flow";
        $this->type="sequenceFlow";
        $this->message=$this->title;
  }
  public function evaluate(Process $proc,$item)
  {
        $from=$item->fromNode;
        $source=$proc->getItemById($from->id);
        if ($source->type=='exclusiveGateway')
                {
                    if ($source->defaultFlowId==$item->id)
                        return true;
                    if ($item->condition!='')
                        return true;
                    return false;
                }
        return true;	
  }
} */
}
