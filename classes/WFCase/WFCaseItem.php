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

namespace OmniFlow\WFCase;

use OmniFlow;
use OmniFlow\ActionManager;

/**
 * Description of WFCaseItem
 *
 * @author ralph
 */
		

class WFCaseItem extends OmniFlow\WFObject
		{
			var $id;
			var $caseId;
			var $processNodeId;
			var $type;
                        var $subType;
			var $label;
			var $actor;
			var $status;
			var $started;
			var $completed;
			var $result;
			var $notes;
			var $timerType;
			var $timer;
			var $timerRepeat;
			var $timerDue;
			var $message;
			var $messageKeys;
			var $signalName;
                        var $caseStatus;
                        var $caseStatusDate;
			var $values=Array();
			var $case;
			

	public function __construct(WFCase $case)
	{
		$this->case=$case;
	}
        public function Error($msg)
        {
                $this->notes="Error:".$msg;
        }
	public function getProcessItem()
	{
		return $this->case->proc->getItemById($this->processNodeId);
	}
	public function getVariables()
	{
		$task=$this->getProcessItem();
		return $task->dataElements;
	}
			
	public function isTask()
	{
		$p=strpos($this->type,"Task");
		
		if ($p===false)
			return false;
		else 
			return true;
			}
	public function isEvent()
	{
		$p=strpos($this->type,"Event");
		
		if ($p===false)
			return false;
		else 
			return true;
	}
	public function getActionView($postForm)
	{
		return ActionManager::getActionView($this,$postForm);
	}
	public function UserTake()
        {
            Assignment::UserTake($this);
            
        }
	public function UserRelease()
        {
            Assignment::UserRelease($this);
            
        }
	public function AssignUser($userId)
        {
            Assignment::AssignUser($this, $userId);
            
        }
	public function Update($status)
	{
		OmniFlow\Context::Log(INFO,"CaseItem:Update $this->id status: $this->status to: $status");
		$this->case->proc->Notify(OmniFlow\enum\NotificationTypes::CaseItemSaving,$this);
		$this->status=$status;
//		$this->result=$this->getProcessItem()->result;

		$db=new OmniFlow\CaseItemModel();
		$db->update($this);
		$this->case->proc->Notify(OmniFlow\enum\NotificationTypes::CaseItemSaved,$this);
		
		$this->case->proc->Notify(OmniFlow\enum\NotificationTypes::CaseSaving,$this->case);
		
                $db=new OmniFlow\CaseModel();
		$db->update($this->case);
		
		$this->case->proc->Notify(OmniFlow\enum\NotificationTypes::CaseSaved,$this->case);
		
	}
	
	public function __toArray()
	{
		$data=parent::__toArray();
//		$data['itemValues']=VariableManager::SaveVariables($this->values);
		return $data;
	}
	public function __fromArray($data)
	{
		parent::__fromArray($data);
	
//		$this->values=VariableManager::LoadVariables($data['itemValues']);
	}
 }
 
