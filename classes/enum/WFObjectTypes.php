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

namespace OmniFlow\enum;

/**
 * Description of WFObjectTypes
 *
 * @author ralph
 */
abstract class WFObjectTypes
{

        const	task="task";
        const	userTask="userTask";
	const	serviceTask="serviceTask";
	const	receiveTask="receiveTask";
	const	sendTask="sendTask";
	const	scriptTask="scriptTask";
	const	manualTask="manualTask";
		
	const	startEvent="startEvent";
	const	endEvent="endEvent";
	const	intermediateCatchEvent="intermediateCatchEvent";
	const	intermediateThrowEvent="intermediateThrowEvent";
	const	messageEvent="messageEvent";
				
	const	exclusiveGateway="exclusiveGateway";
	const	inclusiveGateway="inclusiveGateway";
	const	parallelGateway="parallelGateway";
        const   eventBasedGateway="eventBasedGateway";
        const   complexGateway="complexGateway";
        const	messageFlow="messageFlow";
	const	sequenceFlow="sequenceFlow";
}
