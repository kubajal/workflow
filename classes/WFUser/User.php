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

namespace OmniFlow\WFUser;
/**
 * Description of User
 *
 * @author ralph
 */

class User extends \OmniFlow\WFObject
{
    	var $id=null;
	var $name;
	var $email;
	var $clientId;	/* for multi tenants implementation */
        var $memberships=array();
        var $userCapabilities=array();
        var $roles=array();
        var $asCaseActor;

    public function getMemberships()
    {
        return $this->memberships;
    }
    public function isAdmin()
    {
        foreach($this->roles as $role)
        {
            if ($role==='administrator')
            {
                return true;
            }
        }
        return false;
    }
    public function isMemberOf($userGroup,$workScopeType=null,$workScope=null)
    {
        // ToDo:
        if ($this->isAdmin())
            return true;
        foreach($this->memberships as $membership)
        {
            if ($membership->userGroup ===$userGroup)
            {
                if ($workScopeType===null)
                    return true;
                else {
                    if (($membership->workScopeType===$workScopeType) && 
                        ($membership->workScope ===$workScope))
                        return true;
                }
                    
            }
        }
        return false;
    }
    public function can($capability)
    {
        $caps=$this->userCapabilities;
        
        if (isset($caps[$capability]))
        {
            return $caps[$capability];
        }
        else
        {
            return false;
        }
    }

    public function addCapability($capability)
    {
        $this->userCapabilities[$capability]=true;
    }
    
    public function isLoggedIn()
    {
        if ($this->id!==null)
            return true;
        else
            return false;
    }
}

