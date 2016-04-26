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
    public  function load($processId,BPMN\Process $proc)
    {

	$table=$this->getTable();
		
	$rows=$this->db->select("select * from $table where processId =$processId");
	if (count($rows)==1)
	{
		$row=$rows[0];
        	$proc->__fromArray($row);

	}
	return $proc;	
    }
    
    public  function insert(BPMN\Process $proc)
    {
            //$data=$proc->__toArray();
            $data=array();

            $data['processName']=$proc->processName;
            $data['title']=$proc->title;

            $id=$this->db->insertRow($this->getTable(),$data);
            $proc->processId=$id;
            return $proc;
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
				= (select processId from $table1 "
                        . "where processname ='$processName')";
				
			
		$result = $this->db->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess '.$sql.' res:'.$result);
		if ($result===false)
		{
			Context::Log(ERROR ,"SQL Error".$this->db->error().$sql);
		}
		$sql="delete from $table1 where processname ='$processName'";
			
		$result = $this->db->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess 2 '.$sql.' res:'.$result);
                if ($result===false)
		{
			Context::Log(ERROR ,"SQL Error".$this->db->error().$sql);
		}
		
		
		return $result;
		
	}
	public function update(BPMN\Process $process)
	{

            $this->db->startTransaction();
                
            $data=$process->__toArray();
            $processId=$process->processId;

            $this->db->updateRow($this->getTable(),$data,"processId=$processId");

            $procItemModel=new ProcessItemModel();
            
            $table=$procItemModel->getTable();

            $sql="delete from $table where processId ='$processId'";
				
            $result = $this->db->query($sql);
            
            if ($process->status=='Inactive')
                return;
            
		foreach($process->items as $item)
		{
			if (($item->type=='startEvent') && ($item->getPool()->isExecutable() ))
			{
				$dueDate=null;
				if ($item->hasTimer)
				{
					$dueDate=EventEngine::getDueDate($item);
				}
                            $authorizedGroups= BPMN\AccessRule::getAuthorizedGroups($item);
			$data=array(
				'processId'=> $item->processId,
				'processNodeId'=>$item->id,
				'type' =>$item->type,
                                'subType'=>$item->subType,
				'label'=>$item->label ,
				'timerType'=>$item->timerType ,
				'timer' =>$item->timer ,
				'timerRepeat'=>$item->timerRepeat ,
				'timerDue'=>$dueDate ,
				'message'=> $item->message,
				'signalName'=> $item->signalName,
                                'authorizedGroups'=>$authorizedGroups
						);
		
                		$id=$this->db->insertRow($procItemModel->getTable(),$data);
                                
			}	
		}
                $this->db->commit();
				
	}
	public function delete($processId)
	{

            $this->db->startTransaction();
                
            $procItemModel=new ProcessItemModel();
            
            $table=$procItemModel->getTable();

            $sql="delete from $table where processId ='$processId'";
				
            $result = $this->db->query($sql);

            $table=$this->getTable();
            
            $sql="delete from $table where processId ='$processId'";
            
            $result = $this->db->query($sql);
            
            $this->db->commit();
				
    }
        
        
    public function listStartEvents()
    {
		$table=CaseItemModel::getInstance()->getTable();
                $pTable=ProcessModel::getInstance()->getTable();
                $piTable=ProcessItemModel::getInstance()->getTable();
		$sql="select 'Process Item' as source ,p.processName as processName,pi.processId, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName,authorizedGroups "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where  IfNull(subType,'')=''";
                
		$list= $this->db->select($sql);
        $user=  Context::getUser();
        $roles=$user->roles;
        if (in_array('administrator',$roles)) {
            
            return $list;
        }
                
        $validList=Array();
        foreach($list as $row)
        {
            $valid=false;
            $ags=$row['authorizedGroups'];
            foreach($roles as $role)
            {
                if (strpos($ags,",".$role.",")!==false) {
                    $valid=true;
                    continue;
                }
            }
            if ($valid) {
                $validList[]=$row;
            }
            
        }
        return $validList;
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
				`status` varchar(45) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`processId`),
				KEY `idx_wf_process_name` (`processName`)                                
		) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;";
        return $table;

    }
	
}