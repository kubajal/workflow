<?php

use OmniFlow\OmniFlow\enum\NotificationTypes;
class HelpDeskWorkflow
{
	static function init(OmniFlow\Process $process)
	{
		OmniFlow\Logger::Debug('Custom class invoked');
		$process->associateFunction("Determine Billable Hours","InvoicingWorkflow::CalculateInvoice");

		$process->associateFunction("Create and Send Invoice","InvoicingWorkflow::IssueInvoice");
		
//		$process->AddListener("InvoicingWorkflow::Listener");
		
	}
	static function Listener($event,$object)
	{
		OmniFlow\Logger::Debug('<br />Event'.$event.' for'.get_class($object));
		
		if ($event==OmniFlow\enum\NotificationTypes::CaseSaving)
		{
		}
		if ($event==OmniFlow\enum\NotificationTypes::CaseLoaded)
		{
//			echo '<br />Loading case='. $object->getValue("TestVar").' , '.$object->getValue('TestVar2').' and '.$object->getValue('TestVar3');
//			var_dump($object->values);
		}
		
	}
	static function CalculateInvoice(OmniFlow\ProcessItem $item)
	{
	}
	// set InvoiceId as as a case Variable
	static function IssueInvoice(OmniFlow\ProcessItem $item)
	{
		$proc=$item->proc;
		$case=$proc->dataHandler;
		$amount = $case->GetValue("Amount");
		
		$item->result="Invoice#5001 - amount $amount";
		$case->SetValue("Invoice#","5001");
	}
}

