<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
 	public static function Check($duration=1)
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
 			$case=TaskSvc::Complete($item);
 		}
 		
 	}
 	public function RegisterProcessEvent(Process $proc,ProcessItem $item)
 	{
 		
 	}
 	public function RegisterCaseEvent(WFCase\WFCase $case,WFCase\WFCaseItem $citem)
 	{
 		
 	}
        /*
         *  a Message is issued by an external source that need to be handled
         * 
         *  1. Locate message respondent
         *  2. Fire the message 
         *      a) if start event - start the event
         *      b) invoke the caseItem
         */
  	public static function HandleMessage($messageName,$data)
 	{
            Context::Debug("EventEngine:Handle Message $messageName".var_export($data,true));
            $results=OmniModel::getInstance()->getMessageHandler($messageName);
            
            foreach($results as $result)
            {
                $src=$result['source'];
                if ($src=='Process Item')
                {
                    $procName=$result['processName'];
                    $procNodeId=$result['processNodeId'];
                    Context::Debug("EventEngine:Handle Message $messageName invoking a new process $procName - $procNodeId");
                    
                    ProcessSvc::StartProcess($procName,$procNodeId);
                }

                if ($src=='Case Item')
                {

                    if (ProcessItem::isSenderType($result['type']))
                        continue;
                    
                    $caseId=$result['caseId'];
                    $id=$result['id'];
                    Context::Debug("EventEngine:Handle Message $messageName invoking a current case $caseId - $id");
                    Context::Debug("result:".print_r($result,true));
                    $item=CaseSvc::LoadCaseItem($caseId, $id);
                    TaskSvc::Complete($item,$data);
                }
            }
 	}
 	public static function getDueDate(ProcessItem $item)
 	{
            Context::Log(INFO, "getDueDate $item->timerType");
 		if ($item->timerType=="duration")
 		{
 			/*
 			 * duration is in format minute hour day month year
 			 */
 			$timer=trim($item->timer);
 			$arr=explode(" ",$timer);
 			if (count($arr)<2)
 			{
                             Context::Error("Invalid Timer format for duration, must be at least 3 fields of minute hour day year - $timer ");
                                return null;
 			}
 			$i=0;
 			$hours=0;
 			$minutes=5;
 			$seconds=0;
 			$days=0;
 			$months=0;
 			$years=0;
 			Context::Log(INFO, "getDueDate for a timer type of duration $timer array ".print_r($arr,true));
 			foreach($arr as $entry)
 			{
 				if (!ctype_digit($entry))
 				{
 					Context::Error("Invalid entry # $i - must be an integer '$entry'- $item->timer");
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
 		else
 		{
 			require __DIR__.'\..\..\lib\cron\CronExpression.php';
 				
 	
 			//			date_default_timezone_set('America/Toronto');
 	
 			$cron = \Cron\CronExpression::factory($item->timer);
 			$dueDate=$cron->getNextRunDate()->format('Y-m-d H:i:s');
 				
 	
 			return $dueDate;
 		}
 	}
 	
 	
 }

