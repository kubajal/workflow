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
	/// <summary>
	/// Start:    All in-flows need to be completed before execution
	/// End:      All out-flows will be executed
	/// </summary>
	class ANDGateway extends Gateway
	{

	
                public function Start(WFCase\WFCaseItem $caseItem,$input,$from)
		{
			foreach ($this->inflows as $flow)
			{
				if ($flow->Status != \OmniFlow\enum\StatusTypes::Completed)
				{
					//this.proc.wait(this);
					return false;
				}
			}
			return true;
		}
	
		public function Run(WFCase\WFCaseItem $caseItem,$input,$from)
		{
			return true;
		}
	
		public function Finish(WFCase\WFCaseItem $caseItem,$input,$from)
		{
			foreach ($this->outflows as $flow)
			{
				$flow->Execute($caseItem->case,$input,$caseItem);
			}
			return true;
		}
	}
