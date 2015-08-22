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
 * Description of Gateway
 *
 * @author ralph
 */
/*
 * There’s 7 kinds of gateways differed by its internal marker: 
 *          1 Exclusive, 
 *          2 Inclusive, 
 *          3 Parallel, 
 *          4 Complex, 
 *          5 Event-based, 
 *          6 Parallel Event-based 
 *          7 and Exclusive Event-based.

 */	
class Gateway extends Node
{
	var $defaultFlowId;
	var $direction;
	
	public function isGateway()
	{
		return true;
	
	}
	public function loadFromXML($node)
	{
		parent::loadFromXML($node);
		
		$this->direction=OmniFlow\XMLLoader::getAttribute($node, 'gatewayDirection');
		$this->defaultFlowId=OmniFlow\XMLLoader::getAttribute($node, 'default');
	
	}
	public function getDefaultFlow()
	{
		if ($this->defaultFlowId==null)
			return null;
		return $this->proc->getItemById($this->defaultFlowId);
	}
	public function cancelOthers(WFCase\WFCaseItem $caseItem,$input,$from)
	{
		$case=$caseItem->case;
		foreach($this->outflows as $flow)
		{
			$target=$flow->toNode;
			if ($target->id!=$from->id)
			{
				$item=$case->getItemByProcessId($target->id);
				if ($item!=null)
					$item->Update(\OmniFlow\enum\StatusTypes::Terminated);
			}
		}
	}
	
	
}
