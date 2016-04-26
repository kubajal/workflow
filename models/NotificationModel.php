<?php

namespace OmniFlow;

/*
 * 	Changes:
 * 		add multi-tenant:	clientId
 * 		add table prefix for each environment
 * 
 */


class NotificationModel extends OmniModel
{
        public static function getInstance()
        {
            return new NotificationModel();
        }    
    public function getTable()
    {
        return $this->db->getPrefix()."wf_notification";
    }

    public  function insert(WFCase\Notification $notification)
	{
		
		$data=$notification->__toArray();

		$id=$this->db->insertRow($this->getTable(),$data);
		$notification->id=$id;
		if ($id==null)
		{
			Context::Log(ERROR , "Error: insert failed to retrieve Id");
		}
		return $notification;
		
	}
	public  function update(WFCase\Notification $notification)
	{
		$data=$notification->__toArray();
		$this->db->updateRow($this->getTable(),$data,"id=$notification->id");
		
		return $notification;
	}
    public  function loadCase(WFCase\WFCase $case)
    {
       	$table=$this->getTable();
        $caseId=$case->caseId;

	$rows=$this->db->select("select * from $table where caseId =$caseId");
	
	foreach ($rows as $row)
	{
            $item=new WFCase\Notification();
            $item->__fromArray($row);
            $case->notifications[]=$item; 
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
				`userType` varchar(15) DEFAULT NULL,
				`userId` varchar(45) DEFAULT NULL,
				`userGroup` varchar(45) DEFAULT NULL,
				`actor` varchar(45) DEFAULT NULL,
				`eventType` varchar(15) DEFAULT NULL,
				`eventDate` datetime DEFAULT NULL,                                
				`ruleId` varchar(45) DEFAULT NULL,
 				`status` varchar(45) DEFAULT NULL,
				`dueOn` datetime DEFAULT NULL,                                
				`cancelDate` datetime DEFAULT NULL,                                
				`repeatSequence` int(3) DEFAULT NULL,
				PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8;";
        return $table;
    }
}
