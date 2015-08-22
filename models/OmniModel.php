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
	protected static $connection;

	/**
	 * Connect to the database
	 *
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	public static function connect() {
		// Try and connect to the database
		if(!isset(self::$connection)) {
			$config = Config::getConfig();
			
			self::$connection = new  \mysqli($config->host,$config->user,$config->password,$config->db);
		}

		// If connection was not successful, handle the error
		if(self::$connection === false) {
			// Handle error - notify administrator, log to a file, show an error screen, etc.
			return false;
		}
		return self::$connection;
	}

	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	public static function query($query) {
		// Connect to the database
		$connection = self::connect();

		// Query the database
		$result = $connection -> query($query);

		return $result;
	}
        public static function startTransaction()
        {
            $connection = self::connect();
            
            $connection->autocommit(FALSE);
            $connection->begin_transaction();

        }
        public static function commit()
        {
            $connection = self::connect();
            $connection->commit();
            
        }
        public static function rollback()
        {
            $connection = self::connect();
            $connection->rollback();
        }

	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	public static function select($query) {
		$rows = array();
		$result = self::query($query);
		
		if ($result==false)
		{
			Context::Log(ERROR ,self::error().$query);
		}
		if($result === false) {
			return false;
		}
		while ($row = $result -> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}
        public function ResetCaseData()
        {
            $this->dropTables(true);
            $this->createTables(true);
        }

	public function dropTables($caseDataOnly=false) {

		$tables=$this->getTables($caseDataOnly);
		
		$connection = self::connect();
		
		foreach($tables as $table)
		{
                    $name=$table['name'];
                    
			echo "<br />dropping table $name";
			$sql="Drop table `$name` ";
			echo "<br />$sql";
			$result = $this -> query($sql);
			if ($result==false)
			{
				echo '<br />Error:'.self::error();
				return;
			}
		}		
		
				
	}
    public function createTables($caseDataOnly=false)
    {
        $tables=$this->getTables($caseDataOnly);
        
		
        $connection = self::connect();

        foreach($tables as $table)
        {
            $name=$table['name'];
            $ddl=$table['sql'];
            $sql="create table `$name` $ddl";
            
                echo "<br />creating table $name";
                echo "<br />$sql";
                $result = $this -> query($sql);
                if ($result==false)
                {
                        echo '<br />Error:'.self::error();
                        return;
                }
        }		

        foreach($tables as $table)
        {
            $name=$table['name'];
            $sql="select count(*) from `$name` ";
            
                echo "<br />counting table $name";
                echo "<br />$sql";
                $result = $this -> query($sql);
                if ($result==false)
                {
                        echo '<br />Error:'.self::error();
                        return;
                }
                print_r($result);
        }		
        
    }

    public function getTables($caseDataOnly=false)
    {
        $models=array('CaseModel','CaseItemModel','ProcessModel');
        $tbls=array();
        
        $tbls[]=CaseModel::getTableDDL();
        $tbls[]=CaseItemModel::getTableDDL();
        
        if (!$caseDataOnly)
        {
        $tbls[]=ProcessModel::getTableDDL();
        $tbls[]=ProcessItemModel::getTableDDL();
        }
        return $tbls;

    }
    public function createTables2() {
		// Connect to the database
        $tables=array();
        
        $tables[]=  caseModel::getTable();
        $tables[]= caseItemModel::getTable();
        
		$connection = self::connect();
		echo "<br />Creating wf_case table";
		$sql="		
		CREATE TABLE `wf_case` (
				`caseId` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processName` varchar(45) DEFAULT NULL,
				`isProcess` tinyint(1) DEFAULT 0,
				`processFullName` varchar(450) DEFAULT NULL,
				`status` varchar(45) DEFAULT NULL,
				`caseValues` varchar(4500) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`caseId`)
		) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;";
		
		$result = $this -> query($sql);
		
		if ($result==false)
		{
			echo 'Error:'.self::error();
			//return;
		}
		
		echo '<br />Creating wf_caseitem table';
		
		
		$sql="		
		CREATE TABLE `wf_caseitem` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`caseId` int(11) DEFAULT NULL,
				`processNodeId` varchar(45) DEFAULT NULL,
				`type` varchar(45) DEFAULT NULL,
				`label` varchar(45) DEFAULT NULL,
				`actor` varchar(45) DEFAULT NULL,
				`status` varchar(45) DEFAULT NULL,
				`started` datetime DEFAULT NULL,
				`completed` datetime DEFAULT NULL,
				`result` varchar(45) DEFAULT NULL,
				`timerType` varchar(45) DEFAULT NULL,
				`timer` varchar(45) DEFAULT NULL,
				`timerRepeat` varchar(45) DEFAULT NULL,
				`timerDue` datetime DEFAULT NULL,
				`message` varchar(45) DEFAULT NULL,
				`messageKeys` varchar(450) DEFAULT NULL,
				`itemValues` varchar(4500) DEFAULT NULL,
				`notes` varchar(450) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_wf_caseitem_caseId` (`caseId`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
		
		$result = $this -> query($sql);
				
		if ($result==false)
		{
			echo 'Error:'.self::error();
			return;
		}

		echo '<br />Creating wf_process table';
		$sql="
		CREATE TABLE `wf_process` (
				`processId` int(11) NOT NULL AUTO_INCREMENT,
				`processName` varchar(45) NOT NULL,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processFullName` varchar(450) NOT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`processId`)
		) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;";

		$result = $this -> query($sql);
		
		if ($result==false)
		{
			echo 'Error:'.self::error();
			//return;
		}
				
		
		echo '<br />Creating wf_processitem table';
		
		
		$sql="
		CREATE TABLE `wf_processitem` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`processId` int(11) DEFAULT NULL,
				`processNodeId` varchar(45) DEFAULT NULL,
				`type` varchar(45) DEFAULT NULL,
				`label` varchar(45) DEFAULT NULL,
				`timerType` varchar(45) DEFAULT NULL,
				`timer` varchar(45) DEFAULT NULL,
				`timerRepeat` varchar(45) DEFAULT NULL,
				`timerDue` datetime DEFAULT NULL,
				`message` varchar(45) DEFAULT NULL,
				`messageKeys` varchar(450) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_wf_processitem_caseId` (`processId`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
		
		$result = $this -> query($sql);
		
		if ($result==false)
		{
			echo 'Error:'.self::error();
			return;
		}
		
	
		return $result;
	}
	
	
	/**
	 * Fetch the last error from the database
	 *
	 * @return string Database error message
	 */
	public static function error() {
		$connection = self::connect();
		return $connection -> error;
	}

	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public static function quote($value) {
		
		$connection = self::connect();
		return "'" . $connection -> real_escape_string($value) . "'";

	}

	public static function GetTimers($duration)
	{
		$db=new DB();
                
                $table1=  CaseItemModel::getTable();
                $table2= ProcessItemModel::getTable();
                $table3= ProcessModel::getTable();

		$arr1= $db->select("
			select 'Case Item',caseId, id, (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from $table1
			where timer <> '' and status = 'Started'
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 < $duration
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");

                $arr2= $db->select("
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
	public static function getPrefix()
	{
		return "wf_";
	}
		
        /*
         * retrieves all outstanding events for Timer,Message and Signals
         * 
         */
	public static function listEvents()
	{
		$db=new DB();
		$table=  caseItemModel::getTable();
                $pTable= ProcessModel::getTable();
                $piTable= ProcessItemModel::getTable();
                
		$status ="status not in ('Complete','Terminated') ";
                
		$sql="select 'Case Item' as source,id,null as processName, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and subType in('timer','message','signal')";
		$arr1= $db->select($sql);

		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where  subType in('timer','message','signal')";
                
                
		$arr2= $db->select($sql);
		$results=  array_merge($arr1,$arr2);

		return $results;
	}
	public static function getMessageHandler($message)
	{
		$db=new DB();
		$table=  caseItemModel::getTable();
                $pTable= ProcessModel::getTable();
                $piTable= ProcessItemModel::getTable();
                
		$status ="status not in ('Complete','Terminated') ";
                
		$sql="select 'Case Item' as source,id,null as processName, processNodeId,caseId,type,subType,label,timer,timerDue,message,signalName "
                        . " from $table "
                        . " where  $status"
                        ." and message='$message'";
		$arr1= $db->select($sql);

		$sql="select 'Process Item' as source ,p.processName as processName, pi.id as id,pi.processNodeId,null as caseId,pi.type,subType,label,timer,timerDue,message,signalName "
                        . " from $piTable pi
                            join $pTable  p on p.processId=pi.processId
                            where message='$message'";
                
		$arr2= $db->select($sql);
		$results=  array_merge($arr1,$arr2);
//                print_r($results);
		return $results;
	}
	public static function listTasks($status="")
	{
		
		$type="type like '%Task'";
		
		$db=new DB();
		$table=self::getPrefix()."caseitem";
		if ($status=="")
			$status ="(status not in ('Complete','Terminated')) ";
		if ($type!="")
			$status = $status." and ".$type;
		$sql="select * from $table where $status";

		return $db->select($sql);
		
	}
	public static function listMessages()
	{
		$db=new DB();
		$table=self::getPrefix()."caseitem";
		
		return $db->select("select *
from $table
where message <> '' and status = 'Started'");
		
	}

	public static function insertRow($table,$data)
	{
            Context::Debug("InsertRow -$table ".print_r($data,true));
            
		$conn=self::connect();
		
		foreach($data as $key=>$val)
		{
			$cols[]=$key;
			if (is_numeric($val))
				$vals[]=$val;
			else
				$vals[]=self::quote($val);
		}
		$cols=join($cols,',');
		$vals=join($vals,',');
		
		$sql="insert into $table($cols) values($vals)";
		$result = self::query($sql);
		if ($result==false)
		{
			Context::Log(ERROR , self::error().$sql);
		}
		
		$id = $conn->insert_id;
		return $id;
	}
	public static function updateRow($table,$data,$where)
	{
		$conn=self::connect();
		
		$updts=Array();
	
		foreach($data as $key=>$val)
		{
			$updt=$key.'=';
			
			if (is_numeric($val))
				$updt.=$val;
			else
				$updt.=self::quote($val);
			$updts[]=$updt;
		}
		$updts=join($updts,',');
	
		$sql="update $table set $updts where $where";
		$result = self::query($sql);
		
		Context::Log(INFO,'db:update '.$sql.' res:'.$result);
		if ($result==false)
		{
			Context::Log(ERROR , self::error().$sql);
		}
	
		return $result;
	}
	
	
	
}