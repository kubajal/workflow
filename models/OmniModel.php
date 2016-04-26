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
		
        try {
            
            
		foreach($tables as $table)
		{
                    $name=$table['name'];
                    
			echo "<br />dropping table $name";
			$sql="DROP TABLE IF EXISTS `$name` ";
			//echo "<br />$sql";
			$result = $this->db -> query($sql);
		}		
		
        
        } catch (Exception $ex) {
                
            echo "Error :".$ex->getMessage();
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
        $tbls[]=  NotificationModel::getInstance()->getTableDDL();
        
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
                $table4=  NotificationModel::getInstance()->getTable();
                
                //  Case Items
		$arr1= $this->db->select("
			select 'Case Item' as type,caseId,null as processId, id,
                        (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table1
			where timer <> '' and status = 'Started'
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 < $duration
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");
                //  Process Items
                $arr2= $this->db->select("
			select 'Process Item' as type,null as caseId,p.processId as processId,processNodeId as id,
                        p.processName , (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table2 pi
                        join $table3  p on p.processId=pi.processId
			where timer <> '' 
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 <10
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");
                //  Notifications
		$arr3= $this->db->select("
			select 'Notification' as type ,caseId,null as processId, id,
                        (to_seconds(dueOn)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table4
			where (dueOn is not null  or dueOn <> '' ) and status < 3
                        and (to_seconds(dueOn)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(dueOn)-to_seconds(CURRENT_TIMESTAMP()))/60 < $duration
			order by (to_seconds(dueOn)-to_seconds(CURRENT_TIMESTAMP()))/60");
                
                
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
                
		$sql="select 'Case Item' as source,id,null as processId, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and message='$message'";
		$arr1= $this->db->select($sql);

		$sql="select 'Process Item' as source ,p.processId as processId, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where message='$message'";
                
		$arr2= $this->db->select($sql);
		$results=  array_merge($arr1,$arr2);
//                print_r($results);
		return $results;
	}
	public function getSignalHandler($message)
	{
         
		$table= CaseItemModel::getInstance()->getTable();
                $pTable= ProcessModel::getInstance()->getTable();
                $piTable= ProcessItemModel::getInstance()->getTable();
                
		$status ="status not in ('Complete','Terminated') ";
                
		$sql="select 'Case Item' as source,id,null as processId, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and signalName='$message'";
		$arr1= $this->db->select($sql);

		$sql="select 'Process Item' as source ,p.processId as processId, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where signalName='$message'";
                
		$arr2= $this->db->select($sql);
		$results=  array_merge($arr1,$arr2);
//                print_r($results);
		return $results;
	}
        public function listRecents($forAllUsers=false)
        {
            $tblA=   AssignmentModel::getInstance()->getTable();
            $tblCi=  CaseItemModel::getInstance()->getTable();
            $tblC=  CaseModel::getInstance()->getTable();
            $tblU = $this->db->getPrefix()."users";
            
            $user=  Context::getuser();
            $userId=$user->id;
            $roles= "'" . implode("','", $user->roles) . "'";
            if ($forAllUsers) {
                $condition = " ";
            } else {
                $condition = "where (a.userId = $userId or a.userGroup in ($roles))";
            }

            $sql="SELECT u.user_nicename as userName , a.userGroup , ci.label,ci.caseId,ci.id, c.processName,
            (case when (ci.completed='0000-00-00 00:00:00') 
             THEN
                  ci.started
             ELSE
                  ci.completed
             END) as recent
            ,ci.status 
    from $tblCi ci 
    join $tblC c on ci.caseId = c.caseId
    left outer join $tblA a on ci.id=a.caseItemId 
    left outer join $tblU u on u.ID = a.userId 
    $condition
    ORDER BY (case when (ci.completed='0000-00-00 00:00:00') 
             THEN
                  ci.started
             ELSE
                  ci.completed
             END)
    DESC,ci.id desc limit 40";

            $list= $this->db->select($sql);
            $cases=array();
            $out=array();
            foreach($list as $row)
            {
                $cid = $row['caseId'];
                if (!in_array($cid, $cases)) {
                    $out[]=$row;
                    $cases[]=$cid;
                }
            }
            
            return $out;
        }
	public function listTasks($forAllUsers=false)
	{
            $tblA=   AssignmentModel::getInstance()->getTable();
            $tblCi=  CaseItemModel::getInstance()->getTable();
            $tblC=  CaseModel::getInstance()->getTable();
            $tblU = $this->db->getPrefix()."users";
            
            $user=  Context::getuser();
            $userId=$user->id;
            $roles= "'" . implode("','", $user->roles) . "'";
            if ($forAllUsers) {
                $condition = " ";
            } else {
                $condition = " and (a.userId = $userId or a.userGroup in ($roles))";
            }

            
            $sql="  SELECT u.user_nicename as userName , a.userGroup , ci.label,a.caseId,ci.id, 
                    ci.started , ci.priority, ci.deadline , c.processName
                    FROM $tblA a
                    join $tblCi ci on ci.id=a.caseItemId
                    join $tblC c on ci.caseid=c.caseId
                    left outer join $tblU u on u.ID = a.userId
                    where type in ('userTask')
                    and ci.status not in ('Complete','Terminated')
                    and a.status not in ('D')
                    $condition 
                    order by ci.started";
              /*
		$type="type like '%Task'";
		
		$table=  CaseItemModel::getInstance()->getTable();
		if ($status=="")
			$status ="(status not in ('Complete','Terminated')) ";
		if ($type!="")
			$status = $status." and ".$type;
		$sql="select * from $table where $status";
*/ 		return $this->db->select($sql);
		
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
