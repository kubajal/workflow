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
 * Description of Participant
 * 
 *  used only as a target to MessageFlow
 * 
 *  
  <bpmn2:collaboration id="Collaboration_0vpkhfc">
    <bpmn2:participant id="Participant_0ufgbno" processRef="Process_1" />
    <bpmn2:participant id="Participant_1659ivr" processRef="Process_0a0hex8" />
    <bpmn2:messageFlow id="MessageFlow_1h2wx9h" sourceRef="SendTask_1eshlev" targetRef="Participant_1659ivr" />
    <bpmn2:messageFlow id="MessageFlow_0voa3cz" sourceRef="EndEvent_10mwvz6" targetRef="IntermediateCatchEvent_0whqx4b" />
  </bpmn2:collaboration>
 *
 * @author ralph
 */
class Participant extends Node
{
    var $processRef;
    
    public function Init()
    {
        foreach($this->proc->pools as $pool)
        {
            if ($pool->id==$this->processRef)
                $this->pool=$pool;
        }
    }
    function loadFromXML($node)
    {
            parent::loadFromXML($node);
            $attr=$node->attributes();
            $ref=$attr['processRef']->__ToString();
            $this->processRef=$ref;
            OmniFlow\Context::debug("Participant loaded from xml ProcessRef = $ref");
    }

    public function requiresAccessRules()
    {
        return false;
    }
    public function describe(\OmniFlow\Describer $t)
    {
            $t->title="Participant";
            $t->desc="A business entity or a business role (e.g., a buyer or a seller) that is involved in the 
                business process. <br />
                If Pools are used, then a Participant would be associated with one Pool. 
                <br />In a Collaboration, Participants are informally known as Pools
                    ";
    }     
}
