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
        //  inclusiveGateway and eventBasedGateway
	/// Start:    All Active in-flows need to be completed
	/// End:      any out-flows will be executed based on input
	/// </summary>
	class ORGateway extends Gateway
	{
        	protected function start(WFCase\WFCase $case,$input,$from)
		{
                    if ($this->CheckAllInflowsComplete($case, $input, $from))
			return true;
                    else
                        return false;
		}
	
		protected function run(WFCase\WFCaseItem $caseItem,$input,$from)
		{
			return true;
		}
	
		protected function finish(WFCase\WFCaseItem $caseItem,$input,$from)
		{
			foreach ($this->outflows as $flow)
			{
				$flow->Execute($caseItem->case,$input,$caseItem);
			}
			return true;
		}
	public function describe(\OmniFlow\Describer $t)
	{
		$t->title="Inclusive Gateway (OR)";
		$t->desc="Controls the flow of the process.";
		$t->start=  OmniFlow\KW::converge.' '.OmniFlow\KW::waitIncomingFlows;

                $t->start=  OmniFlow\KW::converge.' '.OmniFlow\KW::waitIncomingFlows;
		$t->completion=  OmniFlow\KW::diverge.' One or More flows will be executed based on their condition.';
                
                
	}                

	}
