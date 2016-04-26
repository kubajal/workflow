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
 * Description of Node
 *
 * @author ralph
 */
class Node extends ProcessItem
{
	var $inflows = Array();
	var $outflows = Array();
	var $inflowsLabels;
	var $outflowsLabels;
	
	public function Init()
	{
		parent::Init();
                // pool
                
		$flows=array();
		foreach($this->inflows as $flow)
		{
                    if (get_class($flow)=='OmniFlow\BPMN\Flow')
			$flows[]=$flow->fromNode->type.':'.$flow->fromNode->name;
		}
		$this->inflowsLabels=join(",",$flows);
		$flows=array();
		
		foreach($this->outflows as $flow)
		{
                    if (get_class($flow)=='OmniFlow\BPMN\Flow')
                    {    
                        $type=$flow->toNode->type;
                        $name=$flow->toNode->name;
			$flows[]=$type.':'.$name;
                    }
                    else
                        OmniFlow\Context::debug("flow is not a flow".get_class($flow));
		}
		$this->outflowsLabels=join(",",$flows);
		
	}
        /*
         * is called in two scenarios
         *      a. No outgoing Message flows
         *      b. Message flow to an external (not executable node)
         */
        
        public function IssueMessage(WFCase\WFCaseItem $caseItem)
        {

            $messageName=$this->message;
            $data=  OmniFlow\Context::getInstance()->outputData;
            

            WFCase\WFCaseItemStatus::$Notes='send message:'.$messageName;

            OmniFlow\Context::Debug("Node $this->label IssueMessage $messageName".print_r($data,true));
            
            \OmniFlow\MessageEngine::Send($messageName, $data);

        }
        public function IssueSignal(WFCase\WFCaseItem $caseItem)
        {

            $messageName=$this->signalName;
            $data=  OmniFlow\Context::getInstance()->outputData;
            
            WFCase\WFCaseItemStatus::$Notes='issue signal:'.$messageName;

            OmniFlow\Context::Debug("Node $this->label Issue Signal $messageName".print_r($data,true));
            
            \OmniFlow\MessageEngine::SendSignal($messageName, $data);

        }
        /*
         * called just before a CaseItem is inserted into the database
         * to setup various values
         */
        public function setup(WFCase\WFCaseItem $caseItem)
        {
            if ($this->hasTimer)
            {
                    $dueDate=  OmniFlow\EventEngine::getDueDate($this);
                    $caseItem->timerDue=$dueDate;
                    OmniFlow\Context::Log(\OmniFlow\Context::INFO,"Event Start: setting timer due date: $dueDate");

            }

        }
        public function getPool()
        {
            foreach($this->proc->pools as $sub)
            {
                if ($sub->id==$this->pool)
                    return $sub;
            }
            return null;
        }

	protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Run Node: type: $this->type - $this->label - $this->id");

		if ($this->actionScript!="")
                {
                    $ret=eval ($this->actionScript);
                    OmniFlow\Context::Log(\OmniFlow\Context::INFO, "executing script: $this->actionScript ret: $ret" );
                }
		return true;
	}
	protected function finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Finish Node: type: $this->type - $this->label - $this->id");

		foreach ($this->outflows as $flow)
		{
			$flow->Execute($caseItem->case,$input,$caseItem);
		}
		return true;
	}
	function Trace()
	{
		parent::Trace();
		foreach ($this->outflows as $flow)
		{
			$flow->Trace();
		}
	}
        function canSendMessages()
        {
                foreach($this->outflows as $flow)
                {
                    if ($flow->type=='messageFlow')
                    {
                        if ($flow->toNode->getPool()->isExecutable())
                            return true;
                    }
                }
                return false;
        }
}
