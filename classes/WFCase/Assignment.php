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

namespace OmniFlow\WFCase;
/**
 * Assignment defines the various userCriteria allowed to perform an action on caseItem
 *
 *  Types 
 *  User Specific:  where userId is set
 *  Non-User Specific :     userId is null
 * Status
 * 1)   CalculateAssignment done at start of the task , 
 *                      status=Active
 * 2)   UserTake Assignment - User Select the task and start working on it
 *                  UserAssignment Status=Active 
 *                  all other D->Disabled
 * 3)   UserAssign - User Assigns another user , 
 *                  UserAssignment A->Active, 
 *                  others D->Disabled
 * 4)   Complete    - Task is complete, C->Complete , all others are D->Disabled
 * 5)   Release:    User releases the Task from Active
 *                   UserAssignment D->Disabled
 *                   Set all others to A
 * 
 * 
 * @author ralph
 * 
 * 
 *  */
Class Assignment extends \OmniFlow\WFObject
{
    var $id;
    var $userId;
    var $userName;
    var $caseId;
    var $caseItemId;
    var $actor;
    var $userGroup;
    var $workScope;
    var $workScopeType;
    var $privilege;     // P, V , A
    var $status='A';    // Active, D: Inactive , C: Completed
    var $canChange='';  // wheither the assignee can Release A or reAssign 
    var $asActor;
    
    const View = "V";
    const Perform="P";
    const Assign="A";

 public function __construct(WFCaseItem $caseItem) {
     $this->caseItemId=$caseItem->id;
     $case=$caseItem->case;
     $this->caseId=$case->caseId;
     $case->assignments[]=$this;
     
 }
 public static function NotAuthorized($processItem, $caseItem)
 {
      throw new \Exception("You are not authorized to perform this function");
     
 }
 
 // Can?
 /*
  *    canDo
  *     P   perform
  *     V
  *     T   take
  *     R
  *     
  */
 public static function getPrivileges($processItem, $caseItem)
 {
     $privileges=Array();
     $rule=self::getRule($processItem, $caseItem, "P");

     if ($rule!==null)
     {
        $privileges[]='V';      // view
        $privileges[]='P';      // perform but not take
        
        $user=  \OmniFlow\Context::getuser();
        
        if ($caseItem==null)
            $userId=null;
        else
            $userId=self::getAssignedUser($caseItem);
        
        if  ( ($userId ===null) || ($user->id===$userId) ) {
                // user can take and perform the case
            
                $privileges[]='T';   // can Take
                
                if ($rule->asActor!=='')
                    $user->asCaseActor=$rule->asActor;
                
                if ($rule->canChange==='true') 
                    $privileges[]='R';  // release
                
        } else {
                // already assigned to another
            
        }
        
     } else {
         
        $rule=self::getRule($processItem, $caseItem, "V");
        if ($rule!==null)
            $privileges[]='V';

        $rule=self::getRule($processItem, $caseItem, "A");
        if ($rule!==null)
            $privileges[]='A';
        
     }
     return $privileges;
 }         
 /* Current User Takes the Assignment 
  * 
 *      New UserAssignment
  */
    
 public static function UserTake(\OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {

        $privileges=self::getPrivileges($processItem, $caseItem);
        
        if (!in_array("T", $privileges))
        {
            if (in_array("P", $privileges)) {
               $userId=self::getAssignedUser($caseItem);
               throw new \Exception("Task is assigned to another user $userId");
            }
            else {
               throw new \Exception("You are not authorized to perform this function");
            }
        }
     
        $user=  \OmniFlow\Context::getuser();
        $userId=$user->id;
        $userName=$user->name;
        $asActor=$user->asCaseActor;
        
         \OmniFlow\Context::debug("Assignment.UserTake $userId $userName as $asActor caseItem: $caseItem.id");
        
        self::updateAssignments($caseItem, self::forGroup(),'D');
        self::newUserAssignments($caseItem, $userId,$asActor);
     
 }
 /* Current User Release the Assignment 
  * 
 *      UserAssignment: Deactivate
  */
 public static function UserRelease(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        $userId=  \OmniFlow\Context::getuser()->id;

     \OmniFlow\Context::debug("Assignment.UserRelease $userId caseItem: $caseItem.id");
        
        self::updateAssignments($caseItem, self::forUser($userid),'D');
        self::updateAssignments($caseItem, self::forGroup(),'A');
     
 }
/* Current User Assigns another User
  * 
 *      UserAssignments: Deactivate
 *      New UserAssignment
  */
 public static function AssignUser(\OmniFlow\WFCase\WFCaseItem $caseItem,$userId)
 {
     \OmniFlow\Context::debug("Assignment.AssignUser $userId caseItem: $caseItem.id");
     
        self::updateAssignments($caseItem, self::forGroup() ,'D');
        self::newUserAssignments($caseItem, $userid);
     
 }
/* Current User Completes the Assignment 
  * 
 *  Impact: Set Status of user To D
 * Also register user role
  */
 public static function UserComplete(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
     
     $user=  \OmniFlow\Context::getuser();
     $asActor=$user->asCaseActor;
     
     \OmniFlow\Context::debug("Assignment.UserComplete  $user.Id as $asActor caseItem: $caseItem.id");
     
     return self::updateAssignments($caseItem, self::forUser($userid) ,'D',$asActor);
 }
 /* task is aborted */
 
 public static function TaskComplete(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
     \OmniFlow\Context::debug("Assignment.TaskComplete $caseItem.id");
     return self::updateAssignments($caseItem,"status='A'", 'C');
 }
      
 /* Checks if current user can perform the task
  *     and logs the role
  */
 public static function CanPerform(\OmniFlow\BPMN\ProcessItem $processItem,$caseItem,$checkOnly=false)
 {
        if (\OmniFlow\Context::$batchMode)
            return true;

        $privileges=self::getPrivileges($processItem, $caseItem);
        
        if (!in_array("T", $privileges))
        {
            if ($checkOnly)
               return false;
            if (in_array("P", $privileges)) {
                
               $userId=self::getAssignedUser($caseItem);
               throw new \Exception("Task is assigned to another user $userId");
            }
            else {
               throw new \Exception("You are not authorized to perform this function");
            }
        }
        return true;
 }
 public static function CanView(\OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        if (\OmniFlow\Context::$batchMode)
            return true;
        
       $rule=self::getRule($processItem, $caseItem, "V");
        if ($rule===null)
            {
            \OmniFlow\Context::debug("Assignment:CanView Not authorized to perform this function $processItem->id $caseItem->id");
               throw new \Exception("You are not authorized to perform this function");
                
            }
        return true;
 }

public static function CanAssign(\OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        if (\OmniFlow\Context::$batchMode)
            return true;
    
       $rule=self::getRule($processItem, $caseItem, "A");
        if ($rule===null)
            {
            \OmniFlow\Context::debug("Not authorized to perform this function $processItem->id $caseItem->id");
               throw new \Exception("You are not authorized to perform this function");
                
            }
        return true;
 }

 public static function getRule(\OmniFlow\BPMN\ProcessItem $processItem,$caseItem,$privilege)
 {
        if ($processItem->isEvent() && $processItem->type=='startEvent') // start event don't have assignment yet
        {
            return \OmniFlow\BPMN\AccessRule::getRule($processItem,$privilege);
        }
        
        $case=$caseItem->case;
        $assignments=$case->assignments;
        
        foreach($assignments as $assignment) {
            if ($assignment->caseItemId == $caseItem->id) {
                
                
                if (!$assignment->checkRule($caseItem))
                    continue;

                if ($assignment->privilege===$privilege)
                {
                    if ($assignment->asActor!=='')
                    {
                        $user->asCaseActor=$assignment->asActor;
                    }
                    return $assignment;
                }
           }
        }
        return \OmniFlow\BPMN\AccessRule::getRule($processItem,$privilege);
//        return null;     
 }
 public static function getAssignedUser(OmniFlow\WFCase\WFCaseItem $caseItem)
 {
            $case=$caseItem->case;
            $assigns=$case->assignments;
            $assignTo='';
            
            foreach($assigns as $assign) {
                if ($assign->caseItemId == $caseItem->id) {
                   if (($assign->status=='A')) {
                        if ($assign->userId!==null) {
                            return $assign->userId;
                        }
                    }
                }
            }
            return null;

 }
 public static function getAssignmentForCaseItem(OmniFlow\WFCase\WFCaseItem $caseItem)
 {
            $case=$caseItem->case;
            $assigns=$case->assignments;
            $assignTo='';
            
            foreach($assigns as $assign) {
                if ($assign->caseItemId == $caseItem->id) {
                   if (($assign->status=='A') || ($assign->status=='C')) {
                    if ($assign->userId!==null) {
                    
                        $assignTo='User:'.$assign->userName;
                        break;
                    }
                    else
                        $assignTo.='Group:'.$assign->userGroup.' ';
                    }
                }
            }
            return $assignTo;

 }
/*
 *  check if rule is applied here 
 */

public static function getUsersForActor(\OmniFlow\WFCase\WFCaseItem $caseItem,$actor)
{
        $users=Array();
        $assignments=$caseItem->case->assignments;
        foreach($assignments as $assignment) {
            if (($actor===$assignment->asActor) && ($assignment->userId !==null))
            {
                $users[]=$assignment->userId;
            }
        }
        
     \OmniFlow\Context::debug("Assignment.getUsersForActor actor $actor caseItem: $caseItem.id users:".print_r($users,true));
        
        return $users;
}
/*
 *  Check if this rule applies
 */
public function checkRule(\OmniFlow\WFCase\WFCaseItem $caseItem)
{
     \OmniFlow\Context::debug("Assignment.checkRule caseItem: $caseItem.id");
    // if rule is based on an actor check it
    $user= \OmniFlow\Context::getUser();
    
    if ($this->actor!=='' && $this->actor!==null)
    {
        $users=self::getUsersForActor($caseItem, $this->actor);
        
        if (in_array($user->id, $users))
                return true;
        else
                return false;
    }
    
    if ($user->id === $this->userId)
        return true;
    
    if ($user->isMemberOf($this->userGroup,$this->workScopeType,$this->workScope))
            return true;
    
    return false;
    
}
private static function forUser($userId=null) {
    if ($userId==null)
        return "userId is not null";
    else
        return "userId='$userId'";
}
private static function forGroup() {
        return "userId is null";
    
}

private static function updateAssignments($caseItem,$condition,$newStatus='A',$asActor='')
{
     \OmniFlow\Context::debug("Assignment.updateAssignments $caseItem.id $condition $newStatus $asActor");
    
    $model=new \OmniFlow\AssignmentModel();
    $model->updateAssignments($caseItem,$condition,$newStatus,$asActor);
}
private static function newUserAssignments($caseItem,$userid,$asActor)
{
     \OmniFlow\Context::debug("Assignment.newUserAssignments $caseItem.id $userId $asActor");
    
        $a=new Assignment($caseItem);
        $a->userId=$userid;
        $a->userName= \OmniFlow\WFUser\User::getUserById($userid)->name;
        $a->caseId=$caseItem->case->caseId;
        $a->caseItemId=$caseItem->id;
        $a->privilege='P';
        $a->status='A';
        $a->asActor=$asActor;
        $a->insert();
        return $a;
}
public function insert()
{
    $model=new \OmniFlow\AssignmentModel();
    $model->insert($this);
}
public function update()
{
    $model=new \OmniFlow\AssignmentModel();
    $model->update($this);
}

}

