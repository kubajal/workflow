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
class AccessRule extends WFObject
{
 var $id;
 var $allowRestrict;    // A: allow, R: Restrict
 var $userGroup;        // user group name
 var $privilege;    
 var $nodeId;      // null for process 
 var $asRole;         // defines a new Role for the user
 var $condition;    // null 
}


