<?php

/*
 * Copyright (c) 2015, Omni-Workflow - Omnibuilder.com by OmniSphere Information Systems. All rights reserved. For licensing, see LICENSE.md or http://workflow.omnibuilder.com/license
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
 * Description of Event
 *
 * @author ralph
 */
class Event extends Node
{
    
	public function isEvent()
	{
		return true;
	}
        
        

	
	function loadFromXML($node)
	{
		parent::loadFromXML($node);
		
		// get timerEvent
		// todo: message info
		
		$dblDash='//';
		
		$node->registerXPathNamespace('model', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

		foreach($node->xpath('model:timerEventDefinition') as $child) {

			//echo '<br /><hr />'.$child->getName();
			$this->hasTimer = true;
			$this->subType=WFSubTypes::TIMER_TYPE;
			foreach(OmniFlow\XMLLoader::children($child) as $grand) {
				//var_dump($grand);
				//echo '<br />timer child:'.$grand->getName();
				if ($grand->getName()=='timeCycle')
				{
					$this->timer=$grand->__ToString();
					$this->timerType='timeCycle';
				}
				if ($grand->getName()=='timeDate')
				{
					$this->timer=$grand->__ToString();
					$this->timerType='timeDate';
				}
				if ($grand->getName()=='timeDuration')
				{
					$this->timer=$grand->__ToString();
					$this->timerType='timeDuration';
				}
			}
				
		}
		foreach($node->xpath('model:messageEventDefinition') as $child) {
			//echo '<br /><hr />'.$child->getName();
			$this->subType= WFSubTypes::MESSAGE_TYPE;
			$this->hasMessage=true;
			$msgName="";
			$msgId=OmniFlow\XMLLoader::getAttribute($node, 'messageRef');
			if ($msgId!=null)
			{
				foreach($this->proc->messages as $id=>$name)
				{
					if ($id==$msgId)
						$this->message=$name;
				}
			
			}
		}
		foreach($node->xpath('model:signalEventDefinition') as $child) {
			//echo '<br /><hr />'.$child->getName();
			$this->subType= WFSubTypes::SIGNAL_TYPE;
			$this->hasSignal=true;
		}

                foreach(\OmniFlow\XMLLoader::getChildren($node) as $child)
                {
                    $name=$child->getName();
                    switch($name)
                    {
                    case 'terminateEventDefinition':
                        $this->subType= WFSubTypes::TERMINATION_TYPE;
                        break;
                    case 'terminateErrorDefinition':
			$this->subType= WFSubTypes::ERROR_TYPE;
                        break;
                    case 'errorEventDefinition':
			$this->subType= WFSubTypes::ERROR_TYPE;
                        break;
                    
                    case 'compensateEventDefinition':
			$this->subType= WFSubTypes::COMPENSATE_TYPE;
                        break;
                    case 'conditionalEventDefinition':
			$this->subType= WFSubTypes::CONDITIONAL_TYPE;
                        break;
                    case 'escalationEventDefinition':
			$this->subType= WFSubTypes::ESCALATION_TYPE;
                        break;
                    case 'linkEventDefinition':
			$this->subType= WFSubTypes::LINK_TYPE;
                        break;
                    case 'cancelEventDefinition':
			$this->subType= WFSubTypes::CANCEL_TYPE;
                        break;
                        
                    }
		}
	}
		
	public function describe(\OmniFlow\Describer $t)
	{
            $t->checkSubItem($this);
            switch($this->type)
            {
                case 'startEvent':
                    $t->title="Start Event";
                    $t->desc="A starting point of the proces.";
                    $t->designOptions=array(\OmniFlow\KW::logic,\OmniFlow\KW::acl);
                    $t->modelOptions=array(\OmniFlow\KW::timer,\OmniFlow\KW::message,\OmniFlow\KW::signal);
                    break;
                case 'endEvent':
                    $t->title="End Event";
                    $t->desc="It indicates where the process will end. ";
                    $t->designOptions=array(\OmniFlow\KW::logic);
                    $t->modelOptions=array("Terminate Event: Will terminate all running activities"); 
                    break;
                case 'intermediateCatchEvent':
                    $t->title="Intermediate Event";
                    break;
                default:
                    $t->title="Intermediate Event";
                    break;
            }
            
	}
	
	/*
	 * initialize the event 
	 */
	
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
		if ($this->hasTimer && $this->type !='startEvent')
		{
			// wait for the timer
			return true;
		}
                
                // todo: following should check for Terminate
                /* not valid login remove
  		if ($this->type=="endEvent")
		{   // check that all nodes are complete, otherwise wait
                    $pool=$this->pool;
                    $proc=$this->proc;
                    $case=$caseItem->case;
                    
                    foreach ($case->items as $citem)
                    {
                            $pItem=$proc->getItemById($citem->processNodeId);

                            if ( $citem->status!=\OmniFlow\enum\StatusTypes::Completed 
                                    && $citem->status!=\OmniFlow\enum\StatusTypes::Terminated
                                    && $citem->id != $caseItem->id
                                    && $pItem->pool==$pool)
                            {
                                \OmniFlow\Context::debug("end event need to wait completion till all other nodes are done");
                                    return true;
                            }
                    }                    
			
		} */
                
                if ($this->hasMessage ===false && $this->hasSignal == false)
                    return false;
                
		if (ProcessItem::isSenderType($this->type))
		{
                    return false;
   		} else {
                    if ($from!==null) {
                        $fromMsgFlow=false;
                        $procItem=$from->getProcessItem();
                        foreach($procItem->outflows as $flow)
                        {
                            if ($flow->toNode->id==$this->id)
                            {
                                if ($flow->type== \OmniFlow\enum\WFObjectTypes::messageFlow) {
                                    $fromMsgFlow=true;
                                    break;
                                }
                            }
                        }
			if ($fromMsgFlow)
				return false;
                    }
                    
                    return true;
                }
                return false;
                
    }
        public function requiresAccessRules()
        {
            if ( ($this->type=='startEvent') && ($this->isExecutable()) && 
                 (!$this->hasMessage) && (!$this->hasSignal) && (!$this->hasTimer)
                 )
            {
                return true;
            }
            else
                return false;
        }
	protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		// an event is invoked;
		/*
		 * Need to know the difference between executed as in Started and Invoked because of a message
		 * 
		 */
            if ($this->requiresAccessRules())
            {
                // only if it is a manual event
                //  $this->checkAccessRules($caseItem);
                WFCase\Assignment::UserTake($this,$caseItem); // will also check for access
            }
            
		if (($this->hasMessage) && ProcessItem::isSenderType($this->type))
		{
			$noMsgFlows=true;
                        foreach($this->outflows as $flow)
                        {
                            if ($flow->type=='messageFlow')
                            {
                                $noMsgFlows=false;
                            }
                        }
			if ($noMsgFlows)
                        {
//                            $this->IssueMessage();
                             \OmniFlow\QueueEngine::addNodeToCase('IssueMessage',
                                        array($this,$caseItem));
                            
                        }
		}
		if (($this->hasSignal) && ProcessItem::isSenderType($this->type))
		{
                          \OmniFlow\QueueEngine::addNodeToCase('IssueSignal',
                                        array($this,$caseItem));
		}
            
            return true;
	}
	/*
	 * 	Event::Finish
	 * 
	 * 
	 * Need to check if it is originated from EventBasedGateway, if so, need to cancel other events
	 * 
	 */	
	protected function finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
            \OmniFlow\Context::Debug("event:finish for $this->id type: $this->type caseItem :$caseItem->id");
                if ($this->type=="startEvent") 
                {
                    if ($this->hasTimer)
                        $this->updateTimer ();
                }
  		elseif ($this->type=="endEvent")
		{
			$this->proc->EndProcess($caseItem->case,$caseItem->getProcessItem());
		}
		elseif (count($this->inflows)>0)
		{
			OmniFlow\Context::Log(\OmniFlow\Context::INFO, "An Event $this->id is finished, checking if comming EventBasedGateway");
			foreach($this->inflows as $sourceFlow)
			$sourceNode=$sourceFlow->fromNode;
			if ($sourceNode->type=='eventBasedGateway')
			{
				OmniFlow\Context::Log(\OmniFlow\Context::INFO, "An Event $this->id is finished, cancelling others event for EventBasedGateway $sourceNode->id");
				$sourceNode->cancelOthers($caseItem,$input,$this);
			}
		}
		
		return parent::finish($caseItem,$input, $from);
	}
        /*
         *  called after Start Event is completed to reset its timer
         * 
         */
        public function updateTimer()
        {
            \OmniFlow\Context::Debug("Event:updateTimer for $this->id");
		$dueDate=  OmniFlow\EventEngine::getDueDate($this);
                $model=new OmniFlow\ProcessItemModel();
                $model->updateTimer($this,$dueDate);
        }
	function Trace()
	{
		foreach ($this->outflows as $flow)
		{
			$flow->Trace();
		}
	}
}
