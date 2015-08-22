<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 * 
 */

namespace OmniFlow;


/**
 * 

 * @author ralph
 */
class NotificationRule extends WFObject
{
 var $id;
 var $userGroup;        // user group name
 var $user;             // user id
 var $notificationEvent;        // Completion of Task
                                // Completion of Event
 var $nodeId;      // null for process 
 var $condition;    // null 
}


