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
 * Description of ProcessItems
 *
 * @author ralph
 */
class ProcessItem extends OmniFlow\WFObject
{
	var $proc;
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
//	var $messageKeys;		// remove
	var $messageRepeat;
        var $signalName;
	var $finalMessageCondition;
	
	var $actionType;
	var $actionScript;			// to be executed
        var $customFunction;
	var $dataElements=array();

	var $xCoord;
	var $yCoord;
	var $subProcess;
        

        /*
         * Check if type of a sender  or receiver
         */
        public static function isSenderType($type)
        {
            if ($type=='sendTask')
                return true;
            
            return false;
        }
	/*
	 * 	this is called internally to complete an outstanding task
	 * 	pre-conditions:	task is already started
	 *  impact:	Task will be completed and outflows will fire
	 */
	public function Complete(WFCase\WFCaseItem $caseItem,$values="",$input="",ProcessItem $from)
	{
		$this->Finish($caseItem,$input,$from);
		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Completed,$values,$from);
	}
	
	public function SetValues(WFCase\WFCaseItem $caseItem,$values,$updateCase=true)
        {
                OmniFlow\Context::Log(INFO,print_r($values,true));

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
                if ($updateCase)
        		$caseItem->case->Update();
            
        }
        public function isExecutable()
        {
            return $this->getSubProcess()->isExecutable();
        }
            
	public function setStatus(WFCase\WFCaseItem $caseItem,$newStatus,$values="",$from=null)
	{        

            if ($newStatus==\OmniFlow\enum\StatusTypes::Completed)
                WFCase\Assignment::TaskComplete($caseItem);
            
		OmniFlow\Context::Log(INFO, "setStatus: $this->id from $caseItem->status to $newStatus");
                if (($caseItem->status==\OmniFlow\enum\StatusTypes::Completed) ||($caseItem->status==\OmniFlow\enum\StatusTypes::Terminated))
                {
//                    throw new \Exception("setStatus: $this->id from $caseItem->status to $newStatus");
                }
		if (is_string($values))
		{
		}
		else if (is_array($values))
		{
                    $this->SetValues($caseItem,$values,false);
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
		$this->proc = $proc;
		$proc->items[]=$this;
		$this->label = $label;
		$this->hasMessage=false;
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
	function Notify($event)
	{
		$this->proc->Notify($event,$this);
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
            $ret=WFCase\Assignment::CanPerform($this, $caseItem);
            if (!$ret)
            {
               throw new \Exception("You are not authorized to perform this function");
                
            }
            
            return true;
        }
       	/*
	 * 	this is called internally to invoke a outstanding task like a 'Receive Task'
	 * 	pre-conditions:	task is already started
	 *  impact:	Task will decide if it is waiting for any more messages
	 */
	public function Invoke(WFCase\WFCaseItem $caseItem,$values="",$input="",$from=null)
	{
            
                $fromLabel="";
                if ($from!=null)
                    $fromLabel=$from->label;

                
                // check Access Rules and assign Role if required

                if ($this->NeedToWait($caseItem,$value,$input,$from))
                {
            	OmniFlow\Context::Log(LOG,"ProcessItem Executing-Invoke: Going into Wait Mode$this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                    return false;
                }
                
		OmniFlow\Context::Log(LOG,"ProcessItem Executing-Invoke: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                
		if (!$this->Run($caseItem,$input,$from))
		{
			return false;
		}
		else 
		{
//			if ($this->result!=null)
//				$input=$this->result;
		}
		OmniFlow\Context::Log(LOG,"ProcessItem Executing-Finish: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
		$ret =$this->Finish($caseItem,$input,$from);
		
                if ($ret==false)
                {
		$this->Notify(OmniFlow\enum\NotificationTypes::Error);
		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Error);
                }
                else
                {
		$this->Notify(OmniFlow\enum\NotificationTypes::NodeCompleted);
		OmniFlow\Context::Log(LOG,"ProcessIterm Executing-setting status to complete: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
                
		$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Completed,$values,$from);
                }

		return $caseItem;
	}
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
            return false;
        }
	/*
         *  Called to Execute a ProcessItem from begining to End
         * 
         *      calls   1) Creates a CaseItem
         *              2) Start
         *              3) Invoke
         *                  4) Run
         *                  5) Finish
         */
	public function Execute(WFCase\WFCase $case,$input,$from)
	{
		
		$caseItem= WFCase\WFCase::createItemHandler($case,$this->proc, $this);
		$fromLabel="";
                if ($from!=null)
                    $fromLabel=$from->label;
		OmniFlow\Context::Log(LOG,"**ProcessItem Executing: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
		

		if (!$this->Start($caseItem,$input,$from))
		{
			$this->Notify(OmniFlow\enum\NotificationTypes::NodeSkipped);
        		OmniFlow\Context::Log(LOG,"ProcessItem Executing node skipped: $this->type - $this->label - from: $fromLabel  $this->id -input=$input" );
			return false;
		}
		else 
		{
			$this->Notify(OmniFlow\enum\NotificationTypes::NodeStarted);
			$this->setStatus($caseItem,\OmniFlow\enum\StatusTypes::Started,null,$from);
		}

                $this->Assign($caseItem);
                
                if ($from==null)
                    $this->Invoke($caseItem,"",$input);
                else
                    $this->Invoke($caseItem,"",$input,$from);
                    
                
	}
	public function Start(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		return true;
	}
	public function Run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		if ($this->actionScript!="")
                {
			$ret=eval ($this->actionScript);
                        OmniFlow\Context::Log(INFO, "executing script: $this->actionScript ret: $ret" );
                }
		
		if ($this->customFunction!="")
		{
//			echo 'invoking custom function';
			$ret=$this->customFunction($this);
//			echo $ret;
		}
		
		return true;
	}
	public function Finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
                WFCase\Assignment::TaskComplete($caseItem);
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
                return $data;
        }

        
	public function describe()
	{
		return $this->type;
	}
}

