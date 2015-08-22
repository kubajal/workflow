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
                // subprocess
                
		$flows=array();
		foreach($this->inflows as $flow)
		{
			$flows[]=$flow->fromNode->type.':'.$flow->fromNode->name;
		}
		$this->inflowsLabels=join(",",$flows);
		$flows=array();
		
		foreach($this->outflows as $flow)
		{
			$flows[]=$flow->toNode->type.':'.$flow->toNode->name;
		}
		$this->outflowsLabels=join(",",$flows);
		
	}
        /*
         * is called in two scenarios
         *      a. No outgoing Message flows
         *      b. Message flow to an external (not executable node)
         */
        public function IssueMessage()
        {

            $messageName=$this->message;
            $data=array();

            OmniFlow\Context::Debug("Node $this->label Issue Message $messageName");
            
            ProcessSvc::HandleMessage($messageName, $data);

        }
        /*
         * called just before a CaseItem is inserted into the database
         * to setup various values
         */
        public function setup(WFCase\WFCaseItem $caseItem)
        {
            if ($this->hasTimer)
            {
                    $dueDate=EventEngine::getDueDate($this);
                    $caseItem->timerDue=$dueDate;
                    OmniFlow\Context::Log(INFO,"Event Start: setting timer due date: $dueDate");

            }

        }
        public function getSubProcess()
        {
            foreach($this->proc->subprocesses as $sub)
            {
                if ($sub->id==$this->subProcess)
                    return $sub;
            }
            return null;
        }

	function Start(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		$this->Notify(OmniFlow\enum\NotificationTypes::NodeStarted);
		OmniFlow\Context::Log(LOG,"start Node: type: $this->type - $this->label - $this->id");

		return true;
	}
	function Run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Run Node: type: $this->type - $this->label - $this->id");
                var_dump($this);
		if ($this->actionScript!="")
                {
                    $ret=eval ($this->actionScript);
                    OmniFlow\Context::Log(INFO, "executing script: $this->actionScript ret: $ret" );
                }
		return true;
	}
	function Finish(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Finish Node: type: $this->type - $this->label - $this->id");

		foreach ($this->outflows as $flow)
		{
			$flow->Execute($caseItem->case,$input,$caseItem);
		}
		$this->Notify(OmniFlow\enum\NotificationTypes::NodeCompleted);
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
}
