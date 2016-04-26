<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */
class caseItemStatusModel extends OmniModel
{
        public static function getInstance()
        {
            return new caseItemStatusModel();
        }    
    public  function getTable()
    {
        return $this->db->getPrefix()."wf_caseItemStatus";
    }

    public function insert(WFCase\WFCaseItemStatus $item)
	{
		
		$item->statusDate=date("Y-m-d H:i:s");
		
		$data=$item->__toArray();

		$id=$this->db->insertRow($this->getTable(),$data);
		$item->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $item;
		
	}
	public function update(WFCase\WFCaseItem $item)
	{
		if ($item->status==\OmniFlow\enum\StatusTypes::Completed)
			$item->completed=date("Y-m-d H:i:s");

		$data=$item->__toArray();
		
		$this->db->updateRow($this->getTable(),$data,"id=$item->id");
		
		return $item;
	}

    public function getTableDDL()
    {
        $table=array();
        $table['name']=$this->getTable();
	$table['sql']="		
                            (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`caseId` int(11) DEFAULT NULL,
				`itemId` int(11) DEFAULT NULL,
				`flowId` int(11) DEFAULT NULL,
				`userId` varchar(45) DEFAULT NULL,
				`actor` varchar(45) DEFAULT NULL,
				`status` varchar(45) DEFAULT NULL,
				`notes` varchar(245) DEFAULT NULL,
				`statusDate` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_wf_caseitemstatus_caseId` (`caseId`,`itemId`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
        return $table;
    }

}