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
 * Description of TimerEngine
 *
 * @author ralph
 */
class TimerEngine 
{
    const SERVER_URL='workflow.omnibuilder.com';
    
    var $server;
    /*
     *  first time registration 
     */
    public function registerClient()
    {
        // todo generate client key
        
        $serverKey=$server->registerClient($clientKey);
        // todo save server and client key
        
    }
    public function unregisterClient()
    {
        // todo generate client key
        
        $server->unregisterClient($serverKey,$clientKey);
        // todo save server and client key
        
    }
    /*
     * receive notification from the timer server
     * returns the nextDueDate
     */
    public static function TimerDue($serverKey,$clientKey,$timerId)
    {
        
    }
    /*
     * Register with server a new timer
     * 
     */
    public function RegisterTimer($timerId,$dueDate)
    {
        $server->register($serverKey,$clientKey,$timerId,$dueDate);
    }
    public function unRegisterTimer($timerId)
    {
        $server->register($serverKey,$clientKey,$timerId);
    }
    /*
     * returns a next dueDate
     */
    public static function getNextDueDate() 
    {
        
    }
    /*
     * returns a list of due timers
     */
    public static function CheckTimers()
    {
            Context::Log(LOG,'Checking Timers');
            $timers=OmniModel::getInstance()->getTimers($duration);

            Context::Log(INFO,'EventManger::Check'.var_export($timers,true));

            foreach($timers as $timer)
            {
// 			var_dump($timer);
                    $caseId=$timer['caseId'];
                    $id=$timer['id'];
                    $item=CaseSvc::LoadCaseItem($caseId, $id);
                    $case=TaskSvc::TimerDue($item);
            }

    }

    
    public static function calculateDueDate($delay,$time=null)
    {
        /*
         * duration is in format minute hour day month year
         */
        $arr=explode(" ",$delay);
        if (count($arr)<2)
        {
             Context::Error("Invalid Timer format for duration, must be at least 3 fields of minute hour day year - $delay ");
                return null;
        }
        $i=0;
        $hours=0;
        $minutes=5;
        $seconds=0;
        $days=0;
        $months=0;
        $years=0;
        Context::Log(INFO, "getDueDate for a timer type of duration $delay array ".print_r($arr,true));
        foreach($arr as $entry)
        {
                if (!ctype_digit($entry))
                {
                        Context::Error("Invalid entry # $i - must be an integer '$entry'- $delay");
                        return null;
                }
                Context::Log(INFO, "getDueDate entry $entry - i: $i");
                if ($entry==" "|| $entry=="")
                        continue;
                switch($i)
                {

                        case 0:
                                $minutes=$entry;
                                break;
                        case 1:
                                $hours=$entry;
                                break;
                        case 2:
                                $days=$entry;
                                break;
                        case 3:
                                $months= $entry;
                                break;
                        case 4:
                                $years= $entry;
                                break;
                }
                $i++;
        }
                /*
                 echo date(DATE_ATOM,
                 mktime ([ int $hour = date("H") [, int $minute = date("i") [, int $second = date("s") [, int $month = date("n") [, int $day = date("j") [, int $year = date("Y")

                 Hour,Minute,Second , Month , Day , Year

                 echo date(DATE_ATOM,
                 mktime(date("H"), date("i"), date("s"), date("n")  , date("j"), date("Y")));
                 */

                $dueDate=date(DATE_ATOM,
                                mktime(date("H") + $hours,
                                                date("i") + $minutes,
                                                date("s") ,
                                                date("n") + $months ,
                                                date("j") + $days,
                                                date("Y") + $years ));
                Context::Log(INFO,'DueDate='.$dueDate." h: $hours m: $minutes month: $months days $days years $years");
                return $dueDate;            
    }
}
/*
 *  remote Class
 * 
 *      send client->Server
 * 
 *          serverKey=registerClient(clientKey)
 *          unregisterClient(serverKey,clientKey)
 *          registerTimer(serverKey,clientKey,timerId,dueDate)
 *          unregisterTimer(serverKey,clientKey,timerId)
 * 
 *      receive server->client
 * 
 *          timerDue(serverKey,clientKey,timerId)
 *          
 *          
 */
class TimerServer
{
    public function RegisterClient($clientKey)
    {
        
    }
    public function UnRegisterClient($serverKey,$clientKey)
    {
        
    }
    /*
     * Register with server a new timer
     * 
     */
    public function RegisterTimer($serverKey,$clientKey,$timerId,$dueDate)
    {
    }
    public function UnRegisterTimer($serverKey,$clientKey,$timerId)
    {
    }
    public function Check()
    {
        
    }
}
