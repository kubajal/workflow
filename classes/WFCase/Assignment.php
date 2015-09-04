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
    var $caseId;
    var $caseItemId;
    var $actor;
    var $userGroup;
    var $workScope;
    var $workScopeType;
    var $privilege;    
    var $status='A';
    var $asActor;

 public function __construct(WFCaseItem $caseItem) {
     $this->caseItemId=$caseItem->id;
     $case=$caseItem->case;
     $this->caseId=$case->caseId;
     $case->assignments[]=$this;
     
 }
 /* Current User Takes the Assignment 
  * 
 *      New UserAssignment
  */
    
 public static function UserTake(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        $user=  \OmniFlow\Context::getuser();
        $userId=$user->id;
        $asActor=$user->asCaseActor;
        
        self::updateAssignments($caseItem, self::forGroup(),false);
        self::newUserAssignments($caseItem, $userId,$asActor);
     
 }
 /* Current User Release the Assignment 
  * 
 *      UserAssignment: Deactivate
  */
 public static function UserRelease(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        $userId=  \OmniFlow\Context::getuser()->id;
        
        self::updateAssignments($caseItem, self::forUser($userid),false);
        self::updateAssignments($caseItem, self::forGroup(),true);
     
 }
/* Current User Assigns another User
  * 
 *      UserAssignments: Deactivate
 *      New UserAssignment
  */
 public static function AssignUser(\OmniFlow\WFCase\WFCaseItem $caseItem,$userId)
 {
        self::updateAssignments($caseItem, self::forGroup() ,false);
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
     return self::updateAssignments($caseItem, self::forUser($userid) ,false,$asActor);
 }
 /* task is aborted */
 
 public static function TaskComplete(\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
     return self::updateAssignments($caseItem,"", false);
 }
      
 /* Checks if current user can perform the task
  *     and logs the role
  */
 public static function CanPerform(\OmniFlow\BPMN\ProcessItem $processItem,\OmniFlow\WFCase\WFCaseItem $caseItem)
 {
        if ($processItem->isEvent()) // start event don't have assignment yet
        {
            return \OmniFlow\BPMN\AccessRule::CanPerform($processItem);
        }
        
        $case=$caseItem->case;
        $assignments=$case->assignments;
        
        foreach($assignments as $assignment) {
            if ($assignment->caseItemId == $caseItem->id) {
                
                $result=$assignment->checkRule($caseItem);
                if ($result==true)
                {
                    if ($assignment->asActor!=='')
                    {
                        $user->asCaseActor=$assignment->asActor;
                    }
                    return true;
                }
           }
        }
        return false;     

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
        return $users;
}
public function checkRule(\OmniFlow\WFCase\WFCaseItem $caseItem)
{
    // if rule is based on an actor check it
    $user= \OmniFlow\Context::getUser();
    
    if ($this->actor!=='')
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

private static function updateAssignments($caseItem,$condition,$activate=true,$asActor='')
{
    $model=new \OmniFlow\AssignmentModel();
    $model->updateAssignments($caseItem,$condition,$activate,$asActor);
}
private static function newUserAssignments($caseItem,$userid,$asActor)
{
        $a=new Assignment($caseItem);
        $a->userId=$userid;
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

