<?php
namespace OmniFlow

{
/*
 * Meta classes to manage the relationship between engine classes, DB and XML files
 * 
 * 	we need to have a matrix between groups and class and types
 * 
 * groups are:
 * 	Signal/Message/Timer  Only One
 * 							Tasks (send/receive)
 * 							Start and Intermediate Events
 * 	Condition			 - valid for all but events
 * 	isDefault			 - only for sequenceflow
 * 	Role				 - tasks only
 * 	Action				 - Tasks , Complex Gateway
 * 	Direction(Split/Join) - Gateway

	We hold meta information about the BPMN model
		model nodes and their structure
		node properties
		how they are retrieved and saved
 * 	
 * 
 */
class MetaProperty
{
	const EditNone = 0;
	const EditDisplay = 1;
	const Editable =2;
	const EditDropDown=3;
	const EditText=5;
	const EditActor=6;
	const EditTimer=7;
	const EditMessage=8;
	const EditSignal=9;
	const EditFlow=10;
	const Save=4;
	
	
	var $name;
	var $group;
	var $displayColumn;
	var $title;
	var $editStyle;
	var $saveStyle;
	var $xmlTag;
	var $xmlExt;
	var $editValues;
	
	public function __construct($name,$group,$title='',$column=1,$editStyle=MetaProperty::EditNone,$saveStyle=MetaProperty::Save)
	{
		$this->name=$name;
		$this->group=$group;
		$this->title=$title;
		$this->displayColumn=$column;
		$this->editStyle=$editStyle;
		$this->saveStyle=$saveStyle;		
		$this->xmlTag=$name;
//		$this->className=__NAMESPACE__.'\ActivityProperty';
		$this->xmlExt=false;
	}
	public function getFieldSpec()
	{
/*
 * 	   	    {type:"fieldset", name:"data", label:"Welcome", inputWidth:"auto", list:[
	   	                                                              	   		{ type:"input" , name:"form_input_name", label:"Name", labelWidth:125, inputWidth:125},
	   	                                                         	   		{ type:"input" , name:"form_input_id", label:"Id", labelWidth:125, inputWidth:125, },
	   	                                                         	   		{ type:"input" , name:"form_input_type", label:"Type", labelWidth:125, inputWidth:125},
	   	                                                         	   		{ type:"checkbox" , name:"form_checkbox_1", label:"Checkbox", labelWidth:100 },
	   	                                                         	   		{ type:"checkbox" , name:"form_checkbox_2", label:"Checkbox", labelWidth:100 }
	   	    ] 


type: "select", label: "Account type", options:[
							{text: "Admin", value: "admin"},
							{text: "Organiser", value: "org"},
							{text: "Power User", value: "poweruser"},
							{text: "User", value: "user"}
						]},
 */
		if ($this->editStyle==self::EditDropDown)
		{
			$opt=array();
			foreach($this->editValues as $val)
			{
				$opt[]=array("text"=>$val,"value"=>$val);
			}			
			return array("type"=>"select" , "name"=>$this->getFieldName(), "label"=>$this->title, 
					"labelWidth"=>105, "inputWidth"=>150, "options"=>$opt);		
		}
		elseif ($this->editStyle==self::EditText)
		return array("type"=>"input" , "name"=>$this->getFieldName(), "rows"=>5, "label"=>$this->title, "labelWidth"=>105, "inputWidth"=>425);		
		else
		return array("type"=>"input" , "name"=>$this->getFieldName(), "label"=>$this->title, "labelWidth"=>105, "inputWidth"=>150);		
	}
	public function getFieldName()
	{
		return 'form_input_'.$this->name;
	}
	public function getFieldSetting($formName)
	{
		if ($this->editStyle==MetaProperty::EditDisplay)
		{
			$name=$this->getFieldName();
			return "$formName.setReadonly('$name',true);";
		}
				
	}
	
	public static function getFieldsSettings($formName,$list)
	{
		$txt="";
		foreach($list as $prop)
		{
			$txt.="\n".$prop->getFieldSetting($formName);
		}
		return $txt;
	}
	public static function getFieldsSpec($list)
	{
		$group="";

		$out=array();
		$fields1=array();
		$fields2=array();
//		$fields1[]=array("type"=>"settings" , "labelWidth"=>80, "inputWidth"=>250);
		
		foreach($list as $prop)
		{
			if ($prop->editStyle==MetaProperty::EditNone) 
				continue;
			Context::Log(INFO, "getFieldsSpec prop $prop->group gr $group".var_export($prop,true));
			$pgroup=$prop->group;
			if ($pgroup=='')
				$pgroup='General';
			
			if ($pgroup!=$group)
			{
				if ($group!="")
				{
				
				if (count($fields2)==0)
					$fields=$fields1;
				else
				{
					$fields=array();
					foreach($fields1 as $fld)
					{
						$fields[]=$fld;
					}
					$fields[]=array("type"=>"newcolumn");
					foreach($fields2 as $fld)
					{
						$fields[]=$fld;
					}
				}
				$grArray=array("type"=>"fieldset", "name"=>$group, "label"=>$group, "inputWidth"=>"auto"
					,"list"=>$fields);

				Context::Log(INFO, "getFieldsSpec group".$group.print_r($grArray,true));
				
				$out[]=$grArray;
				$fields1=array();
				$fields2=array();
				}
			}
			$group=$pgroup; 
			if ($prop->displayColumn==1)
				$fields1[]=$prop->getFieldSpec();
			else
				$fields2[]=$prop->getFieldSpec();
				
		}
		if (count($fields2)==0)
			$fields=$fields1;
		else
		{
			$fields=$fields1;
			$fields[]=array("type"=>"newColumn");
			foreach($fields2 as $fld)
			{
				$fields[]=$fld;
			}
		}
		$grArray=array("type"=>"fieldset", "name"=>$group, "label"=>$group, "inputWidth"=>"auto"
					,"list"=>$fields);

		$out[]=$grArray; 
//		$out=$fields;
		$txt=json_encode($out);
		Context::Log(INFO, "getFieldsSpec".$txt);
//		return $txt;
		return $out;
	}
	//MetaProperty::Add($a,$gr,'id','Id',1,MetaProperty::EditDisplay);
	//MetaProperty::Add($a,$gr,'name','Name',2,MetaProperty::Editable);
	
	//public function __construct($name,$group,$title='',$column=1,$editStyle=MetaProperty::EditNone,$saveStyle=MetaProperty::Save)
	public static function add(&$coll,$group,$name,$description='',$column=1,$editStyle=MetaProperty::EditNone,$saveStyle=MetaProperty::Save)
	{
		$prop=new MetaProperty($name,$group,$description,$column,$editStyle,$saveStyle);
		$coll[$name]=$prop;
		return $prop;
	}

	public function toXML($node,$value,ProcessItem $item)
	{
		if ($this->xmlExt)
			ProcessExtensions::setNode($node, $this->xmlTag, $value);
	}
	public function fromXML($xmlNode,ProcessItem $item)
	{
		$val=$xmlNode->__toString();
		$pn=$this->name;
		$item->$pn=$val;
	}
	
	static $activityProperties;
	
	public static function getActivityProperties()
	{
		if (self::$activityProperties!=null)
			return self::$activityProperties;
		$a=array();	
		
		$gr="General";
		
		MetaProperty::Add($a,$gr,'id','Id',1,MetaProperty::EditDisplay);
		MetaProperty::Add($a,$gr,'name','Name',2,MetaProperty::Editable);
		MetaProperty::Add($a,$gr,'type','Type',1,MetaProperty::EditDisplay);
		MetaProperty::Add($a,$gr,'subType','subType',2,MetaProperty::EditDisplay);
		MetaProperty::Add($a,$gr,'label','Label',1,MetaProperty::Editable);
		MetaProperty::Add($a,$gr,'lane','Lane',2);
		MetaProperty::Add($a,$gr,'actor','Actor',1,MetaProperty::Editable)->xmlExt=true;
		MetaProperty::Add($a,$gr,'subProcess','Sub-Process',2,MetaProperty::EditDisplay);
		$p=MetaProperty::Add($a,$gr,'caseStatus','Case Status',2,MetaProperty::EditDisplay);
                        $p->xmlExt=true;
		
		$gr="Navigation";
		MetaProperty::Add($a,$gr,'inflowsLabels','In-Flows',1,MetaProperty::EditDisplay);
		MetaProperty::Add($a,$gr,'outflowsLabels','Out-Flows',2,MetaProperty::EditDisplay);
		
		$gr="Flow Navigation";
		MetaProperty::Add($a,$gr,'fromNodeLabel','From',1,MetaProperty::EditDisplay);
		MetaProperty::Add($a,$gr,'toNodeLabel','To',2,MetaProperty::EditDisplay);
		
		//-- timer
		$gr="Timer";
		MetaProperty::Add($a,$gr,'hasTimer','');
		$p=MetaProperty::Add($a,$gr,'timerType','Timer Type',1,MetaProperty::EditDropDown);
			$p->xmlExt=true;
			$p->editValues=array('','DateTime','duration');
		MetaProperty::Add($a,$gr,'timer','Timer value',1,MetaProperty::Editable)->xmlExt=true;
		MetaProperty::Add($a,$gr,'timerRepeat','Repeat',2,MetaProperty::Editable)->xmlExt=true;
		
		//-- condition
		$gr="Condition";
		$p=MetaProperty::Add($a,$gr,'condition','Condition',1,MetaProperty::EditText);
			$p->xmlExt=true;
			
		//-- direction gateway
		$gr="Gateway";
		$p=MetaProperty::Add($a,$gr,'direction','Direction?',1,MetaProperty::EditDropDown);
			$p->xmlExt=true;
			$p->editValues=array('','Converging','Diverging'); 
		
		$p=MetaProperty::Add($a,$gr,'defaultFlow','Default Flow',2,MetaProperty::EditFlow);
			$p->xmlExt=true;
	
		// -- message --
		$gr="Message";
		MetaProperty::Add($a,$gr,'hasMessage','');
		MetaProperty::Add($a,$gr,'hasSignal','');
		MetaProperty::Add($a,$gr,'message','Message',1,MetaProperty::Editable,MetaProperty::Save)->xmlExt=true;
		MetaProperty::Add($a,$gr,'messageRepeat','Message is Repeated',1,MetaProperty::Editable,MetaProperty::Save)->xmlExt=true;
		MetaProperty::Add($a,$gr,'messageFinalCondition','Final Message Condition',2,MetaProperty::Editable,MetaProperty::Save)->xmlExt=true;

		
		$gr="Action";
		$p=MetaProperty::Add($a,$gr,'actionType','Action Type',1,MetaProperty::EditDropDown);
			$p->xmlExt=true;
			$p->xmlTag= "$p->name";
			$p->editValues=array('None','Form','Script','Function','Email','Web Service');
		$p=MetaProperty::Add($a,$gr,'actionScript','Action Script',1,MetaProperty::EditText);
			$p->xmlExt=true;
			$p->xmlTag= "$p->name";
		$p=MetaProperty::Add($a,$gr,'actionParameters','Action Parameters',1,MetaProperty::Editable);
			$p->xmlExt=true;
			$p->xmlTag="$p->name";
				

		//-- flow only
		
		$gr="Flow";
		MetaProperty::Add($a,$gr,'fromNode','From Node',1,MetaProperty::EditNone);
		MetaProperty::Add($a,$gr,'toNode','To Node',2,MetaProperty::EditNone);
		
		self::$activityProperties=$a;
		return self::$activityProperties;
	}
	static $processProperties;
	
	public static function getProcessProperties()
	{
		if (self::$processProperties!=null)
			return self::$processProperties;
		$a=array();
				
		MetaProperty::Add($a,'name','Name',MetaProperty::Editable);
		MetaProperty::Add($a,'type','Type',MetaProperty::EditDisplay);
		MetaProperty::Add($a,'label','Label',MetaProperty::Editable);
		MetaProperty::Add($a,'actors','Actor')->xmlExt=true;
		MetaProperty::Add($a,'subProcesss','Sub-Processes');

		self::$processProperties=$a;
		return self::$processProperties;
	}
		
}
}