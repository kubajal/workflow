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

namespace OmniFlow\BPMN;

use OmniFlow;
use OmniFlow\WFCase;

use OmniFlow\Context as Context;

/*
 *	Process Flow
 *
 
 Start Process - Start Event
 
 		1) by Starting the process - StartEvent
 		2) Signal a message that is declared in a Start Event
 		3) Signal a timer that is declared in a start event
 
 End Process - 
 		1) End Event 
 		
 Once an Item is completed, the process PROCEEDS as follows:
 
 		the completed Item sets the Result value to be evaluated by all outflows
 
 		- Invokes all outflows that meet the conditions
 		
 		Single Action:
 			if flow has a condition, it will be evaluated
 			the first flow that meets the condition will be executed
 			if no flow meets the condition, default flow will be executed
 			
 		Multiple Actions:
 			All flows that meet the condition will be executed
 		

 
 
 
 */
abstract class WFSubTypes
{
	const	MESSAGE_TYPE="message";
	const	TIMER_TYPE="timer";
	const	SIGNAL_TYPE="signal";
	const	TERMINATION_TYPE="terminate";
	const	ERROR_TYPE="error";
	const	ESCALATION_TYPE="escalation";
	const	COMPENSATE_TYPE="compensate";
	const	CONDITIONAL_TYPE="conditional";
	const	LINK_TYPE="link";
	const	CANCEL_TYPE="cancel";
        
        // add task sub types here
        
}


/**
 * Description of Process
 *
 * @author ralph
 */

class ProcessMessasge
{
        var $name;
        var $variables;
}

class Process extends OmniFlow\WFObject
{
	static $WorkFlowListeners=Array();
	
        var $processId;
	var $processName;
        var $title;
	var $items = Array();
	var $listeners = Array();
	var $messages=Array();
	var $pools=Array();
	var $errors=Array();
	var $dataElements=Array();
	var $actors=Array();
        var $accessRules=array();
        var $notificationRules=array();
        var $status;        /* Active , Inactive */

	/*
	 * 
	 */
	function __construct($processId)
	{
            $this->processId=$processId;

	}
        /*
         * returns an array of all scripts
         *  processItem
         *  scripttype
         *  script
         */
        function getAllScripts()
        {
            $scripts=Array();
            foreach($this->items as $pitem)
            {
                $nodeId=$pitem->id;
                if ($pitem->condition !=='' && $pitem->condition !=null )
                {
                    $scripts[]=Array("nodeId"=>$nodeId,
                            "type"=>'condition',
                            "script"=>$pitem->condition);
                }
                if ($pitem->actionScript!=='' && $pitem->actionScript!=null)
                {
                    $scripts[]=Array("nodeId"=>$nodeId,
                            "type"=>'action',
                            "script"=>$pitem->actionScript);
                    
                }
                foreach($pitem->scripts as $scr)
                {
                    $scripts[]=Array("nodeId"=>$nodeId,
                            "type"=>'action',
                            "script"=>$scr);
                    
                }
                
            }
            return $scripts;
            
        }
	function getJson()
	{
		$items=array();
		$subs=array();
                $accessRules=array();
                $actors=array();
                $notificationRules=array();
                
          	foreach($this->accessRules as $ar)
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
  
		$eventsArr=  OmniFlow\enum\NotificationTypes::getScriptEvents();
                
                $site= \OmniFlow\Context::getSite();
                $userRoles=$site->userRoles;
		$arr=array();
		$arr['items']=$items;
                $arr['pools']=$subs;
		$deTree=  \OmniFlow\DataManager::getMeta($this);
		$arr['dataElements']=$deTree;
                $arr['actors']=$actors;
		$arr['accessRules']=$accessRules;
		$arr['notificationRules']=$notificationRules;
                $arr['userRoles']= $userRoles;
                $arr['scriptEvents']= $eventsArr;
		
                $arr['itemsDescription']= $this->Describe();

                return $arr;

	}
	function Init()
	{
		foreach($this->items as $item)
		{
			$item->Init();
		}
		
                $sorted=Array();
                $sortedIds=Array();
                foreach($this->items as $item)
                {
                    if ($item->type=='startEvent')
                    {
                        if (!in_array($item->id, $sortedIds)) {
                            $sorted[]=$item;
                            $sortedIds[]=$item->id;
                        }
                        $p=0;
                        while($p<count($sorted)) {
                                $item=$sorted[$p++];
                                foreach($item->outflows as $flow)
                                {
                                    $toNode=$flow->toNode;
                                    if (!in_array($toNode->id, $sortedIds)) {
                                       $sorted[]=$toNode;
                                       $sortedIds[]=$toNode->id;
                                    }
                                }
                        }
                    }
                }
                foreach($this->items as $item)
                {
                    if (!$item->isFlow())
                    {
                      if (!in_array($item->id, $sortedIds)) {
                                       $sorted[]=$item;
                                       $sortedIds[]=$item->id;
                                }
                    }
                    
                }

                $i=1;
                foreach($sorted as $item)
                {
                  $item->seq=$i++;
                }
                
                
		foreach(Process::$WorkFlowListeners as $vproc=>$funct)
		{
			if (($vproc==$this->processName) || ($vproc=="*"))
			{
				if(is_array($funct))
				{
					$className=$funct[0];
					$fileName=$funct[1];
					$this->AddClassListener($className, $fileName);
				}
				else
					$this->AddListener($funct);
			}
		}
		$this->Notify(OmniFlow\enum\NotificationTypes::ProcessLoaded);
		
		foreach($this->items as $item)
		{
			$item->Notify(OmniFlow\enum\NotificationTypes::NodeInitialized);
		}
		
		$this->Notify(OmniFlow\enum\NotificationTypes::ProccessInitialized);
		
	}
	function AddClassListener($className,$fileName)
	{
		$conf=new \OmniFlow\Config();
		
		$classFile=$conf->processPath.'/'.$fileName;
//		echo $classFile;
		OmniFlow\Context::Log(\OmniFlow\Context::INFO,"AddClassListener: $className - $fileName $classFile");
		if (!file_exists($classFile))
		{
			OmniFlow\Context::Log(\OmniFlow\Context::ERROR, "Class file does not exist $classFile");
			return;
		}
		include_once $classFile;
		$function=$className.'::init';
		$ret=call_user_func_array($function, array($this));
		$function=$className.'::Listener';
		$this->listeners[]=$function;
		
	}
	function Trace()
	{
//		$this->statNode->Trace();
	
	}
	
	function AddItem($item)
	{
		$item->proc = $this;
		$items[]=$item;
		return $item;
	}
	public function AddListener($funct)
	{
		OmniFlow\Context::Log(\OmniFlow\Context::INFO,"AddListener: $funct");
		$this->listeners[]=$funct;
	}
	public function Notify($procEvent,$processItem=null)
	{
		if ($processItem==null)
			$processItem=$this;
		//Sample:	function SampleListner($Process,$ProcessItem,$event)
		foreach($this->listeners as $funct)
		{
			$ret=call_user_func_array($funct, array($procEvent,$processItem));
		}
	
	}
	function getStartNode($testMode=false)
        {
            $nodes=array();
            foreach($this->items as $node)
            {
                    if ($node->type=="startEvent")
                    {
                        
                        $isManual=false;
                        if ($node->hasMessage == false && $node->hasTimer==false)
                            $isManual=true;
                        if ( $isManual || $testMode)
                            {
                                $sub=$node->getPool();
                                OmniFlow\Context::log(\OmniFlow\Context::INFO,"start event pool".'end ');
                                if ($sub->isExecutable())
                                {
                                    $nodes[]=$node;
                                }
                            }
                    }
            }		
            return $nodes;

        }
	public function Start(WFCase\WFCase $case,$startNodeId=null)
	{
            OmniFlow\Context::Debug("Process.Start for case $case->caseId - start at $startNodeId");
		$this->Notify(OmniFlow\enum\NotificationTypes::ProccessInitialized);
		
                $starter=$startNodeId;
                if ($startNodeId==null)
                {
                    $starter=$this->getStartNode();
                    if(count($starter)>0)
                       $starter=$starter[0];
                    else
                    {
                      OmniFlow\Context::Error ("No manual start nodes for this process");
                      return;
                    }
                        
                }
                $node=$this->getItemById($starter);
		$node->Execute($case,"",null);
		
		$this->Notify(OmniFlow\enum\NotificationTypes::ProccessStarted);
		
	}
	public function EndProcess(WFCase\WFCase $case,ProcessItem $item=null)
	{
                OmniFlow\Context::Debug("Process.EndProcess for case $case->caseId");
		$this->Notify(OmniFlow\enum\NotificationTypes::ProcessCompleted);
		$case->EndProcess($item);
		
	}
        /*
         *  used for subprocesses
         * 
         *  it sets the caseItem parentId to the calling item for subprocesses
         */
        public function checkParentId(WFCase\WFCase $case,$caseItem)
        {
            // check if subprocess
            if ($this->isSubProcess===NULL) // has not been checked before
            {
                $this->isSubProcess=false;
            }
            if ($this->isSubProcess)
            {
                
            }
        }
	public function Describe()
	{
            $descs=Array();
		foreach ($this->items as $item)
		{
                    $t=new \OmniFlow\Describer(); 
                    $t->id=$item->id;
                    $t->className="";
                    $t->userDoc=$item->description;
                    $t->xmlTag="";
                    $t->start=OmniFlow\KW::autoStart;
                    $t->completion=OmniFlow\KW::autoComplete;
                    $t->designOptions=array();
                    $t->modelOptions=array();
                    $t->label=$item->label;
                    $item->describe($t);

                    $descs[]=$t;
		}
		foreach ($this->messages as $id=>$message)
		{
		}
                
            return $descs;
		
	}
        public function Validate()
        {
            OmniFlow\ValidationRule::ValidateProcess($this);
            OmniFlow\ScriptEngine::Validate($this);
            foreach($this->items as $item)
            {
                if ($item->requiresAccessRules())
                {
                    AccessRule::Validate($item);
                }
            }
        }
        /*
         *  all references to process are by id (system generated sequence #)
         *  Case will reference processId and ProcessVersion
         *  Environemtns:
         *          Dev:    processes\dev
         *          Prod    processes\prod
         * files
         *      proc_<id>.bpmn  
         *      proc_<id>.xml
         *      proc_<id>.svg
         *  also versions :
         *      proc_<id>_<version>.ext
         * 
         *  process is saved in a file based on environment 
         */
	public static function LoadProcess($processId,$processVersion=null,$loadExtensions=true)
	{
            $proc=new Process($processId);
            OmniFlow\Context::Log(\OmniFlow\Context::INFO,'Process:load '.$processId);
  
            $model=new \OmniFlow\ProcessModel();
            $model->load($processId,$proc);

//		$jsonPath = OmniFlow\Config::getConfig()->processPath.'/'.$fileName.'.json';
		//if (!file_exists($jsonPath))
                
            $fromXML=true;

            $start = microtime(true);
            if ($fromXML)
            {
                $loader=new OmniFlow\XMLLoader();
                $loader->loadFile($proc,$loadExtensions);
//                $proc=$loader->proc;
    //		$proc->SaveJson($jsonPath);
                $time_elapsed_secs = microtime(true) - $start;
                OmniFlow\Context::Log(\OmniFlow\Context::INFO,'Process:load '.$processId.' - ended @ '.$time_elapsed_secs);

                return $proc;
            }
            else
            {
                $json=file_get_contents($jsonPath);
                $proc=unserialize($json);
                $time_elapsed_secs = microtime(true) - $start;
                OmniFlow\Context::Log(\OmniFlow\Context::INFO,'Process:load '.$fileName.' -json ended @ '.$time_elapsed_secs);
                return $proc;
            }
            
        }
        public function getImageFileName()
        {
            return Context::getInstance()->processPath.'/proc_'.$this->processId.'.svg';
        }
        public function getExtensionFileName()
        {
            return Context::getInstance()->processPath.'/proc_'.$this->processId.'.xml';
        }
        
        public function getFileName()
        {
            return Context::getInstance()->processPath.'/proc_'.$this->processId.'.bpmn';
        }
        public static function getProcessFileName($id)
        {
            return 'proc_'.$id;
        }
	public static function NewProcess($processName,$processTitle)
	{
            $proc=new Process(1);
            $proc->processName=$processName;
            $proc->title=$processTitle;
            $model=new \OmniFlow\ProcessModel();
            $model->insert($proc);
            
            return $proc;

        }
        /* update Database with modified Process Information ; 
         *  title,status
         *      and start events only
         */
        public function Update()
        {
            $model=new \OmniFlow\ProcessModel();
            $model->update($this);
        }
        /* returns list of processes
         *  processId, process name,  title
         */
        public static function getList()
        {
            $model=new \OmniFlow\ProcessModel();
            return $model->ListProcesses();
            
        }
        public static function Delete($processid)
        {
            $proc=new Process($processid);
            
            $file1=$proc->getFileName();
            $file2=$proc->getExtensionFileName();
            $file3=$proc->getImageFileName();
            
            unlink($file1);                
            unlink($file2);                
            unlink($file3);                
	        
            $model=new \OmniFlow\ProcessModel();
            $model->delete($processid);
            
            
        }
        
        public function Duplicate()
        {
            
        }
        public function Save()
        {
            
        }
	
	
	public function SaveJson($fileName)
	{
		$arr=$this->listeners;
		$this->listeners=array();
		$ret=file_put_contents($fileName,serialize($this) );
		$this->listeners=$arr;
		return $ret;
		
	}

	public function getItemById($id)
	{
		foreach ($this->items as $item)
		{
			if ($item->id == $id)
				return $item;
		}
		
		return null;
	}
	public function getItemByName($name)
	{
		foreach ($this->items as $item)
		{
			if ($item->name == $name)
				return $item;
		}
		
		return null;
	}

	public function associateFunction($nodeName,$function)
	{
		$item=$this->getItemByName($nodeName);
		if ($item!=null)
			$item->customFunction=$function;
//		echo "Associated ".$item->name.'->'.$function;
	}
	public function getDataElement($name)
	{
		return $this->dataElements[$name];
	}
}
