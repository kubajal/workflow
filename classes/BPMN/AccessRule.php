<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 * 
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
 var $condition;    // null 
 
 public static function CanPerform(\OmniFlow\BPMN\ProcessItem $processItem)
 {
        $user=  \OmniFlow\Context::getuser();
        
        foreach($processItem->proc->accessRules as $rule) {
            if ($rule->nodeId == $processItem->id) {
                
                if ($user->isMemberOf($rule->userGroup,$rule->workScopeType,$rule->workScope))
                {
                    if ($rule->asActor!=='')
                    {
                        $user->asCaseActor=$rule->asActor;
                    }
                    return true;
                }
            }
        }
        return false;
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
  * Calculate Assignment for a task
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
                        $rule->CreateAssignment($caseItem,$user);

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
 public function CreateAssignment(\OmniFlow\WFCase\WFCaseItem $caseItem,$userId=null)
 {
     $a=new \OmniFlow\WFCase\Assignment($caseItem);
     
     
     $a->caseId=$caseItem->caseId;
     $a->caseItemId=$caseItem->id;
     $a->privilege=$this->privilege;
     
     if ($userId!==null) {
         $a->userId=$userId;
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


