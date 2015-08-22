<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class ProcessModel extends OmniModel
{
    public static function getTable()
    {
        return self::getPrefix()."process";
    }
    public static function ListProcesses()
	{
		$db=new DB();
		$table=self::getTable();
		
		return $db->select("select *
					from $table
					");
			}
	public function UnRegister($processName)
	{
		$conn=self::connect();
		$table=  ProcessItemModel::getTable();
		$table1=self::getTable();
		
		$sql="delete from $table where processId 
				in (select processId from $table1 where processname ='$processName')";
				
			
		$result = self::query($sql);
		
		Context::Log(INFO,'db:unregisterProcess '.$sql.' res:'.$result);
		if ($result==false)
		{
			Context::Log(ERROR , self::error().$sql);
		}
		$sql="delete from $table1 where processname ='$processName'";
			
		$result = self::query($sql);
		
		Context::Log(INFO,'db:unregisterProcess 2 '.$sql.' res:'.$result);
				if ($result==false)
		{
			Context::Log(ERROR , self::error().$sql);
		}
		
		
		return $result;
		
	}
	public function Register(Process $process)
	{
		$conn=self::connect();

                self::startTransaction();
                
		$data=array(
			'processName'=> $process->processName
			,'created'=>null
			,'updated'=>null					
		);
		
		$id=self::insertRow(self::getTable(),$data);
		
		
		foreach($process->items as $item)
		{
			if (($item->type=='startEvent') && ($item->getSubProcess()->isExecutable() ))
			{
				$dueDate=null;
				if ($item->hasTimer)
				{
					$dueDate=EventEngine::getDueDate($item);
				}
			$data=array(
				'processId'=> $id,
				'processNodeId'=>$item->id,
				'type' =>$item->type,
                                'subType'=>$item->subType,
				'label'=>$item->label ,
				'timerType'=>$item->timerType ,
				'timer' =>$item->timer ,
				'timerRepeat'=>$item->timerRepeat ,
				'timerDue'=>$dueDate ,
				'message'=> $item->message
						);
		
				self::insertRow(ProcessItemModel::getTable(),$data);
			}	
		}
                self::commit();
				
	}
        
    public static function listStartEvents()
    {
		$db=new DB();
		$table=  caseItemModel::getTable();
                $pTable= ProcessModel::getTable();
                $piTable= ProcessItemModel::getTable();
		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where  subType=''";
                
		return $db->select($sql);
    }
        
    public static function getTableDDL()
    {
        $table=array();
        $table['name']=self::getTable();
	$table['sql']="		
		 (
				`processId` int(11) NOT NULL AUTO_INCREMENT,
				`processName` varchar(45) NOT NULL,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processFullName` varchar(450) NOT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`processId`)
		) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;";
        return $table;

    }
	
}