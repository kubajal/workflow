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


class DescriberObject 
{

    var $name;
    var $title;
    var $className;
    var $xmlTag;
    var $descriptor;
    static $types=array();
    public static function getTypes()
    {
        $list=array();


// ----------------------	userTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="userTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="User Task";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::manualComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	serviceTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="serviceTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="Service Task";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::scriptComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	receiveTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="receiveTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="{ return 'Receives a message: '+item.message;}";
		$t->start=KW::autoStart;
		$t->completion=KW::messageReceived;
		$t->designOptions=array("Define Action","Map message data");
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	sendTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="sendTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc=KW::sendsMessage;
		$t->start=KW::autoStart;
		$t->completion=KW::scriptComplete;
		$t->designOptions=array("Define Action","Map message data");
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	scriptTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="scriptTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::scriptComplete;
		$t->designOptions=array("Define Action");
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	manualTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="manualTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::manualComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array();
		
		
		$list[$t->name]=$t;

// ----------------------	startEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="startEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="A starting point of the proces.";
		$t->start=KW::manualStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic,KW::acl);
		$t->modelOptions=array(KW::timer,KW::message,KW::signal);
		
		$list[$t->name]=$t;

		$t=new Describer(); 
		$t->name="startEventmessage";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="A starting point of the proces.";
		$t->start=KW::messageReceived;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic,KW::acl);
		$t->modelOptions=array(KW::timer,KW::message,KW::signal);
		
		$list[$t->name]=$t;
                

		$t=new Describer(); 
		$t->name="startEventsignal";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="A starting point of the proces.";
		$t->start=KW::signalReceived;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic,KW::acl);
		$t->modelOptions=array(KW::timer,KW::message,KW::signal);
		
		$list[$t->name]=$t;
                
                
		$t=new Describer(); 
		$t->name="startEventtimer";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="A starting point of the proces.";
		$t->start="when a schedule time is due.";
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic);
		$t->modelOptions=array(KW::timer,KW::message,KW::signal);
		
		$list[$t->name]=$t;

                
                
// ----------------------	endEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="endEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic);
		$t->modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t->name]=$t;

// ----------------------	intermediateCatchEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="intermediateCatchEventmessage";
		$t->className="";
		$t->xmlTag="";
		$t->title="Intermediate Event";
		$t->desc="Receives a Message";
		$t->start=KW::autoStart;
		$t->completion=KW::messageReceived;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	intermediateCatchEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="intermediateCatchEventtimer";
		$t->className="";
		$t->xmlTag="";
		$t->title="Intermediate Event";
		$t->desc="Timer Event; will wait until the timer is due";
		$t->start=KW::autoStart;
		$t->completion=KW::timer;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	intermediateThrowEvent   ---------------------- 

		$t=new Describer(); 
		$t->name="intermediateThrowEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="Intermediate Event";
		$t->desc="No actions.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;
                
                
		$t=new Describer(); 
		$t->name="intermediateThrowEventmessage";
		$t->className="";
		$t->xmlTag="";
		$t->title="Intermediate Event";
		$t->desc=KW::sendsMessage;
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	messageEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="messageEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="Intermediate Event";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		
		$list[$t->name]=$t;

// ----------------------	exclusiveGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="exclusiveGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="Exclusive Gateway (XOR)";
		$t->desc="Controls the flow of the process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete. 
		 "Only one outgoing flow will be executed based on the conditions.
		 <p /> If none of the conditions are met the default flow will be executed.";
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	inclusiveGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="inclusiveGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="Inclusive Gateway(OR)";
		$t->desc="Controls the flow of the process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	parallelGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="parallelGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="Parallel Gateway (AND)";
		$t->desc="Controls the flow of the process";
		$t->start=KW::converge.' '.KW::waitIncomingFlows;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------   eventBasedGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="eventBasedGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="Event Based Gateway";
		$t->desc="Controls the flow of the process.";
		$t->start=KW::autoStart;
		$t->completion="Waits of the completion of any of children events";
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------   complexGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="complexGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Controls the flow of the process";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	messageFlow   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="messageFlow";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Carries a Message between two nodes";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array();
		
		$list[$t->name]=$t;

// ----------------------	sequenceFlow   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="sequenceFlow";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Defines (the sequence) of flow between activites";
		$t->start="{if (item.condition===''||item.condition===null) return '';
                        return 'Only if <b>'+item.condition+'</b>';}";
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::condition,"Defines Case Status");
		$t->modelOptions=array();
                
		$list[$t->name]=$t;
                
                return $list;
    }
}



/**
 * Description of Describer
 *
 * @author ralph
 */
class KW {
    const manualStart="manually (authorized users invokes this event)";
    const converge="If Converging:";
    const diverge="If Diverging:";
    const waitIncomingFlows="waits for all incoming flows to complete";
    const autoStart="";//"When any incoming flow arrives";
    const manualComplete="when an authorized user designates the task to be complete.";
    const autoComplete="";//"Completes as soon as it arrives";
    const scriptComplete="when the action completes";
    const messageReceived="{
            var msg=item.message;
            if (msg==null) msg='undefined';
            return 'when a message: '+msg+' is received';}";        
    const sendsMessage="{
            var msg=item.message;
            if (msg==null) msg='undefined';
            return 'Sends a message: '+msg;}";        
    const signalReceived="when specified signal is received";
    const logic="Custom Logic can be added";
    const condition="Logical Condition";
    const acl="User Access is controlled";
    const timer="Timer to delay completion to specific time or duration";
    const message="Message to delay completion until a specific message arrives";
    const signal="Signal to delay completion until a specifi signal arrives";
}

class Describer {
    
   var $name;
   var $id;
   var $title;
   var $desc;
   var $userDoc;
   var $start=KW::autoStart;
   var $completion=KW::autoComplete;
   var $designOptions;
   var $modelOptions;
//   var $item;
   var $className;
   var $xmlTag;
  
  
   public static function getProcessDescription(BPMN\Process $proc)
   {
        $descs=  DescriberObject::getTypes();
        
        foreach($descs as $desc)
        {
            $desc->id=$desc->name;
        }

        return $descs;
   }
   public function checkSubItem($processItem)
   {
                $sender=  BPMN\ProcessItem::isSenderType($processItem->type);
                if ($processItem->type=='endEvent')
                    $sender=true;
                if ($processItem->type=='startEvent')
                    $sender=false;

                $msg='';
                $type=$processItem->subType;
                
                
       		if ($processItem->hasMessage) {
                    
                    $msg=$processItem->message;
                    $type='message';
                }
		if ($processItem->hasSignal) {
                    $msg=$processItem->signalName;
                    $type='signal';
                }
		if ($processItem->hasTimer) {
                    $type='timer - '.$processItem->timerType;
                    $msg=$processItem->timer;
                }
                
                if ($msg==null)
                        $msg="to be defined";

                if ($type=='')
                    return;
                // starts
                if ($processItem->type=='startEvent')
                {
                    $this->start="will wait for a $type of type '$msg' before starting";
                    
                } elseif ($sender) {
                // Sends
                    $this->desc.="will send a $type of type '$msg'";
                } elseif ($type=='') {
                    
                } else {
                // Receives
                    $this->completion="will wait for a $type of type '$msg'";
                    
                }
       
   }
}

