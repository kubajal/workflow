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
 * Description of Task
 *
 * @author ralph
 */
class Task extends Node
{
	public function isTask()
	{
		return true;
	}
	public function Init()
	{
		parent::Init();
                if (($this->type=='sendTask')||($this->type=='receiveTask'))
                {
                    $this->subType='message';
                    $this->hasMessage=true;
                }
		if (count($this->outflows)==1)
		{
			$out=$this->outflows[0];
			$next=$out->toNode;
		}
		return;
	}
	public function describe(\OmniFlow\Describer $t)
	{
            $t->desc="Work that needs to be perfomed in a Process.";
            $t->designOptions=array("Define Action");
            
            switch($this->type)
            {
                case 'sendTask':
                    $t->title="Send Task";
                    $t->checkSubItem($this);
                    $t->designOptions=array("Define Action","Map message data");
                    break;
                case 'receiveTask':
                    $t->title="Receive Task";
                    $t->checkSubItem($this);
                    $t->designOptions=array("Define Action","Map message data");
                    break;
                case 'userTask':
                    $t->title="User Task";
                    $t->designOptions=array("Define Action",  OmniFlow\KW::acl);
                    $t->completion=OmniFlow\KW::manualComplete;
                    break;
                case 'serviceTask':
                    $t->title="Service Task";
                    break;
                case 'manualTask':
                    $t->title="Manual Task";
                    $t->completion=OmniFlow\KW::manualComplete;
                    break;
                case 'scriptTask':
                    $t->title="Script Task";
                    break;
                default:
                    $t->title="Task";
                    break;
            }
	}
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
 		OmniFlow\Context::Log(LOG,"Checking wait for Node: class:Task type: $this->type -From: $from $this->label - $this->id $this->actionScript");
            
		if ($this->type=="receiveTask")
		{
                    if ($from===null)
                            return false;
                    
                    if ($from->type=='messageFlow')
                            return false;
                    else
                        return true;
		}
		if (($this->type=="userTask")||($this->type=="task"))
		{
                        return true; // Don't continue
		}
                return false;
        }
        public function requiresAccessRules()
        {
            if (($this->type=="userTask")||($this->type=="task"))
            {
                if ($this->isExecutable())
                    return true;
            }
            return false;
        }
	protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Run Node: class:Task type: $this->type - $this->label - $this->id $this->actionScript");
                
                // following section is for userTask
                switch($this->type)
                {
                    case 'userTask':
                    if ($this->requiresAccessRules())
                    {
                        $privileges=WFCase\Assignment::getPrivileges($this, $caseItem);

                        \OmniFlow\Context::debug("Task.run privileges : $privileges");
                        $modify=false;
                        if (in_array("T", $privileges)) {   // can take
                            $modify=true;
                            WFCase\Assignment::UserTake($this,$caseItem);
                        } else {
                            if (!in_array("V", $privileges))    // can View
                               return WFCase\Assignment::NotAuthorized($this,$caseItem);
                            
                        }
                    }
                    $actionView=\OmniFlow\ActionManager::getActionView($caseItem,$postForm);
                    

                    \OmniFlow\Context::Log(\OmniFlow\Context::INFO,"actionView:$actionView");

                    if ($actionView===true)
                        return false;	
                    \OmniFlow\ActionManager::defaultForm($caseItem,$modify);
                    return false;   // Form Task is not complete
                    break;
                    case 'sendTask':
                        
                        if (! $this->canSendMessages())    // let the msg flow do the work
                            $this->IssueMessage($caseItem);
                    break;
                }
		
		if ($this->actionScript!="")
                {
                    // change here
			$ret=  \OmniFlow\ActionManager::ExecuteAction($this->actionScript,$caseItem);
                }
		
		if ($this->customFunction!="")
		{
//			echo 'calling '.$this->customFunction;
			$ret=call_user_func($this->customFunction,$this,$caseItem,$input,$from);
//			echo '<br /> result='.$this->result;
		}
			
		return true;
	}
}
