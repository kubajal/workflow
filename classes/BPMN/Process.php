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


class SubProcess extends OmniFlow\WFObject
{
    var $id;
    var $name;
    var $implementation;
 public function isExecutable()
 {
     if ($this->implementation=='no')
         return false;
     else
         return true;
 }
}
class Process extends OmniFlow\WFObject
{
	static $WorkFlowListeners=Array();
	
	var $name;
	var $processName;
        var $title;
	var $items = Array();
	private $listeners = Array();
	var $messages=Array();
	var $subprocesses=Array();
	var $errors=Array();
	var $dataElements=Array();
	var $actors=Array();
        var $accessRules=array();
        var $notificationRules=array();


	/*
	 * 
	 */
	function __construct($fullname)
	{
		$this->name=$fullname;
		$this->processName =$fullname;

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

		foreach($this->subprocesses as $sub)
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
                        
		$arr=array();
		$arr['items']=$items;
                $arr['subprocesses']=$subs;
		$deTree=  \OmniFlow\DataManager::getMeta($this);
		$arr['dataElements']=$deTree;
                $arr['actors']=$actors;
		$arr['accessRules']=$accessRules;
		$arr['notificationRules']=$notificationRules;
                $arr['descriptions']= \OmniFlow\Describer::getProcessDescription($this);
		
                
                $json=json_encode($arr);     
                $err=json_last_error();
                
                $json2=json_encode(\OmniFlow\Helper::utf8ize($arr));
                
		return $json;
	}
	function Init()
	{
            $this->title=  str_replace(".bpmn", "",$this->name);
		foreach($this->items as $item)
		{
			$item->Init();
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
		OmniFlow\Context::Log(INFO,"AddClassListener: $className - $fileName $classFile");
		if (!file_exists($classFile))
		{
			OmniFlow\Context::Log(ERROR, "Class file does not exist $classFile");
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
		OmniFlow\Context::Log(INFO,"AddListener: $funct");
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
	function getStartNode()
        {
            $nodes=array();
            foreach($this->items as $node)
            {
                    if ($node->type=="startEvent")
                    {
                            if ($node->hasMessage == false && $node->hasTimer==false)
                            {
                                $sub=$node->getSubProcess();
                                OmniFlow\Context::log(INFO,"start event subprocss".  var_export($sub,true).'end ');
                                if ($sub->isExecutable())
                                {
                                    $nodes[]=$node;
                                }
                            }
                    }
            }		
            return $nodes;
            
            foreach($this->items as $node)
            {
                    if ($node->type=="startEvent")
                    {
                        return $node;
                    }
            }

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
		$this->Notify(OmniFlow\enum\NotificationTypes::ProcessCompleted);
		$case->EndProcess($item);
		
	}
	public function Describe()
	{
		echo "<br /><hr />Describe process";
//		Array sorted = items.OrderBy(o => o.Sequence).ToList<ProcessItem>();
		echo '<table>';
		foreach ($this->items as $item)
		{
			$msg=$item->describe();
			
			$msg='<tr><td>'.str_replace(":","</td><td>",$msg).'</tr>';
			echo $msg;
		}
		foreach ($this->messages as $id=>$message)
		{
			if (isset($message->name))
				$msg="<tr><td>Message</td><td>$message->name</td></tr>";
			echo $msg;
		}
		echo '</table>';
		
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
	
	public static function Load($fileName,$loadExtensions=true)
	{
		OmniFlow\Context::Log(INFO,'Process:load '.$fileName);
		$jsonPath = OmniFlow\Config::getConfig()->processPath.'/'.$fileName.'.json';
		//if (!file_exists($jsonPath))
		$fromXML=true;

		$start = microtime(true);
		if ($fromXML)
		{
		$loader=new OmniFlow\XMLLoader();
		$loader->loadFile($fileName,$loadExtensions);
		$proc=$loader->proc;
//		$proc->SaveJson($jsonPath);
		$time_elapsed_secs = microtime(true) - $start;
		OmniFlow\Context::Log(INFO,'Process:load '.$fileName.' - ended @ '.$time_elapsed_secs);
		
		return $proc;
		}
		else
		{
		$json=file_get_contents($jsonPath);
		$proc=unserialize($json);
		$time_elapsed_secs = microtime(true) - $start;
		OmniFlow\Context::Log(INFO,'Process:load '.$fileName.' -json ended @ '.$time_elapsed_secs);
		return $proc;
		}
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
