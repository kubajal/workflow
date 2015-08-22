<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of ProcessExtensions
 *
 * @author ralph
 */
class ProcessExtensions 
 {
    public static function LoadExtensionFromJson(BPMN\Process $proc,$json)
    {
        
	$properties=MetaProperty::getActivityProperties();
        if (isset($json['items']))
        {
        $jsonItems=$json['items'];

        foreach($jsonItems as $jitem)
        {
            $id=$jitem['id'];
            $item=$proc->getItemById($id);
                    
 		foreach($properties as $prop)
 		{
                    if ($prop->xmlExt==true)
                    {
                        $pname=$prop->name;
                        if (property_exists($item, $pname))
                        {
                            if (isset($jitem[$pname]))
                            {
                                $val=$jitem[$pname];
                                if ($val===null)
                                {

                                }
                                elseif (($val!=='')&& ($val!==null) && $val!=='null')
                                {
                                    $item->$pname=$val;
                                }
                            }
                        }
                    }
 		}
            // now save Item dataElements
            
            if (isset($jitem['dataElements']))
            {
                $itemDes=$jitem['dataElements'];
                $arr=array();
                foreach($itemDes as $de)
                {

                    if ($de['refId']!=='')
                    {
                        $var=new BPMN\ItemVariable();
                        $var->__fromArray($de);

                        $arr[]=  $var;
                    }
                }

                $item->dataElements=$arr;
            }
 	}
        }

        Context::Log(INFO, "Saving Data Elements");

        if (isset($json['dataElements']))
        {
            $proc->dataElements=array();    // remove all existing ones
            
            $des=$json['dataElements'];
            foreach($des as $de)
            {
               $dataElement= new BPMN\DataElement();
               $dataElement->__fromArray($de);
               $proc->dataElements[$dataElement->name]=$dataElement;
            } 
        }
        Context::Log(INFO, "Saving Data Elements ended");
        
        if (isset($json['subprocesses']))
        {
            
            $subs=$json['subprocesses'];
            
            foreach($subs as $sub)
            {
                
               $id=$sub['id'];
               
               $impl=$sub['implementation'];
               foreach($proc->subprocesses as $subProc)
               {
                   if ($subProc->id == $id)
                   {
                       $subProc->implementation=$impl;
                   }
               }
            } 
        }

        
        if (isset($json['actors']))
        {
            $actors=$json['actors'];
            
            $proc->actors=array();    // remove all existing ones

            
            foreach($actors as $ar)
            {
                
               $actor= new \OmniFlow\BPMN\Actor();
               $actor->__fromArray($ar);
               $proc->actors[]=$actor;
            } 
        }
        
        
        if (isset($json['accessRules']))
        {
            $ars=$json['accessRules'];
            
            $proc->accessRules=array();    // remove all existing ones

            
            foreach($ars as $ar)
            {
                
               $acessRule= new \OmniFlow\BPMN\AccessRule();
               $acessRule->__fromArray($ar);
               $proc->accessRules[]=$acessRule;
            } 
        }
        
        if (isset($json['notificationRules']))
        {
            $ars=$json['notificationRules'];
            
            $proc->notificationRules=array();    // remove all existing ones

            
            foreach($ars as $ar)
            {
               $rule= new NotificationRule();
               $rule->__fromArray($ar);
               $proc->notificationRules[]=$rule;
            } 
        }
      
        
//        self::saveExtensions($proc);
    }
    
    static function saveExtensions(BPMN\Process $proc)
    {
	$config=new Config();
	$fileName=str_replace('.bpmn', '.xml',$proc->processName);
 		
	$path = $config->processPath.'/'.$fileName;
 		 		
	$txt=
'<?xml version="1.0"?>
<processExtensions>
<items></items>
<dataElements></dataElements>
<actors></actors>
<accessRules></accessRules>
<notificationRules></notificationRules>
<subprocesses></subprocesses>
</processExtensions>'; 					
        file_put_contents($path, $txt);

        $entries = file_get_contents($path);
        $xml = new \SimpleXmlElement($entries);


 	// ------------- items -----------------
 	$ItemsNodes=$xml->xpath("//items");
                
               
 	if (count($ItemsNodes)==1)
            $ItemsNodes=$ItemsNodes[0];

	$properties=MetaProperty::getActivityProperties();

        foreach($proc->items as $item)
        {
 		$node=$ItemsNodes->addChild("item");
 		$node->addAttribute('id',$item->id);
 		 		
 			
 		$newXML="";
 		foreach($properties as $prop)
 		{
                    if ($prop->xmlExt==true)
                    {
                        $pname=$prop->name;
                        if (property_exists($item, $pname))
                        {
                            $val=$item->$pname;
                            if (($val!='')&& ($val!=null))
                                $newXML.=$prop->toXML($node,$val,$item);	
                        }
                    }
 		}
                
                foreach($item->dataElements as $variable)
                {
                    $varNode=$node->addChild("variable");
                    $variable->__toXML($varNode);
                }
                
                
 	}
 	$DENodes=$xml->xpath("//dataElements");
                
 	if (count($DENodes)==1)
            $DENodes=$DENodes[0];
        
        foreach($proc->dataElements as $de)
        {
            $node=$DENodes->addChild("dataElement");
            $de->__toXML($node);
        }
        

 	$Nodes=$xml->xpath("//actors");
                
 	if (count($Nodes)==1)
            $Nodes=$Nodes[0];
        
        foreach($proc->actors as $de)
        {
            $node=$Nodes->addChild("actor");
            $de->__toXML($node);
        }
        
        

 	$DENodes=$xml->xpath("//accessRules");
                
 	if (count($DENodes)==1)
            $DENodes=$DENodes[0];
        
        foreach($proc->accessRules as $de)
        {
            $node=$DENodes->addChild("accessRule");
            $de->__toXML($node);
        }
        
 	$DENodes=$xml->xpath("//notificationRules");
                
 	if (count($DENodes)==1)
            $DENodes=$DENodes[0];
        
        foreach($proc->notificationRules as $de)
        {
            $node=$DENodes->addChild("notificationRule");
            $de->__toXML($node);
        }
        
        
 	$DENodes=$xml->xpath("//subprocesses");
                
 	if (count($DENodes)==1)
            $DENodes=$DENodes[0];
        
        foreach($proc->subprocesses as $de)
        {
            $node=$DENodes->addChild("subprocess");
            $de->__toXML($node);
        }
        
        $str=$xml->asXML();
        
//        $str=str_replace("~~n~~", "&#xD;", $str);
        $str=str_replace("~~n~~", "&#xA;", $str);
        file_put_contents($path, $str);
//	$xml->saveXML($path);
        
    }        
/* 	function saveExtensions(Process $proc,ProcessItem $item,$values)
 	{
		$config=new Config();
 		$fileName=str_replace('.bpmn', '.xml',$proc->processName);
 		
		$path = $config->processPath.'/'.$fileName;
 		 		
 		if (!file_exists($path))
 		{
 			$txt=
'<?xml version="1.0"?>
<processExtensions>
<items>
</items>
</processExtensions>'; 					
 			file_put_contents($path, $txt);
 		}
 		$entries = file_get_contents($path);
 		$xml = new \SimpleXmlElement($entries);

 		
 		$id =$item->id;
 		
 		// ------------- items -----------------
 		$ItemsNodes=$xml->xpath("//items");
 		if (count($ItemsNodes)==1)
 			$ItemsNodes=$ItemsNodes[0];

 		$nodes=$xml->xpath("//items/item[@id='$id']");

 		if (count($nodes)==1)
 		{
 			$node=$nodes[0];
 		}
 		else
 		{ 
 			$node=$ItemsNodes->addChild("item");
 			$node->addAttribute('id',$id);
 		}
 			
 		
 		$properties=MetaProperty::getActivityProperties();
 			
 		$newXML="";
 		foreach($values as $name=>$val)
 		{
 			$prop=$properties[$name];
 			$newXML.=$prop->toXML($node,$val,$item);	
 		}
 		
 		$xml->saveXML($path);
		
 			
 	} */
 	static function setNode($parent,$nodeName,$value="",$attributes=array())
 	{
 		$nodes=$parent->xpath($nodeName);
 		if (count($nodes)>0)
 			$node=$nodes[0];
 		else
 			$node=$parent->addChild($nodeName);

 		dom_import_simplexml($node)->nodeValue = $value;
 		
 		foreach($attributes as $aName=>$val)
 		{
 			$node->addAttribute($aName,$val);
 		}
 	}
 	
 	function loadExtensions($fileName,Process $proc)
 	{
 		if (!file_exists($fileName))
 			return;
 		$entries = file_get_contents($fileName);
 		
 		/*
 		$xml = simplexml_load_string($entries);
 		$json = json_encode($xml);
 		$array = json_decode($json,TRUE); 	
 		$object = json_decode($json);
 		echo(var_export($object,true));
 			*/
 		
 		$xml = new \SimpleXmlElement($entries);
 		
 		DataManager::loadDataModel($xml,$proc);
 		
 		
 		// ------------- Messages -----------------
 		foreach($xml->xpath('//messageDefinitions/message') as $msgNode) {
 			$msgName=XMLLoader::getAttribute($msgNode, 'name');
 			//				echo '<br />message: '.$msgNode->getName().' '.$msgName;
 				
 			$variables=array();
 	
 			foreach($msgNode->xpath('variable') as $val) {
 				$name=XMLLoader::getAttribute($val, 'name');
 				$type=XMLLoader::getAttribute($val, 'type');
 				//					echo '- value: '.$name.' '.$type;
 				$variables[$name]=$type;
 			}
 			$msgObj=new ProcessMessasge();
 			$msgObj->name=$msgName;
 			$msgObj->variables=$variables;
 			$proc->messages[]=$msgObj;
 		}
 		//			var_dump($this->proc->messages);
 		
 		$properties=array();
 		foreach(MetaProperty::getActivityProperties() as $prop)
 		{
 			if ($prop->xmlTag!="")
				$properties[$prop->xmlTag]=$prop; 			
 		}
 	
 		// ------------- items -----------------
 		foreach($xml->xpath('//items/item') as $item) {
 			$this->parseItemExtension($item,$proc,$properties);
 		}
                
                // subprocesses
                
 		foreach($xml->xpath('//subprocesses/subprocess') as $item) {
                        $id=  XMLLoader::getAttribute($item,'id');
                        $impl=  XMLLoader::getAttribute($item,'implementation');
                        
                        foreach($proc->subprocesses as $subProc)
                        {
                            if ($subProc->id == $id)
                            {
                                $subProc->implementation=$impl;
                            }
                        }                        
 		}

 		foreach($xml->xpath('//actors/actor') as $item) {
                    
                    $actor= new \OmniFlow\BPMN\Actor();
                    $actor->__fromXML($item);
                    $proc->actors[]=$actor;
                    
 		}
                

                // accessRules
                
 		foreach($xml->xpath('//accessRules/accessRule') as $item) {
                    
                    $acessRule=new \OmniFlow\BPMN\AccessRule();
                    $acessRule->__fromXML($item);
                    $proc->accessRules[]=$acessRule;
                    
 		}
 		foreach($xml->xpath('//notificationRules/notificationRule') as $item) {
                    
                    $acessRule= new \OmniFlow\BPMN\NotificationRule();
                    $acessRule->__fromXML($item);
                    $proc->notificationRules[]=$acessRule;
                    
 		}
                
                
 	}
 	/*
 	 * parses the xml for node extensions
 	 *
 	 */
 	function parseItemExtension($item,Process $proc,$properties)
 	{
 		$id=XMLLoader::getAttribute($item, 'id');
 		$type=XMLLoader::getAttribute($item, 'type');
 		$name=XMLLoader::getAttribute($item, 'name');
 		//			echo '<br />Item: '.$id. ' name:'.$name;
 		$processItem=null;
 		if ($id!==null)
 		{
 			$processItem=$proc->getItemById($id);
 		}
 		else
 		{
 			$processItem=$proc->getItemByName($name);
 		}
 		
 		if ($processItem==null)
 		{
 			Context::Log(ERROR,"Item specified in xml file not found $id-$name");
 			return;
 		}
 	
 	//	foreach(XMLLoader::children($item) as $child)
 		foreach($item->children() as $child)
 		{
 			$nodeName=$child->getName();
 			if (isset($properties[$nodeName]) ) 
 			{
                            $prop=$properties[$nodeName];
 				
                            $prop->fromXML($child,$processItem); 				
 			}
                        elseif ($nodeName=='variable')
                        {
//                            Context::Log(INFO,"loadextensions item variable $id ");
                            $itemVar=new BPMN\ItemVariable();
                            $itemVar->__fromXML($child);
                            $processItem->dataElements[]=$itemVar;
                        }
 			
 		}
//		 Context::Log(INFO,"loadextensions item variable $id ".var_export($processItem->dataElements,true));
	
 	} 	
 }
 