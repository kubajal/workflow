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


/**
 * This is the NOtification Rules
 *  generates Notification objects

 * @author ralph
 */
class NotificationRule extends \OmniFlow\WFObject
{
 var $id;
 var $userType;   // User , Group, Actor , assigN users
 var $userId;             // user id
 var $userGroup;        // user group name
 var $actor;
 
 var $nodeId;      // null for process 
 
 var $eventType;     //  Start , Completion , Assigned , Unassigned work, Error  
 var $delay;
 
 var $cancelIf;      // Completed , Assigned
 var $template; //         template    name of template to use
 var $repeatAfter; //        repeatAfter (number of days notification will repeat)
 var $repeatCount; //  number of times to repeat

public static function getRule(Process $process,$ruleId)
{
    foreach($process->notificationRules as $rule)
    {
        if ($rule->id===$ruleId)
            return $rule;
    }
    return null;
}
/*
 *  Issued when
 *              Item Started, right after assigned
 *              Item Completed
 *              Item Assigned
 *              Item Unassigned
 *              Any Item Error
 *              Process/Case Start
 *              Process Completed
 *  It decides what type of notification to Create
 */
 public static function ChekNotificationsForItem($event,
         \OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
     
        $case=$caseItem->case;
        $caseId=$case->caseId;
        
        $notificationRules=$processItem->proc->notificationRules;
        
        foreach($notificationRules as $rule)
        {
            $noteType=null;
            // Check for creattion
            if ($event==\OmniFlow\enum\NotificationTypes::Error
                        && $rule->eventType =='E' ) {
                
                    $noteType='Process';
                        
            }
            
            if (($rule->nodeId == $processItem->id) || ($rule->nodeId == '__Process__'))
            {
                if ($event==\OmniFlow\enum\NotificationTypes::NodeStarted) {
                       
                    if ($rule->eventType =='S' ) 
                        $noteType='Item';
                    if ($rule->cancelIf =='S' ) 
                        $noteType='Cancel';
                        
                }
                if ($event==\OmniFlow\enum\NotificationTypes::NodeAssigned) {
                        
                    if ($rule->eventType =='A' ) 
                        $noteType='Item';
                    if ($rule->cancelIf =='A' ) 
                        $noteType='Cancel';
                }
                if ($event==\OmniFlow\enum\NotificationTypes::NodeUnAssigned) {
                        
                    if ($rule->eventType =='U' ) 
                        $noteType='Item';
                    if ($rule->cancelIf =='U' ) 
                        $noteType='Cancel';
                }
                if ($event==\OmniFlow\enum\NotificationTypes::NodeCompleted) {

                    if ($rule->eventType =='C' ) 
                        $noteType='Item';
                    if ($rule->cancelIf =='C' ) 
                        $noteType='Cancel';
                }
            }
            
            if ($noteType==='Cancel')
            {
                $notes=$case->notifications;
                foreach($notes as $note)
                {
                    if ($note->ruleId==$rule->id)
                    {
                        $note->cancel();
                    }
                }
            }
            elseif ($noteType!==null)
            {
                $rule->createNotification($noteType,$processItem,$caseItem);
            }
            
            
        }
     
 }
/*
  * Calculate Notificaiton for a task
  */
 private function createNotification($noteType,
            \OmniFlow\BPMN\ProcessItem $processItem,
            \OmniFlow\WFCase\WFCaseItem $caseItem)
 {
                $note=new \OmniFlow\WFCase\Notification($caseItem->case);
                $note->userType=$this->userType;
                /* todo Calculate user for assignment */
                $note->actor;

                $note->userId=$this->userId;
                $note->userGroup=$this->userGroup;

                $note->caseId=$caseItem->case->caseId;
                if ($noteType=='Item')
                    $note->caseItemId=$caseItem->id;    

                $note->eventType=$this->eventType;
                
                $note->eventDate=date('Y-m-d H:i:s');
               
                        
                $note->ruleId=$this->id;
                
                $note->dueOn=null; 
                if ($this->delay!=='') {
                    $note->dueOn=  \OmniFlow\EventEngine::getDueDateForDelay($this->delay);
                }

                if ($note->CheckToIssue($caseItem))
                    $note->status=4;
                else 
                    $note->status=1;

                $note->insert();
 }
}
