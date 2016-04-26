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
 * Description of ProcessItems
 *
 * @author ralph
 */
class ProcessItem extends OmniFlow\WFObject
{
	var $proc;
        var $processId;
        var $seq=null;       // a sequential number 1..n
	var $id;	// from the XML File
	var $name;	// from the XML File
	var $type;	// from the XML File
	var $subType;
        var $superType;
	var $label;
	var $lane;
	var $actor;

	//	TMS	Timer Message Signal
	var $hasTimer;
	var $timerType;
	var $timer;
	var $timerRepeat;

        var $caseStatus;
        
	var $condition;
	
	var $hasMessage;
	var $hasSignal;
	var $message;			// name of message for event or receiveTask
	var $messageKeyCaseExpression;		
	var $messageKeyMsgExpression;		
        var $signalName;
	
	var $actionType;
	var $actionScript;			// to be executed
        var $customFunction;
	var $dataElements=array();

	var $xCoord;
	var $yCoord;
	var $pool;
        
        var $priority;
        var $deadline;
        var $deadlineFrom;
        var $effort;
        var $description;
//        var $subItem;
        var $scripts=array();
        

        /*
         * Check if type of a sender  or receiver
         */
        public static function isSenderType($type)
        {
            if ($type=='sendTask' || $type == 'intermediateThrowEvent')
                return true;
            
            return false;
        }
	/*
	 * 	this is called internally to complete an outstanding task
	 * 	pre-conditions:	task is already started
	 *  impact:	Task will be completed and outflows will fire
         * 
         * 
	 */
	public function Complete(WFCase\WFCaseItem $caseItem,ProcessItem $from)
	{
            $input='';
            \OmniFlow\Context::Debug("ProcessItem:Complete calling finish $this->id $caseItem->id");
		$this->finish($caseItem,$input,$from);
                WFCase\Assignment::TaskComplete($caseItem);

		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Completed,$values,$from);
	}
	public function ReceiveMessage(WFCase\WFCaseItem $caseItem,$messageName,$values)
        {
            return $this->SaveData($caseItem, $values,  \OmniFlow\enum\StatusTypes::Completed);
        }
	public function ReceiveSignal(WFCase\WFCaseItem $caseItem,$signaleName,$values)
        {
            return $this->SaveData($caseItem, $values,  \OmniFlow\enum\StatusTypes::Completed);
        }
        public function TimerDue($caseItem)
        {
            $this->Complete($caseItem, null);
        }
        /*
         *  
         */
	public function SaveData(WFCase\WFCaseItem $caseItem,$values,$newStatus=  \OmniFlow\enum\StatusTypes::Updated)
        {
                OmniFlow\Context::Log(\OmniFlow\Context::INFO,print_r($values,true));

                $case = $caseItem->case;

                
                foreach($this->dataElements as $variable)
                {
                    $de=$variable->getDataElement($this->proc);
                    $varName=$variable->field;
                    if ($varName=='')
                        $varName=$de->name;

                    if (isset($values[$varName]))
                    {
                        $value=$values[$varName];

                        $case->SetValue($de->name,$value);
                    }
                    else    // null out the values that are not in input to fix for checkboxes
                    {
                        $case->SetValue($de->name,"");

                    }

                }
                
		if (!$this->Notify(OmniFlow\enum\NotificationTypes::NodeValidate,$caseItem))
                        return false;
                
		$this->Notify(OmniFlow\enum\NotificationTypes::NodeSaved,$caseItem);
                
                
                if ($newStatus==OmniFlow\enum\StatusTypes::Completed)
                    $this->Complete ($caseItem, null);
                else {
                    $this->setStatus($caseItem, $newStatus);
                }
            
        }
        public function isExecutable()
        {
            return $this->getPool()->isExecutable();
        }
            
	private function setStatus(WFCase\WFCaseItem $caseItem,$newStatus,$values="",$from=null)
	{        

            if ($newStatus == \OmniFlow\enum\StatusTypes::Completed)
                WFCase\Assignment::TaskComplete($caseItem);
            
		$this->Notify(OmniFlow\enum\NotificationTypes::NodeCompleted,$caseItem);
		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessIterm Executing-setting status to : $newStatus $this->type - $this->label - from: $from  $this->id" );
            
		OmniFlow\Context::Log(\OmniFlow\Context::INFO, "setStatus: $this->id from $caseItem->status to $newStatus");
                if (($caseItem->status==\OmniFlow\enum\StatusTypes::Completed) ||($caseItem->status==\OmniFlow\enum\StatusTypes::Terminated))
                {
//          toDo: uncomment          throw new \Exception("setStatus: $this->id from $caseItem->status to $newStatus");
                }
		if (is_string($values))
		{
		}
		else if (is_array($values))
		{
                    $this->SaveData($caseItem,$values,false);
		}
                $itemStatus= new WFCase\WFCaseItemStatus($caseItem,$newStatus,$from);
                $itemStatus->insert();
		$caseItem->Update($newStatus);
	}
	function Trace()
	{
		//echo "<br />Trace:". $this->describe();	
	}
	public function __Construct($proc,$label="")
	{
                $this->processId=$proc->processId;
		$this->proc = $proc;
		$proc->items[]=$this;
		$this->label = $label;
		$this->hasMessage=false;
                $this->hasSignal=false;
		$this->hasTimer=false;
	}
	public function loadFromXML($node)
	{
		$this->id=OmniFlow\XMLLoader::getAttribute($node, 'id');
		$this->name=OmniFlow\XMLLoader::getAttribute($node, 'name');
	
		$this->type=$node->getName();
		$this->label=$this->name;
		
	
	}
	public function isTask()
	{
		return false;
	}
	public function isEvent()
	{
		return false;
	}
	public function isGateway()
	{
		return false;
		
	}
	public function isFlow()
	{
		return false;	
	}
	function Notify($event,$caseItem=null)
	{
            $this->proc->Notify($event,$this);
            
            if ($caseItem==null)
                return true;
            
            NotificationRule::ChekNotificationsForItem($event, $this, $caseItem);
            
            if (isset($this->scripts[$event]))
            {
                $script=$this->scripts[$event];
                $ret=  \OmniFlow\ActionManager::ExecuteAction($script,$caseItem);
                
                return $ret;
            }
            return true;
	}
	public function Init()
	{
            if ($this->isTask())       $this->superType='Task';
            if ($this->isEvent())      $this->superType='Event';
            if ($this->isFlow())      $this->superType='Flow';
            if ($this->isGateway())      $this->superType='Gateway';

            
            if ($this->label=='')
			$this->label=$this->type;
                
                foreach($this->dataElements as $var)
                {
                    foreach($this->proc->dataElements as $de)
                    {
                        if ($var->refId==$de->id)
                        {
                            $var->name=$de->name;
                        }
                    }
                }
		return;
	}
        public function requiresAccessRules()
        {
            return false;
        }
        public function checkAccessRules($caseItem)
        {
            return WFCase\Assignment::GetPrivilege($this, $caseItem);
        }
	/*
         *  Called to Execute a ProcessItem from begining to End
         *Execute::<<--- invoked from process
         *  1) start (inherited)    check if item to be skipped or runnable
         *  2) create Case Item
         *          Event:NodeStarted
         *  3)  assign
         *          Event:NodeAssigned
         *  4)   NeedToWait (inherited) Check For waiting? 
         *      goto invoke
         *Invoke:
         *   5) Run       perform the work
         *   6) isComplete (inherited)
         *   7) Finish    calls outflows
         */
	public function Execute(WFCase\WFCase $case,$input,$from)
	{
		
		$fromLabel="";
                if ($from!=null)
                    $fromLabel=$from->label;
		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"**ProcessItem Executing: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
		

                if (!$this->isExecutable()) {
        		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessItem Executing node not executable - skipped: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                        return false;
                }
                
		if (!$this->start($case,$input,$from))
		{
			$this->Notify(OmniFlow\enum\NotificationTypes::NodeSkipped);
        		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessItem Executing node skipped: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
			return false;
		}
                
                $caseItem= WFCase\WFCase::createItemHandler($case,$this->proc, $this);

                $this->Notify(OmniFlow\enum\NotificationTypes::NodeStarted,$caseItem);
                $this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Started,null,$from);

                $this->Assign($caseItem);
                $this->Notify(OmniFlow\enum\NotificationTypes::NodeAssigned,$caseItem);
                
            //**** NeedToWait    
                // check Access Rules and assign Role if required

                if ($this->NeedToWait($caseItem,$input,$from))
                {
            	OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessItem Executing Going into Wait Mode$this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                    return false;
                }
                
		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessItem Executing continue to Invoke: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                

                if ($from==null)
                    $this->Invoke($caseItem,"",$input);
                else
                    $this->Invoke($caseItem,"",$input,$from);
                    
                
	}
       	/*
	 * 	this is called externally and internally to invoke a outstanding task like a 'Receive Task'
	 * 	pre-conditions:	task is already started
	 *  impact:	Task will decide if it is waiting for any more messages
	 */
	public function Invoke(WFCase\WFCaseItem $caseItem,$values="",$input="",$from=null)
	{
            
                $fromLabel="";
                if ($from!=null)
                    $fromLabel=$from->label;

		OmniFlow\Context::Log(\OmniFlow\Context::LOG,"ProcessItem Invoke : $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                
                $this->Notify(OmniFlow\enum\NotificationTypes::NodePreRun,$caseItem);
                
                
                if (!$this->run($caseItem, $input, $from)) {
			return false;
                }
                
                
                $this->Notify(OmniFlow\enum\NotificationTypes::NodeRun,$caseItem);
                
                
                if (!$this->isComplete($caseItem, $input, $from))
                    return $caseItem;
                
            \OmniFlow\Context::Debug("ProcessItem:invoke calling finish $this->id $caseItem->id");
		$ret =$this->finish($caseItem,$input,$from);
		
                if ($ret==false)
                {
		$this->Notify(OmniFlow\enum\NotificationTypes::Error,$caseItem);
		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Error);
                }
                else
                {
                
		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Completed,$values,$from);
                }

		return $caseItem;
	}
        
        /*
         * start    is called by Execute
         * Inherited 
         */
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
            return false;
        }
        /*
         * start    is called by Invoke
         * Inherited 
         */
	protected function isComplete(WFCase\WFCaseItem $caseItem,$input,$from)
        {
            return true;
        }
        /*
         * start    is called by Execute
         * Inherited 
         */
	protected function start(WFCase\WFCase $case,$input,$from)
	{
		return true;
	}
        /*
         * run    is called by Invoke
         * Inherited 
         */
	protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
                return true;
        }

        /*
         * start    is called by Invoke
         * Inherited 
         */
	protected function finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		return true;
	}
        
        /*
         * Assign
         * Create Assignments based on AccessRules 
         * 
         */
        public function Assign(WFCase\WFCaseItem $caseItem)
        {
            return AccessRule::AssignTask($this,$caseItem);
        }

        public function __toArray()
        {
                $data=parent::__toArray();
                
                $els=array();
                foreach($this->dataElements as $var)
                {
                    $els[]=$var->__toArray();
                }
                
                $data['dataElements']=$els;

                $scrs=array();
                foreach($this->scripts as $sid=>$scr)
                {
                    $scrs[]=Array("id"=>$sid,"script"=>$scr);
                }
                
                $data['scripts']=$scrs;
                return $data;
        }

	public function describe(\OmniFlow\Describer $t)
	{

	}
}

