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
 * Description of DesignerSteps
 *
 * @author ralph
 */
class DesignerSteps {
    
    
/*
 * build a list of design steps
 */
public function GetSteps(Process $proc)
{
    $steps=Array();
    /*
     * 
0.  What is implementable
        (only if pools are being used)
1.  Data Elements
2.  Conditions flow
3.  Timers
        Check for timers?
4.  Messages
5.  Signals
6.  Forms
7.  Access Rules
8.  Notifications
     */
}
    
public function getImplementable($proc)
{
        if (count($proc->pools)>0)
        {
            
        }
        else
        {
            return null;
        }
}
public function getDataElements($proc)
{
    $deTree=  \OmniFlow\DataManager::getMeta($proc);
    
    if (count($deTree)==0)
    {
        return Array("title"=>"Data Elements",
            "Action"=>"Define the data elements that are required for the process");
    }
    else
        return null;
}
public function getCondition($proc)
{
    $deTree=  \OmniFlow\DataManager::getMeta($proc);
    
    if (count($deTree)==0)
    {
        return Array("title"=>"Flow Conditions",
            "Action"=>"Define the flow conditions");
    }
    else
        return null;
}
public function getEvents($proc)
{
    foreach($proc->items as $item)
    {
        switch($item->subType)
        {
            case 'timer':
                break;
            case 'message':
                break;
            case 'signal':
                break;
        }
    }
}
}
/*
 *           	foreach($this->accessRules as $ar)
			{
			$iArr=$ar->__toArray();
			$accessRules[]=$iArr;
			}

		foreach($this->pools as $sub)
			{
			$iArr=$sub->__toArray();
			$subs[]=$iArr;
			}
		foreach($this->items as $item)
			{
			$iArr=$item->__toArray();
			$items[]=$iArr;
			}
		foreach($this->actors as $actor)
			{
			$actorArr=$actor->__toArray();
			$actors[]=$actorArr;
			}
          	foreach($this->notificationRules as $ar)
			{
			$iArr=$ar->__toArray();
			$notificationRules[]=$iArr;
			}

 */