<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class DB
{
	// The database connection
	protected static $connection;

	/**
	 * Connect to the database
	 *
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	public function connect() {
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
	public function query($query) {
		// Connect to the database
		$connection = $this -> connect();

		// Query the database
		$result = $connection -> query($query);

		return $result;
	}

        public static function startTransaction()
        {
            $db=new DB();
            $connection = $db-> connect();
            
            $connection->autocommit(FALSE);
            $connection->begin_transaction();

        }
        public static function commit()
        {
            $db=new DB();
            $connection = $db-> connect();
            $connection->commit();
            
        }
        public static function rollback()
        {
            $db=new DB();
            $connection = $db-> connect();
            $connection->rollback();

        }
	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	public function select($query) {
		$rows = array();
		$result = $this -> query($query);
		
		if ($result==false)
		{
			Context::Log(ERROR ,$this->error().$query);
		}
		if($result === false) {
			return false;
		}
		while ($row = $result -> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function dropTables() {

		$tables=array("wf_case","wf_caseitem","wf_process","wf_processItem");
		
		$connection = $this -> connect();
		
		foreach($tables as $table)
		{
			echo '<br />dropping $table table';
			$sql="Drop table if exists `$table` ";
			
			$result = $this -> query($sql);
			if ($result==false)
			{
				echo '<br />Error:'.$this->error();
				return;
			}
		}		
		
				
	}
	public function createTables() {
		// Connect to the database
		$connection = $this -> connect();
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
			echo 'Error:'.$this->error();
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
			echo 'Error:'.$this->error();
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
			echo 'Error:'.$this->error();
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
			echo 'Error:'.$this->error();
			return;
		}
		
	
		return $result;
	}
	
	
	/**
	 * Fetch the last error from the database
	 *
	 * @return string Database error message
	 */
	public function error() {
		$connection = $this -> connect();
		return $connection -> error;
	}

	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function quote($value) {
		
		$connection = $this -> connect();
		return "'" . $connection -> real_escape_string($value) . "'";

	}
	public function test()
	{
		$this->insertCase(new WFCase\WFCase());
	
	}
	public static function GetTimers($duration)
	{
		$db=new DB();

		return $db->select("
			select *, (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 inMinutes
			from wf_caseitem
			where timer <> '' and status = 'Started'
			and (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 is not null
			and  (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60 <10
			order by (to_seconds(timerDue)-to_seconds(CURRENT_TIMESTAMP()))/60");
	
	}
	public static function getPrefix()
	{
		return "wf_";
	}
		
	public static function listEvents($status="")
	{
		
		$type="type like '%Event'";
		
		$db=new DB();
		$table=self::getPrefix()."caseitem";
		if ($status=="")
			$status ="(status not in ('Complete','Terminated')) ";
		if ($type!="")
			$status = $status." and ".$type;
		$sql="select * from $table where $status";

		return $db->select($sql);
		
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
	public static function listCases($status=null)
	{
		$db=new DB();
		$table=self::getPrefix()."case";
		return $db->select("Select * from $table");
		
		
	}
	public function insert($table,$data)
	{
		$conn=$this->connect();
		$table=self::getPrefix().$table;
		
		foreach($data as $key=>$val)
		{
			$cols[]=$key;
			if (is_numeric($val))
				$vals[]=$val;
			else
				$vals[]=$this->quote($val);
		}
		$cols=join($cols,',');
		$vals=join($vals,',');
		
		$sql="insert into $table($cols) values($vals)";
		$result = $this->query($sql);
		if ($result==false)
		{
			Context::Log(ERROR , $this->error().$sql);
		}
		
		$id = $conn->insert_id;
		return $id;
	}
	public function update($table,$data,$where)
	{
		$conn=$this->connect();
		$table=self::getPrefix().$table;
		
		$updts=Array();
	
		foreach($data as $key=>$val)
		{
			$updt=$key.'=';
			
			if (is_numeric($val))
				$updt.=$val;
			else
				$updt.=$this->quote($val);
			$updts[]=$updt;
		}
		$updts=join($updts,',');
	
		$sql="update $table set $updts where $where";
		$result = $this->query($sql);
		
		Context::Log(INFO,'db:update '.$sql.' res:'.$result);
		if ($result==false)
		{
			Context::Log(ERROR , $this->error().$sql);
		}
	
		return $result;
	}
	
	
	public function insertCase(WFCase\WFCase $case)
	{
/*		$conn=$this->connect();
		
		
		$sql="insert into wf_case(title,description,processName,processFullName)
				values('$case->label',null,'$case->processName','$case->processFullName')";
		
		$result = $this->query($sql);
		if ($result==false)
		{
			echo $this->error();
		}
		
		$id = $conn->insert_id; */
		$data=$case->__toArray();
		$id=$this->insert("case",$data);
		$case->caseId=$id;
		return $case;
		
	}
	
	public function insertItem(WFCase\WFCaseItem $item)
	{
		
		$item->started=date("Y-m-d H:i:s");
		
		$data=$item->__toArray();
		$id=$this->insert("caseitem",$data);
		$item->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $item;
		
	}
	public static function ListProcesses()
	{
		$db=new DB();
		$table=self::getPrefix()."process";
		
		return $db->select("select *
					from $table
					");
			}
	public function UnRegisterProcess($processName)
	{
		$conn=$this->connect();
		$table=self::getPrefix()."processitem";
		$table1=self::getPrefix()."process";
		
		$sql="delete from $table where processId 
				in (select processId from $table1 where processname ='$processName')";
				
			
		$result = $this->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess '.$sql.' res:'.$result);
		if ($result==false)
		{
			Context::Log(ERROR , $this->error().$sql);
		}
		$sql="delete from $table1 where processname ='$processName'";
			
		$result = $this->query($sql);
		
		Context::Log(INFO,'db:unregisterProcess 2 '.$sql.' res:'.$result);
				if ($result==false)
		{
			Context::Log(ERROR , $this->error().$sql);
		}
		
		
		return $result;
		
	}
	public function RegisterProcess(Process $process)
	{
		$conn=$this->connect();
		/*
		 * 		CREATE TABLE `wf_process` (
				`processId` int(11) NOT NULL AUTO_INCREMENT,
				`processName` varchar(45) NOT NULL,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processFullName` varchar(450) NOT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
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
		 */
		$data=array(
			'processName'=> $process->processName
			,'created'=>null
			,'updated'=>null					
		);
		
		$id=$this->insert("process",$data);
		
		
		foreach($process->items as $item)
		{
			if (($item->type=='startEvent') && ($item->hasTimer || $item->hasMessage ) )
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
				'label'=>$item->label ,
				'timerType'=>$item->timerType ,
				'timer' =>$item->timer ,
				'timerRepeat'=>$item->timerRepeat ,
				'timerDue'=>$dueDate ,
				'message'=> $item->message
						);
		
				$id=$this->insert("processItem",$data);
			}	
		}
				
	}
	public function updateCase(WFCase\WFCase $case)
	{
		$data=$case->__toArray();
		$this->update("case",$data,"caseId=$case->caseId");
	
		return $case;
	
	}
	
	public function updateItem(WFCase\WFCaseItem $item)
	{
/*		$conn=$this->connect();
	
	
		$sql="update wf_caseitem set label='$item->label',status='$item->status',result='$item->result', updated=now() 
		 where id =".$item->id;
	
		$result = $this->query($sql);
		if ($result==false)
		{
			echo "Error:".$sql.$this->error();
			var_dump($item);
		}*/
		if ($item->status==\OmniFlow\enum\StatusTypes::Completed)
			$item->completed=date("Y-m-d H:i:s");

		$data=$item->__toArray();
		
		$this->update("caseitem",$data,"id=$item->id");
		
	
		return $item;
	
	}
	
	
	function loadCase($caseId,WFCase\WFCase $case)
	{

	$table=self::getPrefix()."case";
		
	$rows=$this->select("select * from $table where caseId =$caseId");
	if (count($rows)==1)
	{
		$row=$rows[0];
		
		/*
		$case->caseId=$caseId;
		$case->processName=$row['processName'];
		$case->processFullName=$row['processFullName'];
		*/
		$case->__fromArray($row);

	}
	$table=self::getPrefix()."caseitem";
	
	$rows=$this->select("select * from $table where caseId =$caseId");
	
	
	foreach ($rows as $row)
	{
		$item=new WFCase\WFCaseItem($case);
		$item->__fromArray($row);
		/*
		$item->id=$row['id'];
		$item->caseId=$caseId;
		$item->type=$row['type'];
		$item->label=$row['label'];
		$item->processNodeId=$row['processNodeId'];
		$item->status=$row['status'];
		$item->actor=$row['actor'];
		$item->result=$row['result']; */
		$case->items[]=$item; 
	}
	
	return $case;	
	}
}