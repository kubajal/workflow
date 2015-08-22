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
    public static function getTable()
    {
    return self::getPrefix()."caseItemStatus";
    }

    public static function insert(WFCase\WFCaseItem $item)
	{
		
		$item->started=date("Y-m-d H:i:s");
		
		$data=$item->__toArray();
		$id=self::insertRow(self::getTable(),$data);
		$item->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $item;
		
	}
	public static function update(WFCase\WFCaseItem $item)
	{
		if ($item->status==\OmniFlow\enum\StatusTypes::Completed)
			$item->completed=date("Y-m-d H:i:s");

		$data=$item->__toArray();
		
		self::updateRow(self::getTable(),$data,"id=$item->id");
		
		return $item;
	}

    public static function getTableDDL()
    {
        $table=array();
        $table['name']='wf_caseitemstatus';
	$table['sql']="		
                            (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`caseId` int(11) DEFAULT NULL,
				`itemId` int(11) DEFAULT NULL,
				`processNodeId` varchar(45) DEFAULT NULL,
				`userId` varchar(45) DEFAULT NULL,
				`status` varchar(45) DEFAULT NULL,
				`statusDate` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_wf_caseitemstatus_caseId` (`caseId`,`itemId`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
        return $table;
    }

}