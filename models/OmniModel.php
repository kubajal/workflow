<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class OmniModel 
{
	// The database connection
	/**
	 * Connect to the database
	 *
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
        var $db;
        public static function getInstance()
        {
            return new OmniModel();
        }
        public function __construct() {
                global $wpdb;
            
                if ($wpdb!==null) {
                  $this->db=new DB_WP();
                }
                else {
                $this->db=new DB();
                }
        }
        
        public function resetCaseData()
        {
            $this->dropTables(true);
            $this->createTables(true);
        }
        public function installDB()
        {
            $this->dropTables(false);
            $this->createTables(false);
        }

        public function uninstallDB()
        {
            $this->dropTables(false);
        }

	public function dropTables($caseDataOnly=false) {

		$tables=$this->getTables($caseDataOnly);
		
		
		foreach($tables as $table)
		{
                    $name=$table['name'];
                    
			echo "<br />dropping table $name";
			$sql="DROP TABLE IF EXISTS `$name` ";
			//echo "<br />$sql";
			$result = $this->db -> query($sql);
		}		
		
				
	}
    public function createTables($caseDataOnly=false)
    {
        $tables=$this->getTables($caseDataOnly);
        
        foreach($tables as $table)
        {
            $name=$table['name'];
            $ddl=$table['sql'];
            $sql="create table `$name` $ddl";
            
                echo "<br />creating table $name";
                //echo "<br />$sql";
                $result = $this->db -> query($sql);

        }		

        foreach($tables as $table)
        {
            $name=$table['name'];
            $sql="select count(*) from `$name` ";
            
                echo "<br />verifying table $name";
                //echo "<br />$sql";
                $result = $this->db -> query($sql);
                if ($result==false)
                {
                        echo '<br />Error:'.$this->db->error();
                        return;
                }
                //print_r($result);
        }		
        
    }

    public function getTables($caseDataOnly=false)
    {
        $models=array('CaseModel','CaseItemModel','ProcessModel');
        $tbls=array();
        
        $tbls[]=CaseModel::getInstance()->getTableDDL();
        $tbls[]=CaseItemModel::getInstance()->getTableDDL();
        $tbls[]=CaseItemStatusModel::getInstance()->getTableDDL();
        $tbls[]=AssignmentModel::getInstance()->getTableDDL();
        
        if (!$caseDataOnly)
        {
        $tbls[]=ProcessModel::getInstance()->getTableDDL();
        $tbls[]=ProcessItemModel::getInstance()->getTableDDL();
        }
        return $tbls;

    }
    

	public function GetTimers($duration)
	{
                
                $table1=CaseItemModel::getInstance()->getTable();
                $table2=ProcessItemModel::getInstance()->getTable();
                $table3=ProcessModel::getInstance()->getTable();

		$arr1= $this->db->select("
			select 'Case Item',caseId, id, (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table1
			where timer <> '' and status = 'Started'
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 < $duration
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");

                $arr2= $this->db->select("
			select 'Process Item',null as caseId, id,p.processName , (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table2 pi
                        join $table3  p on p.processId=pi.processId
			where timer <> '' 
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 <10
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");
                
                $results=  array_merge($arr1,$arr2);
                return $results;
	
	}
		
        /*
         * retrieves all outstanding events for Timer,Message and Signals
         * 
         */
	public function listEvents()
	{

		$table=CaseItemModel::getInstance()->getTable();
                $pTable=ProcessModel::getInstance()->getTable();
                $piTable=ProcessItemModel::getInstance()->getTable();
                
		$status ="status not in ('Complete','Terminated') ";
                
		$sql="select 'Case Item' as source,id,null as processName, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and subType in('timer','message','signal')";
		$arr1= $this->db->select($sql);

		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where  subType in('timer','message','signal')";
                
                
		$arr2= $this->db->select($sql);
		$results=  array_merge($arr1,$arr2);

		return $results;
	}
	public function getMessageHandler($message)
	{
         
		$table= CaseItemModel::getInstance()->getTable();
                $pTable= ProcessModel::getInstance()->getTable();
                $piTable= ProcessItemModel::getInstance()->getTable();
                
		$status ="status not in ('Complete','Terminated') ";
                
		$sql="select 'Case Item' as source,id,null as processName, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and message='$message'";
		$arr1= $this->db->select($sql);

		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where message='$message'";
                
		$arr2= $this->db->select($sql);
		$results=  array_merge($arr1,$arr2);
//                print_r($results);
		return $results;
	}
	public function listTasks($status="")
	{
		
              
		$type="type like '%Task'";
		
		$table=  CaseItemModel::getInstance()->getTable();
		if ($status=="")
			$status ="(status not in ('Complete','Terminated')) ";
		if ($type!="")
			$status = $status." and ".$type;
		$sql="select * from $table where $status";

		return $this->db->select($sql);
		
	}
	public function listMessages()
	{
               
		$table=$this->db->getPrefix()."caseitem";
		
		return $this->db->select("select *
from $table
where message <> '' and status = 'Started'");
		
	}
public function startTransaction()
{
    $this->db->startTransaction();
}
public function commit()
{
    $this->db->commit();
}
public function rollback()
{
    $this->db->rollback();
}
}
