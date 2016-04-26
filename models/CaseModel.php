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
        public static function getInstance()
        {
            return new CaseModel();
        }
public function getTable()
{
    return $this->db->getPrefix()."wf_case";
}


        // The database connection
public  function getList($status=null)
{
    $table=$this->getTable();


       return $this->db->select("Select * from $table");
}
public  function insert(WFCase\WFCase $case)
{
        $case->created=date("Y-m-d H:i:s");
        $data=$case->__toArray();
        
        $id=$this->db->insertRow($this->getTable(),$data);
        $case->caseId=$id;
        return $case;
}
	
public  function update(WFCase\WFCase $case)
	{
        $case->updated=date("Y-m-d H:i:s");
	$data=$case->__toArray();

	$this->db->updateRow($this->getTable(),$data,"caseId=$case->caseId");

	return $case;
	
	}
	
    public  function load($caseId,WFCase\WFCase $case)
    {

	$table=$this->getTable();
		
	$rows=$this->db->select("select * from $table where caseId =$caseId");
	if (count($rows)==1)
	{
		$row=$rows[0];
        	$case->__fromArray($row);

	}
	CaseItemModel::getInstance()->loadCase($case);
	AssignmentModel::getInstance()->loadCase($case);
	NotificationModel::getInstance()->loadCase($case);
        
	return $case;	
    }
    public function getTableDDL()
    {
        $table=array();
        $table['name']=$this->getTable();
	$table['sql']="		
                        (
				`caseId` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(45) DEFAULT NULL,
				`description` varchar(45) DEFAULT NULL,
				`processName` varchar(45) NOT NULL,
				`processId` int(11) NOT NULL ,
				`processVersion` varchar(45) NOT NULL,
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