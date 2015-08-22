<?php

/*
 * Copyright (C) 2015 ralph
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmniFlow\BPMN;

use OmniFlow;
use OmniFlow\WFCase;

/**
 * Description of Flow
 *
 * @author ralph
 */
class Flow extends ProcessItem
{
	var $fromNode;
	var $toNode;
	
	var $fromNodeLabel;
	var $toNodeLabel;
	

	public function Init()
	{
		parent::Init();
	
		$this->fromNodeLabel=$this->fromNode->label;
		$this->toNodeLabel=$this->toNode->label;
		
	}
	
	public function isFlow()
	{
		return true;
	}
	
	function Trace()
	{
		parent::Trace();
		$this->toNode->Trace();
		
	}
	function loadFromXML($node)
	{
		parent::loadFromXML($node);
		$attr=$node->attributes();
		$src=$attr['sourceRef']->__ToString();
		$trg=$attr['targetRef']->__ToString();
		$from=$this->proc->getItemById($src);
		$to=$this->proc->getItemById($trg);
		
		if ($from==null)
		{
			OmniFlow\Context::Log(ERROR,'Missing a source node: Error in loading '.parent::describe().' Node id='.$src);
		}
		if ($to==null)
		{
			OmniFlow\Context::Log(ERROR,'Missing a target node: Error in loading '.parent::describe().' Node id='.$trg);
		}
		
		// get the condition
		
		$dblDash='//';
		
		$node->registerXPathNamespace('model', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
		
		foreach($node->xpath('model:conditionExpression') as $child) {
//			OmniFlow\Context::Log(INFO,'located a condition expresssion'.$child->__toString());
			$this->condition=$child->__toString();
		}	
		
		$this->fromNode = $from;
		$this->toNode = $to;
		$to->inflows[]=$this;
		$from->outflows[]=$this;
	}
	public function describe()
	{
		return parent::describe().":from ".Helper::ItemRef($this->fromNode). ' to '.Helper::ItemRef($this->toNode);
	}
	
	public function Execute(WFCase\WFCase $case,$input,$from)
	{
	
		OmniFlow\Context::Log(LOG,"**Flow::Executing: $this->type - $this->label - from: $from->label $this->id -input=$input" );
	
		$caseItem=$from;
                
                // check condition now
                
                if ($this->condition!=='' && $this->condition !=null )
                {
                    $ret=  \OmniFlow\ActionManager::ExecuteCondition($case,$this->condition);
                     if ($ret!=true) {
                         
        		OmniFlow\Context::Log(LOG,"Flow::condition is not true - skipping flow" );
                        return true;
                         
                     }
                         
                }
	
		if (!$this->Run($caseItem,$input,$from))
		{
			return false;
		}
		else
		{
//			if ($this->result!=null)
//				$input=$this->result;
		}
		$ret =$this->Finish($caseItem,$input,$from);
	
		return $caseItem;
	}
	
	public function Run(WFCase\WFCaseItem $caseItem,$input,WFCase\WFCaseItem $from)
	{
		OmniFlow\Context::Log(LOG,"Run Flow: $this->type - $this->label - input=$input");
                
                // Set Case Status here
                // a) Explicit from flow CaseStatus
                // b) Implicit for Events
                $caseStatus='';
                if ($this->caseStatus!=='')
                    $caseStatus=$this->caseStatus;
                else
                {
                    if ($from->isEvent())
                    {
                        $caseStatus=$from->label;
                    }
                }
                if ($caseStatus!='')
                {
                    $from->caseStatus=$caseStatus;
                }
		if ($this->type== \OmniFlow\enum\WFObjectTypes::messageFlow)
		{
			/*	1 locate an open item
			 */
                        OmniFlow\Context::Log(LOG,"Run Message Flow: $this->type - $this->label - input=$input");
			$targetItem=$caseItem->case->getItemByProcessId($this->toNode->id);
			
			if ($targetItem!=null)
			{
				OmniFlow\Context::Log(INFO, "message flow completing the target task ".$this->toNode->label);
				//$this->toNode->Complete($targetItem,null,$input,$this);
                                \OmniFlow\QueueEngine::addNodeToCase('Complete',
                                        array($this->toNode,$targetItem,null,null,$this));
				
			}
			else
			{
                            if ($this->toNode->getSubProcess()->isExecutable())
                            {
				OmniFlow\Context::Log(INFO, "message flow starting a new task ".$this->toNode->label);
				//return $this->toNode->Execute($caseItem->case,$this->label,$caseItem);
                                \OmniFlow\QueueEngine::addNodeToCase('Execute',array($this->toNode,$caseItem->case,null,$caseItem));
                            }
                            else
                            { 
				OmniFlow\Context::Log(INFO, "message flow is targeting an non-executable nodestarting a new task ".$this->toNode->label);
//                                $this->fromNode->IssueMessage();
                                \OmniFlow\QueueEngine::addNodeToCase('IssueMessage',
                                        array($this->fromNode,$from));

                            }
			}
		}
		else 
                {  //this code will move to the queue
                    //  return $this->toNode->Execute($caseItem->case,$input,$caseItem);
                    OmniFlow\Context::Log(INFO, "sequence flow starting a new task ".$this->toNode->label);
                    OmniFlow\QueueEngine::addNodeToCase('Execute',array($this->toNode,$caseItem->case,null, $caseItem));
                }
	}

}
