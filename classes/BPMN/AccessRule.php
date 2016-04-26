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

abstract class AccessPrivilege
{
    const VIEW='V';
    const START='S';
    const PERFORM='P';
    const ASSIGN='A';
    const MONITOR='M';
}

/**
 * Description of AccessRule
 * 
 * [Allow|Restrict]  [User Expression] to [Privilege] on [Object type] for [scope]
 *
 * @author ralph
 */
class AccessRule extends \OmniFlow\WFObject
{
 var $id;
 var $userGroup;        // user group name
 var $actor;
 var $privilege;    
 var $nodeId;      // null for process 
 var $asActor;         // defines a new Role for the user
 var $workScopeType;
 var $workScopeVariable;
 var $canChange;    // Can the Assignee Change the Assignment by Release or Re-Assign
 
 public static function getRule(\OmniFlow\BPMN\ProcessItem $processItem)
 {
        $user=  \OmniFlow\Context::getuser();
        
        foreach($processItem->proc->accessRules as $rule) {
            if (($rule->nodeId == $processItem->id) || ($rule->nodeId == '__Process__')) {
                
                if ($user->isMemberOf($rule->userGroup,$rule->workScopeType,$rule->workScope))
                {
                    if ($rule->asActor!=='')
                    {
                        $user->asCaseActor=$rule->asActor;
                    }
                    return $rule;
                }
            }
        }
        return null;
 }
 public static function getAuthorizedGroups(\OmniFlow\BPMN\ProcessItem $processItem)
 {
//   $authorizedGroups=  BPMN\NotificationRule::getAuthorizedGroups($item);
     $authorizedGroups=",";
        foreach($processItem->proc->accessRules as $rule) {
            if (($rule->nodeId == $processItem->id) || ($rule->nodeId == '__Process__'))
                {
                $authorizedGroups.=$rule->userGroup.',';
            }
        }
        
        return $authorizedGroups;
     
 }
 public static function Validate(\OmniFlow\BPMN\ProcessItem $processItem)
 {
        foreach($processItem->proc->accessRules as $rule) {
            if (($rule->nodeId == $processItem->id) || ($rule->nodeId == '__Process__'))
                {
                return true;
            }
        }
        
        \OmniFlow\Context::Log(\OmniFlow\VALIDATION_ERROR, "No Access Rules defined for {$processItem->label}");        
        return false;
     
 }

 /*
  * Calculate Assignment for a task done on the start of the task; to allow users to access it
  * 
  */
 public static function AssignTask(\OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
//     if ($processItem->isEvent()) // no need for start event
//         return true;
     
        foreach($processItem->proc->accessRules as $rule) {
            if (($rule->nodeId == $processItem->id) || ($rule->nodeId == '__Process__'))
                {

                if ($rule->actor !=='') {  
                    
                    $users=  \OmniFlow\WFCase\Assignment::getUsersForActor($caseItem, $rule->actor);
                    foreach($users as $user)
                    {
                        $rule->CreateAssignment($caseItem,$user,$rule->actor);

                    }

                }
                else {
                    $rule->CreateAssignment($caseItem);
     
                }
            }
        }
        return true;
}
 /*
  *     create a new Assignment record for this rule
  */
 public function CreateAssignment(\OmniFlow\WFCase\WFCaseItem $caseItem,$userId=null,$asActor=null)
 {
     $a=new \OmniFlow\WFCase\Assignment($caseItem);
     
     
     $a->caseId=$caseItem->caseId;
     $a->caseItemId=$caseItem->id;
     $a->privilege=$this->privilege;
     $a->canChange=$this->canChange;
     if ($userId!==null) {
         $a->userId=$userId;
         $a->userName= \OmniFlow\WFUser\User::getUserById($userId)->name;
         $a->asActor=$asActor;
     }
     else {
        $a->actor=$this->actor;
        $a->asActor=$this->asActor;
        $a->userGroup=$this->userGroup;
        if ($this->workScopeType !=='')
        {   // calculate workscope based on case variables
            $case=$caseItem->case;
            $scopeVal=$case->GetValue($this->workScopeVariable);
            $a->workScopeType=$this->workScopeType;
            $a->workScope=$scopeVal;
        }
     }
     
     $a->insert();
     
 }
}


