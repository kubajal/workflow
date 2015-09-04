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
use OmniFlow\BPMN;

use OmniFlow\CaseModel;
use OmniFlow\CaseItemModel;
use OmniFlow\ActionManager;
/**
 * Description of WFCase
 *
 * @author ralph
 */
class WFCase extends \OmniFlow\WFObject
	{
		var $caseId;
		var $title;
		var $processName;
		var $processFullName;
		var $caseStatus;
                var $created;
                var $updated;
		var $proc;
		var $items = Array();
		var $values=Array();
                var $assignments=Array();
                var $participants=Array();
                
		var $isProcess=0;
		public function describe()
		{
			echo '<br />Describe Case'.$this->processName."-Id".$this->caseId;
			foreach($this->items as $item)
			{
				echo '<br />  CaseItem:'.$item->label.':'.$item->processNodeId.":".$item->status;
			}
	
		}
                public static function SampleCaseForProcess($proc)
                {
                        $case=new WFCase();
                        $case->processName = $proc->processName;
                        $case->processFullName = $proc->processName;
                        $case->values=\OmniFlow\DataManager::createDataObject($proc);
                        $case->proc=$proc;        

                        return $case;

                }                
		static function NewCase($proc)
		{
			$case=new WFCase();
			$case->processName = $proc->processName;
			$case->processFullName = $proc->processName;
			$case->values=OmniFlow\DataManager::createDataObject($proc);
			$case->proc=$proc;
			$db= new CaseModel();
			$db->insert($case);
			return $case;
		}
                public function Update()
                {
                    $this->proc->Notify(OmniFlow\enum\NotificationTypes::CaseSaving,$this->case);
		
                    $db=new OmniFlow\CaseModel();
                    $db->update($this);
		
                    $this->proc->Notify(OmniFlow\enum\NotificationTypes::CaseSaved,$this->case);

                }
		static function LoadCase($caseId)
		{
			
			if ($caseId==null ||empty($caseId))
			{
				OmniFlow\Context::Log(ERROR, 'LoadCase needs a valid CaseId ');
				return null;
			}			
			$db=new CaseModel();
			
			$case=new WFCase();
			
			$case=$db->load($caseId, $case);

			$proc=BPMN\Process::Load($case->processFullName);

			$case->proc=$proc;
		
			foreach ($case->items as $item)
			{
				$pitem=$proc->getItemById($item->processNodeId);
			}
		
                        OmniFlow\Context::Log(INFO, "Case Loaded ".print_r($case->values,true));
			$proc->Notify(OmniFlow\enum\NotificationTypes::CaseLoaded,$case);
			return $case;
		}
		static function createItemHandler(WFCase $case,BPMN\Process $proc,BPMN\ProcessItem $processItem)
		{
			if ($processItem->isFlow())
				return;
			
			// check if there is already an open item for this node
			foreach($case->items as $xItem)
			{
				if (($xItem->processNodeId == $processItem->id)
						&& ($xItem->status !=\OmniFlow\enum\StatusTypes::Completed))
				{
					return $xItem;
				}
			}
			
			
			$item= new WFCaseItem($case);
                        $processItem->setup($item);
			$item->caseId=$case->caseId;
			$item->processNodeId = $processItem->id;
			//var_dump($processItem);
			$item->type=$processItem->type;
			$item->subType=$processItem->subType;
			$item->label = $processItem->label;
			$item->actor = $processItem->actor;
			$item->timer = $processItem->timer;
			$item->timerType = $processItem->timerType;
			$item->timerRepeat = $processItem->timerRepeat;
			$item->message = $processItem->message;
			$item->signalName = $processItem->signalName;
			$case->items[]=$item;
			$db=new OmniFlow\CaseItemModel();
			$db->insert($item);
				
			return $item;
		}
		function getItemByProcessId($ProcessItemId)
		{
			foreach($this->items as $xItem)
			{
				if ($xItem->processNodeId == $ProcessItemId)
				{
					return $xItem;
				}
			}
			return null;
		}
		function getItem($id)
		{
			foreach($this->items as $item)
			{
				if ($item->id ==$id)
					return $item;
			}
			return null;
		}
				
		function EndProcess(ProcessItem $endItem)
		{
			$subProc=$endItem->subProcess;
			foreach ($this->items as $item)
			{
				$pItem=$this->proc->getItemById($item->processNodeId);
				
				if ($item->status!=\OmniFlow\enum\StatusTypes::Completed && $pItem->subProcess==$subProc)
				{
					$item->Update(\OmniFlow\enum\StatusTypes::Terminated);
				}
			}
		}		
		public function GetValue($variableName)
		{
                    if (isset($this->values[$variableName]))
                        return $this->values[$variableName];
                    else
                        return null;
			//return OmniFlow\DataManager::getValue($this->values,$variableName);
		}
		public function SetValue($variableName,$value)
		{
                        $this->values[$variableName]=$value;
			//OmniFlow\DataManager::setValue($this->values,$variableName,$value);
		}

		public function __toArray()
		{
			$data=parent::__toArray();
			$data['caseValues']=OmniFlow\DataManager::SaveData($this->values);
			return $data;
		}
		public function __fromArray($data)
		{
			parent::__fromArray($data);
			$this->values=OmniFlow\DataManager::GetData($data['caseValues']);
				
		}
}

