<?php 
namespace OmniFlow
{
/*
 * 
 * 	Process class holds an array of dataObjects
 * 	these are instances of DataObjects
 * 	the first instance is the root object for the process
 * 
 */
class DataManager
{
	/*
	 * copy of getdataObjectsTree
	 */
	public static function getMeta(Process $proc)
	{
		$vars=array();
		foreach ($proc->dataElements as $de)
		{
			$name=$de->name;
			$val=$de->__toArray();
			$vars[]=$val;
		}
		return $vars;
		
	}
 	public static function loadDataModel($xml,Process $proc)
 	{
 		$procData=new ProcessData($proc);
 			
 		$des=array();
 		foreach($xml->xpath('//dataElements') as $dNode) {
 			
			foreach(XMLLoader::getChildren($dNode) as $child)
			{
				if ($child->getName()!='dataElement')
				{
					Context::Log(ERROR,'expecting an "dataElement" found :'.$child->getName());
					continue;
				}
				
				$de= new BPMN\DataElement();
                                $de->__fromXML($child);
                                $des[$de->name]=$de;
			}
			$proc->dataElements=$des;
			return;
			
 		}
 	}

	public static function getRootVars($proc,$data)
	{
		$vars=array();
		foreach ($proc->dataObjects as $de)
		{
			if ($de->parent==null)
			{
				$name=$de->name;
				$val=$data->$name;
				$vars[$name]=$val;
			}
		}
		return $vars;
	}
 	/*
	 * Creates an object to map to root process object
	 *
	 * is called for new case
	 *
	 */
	public static function createDataObject($proc)
	{
//                Context::Log(INFO, "case Object". print_r($proc->dataElements,true));

		$object=array(); // new \stdClass();
                
		foreach ($proc->dataElements as $de)
		{
                    $var = $de->name;
                    $object[$var]='';
		}
//                Context::Log(INFO, "case Object".var_export($object,true));
		return $object;
	}
	
	public static function GetValue($data,$variableName)
	{
		return ProcessData::getObject($data,$variableName);
	}
	public static function SetValue($data,$variableName,$value)
	{
		$obj=ProcessData::getObject($data,$variableName,true,$value);
	
		Context::Log(INFO, 'ProcessData::SetValue '.$variableName.' = '.var_export($value,true).
				' obj: '.var_export($obj,true).' data: '.var_export($data,true));
	}
	
	public static function SaveData($data)
	{
		return serialize($data);
	
	}
	public static function GetData($json)
	{
		return unserialize($json);
	}
	
	
}
/*
 *
 * 	ProcessData Manages dataElments for the process
 *
 * Data Elements structure
 *
 * 	<dataObjects>
 <dataObject name="root" type="processRoot">
 <dataElement name="problem" type="text"/>
 <dataElement name="customer" type="relatationship"  targetObject="customer" required="Yes"/>
 <dataElement name="resolution" type="text"/>
 <dataElement name="escalation" type="string" validValues="Yes,No" scope="case"/>
 <dataElement name="customerFeedback" type="text""/>
 <dataElement name="customerResponse" type="string" validValues="Yes,No""/>
 </dataObject>
 <dataObject name="customer" type="public">
 <dataElement name="id" type="text""/>
 <dataElement name="name" type="text""/>
 <dataElement name="email" type="text""/>
 </dataObject>
 </dataObjects>

 Contains 2 basic objects
 root object
 other objects for reference only
 */
class ProcessData
{

	public static function AddElement(Process $proc,$name,$title,$dataType,$isMultiple,$scope,$values,$parentName=null)
	{
		$de=new BPMN\DataElement();
		$de->name=$name;
		$de->isMultiple=$isMultiple;
		$de->dataType=$dataType;
		$de->validValues=$values;
		$de->scope=$scope;
		if ($parentName==null)
		{
			$de->pathName=$name;
			$proc->dataObjects[$de->pathName]=$de;
		}
		else
		{
			$de->pathName=$parentName.'.'.$name;
			if (!isset($proc->dataObjects[$parentName]))
			{
				//				var_dump($proc->dataObjects);
			}
			$parent=$proc->dataObjects[$parentName];
			$de->parent=$parent;
			$parent->children[]=$de;
			$proc->dataObjects[$de->pathName]=$de;
		}
	}
	public static function getObject($data,$pathName,$createInstance=false,$value=null)
	{
		try
		{
			$path=explode('.',$pathName);
			$parentNode=$data;
			$i=0;
			$lastNode=false;
			foreach($path as $node)
			{
				if ($i==(count($path)-1))
					$lastNode=true;
					
				$i++;

				if (is_array($parentNode))
				{
					if (isset($parentNode[$node]))
					{
						$parentNode=$parentNode[$node];
					}
					elseif ($node=='[]')
					{
						$obj=new \stdClass();
						$parentNode[]=$obj;
						$parentNode=$obj;
					}
					elseif ($createInstance)
					{
						$obj=new \stdClass();
						$parentNode[]=$obj;
						$parentNode=$obj;
						$obj->$node=new \stdClass();
						$parentNode=$obj->$node;
					}
					else
					{
						Context::Log(ERROR, "dataElement $pathName not found in Array and create not specified");
						return null;
					}
				}
				else
				{
					if (property_exists($parentNode,$node))
					{
						if ($lastNode && $value!=null)
							$parentNode->$node=$value;
						$parentNode=$parentNode->$node;
					}
					else
					{
						Context::Log(ERROR, "dataElement $pathName not found");
						return null;
					}
				}
			}
		}
		catch(\Exception $exc)
		{
			Context::Log(ERROR, $exc->message);
		}
		return $parentNode;
	}
	public static function getdataObjectsTree($proc)
	{

		$vars=array();
		foreach ($proc->dataObjects as $de)
		{
			if ($de->parent==null)
			{
				$name=$de->name;
				$val=$de->getAsTree();
				$vars[$name]=$val;
			}
		}
		return $vars;
	}

}
/*
class DataObject
{
	var $name;
	var $title;
	var $multiple;
	var $description;
	var $scope;
	var $elements=array();

	public function fromXML($node)
	{
		$this->name=XMLLoader::getAttribute($node,'name');
		$this->title=XMLLoader::getAttribute($node,'title');
		$this->multi=XMLLoader::getAttribute($node,'multiple');
		$this->scope=XMLLoader::getAttribute($node,'scope');
	}
	public function getMeta()
	{
		$reflect = new \ReflectionClass($this);
		$props   = $reflect->getProperties();
	
		$data=Array();
		foreach ($props as $prop) {
			$name=$prop->getName();
			$val=$this->$name;
			if (!is_array($val) && !is_object($val))
			{
				$data[$name]=$val;
	
			}
		}
			$childrenData=array();
			foreach($this->elements as $child)
			{
				$childData=$child->getMeta();
				$childrenData[]=$childData;
			}
			$data['dataElements']=$childrenData; 
		return $data;
	}
	
}
*/
/*
 * 	path to caseData
 *
 * 	setValue('root.child.grandchild',value);
 *  setValue('order.pizza[First].size','Large');
 *  setValue('order.pizza[New].size','Large');
 *
 *  Array expressions are:
 *  	[sequence]
 *  	[First]
 *  	[New]
 *  	[Last]
 *  	[*]	all
 *  	[size=='Large']
 */
}