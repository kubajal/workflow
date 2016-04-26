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

/**
 * Description of OmniFlow\enum\NotificationTypes
 *
 * @author ralph
 */

namespace OmniFlow\enum;

class NotificationTypes
{
	const ProcessLoaded = 0;		// is called everytime the process is loaded 
	const ProccessInitialized = 1;	// is called before the start of the processs only once
	const ProccessStarted = 2;		// is called at the start of the processs only once
	const ProcessCompleted=3;		// is called at the completion of the process, once
	const ProcessPaused =4;			// everytime the process is paused waiting for an input or event
	const ProcessResumed =5;		// everytime the process is resumed after paused
	
	//	node any part of the process including, events, tasks, gateways and  flow
	const NodeInitialized =10;		// node is initialized
	const NodeSkipped =11;			// if conditions are not met, node is skipped
	const NodeStarted =12;			// node just started
	const NodeRun =13;
        const NodePreRun=14;
	const NodeCompleted =15;			// node is completed
	const NodeTerminated =16;			// Because case is Compeleted or other condition exhausted
        const NodeAssigned =21;
        const NodeUnAssigned=22;
        const NodeUpdated=23;              // form edited
        const NodeValidate=24;           
        const NodeSaved=25;              
        const NodeTaken=26;


        const CaseLoaded=9;
	const CaseSaving=10;
	const CaseSaved=11;
	const CaseItemSaving=12;
	const CaseItemSaved=13;
	const Error=99;

    public static function getScriptEvents()
    {
		$eventsArr=Array(
                    array("event"=>"Start","id"=>NotificationTypes::NodeStarted),
                    array("event"=>"Assign","id"=>NotificationTypes::NodeAssigned),
                    array("event"=>"UnAssign","id"=>NotificationTypes::NodeUnAssigned),
                    array("event"=>"Take","id"=>  NotificationTypes::NodeTaken),
                    array("event"=>"Pre-Run","id"=>  NotificationTypes::NodePreRun),
                    array("event"=>"Run","id"=>NotificationTypes::NodeRun),
                    array("event"=>"Validate","id"=>NotificationTypes::NodeValidate),
                    array("event"=>"Saved","id"=>NotificationTypes::NodeSaved),
                    array("event"=>"Terminate","id"=>NotificationTypes::NodeTerminated),
                    array("event"=>"Error","id"=>NotificationTypes::Error),
                    array("event"=>"Complete","id"=>NotificationTypes::NodeCompleted)
                );
        return $eventsArr;
    }   
}

