<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */


class AssignmentModel extends OmniModel
{
        public static function getInstance()
        {
            return new AssignmentModel();
        }    
    public function getTable()
    {
        return $this->db->getPrefix()."wf_assignment";
    }

    public  function insert(WFCase\Assignment $assignment)
	{
		
		$data=$assignment->__toArray();

		$id=$this->db->insertRow($this->getTable(),$data);
		$assignment->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $assignment;
		
	}
	public  function update(WFCase\Assignment $assignment)
	{
		$data=$assignment->__toArray();
		$this->db->updateRow($this->getTable(),$data,"id=$assignment->id");
		
		return $assignment;
	}
        public function updateAssignments($caseItem,$condition,$newStatus,$asActor)
        {
            
            $sql='update '.$this->getTable(). " set status ='".$newStatus."'";
            
            if ($asActor!=='')
            {
                $sql.=", asActor='$asActor' ";
            }
            
            $sql.=" where caseItemId=".$caseItem->id;
            
            if ($condition !='')
            {
                $sql.=" and $condition";
            }
            
            $result = $this->db->query($sql);
            
        }        
    public  function loadCase(WFCase\WFCase $case)
    {
       	$table=$this->getTable();
        $caseId=$case->caseId;

	$rows=$this->db->select("select * from $table where caseId =$caseId");
	
	foreach ($rows as $row)
	{
            $item=new WFCase\Assignment();
            $item->__fromArray($row);
            $case->assignments[]=$item; 
	}
    }
        
    public function getTableDDL()
    {
        $table=array();
        $table['name']=$this->getTable();
	$table['sql']="		
                            (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`caseId` int(11) DEFAULT NULL,
				`caseItemId` int(11) DEFAULT NULL,
				`userId` varchar(45) DEFAULT NULL,
				`userName` varchar(45) DEFAULT NULL,
				`userGroup` varchar(45) DEFAULT NULL,
				`workScope` varchar(45) DEFAULT NULL,
				`workScopeType` varchar(45) DEFAULT NULL,
				`actor` varchar(45) DEFAULT NULL,
 				`privilege` varchar(45) DEFAULT NULL,
 				`status` varchar(45) DEFAULT NULL,
				`asActor` varchar(45) DEFAULT NULL,
				`canChange` varchar(45) DEFAULT NULL,
				PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
        return $table;
    }
}