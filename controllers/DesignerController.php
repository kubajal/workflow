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
class DesignerController extends Controller{
    public function Action_evaluteScript($req)
    {
	header('Content-Type: application/json');
        
        $script=$req['script'];
        $caseId=$req['caseId'];
        
        $case=WFCase\WFCase::LoadCase($caseId);
                
        $process=$case->proc;
                
        $ret=ScriptEngine::Evaluate($script,$case);
        
        echo $ret;
        
    }
    public function Action_debugScripts($req)
    {
        
        $v=new ExpressionDebugSymfonyView();
	$v->header();
        $v->display();
        $v->endPage();
    }
    public function Action_validate($req)
    {
	$file=$req["file"];
	$proc=BPMN\Process::Load($file,true);
        
        $proc->Validate();
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
    public function Action_editTimer($req)
    {
	$v2=new EditTimerView();
	$v2->display();
        
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
                $jsonData = str_replace("\\", "",$jsonData);
                $jsonData=  json_decode($jsonData,true);
                
                $jsonError=  Helper::getJsonError();
                if ($jsonError!='')
                {
		Context::Log(INFO,'json '.var_export($json,true));
		Context::Log(INFO,'json_decode '.var_export($jsonData,true). ' Json Error:'.$jsonError);
                }
		$proc=BPMN\Process::Load($file,true);

                ProcessExtensions::SaveExtensionFromJson($proc, $jsonData);
                
		}

}
