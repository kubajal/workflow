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

/*
 * 
 * 
 * Messages
 * 
 * start event with message
 *  process.register will subscribe these events
 *  caseItem.start will subscribe these events
 * 
 * HandleMessage(
 * 
 * 
 * 
 * 
 */

namespace OmniFlow;


/**
 * Description of EventEngine
 *
 * @author ralph
 */
class EventEngine
 {
 	public static function Check($duration=5)
 	{
            try {
 		Context::Log(LOG,'Checking Timers');
 		$timers=OmniModel::getInstance()->getTimers($duration);
 		
 		Context::Log(INFO,'EventManger::Check'.var_export($timers,true));
 		$counter=0;	
 		foreach($timers as $timer)
 		{
                    /* 3 types
                     *  Case Item
                     *  Process Item
                     *  Notification
                     * */
// 			var_dump($timer);
                    $counter++;
                    $type=$timer['type'];
                    switch ($type)
                    {
                        case 'Case Item':
                            /*
                             * Execute the item which will reset the timer 
                             */
                            $caseId=$timer['caseId'];
                            $id=$timer['id'];
                            if ($caseId!==null)
                            {
                                WFCase\WFCaseItemStatus::$Notes='timer is due';

                                $item=CaseSvc::LoadCaseItem($caseId, $id);
                                $case=TaskSvc::TimerDue($item);
                            }
                            break;
                        case 'Process Item':
                            $processId=$timer['processId'];
                            $id=$timer['id'];
                            if ($processId!==null)
                            {
                                WFCase\WFCaseItemStatus::$Notes='timer is due starting a process';
                                // need to reset the timer
                        	$case=ProcessSvc::StartProcess($processId,$id);                            
                                // todo update process item with new timer
/*                                $proc=$case->proc;
                                $processItem=$proc->getItemById($id);
                                $dueDate=  OmniFlow\EventEngine::getDueDate($processItem);
                                $caseItem->timerDue=$dueDate;
                                OmniFlow\Context::Log(\OmniFlow\Context::INFO,"Event Start: setting timer due date: $dueDate"); */
                            }
                            break;
                    }
 		}
            }
            catch(Exception $exc)
            {
                Context::Exception($ex);
            }
            return $counter;
 	}

        public static function getDueDateForDelay($delay)
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
 	public static function getDueDate(BPMN\ProcessItem $item)
 	{
            Context::Log(INFO, "getDueDate $item->timerType");
 		if ($item->timerType===null )
                    return null;
 		else if ($item->timerType=="duration")
 		{
                    return self::getDueDateForDelay($item->timer);
 		}
 		else
 		{
// 			require_once __DIR__.'..\lib\cron\CronExpression.php';
 				
 	
 			//			date_default_timezone_set('America/Toronto');
                        
                        try {
 	
 			$cron = \Cron\CronExpression::factory($item->timer);
 			$dueDate=$cron->getNextRunDate()->format('Y-m-d H:i:s');
                        }
                        catch (Exception $exc)
                        {
                            return null;
                        }
 				
 	
 			return $dueDate;
 		}
 	}
 	
 	
 }

