<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class CaseModel extends OmniModel
{

public static function getTable()
{
    return self::getPrefix()."case";
}

public static function resetData()
{
    
}
        // The database connection
public static function getList($status=null)
{
    $table=self::getTable();
       return self::select("Select * from $table");
}
public static function insert(WFCase\WFCase $case)
{
        $data=$case->__toArray();
        $id=self::insertRow(self::getTable(),$data);
        $case->caseId=$id;
        return $case;
}
	
public static function update(WFCase\WFCase $case)
	{
	$data=$case->__toArray();
	self::updateRow(self::getTable(),$data,"caseId=$case->caseId");

	return $case;
	
	}
	
    public static function load($caseId,WFCase\WFCase $case)
    {

	$table=self::getPrefix()."case";
		
	$rows=self::select("select * from $table where caseId =$caseId");
	if (count($rows)==1)
	{
		$row=$rows[0];
        	$case->__fromArray($row);

	}
	caseItemModel::loadCase($case);
	return $case;	
    }
    public static function getTableDDL()
    {
        $table=array();
        $table['name']='wf_case';
	$table['sql']="		
                        (
				`caseId` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processName` varchar(45) DEFAULT NULL,
				`isProcess` tinyint(1) DEFAULT 0,
				`processFullName` varchar(450) DEFAULT NULL,
				`caseStatus` varchar(45) DEFAULT NULL,
				`casestatusDate` datetime DEFAULT NULL,
				`caseValues` varchar(4500) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`caseId`)
		) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;";
        return $table;
    }
}