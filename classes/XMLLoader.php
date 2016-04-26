<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

use OmniFlow\BPMN;
/**
 * Description of XMLLoader
 *
 * @author ralph
 */
class XMLLoader
{
    var $proc;
    var $xml;
    function LoadFile($proc,$loadExtensions=true)
    {
            $fileName=$proc->getFileName();
        
            $path = $fileName;

            if (!file_exists($path))
            {
                Context::Error("File $path does not exist");
                return null;
            }
            $entries = file_get_contents($path);
            $this->xml = new \SimpleXmlElement($entries);

            $this->xml->registerXPathNamespace('model', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            $this->proc = $proc;

            $this->loadMessages();

            $this->loadNodes("task","OmniFlow\BPMN\Task");
            $this->loadNodes("userTask","OmniFlow\BPMN\Task");
            $this->loadNodes("serviceTask","OmniFlow\BPMN\Task");
            $this->loadNodes("receiveTask","OmniFlow\BPMN\Task");
            $this->loadNodes("sendTask","OmniFlow\BPMN\Task");
            $this->loadNodes("scriptTask","OmniFlow\BPMN\Task");
            $this->loadNodes("manualTask","OmniFlow\BPMN\Task");
            $this->loadNodes("subProcess","OmniFlow\BPMN\Task");


            $this->loadNodes("startEvent","OmniFlow\BPMN\Event");
            $this->loadNodes("endEvent","OmniFlow\BPMN\Event");
            $this->loadNodes("intermediateCatchEvent","OmniFlow\BPMN\Event");
            $this->loadNodes("intermediateThrowEvent","OmniFlow\BPMN\Event");
            $this->loadNodes("messageEvent","OmniFlow\BPMN\Event");
                

            $this->loadNodes("exclusiveGateway","OmniFlow\BPMN\XORGateway");
            $this->loadNodes("inclusiveGateway","OmniFlow\BPMN\ORGateway");
            $this->loadNodes("parallelGateway","OmniFlow\BPMN\ANDGateway");
            $this->loadNodes("eventBasedGateway","OmniFlow\BPMN\EventBasedGateway");
            
            $this->loadLanes();
            
            $this->loadNodes("participant","OmniFlow\BPMN\Participant",false);
            
            
            $this->loadNodes("messageFlow","OmniFlow\BPMN\Flow",false);
            $this->loadNodes("sequenceFlow","OmniFlow\BPMN\Flow",false);

/*
 *  we need to get particpants as well
 *   <bpmn2:collaboration id="Collaboration_0vpkhfc">
    <bpmn2:participant id="Participant_0ufgbno" processRef="Process_1" />
    <bpmn2:participant id="Participant_1659ivr" processRef="Process_0a0hex8" />
    <bpmn2:messageFlow id="MessageFlow_1h2wx9h" sourceRef="SendTask_1eshlev" targetRef="Participant_1659ivr" />
    <bpmn2:messageFlow id="MessageFlow_0voa3cz" sourceRef="EndEvent_10mwvz6" targetRef="IntermediateCatchEvent_0whqx4b" />
  </bpmn2:collaboration>

 */            
            

            foreach($this->proc->items as $node)
            {
                    $node->actor=$node->lane;
            }

            $this->loadShapes();
            
            if ($loadExtensions) {
                $ext=new ProcessExtensions();
                $ext->loadExtensions(str_replace('.bpmn', '.xml',$path),$this->proc);
            }

            $this->proc->Init();

            return $this->proc;

    }

    function loadMessages()
    {
            /*

            <semantic:message id="_1275940932310" name="msg1" />
            <semantic:message id="_1275940932433" name="msg2" />
     */

            foreach($this->xml->xpath('//model:message') as $msgNode) {
                    Context::Log(LOG,'loading message'.$msgNode->getName());

                    $name=XMLLoader::getAttribute($msgNode, 'name');

                    $id=XMLLoader::getAttribute($msgNode, 'id');

                    $this->proc->messages[$id]=$name;
            }

    }

    function loadNodes($name,$class,$processBased=true)
    {
            $dblDash='//';

            if ($processBased)
            {

                    foreach($this->xml->xpath($dblDash.'model:process') as $procNode) {

                            $procNode->registerXPathNamespace('model', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

                            $attr=$procNode->Attributes();
                            $poolId = $attr['id'];

                            $participant = $this->xml->xpath($dblDash."model:participant[@processRef='$poolId']");

                            $actor="";
                            if (count($participant)>0)
                                            {
                                                    $participant=$participant[0];
                                                    $pattr=$participant->attributes();
                                                    $actor=$pattr['name'];
                                                    if ($actor!=null) 
                                                            $actor=$actor->__ToString();
                                            }


                            foreach($procNode->xpath('model:'.$name) as $child) {
                                    //				echo '<br />'.$child->getName();
                                    $obj = new $class($this->proc);
                                    $obj->pool=$poolId->__toString();;
                                    $obj->loadFromXML($child);
                                    $obj->actor=$actor;
                            }

                    }
            }
            else
            {
                    foreach($this->xml->xpath($dblDash.'model:'.$name) as $child) {
                            //				echo '<br />'.$child->getName();
                            $obj = new $class($this->proc);
                            $obj->loadFromXML($child);
                    }

            }

    }


    function loadLanes()
    {
            /*
             *       <model:lane id="_MCobNNbZEeSumZslTrMS1A" name="Finance">
    <model:flowNodeRef>_MCobNdbZEeSumZslTrMS1A</model:flowNodeRef>
    <model:flowNodeRef>_MCobOtbZEeSumZslTrMS1A</model:flowNodeRef>
    <model:flowNodeRef>_MCobQdbZEeSumZslTrMS1A</model:flowNodeRef>
    <model:flowNodeRef>_MCobYtbZEeSumZslTrMS1A</model:flowNodeRef>
    <model:flowNodeRef>_MCo_1dbZEeSumZslTrMS1A</model:flowNodeRef>
    <model:flowNodeRef>_MCpAjtbZEeSumZslTrMS1A</model:flowNodeRef>
    </model:lane>
             */

            $seq=0;
            foreach($this->xml->xpath('//model:process') as $procNode) {
                    $seq++;
                    $procNode->registerXPathNamespace('model', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

                    $poolId = self::getAttribute($procNode, 'id');
                    
                    if ($poolId ===null || $poolId==='')
                        $poolId ='process_'.$seq;
                            
                    $poolName = self::getAttribute($procNode,'name');
                    if ($poolName=='')
                        $poolName=$poolId;
                    Context::Log(INFO, "model:process tag $poolId - $poolName");
                    
                    $pool=new BPMN\Pool($this->proc);
                    $pool->type='pool';
                    $pool->id=$poolId;
                    $pool->loadFromXML($procNode);
                    $pool->name=$poolName;

                    $this->proc->pools[$poolName]=$pool;
                    
            }
        
        
            foreach($this->xml->xpath('//model:lane') as $laneNode) {

                    $attr=$lane=$laneNode->attributes();
                    
                    if (isset($attr['name']))
                        $lane=$attr['name']->__ToString();


                    foreach(XMLLoader::children($laneNode) as $child) {
                            if ($child->getName()=='flowNodeRef')
                            {
                                    $tid=$child->__ToString();
                                    $pitem=$this->proc->getItemById($tid);
                                    $pitem->lane=$lane;

//                                    Context::Log(INFO, "Lane node: $lane node $pitem->label");
                            }
                    }
            }

    }
    static function getAttribute($node,$attributeName)
    {
            $attrs=$node->attributes();
            $attr=$attrs[$attributeName];
            if ($attr!=null)
                    return $attr->__toString();
            else 
                    return null;
    }
    function loadShapes()
    {
            /*
                    <omgdc:Bounds height="161.0" width="979.0" x="30.0" y="182.0"/>
             */
    //			$namespaces = $this->xml->getDocNamespaces();

    //			var_dump($namespaces);
            //foreach($namespaces as $ns)
                    {
    //				$this->xml->registerXPathNamespace('di', $namespaces['di']);
    //				$this->xml->registerXPathNamespace('dc', $namespaces['dc']);
                    }
            //$this->xml->registerXPathNamespace('di', $namespaces['bpmndi']);

    //			foreach($this->xml->xpath('//di:BPMNShape') as $shape) {
            foreach($this->xml->xpath("//*[local-name() = 'BPMNShape']") as $shape) {
    //
    //				var_dump($shape);
                    $id = XMLLoader::getAttribute($shape,'bpmnElement');
    //				echo $id;

                    foreach($shape->xpath("child::node()") as $bound) {
                    //foreach($shape->children('dc',TRUE) as $bound) {
    //					var_dump($bound);
    //					echo $bound->getName();
                            if ($bound->getName()=='Bounds')
                            {
                                    $attr=$bound->attributes();
    //						echo $attr['x'].'-'.$attr['y'];
                                    $item =$this->proc->getItemById($id);
                                    if ($item!=null)
                                            {
                                            $item->xCoord =$attr['x']->__toString();
                                            $item->yCoord =$attr['y']->__toString();
                                            }
                            }
                    }
            }

    }
    static function children($node)
    {
            $children=Array();

            foreach($node->xpath("descendant::node()") as $child) {
                    $children[]=$child;
            }

    /*			$children=$node->children('model',TRUE);
            if (count($children)==0)
                    $children=$node->children(); */

            return $children; 

    }
    static function getChildren($node)
    {
            $nsList = $node->getDocNamespaces(true);

            $children=array();

            if (count($nsList)==0)
            {
                    foreach($node->children() as $child)
                    {
                            $children[]=$child;
                    }
            }
            else
            {
                    foreach($nsList as $ns=>$nsFull)
                    {
                            foreach($node->children($nsFull) as $child)
                            {
                                    $children[]=$child;
                            }
                    }
            }
            return $children;
    }

}
	

