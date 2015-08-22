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
            public static function getInstance()
        {
            return new ProcessModel();
        }
    public function getTable()
    {
        return $this->db->getPrefix()."wf_process";
    }
    public function ListProcesses()
	{
		$table=$this->getTable();
		
		return $this->db->select("select *
					from $table
					");
			}
	public function UnRegister($processName)
	{
		$table= ProcessItemModel::getInstance()->getTable();
		$table1=$this->getTable();
		
		$sql="delete from $table where processId 
				in (select processId from $table1 where processname ='$processName')";
				
			
		$result = $this->db->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess '.$sql.' res:'.$result);
		if ($result==false)
		{
			Context::Log(ERROR , $this->db->error().$sql);
		}
		$sql="delete from $table1 where processname ='$processName'";
			
		$result = $this->db->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess 2 '.$sql.' res:'.$result);
				if ($result==false)
		{
			Context::Log(ERROR , $this->db->error().$sql);
		}
		
		
		return $result;
		
	}
	public function Register(Process $process)
	{

                $this->db->startTransaction();
                
		$data=array(
			'processName'=> $process->processName
			,'created'=>null
			,'updated'=>null					
		);
		
		$id=$this->db->insertRow($this->getTable(),$data);
		
		
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
		
				self::insertRow(ProcessItemModel::getInstance()->getTable(),$data);
			}	
		}
                $this->db->commit();
				
	}
        
    public function listStartEvents()
    {
		$table=CaseItemModel::getInstance()->getTable();
                $pTable=ProcessModel::getInstance()->getTable();
                $piTable=ProcessItemModel::getInstance()->getTable();
		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where  subType=''";
                
		return $this->db->select($sql);
    }
        
    public function getTableDDL()
    {
        $table=array();
        $table['name']=$this->getTable();
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