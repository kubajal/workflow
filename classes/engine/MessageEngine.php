<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of EventEngine
 *
 * @author ralph
 */
class MessageEngine
 {
 	public function RegisterProcessEvent(Process $proc,ProcessItem $item)
 	{
 		
 	}
 	public function RegisterCaseEvent(WFCase\WFCase $case,WFCase\WFCaseItem $citem)
 	{
 		
 	}
  	public static function Recieve($data)
 	{
            $msgName=$data['message'];
            $queryResults=self::LocateMessageReceipent($msgName, $data);
 		
 	}
        /*
         * a Process Start message
         * 
         * MessageName
         * Keys
         * 
         * 
         */
        public static function LocateMessageReceipent($messageName,$values)
        {
            $queryResults=ProcessItemModel::getMessageItem($messageName);
            
            if (count($queryResults)==0)
            {
                Context::Error("Message '$messageName' not found");
                return null;
            }
            if (count($queryResults)>0)
            {
                Context::Error("Message '$messageName' is implemented in many items ".print_r($queryResults,true));
                return null;
            }
            else
            {
                $impl=$queryResults[0];
                if (($impl['type']==='startEvent') && ($impl['messageKeys']===''))
                {
                    //start a new process with the message
                }
                else
                {
                    // locate case with the keys
                    $keyValues=array();
                    foreach($impl['messageKeys'] as $key)
                    {
                        $value=$values[$key];
                        $keyValues[$key]=$value;
                    }
                    $caseItem=CaseItemModel::locateMessageItem($messageName,$keyValues);
                }
            }
        }
 	
 }

