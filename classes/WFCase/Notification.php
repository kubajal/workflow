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

 * 
 * 
 * @author ralph
 * 
 * 
 *  */
Class Notification extends \OmniFlow\WFObject
{
    var $id;
    var $ruleId;

    var $userType;   // User , Group, Actor , assigN users
    var $userId;
    var $userGroup;
    var $actor;
    
    var $caseId;
    var $caseItemId;    // if null valid for entire case
    
    var $eventType;     //  Start , Completion , Unassigned work, Error  
    var $eventDate;
    var $dueOn;
    
    var $cancelDate;
//    var $cancelIf;      // Completed , Assigned not needed saved in the rule
    var $status;
    var $repeatSequence;    // nTh time this being issued
    
    /* Life Cycle 
     * 
     *  1.  Source Event occured, Notification Created
     *  2.  waiting for delay to take place
     *  3.  Cancelled before issued
     *  4.  Issued  
     *  5.  Cencelled after Issued
     */
public function getEventTitle()
{
    switch ($this->eventType)
    {
        case 'S':
            return 'Started';
        case 'C':
            return 'Completed';
        case 'U': return 'Unassigned';
        case 'E': return 'Error';
    }    //  Start , Completion , Unassigned work, Error  
    
}

 public function __construct(WFCase $case,WFCaseItem $caseItem) {

     $this->caseItemId=$caseItem->id;
     $this->caseId=$case->caseId;
     $case->notifications[]=$this;
     
 }
public function CheckToIssue(WFCaseItem $caseItem)
{
    if ($this->dueOn===null)
        return $this->Issue ($caseItem);
    else {
        return false;
    }
}
function getUsers(WFCaseItem $caseItem)
{
    $users=Array();
    //
    if ($this->userType==='U' || $this->userType==='A') {
    // get by id
     $users[] = get_user_by( 'id', $this->userId ); 
    } elseif ($this->userType==='G') {
    // by userGroup
     $users = get_users( "orderby=nicename&role=$this->userGroup" );
    } elseif ($this->userType==='A') {
     // by Actor
     $users = Assignment::getUsersForActor($caseItem,$this->actor);
    }
    return $users;
}
public function Issue(WFCaseItem $caseItem)
{
    $users=$this->getUsers($caseItem);
    $rule=  \OmniFlow\BPMN\NotificationRule::getRule($caseItem->case->proc, $this->ruleId);
    $templateName=$rule->template;
    $script=self::loadTemplate($templateName);
    
    foreach($users as $user) {
        
       $eng=\OmniFlow\ScriptEngine::Evaluate($script, $caseItem->case,$caseItem,$this,$user);
       $subject=$eng->vars['subject'];
       $message=$eng->vars['message'];
       $email=new \OmniFlow\EmailEngine();
       $email->subject=$subject;
       $email->message=$message;
       $email->to=$user->data->user_email;
       $ret=$email->send();
       echo "sent email $subject to $email->to ";
       \OmniFlow\Context::debug("sent email to $email->to subj: $email-subject text: $email->message");
    }
    return true;
            
}
public function cancel()
{
    $this->status=3;
    $this->update();
}
public function insert()
{
    $model=new \OmniFlow\NotificationModel();
    $model->insert($this);
}
public function update()
{
    $model=new \OmniFlow\NotificationModel();
    $model->update($this);
}
public static function loadTemplate($templateName)
{
    if ($templateName==='')
        $templateName='default';
    
    $fileName=  \OmniFlow\Context::getInstance()->processPath.'/'.$templateName.".template";
    
    if (!file_exists($fileName))
        return;
    $text= file_get_contents($fileName);

    return $text;
}
}

