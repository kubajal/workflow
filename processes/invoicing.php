<?php


use OmniFlow\OmniFlow\enum\NotificationTypes;
class InvoicingWorkflow
{
	static function init(OmniFlow\Process $process)
	{
		OmniFlow\Logger::Debug('Custom class invoked');
		$process->associateFunction("Determine Billable Hours","InvoicingWorkflow::CalculateInvoice");

		$process->associateFunction("Create and Send Invoice","InvoicingWorkflow::IssueInvoice");
		
		$process->AddListener("InvoicingWorkflow::Listener");
		
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
	static function CalculateInvoice(OmniFlow\ProcessItem $item,
                        OmniFlow\WFCase\WFCaseItem $caseItem,$input,$from)
	{

		$proc=$item->proc;
		$case=$caseItem->case;

		$case->SetValue("billableHours",500);
		$case->SetValue("amount",15000);
	}
	// set InvoiceId as as a case Variable
	static function IssueInvoice(OmniFlow\ProcessItem $item,
                        OmniFlow\WFCase\WFCaseItem $caseItem,$input,$from)
	{
		$proc=$item->proc;
		$case=$caseItem->case;
		$amount = $case->GetValue("amount");
		
		$case->SetValue("invoiceNo","5001");
	}
}

/*
 * 	custom class to handle invoicing 
 * an example of how to save and retrieve custom objects as process variables
 */
class ACMEInvoice
{
	var $invoiceId;
	var $customerName;
	var $customerAddress;
	var $amount;
	var $date;
	
	
}

class WF_variable_handler_ACMEInvoice
{
	var $id;
	
	public function toString(ACMEInvoice $invoice)
	{
		//return serialize($invoice);
		$this->id=$invoice->invoiceId;
		
	}
	public function fromString()
	{
		// return unserialize($string);
		$inv=new ACMEInvoice();
		$inv->invoiceId=$this->id;
		return $inv;		
	}
}
