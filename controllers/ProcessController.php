<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of Controller_process
 *
 * @author ralph
 */
class ProcessController extends Controller{
    public function Action_validate($req)
    {
	$file=$req["file"];
	$proc=BPMN\Process::Load($file,true);
        
        $proc->Validate();
        if (Context::$validitionErrorsCount==0)
        {
            echo "No Validation Messages";
        }
    }
    public function Action_test($req)
    {
        $this->Action_start($req);
    }
    
    public function Action_start($req)
    {
	$file=$req["file"];
	
	$case=ProcessSvc::StartProcess($file);
        
        $this->DisplayErrors();
        if ($case!=null)
        {
	$proc=$case->proc;
	$imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
        
        QueueEngine::checkQueue();
        
        $v=new CaseView();
        $v->header();
	$v->ShowCase($case,$imageFile);                
        $v->endPage();
        }
        
    }

    public function Action_getJson($req)
    {
	header('Content-Type: application/json');

	$file=$req["file"];
	$proc=BPMN\Process::Load($file,true);
		
	$json=$proc->getJson();
                
	Context::Log(INFO,'json '.$json);
	Context::Log(INFO,'json error'.json_last_error());
		echo $json;
    }

    public function Action_describe($req)
    {
	$file=$req["file"];
	$proc=BPMN\Process::Load($file,true);
	$v2=new ProcessView();
        
        $localMenus=array();
        $localMenus[]=array("process.test&file=".$file, "Simulate>","");
        $localMenus[]=array("local.cancel", "Cancel","cancelChanges();return;");
        $localMenus[]=array("local.saveJson", "Save","saveJson();return;");
        $localMenus[]=array("local.validate", "Validate","validate();return;");
        $localMenus[]=array("local.examine", "Examine","debugWindow(procJson);;return;");
        $localMenus[]=array("modeler.edit&file=".$file, "Back to Model","");

        $v2->header(true,false,$localMenus);
	$v2->DescribeProcess($proc,$file);
        $v2->endPage();
       
    }
    public function Action_startList($req) {
                $model=new ProcessModel();
                $rows=$model->listStartEvents();
                
		$actions['process.start']='Start';
                
		$v=new ProcessListView();
		$v->header();
                $v->listProcesses($rows,$actions);
                $v->endPage();
	
    }		
    
    public function Action_list($req) {
		$rows=DB::listProcesses();
                
		$actions['modeler.edit']='Model';
		$actions['process.describe']='Design';
		$actions['process.start']='Start';
		$actions['process.unregister']='unRegister';
		$v=new ProcessListView();
                $v->header();
                $v->listProcesses($rows,$actions);
                $v->endPage();
	
    }		
    public function Action_register($req) {
		$file=$req["file"];
		$proc=BPMN\Process::Load($file,true);
		$db=new ProcessModel();
		$db->Register($proc);
    }		
    public function Action_unregister($req) {
        $file=$req["file"];
        $proc=BPMN\Process::Load($file,true);
	$db=new ProcessModel();
        $db->unRegister($file);
    }		

    public function Action_saveJson($req) {
//		header('Content-Type: application/json');

		$file=$req["file"];
		$json=$req["json"];
                
                $jsonData=html_entity_decode($json);
                
                // to preserve linefeeds and in xml place &#xD; instead 
                $jsonData = str_replace("\\n", "~~n~~",$jsonData);
                $jsonData = str_replace("\\", "",$jsonData);
                Context::Log(INFO,'json data after replace'.$jsonData);
                $jsonData=  json_decode($jsonData,true);
                
                $jsonError=  Helper::getJsonError();
//                if ($jsonError!='')
                {
//		Context::Log(INFO,'json '.var_export($json,true));
		Context::Log(INFO,'json_decode '.var_export($jsonData,true). ' Json Error:'.$jsonError);
		Context::Log(INFO,'json_decode data  '.var_export($jsonData['dataElements'],true));
                }
		$proc=BPMN\Process::Load($file,true);

                ProcessExtensions::SaveExtensionFromJson($proc, $jsonData);
                
		}

    public function Action_show($req) {
		$actions['modeler.edit']='Model';
		$actions['process.describe']='Design';
		$actions['process.test']='Test';
		$actions['process.register']='Register';
		$v=new ProcessListView();
                
                $localMenus=array();
                $localMenus[]=array("modeler.import", "Import ...","");
                $localMenus[]=array("modeler.new", "New Process","newModel();");
                
                $v->header(true,false,$localMenus);
		$v->ProcessList($this->getProcessTypes(),$actions);
                $v->endPage();

}

}
