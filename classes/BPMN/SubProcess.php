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

/*
 * ProcessItem
 *          subProcessId
 * 
 * CaseItem 
 *          parentId    points to caseItem.id of the calling process
 *          subProcessId points to processId in case of subprocess
 * 
 * Points:
 *      subprocess item - run
 *          invoke the subprocess 
 *      case-item execute
 *          detect that you are a in a subprocess?
 *              from  points to another caseItem with parentId, copy parentId
 *      flow- next node
 *          
 *      child end process
 *              same
 *      loadCase of subprocess
 *              if caseItem has a parentId, then we should load that process
 * 
 * 
 * ================================ 
 * 
 */
namespace OmniFlow\BPMN;

use OmniFlow;
use OmniFlow\WFCase;

/**
 * Description of Task
 *
 * @author ralph
 */
class SubProcess extends Node
{
	public function subProcess()
	{
		return true;
	}
	public function Init()
	{
		parent::Init();
	}
	public function describe(\OmniFlow\Describer $t)
	{
		$t->desc="Sub-Process or Pool representing a Participant in a Collaboration. "
                        . "<br />Graphically, a Pool is a container for partitioning a Process from other Pools/Participants. ";
		$t->title="Sub-process or Pool";
                
	} 
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
 		OmniFlow\Context::Log(LOG,"Checking wait for Node: class:Task type: $this->type -From: $from $this->label - $this->id $this->actionScript");
            
                return true; // Don't continue
        }
        public function requiresAccessRules()
        {
            return false;
        }
        /*
         *  invokes the called-process
         */
	protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
            OmniFlow\Context::Log(LOG,"Run Node: class:Subprocess type: $this->type - $this->label - $this->id $this->actionScript");
         
            return $this->startSubProcess($caseItem,$input,$from);
        }
			
	protected function startSubProcess(WFCase\WFCaseItem $caseItem,$input,$from)
	{
            OmniFlow\Context::Log(LOG,"Run Node: class:Subprocess type: $this->type - $this->label - $this->id $this->actionScript");
            $processId=$this->subProcessId;
            
            $starter=$this->startNodeId;
            $proc= BPMN\Process::LoadProcess($processId);
                if ($starter===null)
                {
                    $starter=$proc->getStartNode($testMode);
                    if (count($starter)==0)
                    {
                        Context::Error("No Start Event is available for this process");
                        return;
                    }
                    else
                    {
                        $starter=$starter[0];
                        $starterId=$starter->id;
                    }
                } else {
                    if (is_string($startNodeId))
                    {
                        $starterId=$startNodeId;
                        $starter=$proc->getItemById($startNodeId);
                    }
                }
                /* from now-on all items will have this */
                
                WFCase\WFCaseItem::$subProcessId=$processId;
                WFCase\WFCaseItem::$parentId=$caseItem->id;
                
		$proc->Start($case,$starterId);
                
            return true;
	}
        /*
         *  is called at end of the subprocess
         */
	protected function endSubProcess(WFCase\WFCaseItem $caseItem,$input)
        {
            WFCase\WFCaseItem::$subProcessId=null;
            WFCase\WFCaseItem::$parentId=null;
            return $this->SaveData($caseItem, $input,  \OmniFlow\enum\StatusTypes::Completed);

        }
}
