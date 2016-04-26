<?php

namespace OmniFlow;
/*
 * Copyright (c) 2015, Omni-Workflow - Omnibuilder.com by OmniSphere Information Systems. All rights reserved. For licensing, see LICENSE.md or http://workflow.omnibuilder.com/license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class DB 
{
	// The database connection
	protected static $_connection;
        protected static $supportsTrans=false;
        
        protected $connection;

	/**
	 * Connect to the database
	 *
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
        public function __construct() {
		// Try and connect to the database
                
		if(!isset(self::$_connection)) {
			$config = Config::getConfig();
			
			self::$_connection = new  \mysqli($config->host,$config->user,$config->password,$config->db);

		}

		// If connection was not successful, handle the error
		if(self::$_connection === false) {
			// Handle error - notify administrator, log to a file, show an error screen, etc.
			return false;
		}
		$this->connection = self::$_connection;
	}
        
	public function getPrefix()
	{
		return "wp_";
	}        
        private function hasError($query)
        {
                $err=$this->connection -> error;
                if ($err!='')
                {
        	Context::Log(ERROR ,$err.$query);
                return true;
                }
                else {
                    return false;
                }
            
        }

	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	public function query($query) {
		// Connect to the database

		// Query the database
		$result = $this->connection -> query($query);
                if ($this->hasError($query))
                    return false;
		return $result;
	}


        public function startTransaction()
        {
            if (self::$supportsTrans===true) {
                
                $this->connection->autocommit(FALSE);
                $this->connection->begin_transaction();
            }

        }
        public static function commit()
        {
            if (self::$supportsTrans===true) {

                $this->connection->commit();
            }
        }
        public static function rollback()
        {
            if (self::$supportsTrans===true) {
                $this->connection->rollback();
            }
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
		
                if ($this->hasError($query))
                    return false;
                
		while ($row = $result -> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function quote($value) {
		
		return "'" . $this->connection -> real_escape_string($value) . "'";

	}
		
	public function insertRow($table,$data)
	{
         Context::Debug("InsertRow -$table ".print_r($data,true));
	
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
                if ($this->hasError($query))
                    return false;
		
		$id = $this->connection->insert_id;
		return $id;
	}
	public function updateRow($table,$data,$where)
	{
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
                if ($this->hasError($query))
                    return false;
		
		Context::Log(INFO,'db:update '.$sql.' res:'.$result);
	
		return $result;
	}

}