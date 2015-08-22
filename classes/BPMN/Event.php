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
		
		foreach($node->xpath('model:terminateEventDefinition') as $child) {
				$this->subType= WFSubTypes::TERMINATION_TYPE;
		}
		foreach($node->xpath('model:terminateErrorDefinition') as $child) {
			$this->subType= WFSubTypes::ERROR_TYPE;

		}
		
	}
		
	// Todo: get Message
	public function describe()
	{
	
		$msg=parent::describe();
		if ($this->hasMessage)
			$msg.=":hasMessage:$this->message";
		if ($this->hasTimer)
			$msg.=":hasTimer:$this->timer";
		
		if ($this->timer!=null)
			$msg.=":Timer ".$this->timerType.'='.$this->timer;
			
		return $msg;
	}
	
	/*
	 * initialize the event 
	 */
	
	public function Start(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		return true;
	}
	
	function Run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		// an event is invoked;
		/*
		 * Need to know the difference between executed as in Started and Invoked because of a message
		 * 
		 */
		if ($this->hasTimer)
		{
			// wait for the timer
			return false;
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
		if ($this->type=='intermediateCatchEvent')
		{
                        if ($from===null)
                            return true;
			if ($from->type=='messageFlow')
				return true;
			else
				return false;
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
	function Finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		if ($this->type=="endEvent")
		{
			$this->proc->EndProcess($caseItem->case,$caseItem->getProcessItem());
		}
		elseif (count($this->inflows)>0)
		{
			OmniFlow\Context::Log(INFO, "An Event $this->id is finished, checking if comming EventBasedGateway");
			foreach($this->inflows as $sourceFlow)
			$sourceNode=$sourceFlow->fromNode;
			if ($sourceNode->type=='eventBasedGateway')
			{
				OmniFlow\Context::Log(INFO, "An Event $this->id is finished, cancelling others event for EventBasedGateway $sourceNode->id");
				$sourceNode->cancelOthers($caseItem,$input,$this);
			}
		}
		
		return parent::Finish($caseItem,$input, $from);
	}	
	function Trace()
	{
		foreach ($this->outflows as $flow)
		{
			$flow->Trace();
		}
	}
}
