<?php

namespace OmniFlow;

use OmniFlow\BPMN;
use OmniFlow\WFCase;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API
 *
 * @author ralph
 */
class ProcessSvc
{
	///
	///	Enlist a Listener 
	///
	public static function AssociateClass($processName,$className,$filePath)
	{
	
		BPMN\Process::$WorkFlowListeners[$processName]=array($className,$filePath);
	}
	
	public static function AddListener($processName,$function)
	{
		
		BPMN\Process::$WorkFlowListeners[$processName]=$function;
	}

	  
	// Starts a new Process returning the CaseId
	public static function StartProcess($processId,$startNodeId=null,$testMode=false)
	{
                $starter=$startNodeId;
		$proc= BPMN\Process::LoadProcess($processId);
                if ($starter===null)
                {
                    $starter=$proc->getStartNode($testMode);
                    if (count($starter)==0)
                    {
                        Context::Error("No Start Event is available for this process");
                        return;
                    }
                    else
                    {
                        $starter=$starter[0];
                        $starterId=$starter->id;
                    }
                } else {
                    if (is_string($startNodeId))
                    {
                        $starterId=$startNodeId;
                        $starter=$proc->getItemById($startNodeId);
                    }
                }
                
                WFCase\Assignment::CanPerform($starter, null);
                        
                 $newCase=WFCase\WFCase::NewCase($proc);
		$proc->Start($newCase,$starterId);
		return $newCase;
		
	}
	
	/*
	 * CheckTimer
	 * is called frequently by a cron job to check the for outstanding timers
	 *
	 *	Parameter: duration is the duration till the next time the cron job will call in minutes
	 *
	 */
	
	// Fire an Event for Case
	public static function TriggerlEvent($caseId,$event,$data)
	{
		
	}
}


class TaskSvc
{
	/*
         *  ReceiveData command
         *  Process the message for the item
         * 
         */
	public static function ReceiveMessage(WFCase\WFCaseItem $caseItem,$messageName,$messageData)
        {
                $task=self::getTask($caseItem);
                $task->ReceiveMessage($caseItem,$messageName,$messageData);
            
        }
	/*
         *  SignalData command
         *  Process the Signal for the item
         * 
         */
	public static function ReceiveSignal(WFCase\WFCaseItem $caseItem,$signalName,$signalData)
        {
                $task=self::getTask($caseItem);
                $task->ReceiveSignal($caseItem,$signalName,$signalData);
            
        }
	/*
         *  SaveData
         *  scenario 1:    is called when a user form is saved
         * 
         */
	public static function SaveData(WFCase\WFCaseItem $caseItem,$values,$newStatus= enum\StatusTypes::Updated)
	{
        Context::Log(INFO, 'API::Run id:'.$caseItem.id.'  values: '.print_r($values,true));

                $task=self::getTask($caseItem);
                
		$task->SaveData($caseItem,$values,$newStatus);
	}
        private static function getTask($caseItem)
        {
		$case= $caseItem->case;
		$proc=$case->proc;
                
		$taskId = $caseItem->processNodeId;
		
		$task = $proc->getItemById($taskId);
		if ($task==null)
		{
                        $caseId=$case->caseId;
                        $itemId=$caseItem.id;
			Context::Log(ERROR,"Error task not found for $taskId in Case $caseId - $itemId");
			return null;
		}
            return $task;
        }
        /*
         *  is called when the timer is due
         */
	public static function TimerDue(WFCase\WFCaseItem $item)
        {
                $task=self::getTask($item);
                
		$task->TimerDue($item);
            
        }
    public static function Invoke($caseId,$itemId)
    {
       
        WFCase\WFCaseItemStatus::$Notes='Task Executing invoked from url';
        
	$case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$item = $case->getItem($itemId);
        
	$taskId = $item->processNodeId;
	$task = $proc->getItemById($taskId);
        
        $task->Invoke($item);

        return $item;
        
    }
        
        // todo : remove 
    /*
    public static function Complete(WFCase\WFCaseItem $item,$values=null)
    {
            Context::Log(INFO, 'API::Run id:'.$item->id.' values: '.print_r($values,true));

            $case= $item->case;
            $proc=$case->proc;

            $taskId = $item->processNodeId;

            $task = $proc->getItemById($taskId);
            if ($task==null)
            {
                    Context::Log(ERROR,"Error task not found for $taskId in Case $caseId - $itemId");
                    return false;
            }
            $task->Complete($item);

            return $case;
    } */
}

Class CaseSvc
{
	public static function LoadCase($caseId)
	{
		Context::Log(INFO, 'LoadCase '.$caseId);
		 $case=WFCase\WFCase::LoadCase($caseId);
		 return $case;
	}
	public static function LoadCaseItem($caseId,$itemId)
	{
		 $case=WFCase\WFCase::LoadCase($caseId);
		 
		 $proc=$case->proc;
		 $item = $case->getItem($itemId);
		 
		 if ($item==null)
		 {
		 	Context::Log(ERROR,'Error no such item'.$itemId);
		 }
		 
		 return $item;
		 	
	}
}
