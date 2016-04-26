<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */


class CaseItemModel extends OmniModel
{
        public static function getInstance()
        {
            return new CaseItemModel();
        }    
    public function getTable()
    {
        return $this->db->getPrefix()."wf_caseitem";
    }

    public  function insert(WFCase\WFCaseItem $item)
	{
		
		$item->started=date("Y-m-d H:i:s");
		
		$data=$item->__toArray();

		$id=$this->db->insertRow($this->getTable(),$data);
		$item->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $item;
		
	}
	public  function update(WFCase\WFCaseItem $item)
	{
		if ($item->status==\OmniFlow\enum\StatusTypes::Completed)
			$item->completed=date("Y-m-d H:i:s");

		$data=$item->__toArray();
		$this->db->updateRow($this->getTable(),$data,"id=$item->id");
		
		return $item;
	}
    public  function loadCase(WFCase\WFCase $case)
    {
       	$table=$this->getTable();
        $caseId=$case->caseId;

	$rows=$this->db->select("select * from $table where caseId =$caseId");
	
	foreach ($rows as $row)
	{
            $item=new WFCase\WFCaseItem($case);
            $item->__fromArray($row);
            $case->items[]=$item; 
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
				`processNodeId` varchar(45) DEFAULT NULL,
				`type` varchar(45) DEFAULT NULL,
				`subType` varchar(45) DEFAULT NULL,
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
				`messageKey` varchar(450) DEFAULT NULL,
				`signalName` varchar(45) DEFAULT NULL,
				`itemValues` varchar(4500) DEFAULT NULL,
				`caseStatus` varchar(45) DEFAULT NULL,
				`caseStatusDate` datetime DEFAULT NULL,
                                `subProcessId` int(11) DEFAULT NULL,
                                `parentId` int(11) DEFAULT NULL,
				`priority` varchar(45) DEFAULT NULL,
				`deadline` datetime DEFAULT NULL,
				`effort` varchar(45) DEFAULT NULL,
				`notes` varchar(450) DEFAULT NULL,
				`created` datetime DEFAULT NULL,
				`updated` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_wf_caseitem_caseId` (`caseId`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
        return $table;
    }

}