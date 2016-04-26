<?php

/*
include_once("processes/invoicing.php");


function default_listener($event,$process)
{
	
	if ($event==OmniFlow\enum\NotificationTypes::ProcessLoaded)
	{
//		if ($process->name=="Invoicing.bpmn" || $process->name=="Invoicing from IO.bpmn")
		{
				InvoicingWorkflow::init($process);
		}
			
	}
	if ($event==OmniFlow\enum\NotificationTypes::NodeInitialized)
	{
		$processItem=$process;
	if ($processItem!=null)
	{
		if (isset($processItem->label))
		if (isset($processItem->id))
		{
		
			if ($processItem->id=="_5iLHZPz6EeSHnZdgWD4apw")
			{
				$processItem->serviceType="script";
				
				$processItem->script = 'echo "Billable Hours for client=5001 are 42.5";';			
			
			}
		}
	}
	}
//	echo $msg;	
		
}

OmniFlow\ProcessSvc::AddListener("*","default_listener");

OmniFlow\ProcessSvc::AssociateClass("Help Desk.bpmn", "HelpDeskWorkflow", "Help_Desk.php");

*/