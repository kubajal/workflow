<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
// ----------------------	task   ---------------------- 
		  
		$t=new Describer(); 
		$t->name="task";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		   
		
		$list[$t->name]=$t;

// ----------------------	userTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="userTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	serviceTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="serviceTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	receiveTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="receiveTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	sendTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="sendTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	scriptTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="scriptTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	manualTask   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="manualTask";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Work that needs to be perfomed in a Process.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array("Define Action",KW::acl);
		$t->modelOptions=array("More to come...");
		
		
		$list[$t->name]=$t;

// ----------------------	startEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="startEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="Start Event is the where the Process can start. <br />"
                        . "{
                         var nodes=JsonNodes('items.[node.type==''startEvent'']');
                        if (nodes.length>1] return 'this process has '+nodes.length+ Start Events';}";
		$t->start=KW::manualStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic,KW::acl);
		$t->modelOptions=array(KW::timer,KW::message,KW::signal);
		
		$list[$t->name]=$t;

// ----------------------	endEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="endEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::logic);
		$t->modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t->name]=$t;

// ----------------------	intermediateCatchEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="intermediateCatchEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t->name]=$t;

// ----------------------	intermediateThrowEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="intermediateThrowEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t->name]=$t;

// ----------------------	messageEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="messageEvent";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("More to come...");
		
		
		$list[$t->name]=$t;

// ----------------------	exclusiveGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="exclusiveGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
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
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------	parallelGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="parallelGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------   eventBasedGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="eventBasedGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("More to come...");
		
		$list[$t->name]=$t;

// ----------------------   complexGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t->name="complexGateway";
		$t->className="";
		$t->xmlTag="";
		$t->title="";
		$t->desc="End Event is the where the Process Ends.";
		$t->start=KW::autoStart;
		$t->completion=KW::autoComplete;
		$t->designOptions=array();
		$t->modelOptions=array("More to come...");
		
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
		$t->start="Only if the specified condition is met.";
		$t->completion=KW::autoComplete;
		$t->designOptions=array(KW::condition);
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
    const manualStart="Manually Start";
    const autoStart="When any incoming flow arrives";
    const manualComplete="When an authorized user designates the task to be complete.";
    const autoComplete="Completes as soon as it arrives";
    const logic="Custom Logic can be added";
    const condition="Logical Condition";
    const acl="User Access is controlled";
    const timer="Timer to delay completion to specific time or duration";
    const message="Message to delay completion until a specific message arrives";
    const signal="Signal to delay completion until a specifi signal arrives";
}

class Describer {
    
   var $name;
   var $title;
   var $desc;
   var $start=KW::autoStart;
   var $completion=KW::autoComplete;
   var $designOptions;
   var $modelOptions;
//   var $item;
   var $className;
   var $xmlTag;
  
  
   public static function getProcessDescription(Process $proc)
   {
        $descs=  DescriberObject::getTypes();
        
        foreach($descs as $desc)
        {
            $desc->id=$desc->name;
        }

        return $descs;
   }
}

