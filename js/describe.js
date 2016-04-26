/* to describe the model */


function Describer(itemId)
{
    var self=this;
    
    self.KW_manualStart="Manually Start";
    self.KW_converge="If Converging:";
    self.KW_diverge="If Diverging:";
    self.KW_waitIncomingFlows="Waits for all incoming flows to complete";
    self.KW_autoStart="When any incoming flow arrives";
    self.KW_manualComplete="When an authorized user designates the task to be complete.";
    self.KW_autoComplete="Completes as soon as it arrives";
    self.KW_scriptComplete="Completes when the action completes";
    self.KW_messageReceived="Completes when message is received";
    self.KW_logic="Custom Logic can be added";
    self.KW_condition="Logical Condition";
    self.KW_acl="User Access is controlled";
    self.KW_timer="Timer to delay completion to specific time or duration";
    self.KW_message="Message to delay completion until a specific message arrives";
    self.KW_signal="Signal to delay completion until a specifi signal arrives";
    
    
//-------------------------------------------------------------------------------
self.getDataRow= function (rowNo)
{
    return self.dataView.getItem(rowNo);
}
//-------------------------------------------------------------------------------
self.describeItem = function (itemId) 
{		  

    var item = getObject('items',itemId);	
    
    var iType=item.type;
    
        if (iType=='task') {
// ----------------------	task   ---------------------- 
		$t=new Describer(); 
		$t.name="task";
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_scriptComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		   
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	userTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="userTask";
		
		
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_manualComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	serviceTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="serviceTask";
		
		
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_scriptComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	receiveTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="receiveTask";
		
		
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_messageReceived;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	sendTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="sendTask";
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_scriptComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

         } 
         else if (iType=='task') {
// ----------------------	scriptTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="scriptTask";
		
		
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_scriptComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	manualTask   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="manualTask";
		
		
		
		$t.desc="Work that needs to be perfomed in a Process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_manualComplete;
		$t.designOptions=array("Define Action",self.KW_acl);
		$t.modelOptions=array("More to come...");
		
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	startEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="startEvent";
		
		
		
		$t.desc="Start Event is the where the Process can start. <br />"
                        . "{
                         var nodes=JsonNodes('items.[node.type==''startEvent'']');
                        if (nodes.length>1] return 'this process has '+nodes.length+ Start Events';}";
		$t.start=self.KW_manualStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array(self.KW_logic,self.KW_acl);
		$t.modelOptions=array(self.KW_timer,self.KW_message,self.KW_signal);
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	endEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="endEvent";
		
		
		
		$t.desc="End Event is the where the Process Ends.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array(self.KW_logic);
		$t.modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	intermediateCatchEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="intermediateCatchEvent";
		
		
		
		$t.desc="End Event is the where the Process Ends.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	intermediateThrowEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="intermediateThrowEvent";
		
		
		
		$t.desc="End Event is the where the Process Ends.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("Terminate Event: Will terminate all running activities");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	messageEvent   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="messageEvent";
		
		
		
		$t.desc="End Event is the where the Process Ends.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("More to come...");
		
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	exclusiveGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="exclusiveGateway";
		
		
		
		$t.desc="Controls the flow of the process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete. 
		 "Only one outgoing flow will be executed based on the conditions.
		 <p /> If none of the conditions are met the default flow will be executed.";
		$t.designOptions=array();
		$t.modelOptions=array();
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	inclusiveGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="inclusiveGateway";
		
		
		
		$t.desc="Controls the flow of the process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	parallelGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="parallelGateway";
		
		
		
		$t.desc="Controls the flow of the process";
		$t.start=self.KW_converge.' '.self.KW_waitIncomingFlows;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------   eventBasedGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="eventBasedGateway";
		
		
		
		$t.desc="Controls the flow of the process.";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------   complexGateway   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="complexGateway";
		
		
		
		$t.desc="Controls the flow of the process";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array("More to come...");
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	messageFlow   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="messageFlow";
		
		
		
		$t.desc="Carries a Message between two nodes";
		$t.start=self.KW_autoStart;
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array();
		$t.modelOptions=array();
		
		$list[$t.name]=$t;

        } else if (iType=='task') {
// ----------------------	sequenceFlow   ---------------------- 
		 
		$t=new Describer(); 
		$t.name="sequenceFlow";
		
		
		
		$t.desc="Defines (the sequence) of flow between activites";
		$t.start="Only if the specified condition is met.";
		$t.completion=self.KW_autoComplete;
		$t.designOptions=array(self.KW_condition,"Defines Case Status");
		$t.modelOptions=array();
                
		$list[$t.name]=$t;
        }
                return $list;
}
