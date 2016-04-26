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


namespace OmniFlow;

/**
 * MessageEngine    Handles Message and Signals
 *
 * @author ralph
 */
class MessageEngine
 {
        public static function Send($message,$data)
        {
            // 1. place in queue
            QueueEngine::addMessage($message, $data);
        }
        public static function SendSignal($signal,$data)
        {
            // 1. place in queue
            self::ProcessSignal($signal, $data);
        }

        /*  called by external sources
         * 
         * 
         */
  	public static function Recieve($data)
 	{
            QueueEngine::addMessage($message, $data);
 	}
        /*
         *  Process the Mesage
         *  invoked after queue
         *  this does not include MessageFlow messages
         * 
         */
        /*
         *  a Message is issued by an external source that need to be handled
         * 
         *  1. Locate message respondent
         *  2. Fire the message 
         *      a) if start event - start the event
         *      b) invoke the caseItem
         */
  	public static function Process($messageName,$data)
 	{
            Context::Debug("MessageEngine:Handle Message: '$messageName' values: ".var_export($data,true));
            $results=OmniModel::getInstance()->getMessageHandler($messageName);
            
            Context::getInstance()->inputData=$data;
            
            foreach($results as $result)
            {
                $src=$result['source'];
                if ($src=='Process Item')
                {
                    $procId=$result['processId'];
                    $procNodeId=$result['processNodeId'];
                    Context::Debug("MessageEngine:Handle Message $messageName invoking a new process $procName - $procNodeId");
                    WFCase\WFCaseItemStatus::$Notes="Message '$messageName' is received-start process";
                    

                    $case=ProcessSvc::StartProcess($procId,$procNodeId);
                    
                    $item=$case->getItemByProcessId($procNodeId);
                    TaskSvc::ReceiveMessage($item,$messageName,$data);                    
                }

                if ($src=='Case Item')
                {
//Todo: Bug : should not issue message to CaseItem Start Event , only to ProcessItem
                    if (\OmniFlow\BPMN\ProcessItem::isSenderType($result['type']))
                        continue;
                    
                    $caseId=$result['caseId'];
                    $id=$result['id'];
                    Context::Debug("MessageEngine:Handle Message $messageName invoking a current case $caseId - $id");
                    Context::Debug("result:".print_r($result,true));
                    WFCase\WFCaseItemStatus::$Notes="Message '$messageName' is received";
                    $item=CaseSvc::LoadCaseItem($caseId, $id);
                    TaskSvc::ReceiveMessage($item,$messageName,$data);                    
                }
            }
 	}
  	public static function ProcessSignal($signalName,$data)
 	{
            Context::Debug("EventEngine:Handle Signal $signalName".var_export($data,true));
            $results=OmniModel::getInstance()->getSignalHandler($signalName);
            
            foreach($results as $result)
            {
                $src=$result['source'];
                if ($src=='Process Item')
                {
                    $procId=$result['processId'];
                    $procNodeId=$result['processNodeId'];
                    Context::Debug("EventEngine:Handle signal $signalName invoking a new process $procName - $procNodeId");
                    WFCase\WFCaseItemStatus::$Notes="Signal '$signalName' is received-start process";
                    
                    $case=ProcessSvc::StartProcess($procId,$procNodeId);
                    $item=$case->getItemByProcessId($procNodeId);
                    TaskSvc::ReceiveSignal($item,$signalName,$data);                    
                    
                }

                if ($src=='Case Item')
                {

                    if (\OmniFlow\BPMN\ProcessItem::isSenderType($result['type']))
                        continue;
                    
                    $caseId=$result['caseId'];
                    $id=$result['id'];
                    Context::Debug("EventEngine:Handle Signal $signalName invoking a current case $caseId - $id");
                    Context::Debug("result:".print_r($result,true));
                    WFCase\WFCaseItemStatus::$Notes="Signal '$signalName' is received";
                    $item=CaseSvc::LoadCaseItem($caseId, $id);
                    TaskSvc::ReceiveSignal($item,$signalName,$data);                    
                }
            }
 	}
 	
 }

