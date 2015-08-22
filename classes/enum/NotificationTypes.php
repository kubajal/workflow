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

/**
 * Description of OmniFlow\enum\NotificationTypes
 *
 * @author ralph
 */

namespace OmniFlow\enum;

abstract class NotificationTypes
{
	const ProcessLoaded = 0;		// is called everytime the process is loaded 
	const ProccessInitialized = 1;	// is called before the start of the processs only once
	const ProccessStarted = 1;		// is called at the start of the processs only once
	const ProcessCompleted=8;		// is called at the completion of the process, once
	const ProcessPaused =2;			// everytime the process is paused waiting for an input or event
	const ProcessResumed =2;		// everytime the process is resumed after paused
	
	//	node any part of the process including, events, tasks, gateways and  flow
	const NodeLaunched =2;			// node is reached but not yet evaluated, could be skipped
	const NodeInitialized =3;		// node is initialized
	const NodeSkipped =4;			// if conditions are not met, node is skipped
	const NodeStarted =5;			// node just started
	const NodeRun =6;
	const NodeCompleted =7;			// node is completed
	const NodeTerminated =8;			// Because case is Compeleted or other condition exhausted
	
	const CaseLoaded=9;
	const CaseSaving=10;
	const CaseSaved=11;
	const CaseItemSaving=12;
	const CaseItemSaved=13;
	const Error=99;
	 
}

