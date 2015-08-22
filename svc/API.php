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
	public static function StartProcess($fileName,$startNodeId=null)
	{
                $starter=$startNodeId;
		$proc= BPMN\Process::Load($fileName);
                if ($starter===null)
                {
                    $starter=$proc->getStartNode();
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
                }
                
                WFCase\Assignment::CanPerform($starter, null);
                        
		$newCase=WFCase\WFCase::NewCase($proc);
		$proc->Start($newCase,$starterId);
		return $newCase;
		
	}
	/*
	 * ExternalMessage
	 * is called when an external system like 'wordPress' calls a message
	 * 
	 * OmniWorkflow: will search for the appropriate Process or Case to Respond to this message
	 * based on various Message Definitions
	 * 
	 * 		This is called by plugin functions for wordPress messageName is WordPress_<actionName>
	 * 			for example:	WordPress_save_post
	 * 	 
	 *			add_action( 'save_post', 'omni_workflow_wordPress_save_post' );
	 * 
	 * 			
	 */
	public static function HandleMessage($messageName,$data)
	{
		EventEngine::HandleMessage($messageName,$data);
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
class SystemSvc
{
    	public static function CheckTimer($duration=60)
	{
		EventEngine::Check($duration);
	}
	

}

class TaskSvc
{
	public static function Invoke($caseId,$itemId,$values=null)
	{
		Context::Log(INFO, 'API::Invoke id:'.$itemId.'  values: '.print_r($values,true));
		
		$case= CaseSvc::LoadCase($caseId);
		$proc=$case->proc;
		$item = $case->getItem($itemId);
		
		$taskId = $item->processNodeId;
		
		$task = $proc->getItemById($taskId);
		if ($task==null)
		{
			Context::Log(ERROR,"Error task not found for $taskId in Case $caseId - $itemId");
			return false;
		}
//		Context::Log(LOG,( '<br /><hr /> Set Status - Task');
		$task->Invoke($item,$values,"",null);
		
		return $case;
	}
	// 
	public static function SetStatus($caseId,$itemId,$status=\OmniFlow\enum\StatusTypes::Completed,$values=null)
	{
		Context::Log(INFO, 'API::SetStatus id:'.$itemId.' status:'.$status.' values: '.print_r($values,true));
		
		$case= CaseSvc::LoadCase($caseId);
		$proc=$case->proc;
		$item = $case->getItem($itemId);
		
		$taskId = $item->processNodeId;
		
		$task = $proc->getItemById($taskId);
		if ($task==null)
		{
			Context::Log(ERROR,"Error task not found for $taskId in Case $caseId - $itemId");
			return false;
		}
//		Context::Log(LOG,( '<br /><hr /> Set Status - Task');
		$task->setStatus($item,$status,$values);
		
		if(is_array($values))
		{
			$values="";
		}

		if ($status==\OmniFlow\enum\StatusTypes::Completed)
		{
			$task->Finish($item,$values,null);
		}		
		// var_dump($task);
//		$proc->Save();
		
		return $case;
	}
}
Class Tester
{
    public function StartProcess($processName)
    {
        
    }
    public function SimulateUser($userId)
    {
        
    }
    public function SimulateRole($roleName)
    {
        
    }
    public function SimulateUserGroup($userGroup,$userScope=null)
    {
        
    }
    public function InvokeTask($taskId)
    {
        
    }
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
