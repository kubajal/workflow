<?php

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


namespace OmniFlow;


/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class DB_WP
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
                global $wpdb;
                $this->connection=$wpdb;
                
	}
        private function hasError($query)
        {
                $err=$this->connection->last_error;
                if ($err!='')
                {
        	Context::Log(ERROR ,$err.$query);
                return true;
                }
                else {
                    return false;
                }
            
        }
	public function getPrefix()
	{
		return $this->connection->prefix;
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
                $result= $this->connection->get_results($query,ARRAY_A);
                
                if ($this->hasError($query))
                    return false;
                else
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
                
                foreach($result as $row) {
//		while ($row = $result-> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Fetch the last error from the database
	 *
	 * @return string Database error message
	 */
	public function error() {
            
        	$err=$this->connection ->print_error();
                $msg=$this->connection->last_error;
                return $err;
                
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
/*
  
$wpdb->query( $wpdb->prepare( 
	"
		INSERT INTO $wpdb->postmeta
		( post_id, meta_key, meta_value )
		VALUES ( %d, %s, %s )
	", 
        array(
		10, 
		$metakey, 
		$metavalue
	) 
) );       

*/         
         
        $valTypes=Array();
         
        foreach($data as $key=>$val)
        {
             if ($val!==null)
             {
                $cols[]=$key;
                if (is_numeric($val))
                    $valTypes[]='%d';
                else
                    $valTypes[]='%s';

                $vals[]=$val;
             }
        }
        $cols=join($cols,',');
        $valTypes=join($valTypes,',');
        $query=
	"
		INSERT INTO $table
		( $cols )
		VALUES ( $valTypes )
	";
                $this->connection->query( $this->connection->prepare( $query, $vals));
                
                if ($this->hasError($query))
                    return false;
		
		$id = $this->connection->insert_id;
		return $id;
	}
public function updateRow($table,$data,$where)
{
        Context::Debug("updateRow -$table where $where ".print_r($data,true));
    
	$updts=Array();
        $vals=Array();
         
        foreach($data as $key=>$val)
        {
            $cols[]=$key;
            if (is_numeric($val))
                $updt=$key.'= %d';
            else
                $updt=$key.'= %s';

            $vals[]=$val;
            $updts[]=$updt;
        }
	$updts=join($updts,',');
        
	$sql="update $table set $updts  where $where";
        $query=$this->connection->prepare( $sql, $vals);
        $this->connection->query($query );
        
        if ($this->hasError($query))
            return false;
}

}
