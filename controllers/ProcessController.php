<?php

/*
 * Copyright (c) 2015, Omni-Workflow - Omnibuilder.com by OmniSphere Information Systems. All rights reserved. For licensing, see LICENSE.md or http://workflow.omnibuilder.com/license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
        $proc=$this->Action_saveJson($req,false);
        
//	$file=$req["file"];
//	$proc=BPMN\Process::Load($file,true);
        
        $proc->Validate();
        if (Context::$validitionErrorsCount==0)
        {
            echo "No Validation Messages";
        }
    }
    public function Action_test($req)
    {
        $this->Action_start($req,true);
    }
    
    public function Action_start($req,$testMode=false)
    {
//	$file=$req["file"];
        $processId=$req['processId'];
        WFCase\WFCaseItemStatus::$Notes='Process start invoked from url';
	
	$case=ProcessSvc::StartProcess($processId,null,$testMode);
        
        $this->DisplayErrors();
        if ($case!=null)
        {
            $proc=$case->proc;
            $imageFile = $proc->getImageFileName();

            $this->doBatch();

            $lastItem=$case->items[count($case->items)-1];
            if ($lastItem->type == 'userTask') {
                $req['caseId']=$case->caseId;
                $req['action']='task.execute';
                $req['id']=$lastItem->id;
                $cont=new Controller();
                $cont->Action($req);
                }
            else {
                $v=new CaseView();
                $v->header();
                $v->ShowCase($case,$imageFile);                
                $v->endPage();
            }
        }
        
    }
    public function Check_getJson($req)
    {
        return true;
    }

    public function Action_getJson($req)
    {
        ob_start();
	header('Content-Type: application/json');

//	$file=$req["file"];
//	$proc=BPMN\Process::Load($file,true);
        $processId=$req['processId'];
	$proc=BPMN\Process::LoadProcess($processId,true);        
        
		
	$arr=$proc->getJson();
        
        $msgs=ob_get_clean();
        
        $arr['errors']=$msgs;
        
        $json=json_encode($arr);     
        $err=json_last_error();

	Context::Log(INFO,'json '.$json);
	Context::Log(INFO,'json error'.json_last_error());

        echo $json;
    }
    
    public function Check_view($req)
    {
        return true;
    }
    public function Action_view($req)
    {
        $readOnly=true;
        
        if (Context::getuser()->can('design'))
            $readOnly=false;
        
//	$file=$req["file"];
//	$proc=BPMN\Process::Load($file,true);
        $processId=$req['processId'];
	$proc=BPMN\Process::LoadProcess($processId,true);        
	$v2=new ProcessView();
        
        $localMenus=array();

        $v2->header(false,false,$localMenus);
	$v2->ViewProcess($proc);
        $v2->endPage();
       
    }

    public function Action_describe($req)
    {
        $readOnly=true;
        
        if (Context::getuser()->can('design'))
            $readOnly=false;
        
//	$file=$req["file"];
//	$proc=BPMN\Process::Load($file,true);
        $processId=$req['processId'];
	$proc=BPMN\Process::LoadProcess($processId,true);        
	$v2=new ProcessView();
        
        $localMenus=array();
        $localMenus[]=array("process.test&processId=".$processId, "Simulate>","");
        
        if ($readOnly==false) {
            $localMenus[]=array("local.cancel", "Cancel","cancelChanges();return;");
            $localMenus[]=array("local.saveJson", "Save","saveJson();return;");
            $localMenus[]=array("local.validate", "Validate","validate();return;");
            $localMenus[]=array("local.examine", "Examine","debugWindow(procJson);;return;");
        }
        $localMenus[]=array("modeler.edit&&processId=".$processId, "Back to Model","");

        $v2->header(true,false,$localMenus);
	$v2->DesignProcess($proc);
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
		$rows=ProcessModel::getInstance()->listProcesses();
                
		$actions['modeler.edit']='Model';
		$actions['process.describe']='Design';
		$actions['process.start']='Start';
		$actions['process.unregister']='unRegister';
		$v=new ProcessListView();
                $v->header();
                $v->listProcesses($rows,$actions);
                $v->endPage();
	
    }		
    public function Action_download($req) {
        $file=$req['file'];
        $scrPath=  Config::getConfig()->scriptPath;
        $fullPath=$scrPath.$file;
        
        $fullPath=str_replace("/","\\",$fullPath);
            
        if ($fd = fopen ($fullPath, "r")) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a file download
                break;
                // add more headers for other content types here
                default;
                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
                break;
            }
            header("Content-length: $fsize");
            header("Cache-control: private"); //use this to open files directly
            while(!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose ($fd);
        exit();
    }
    public function Action_exportAll($req) {
    	$v=new ProcessListView();
        $v->header();
    	$rows=ProcessModel::getInstance()->listProcesses();

        $scrPath=  Config::getConfig()->scriptPath;
        $bUrl=\OmniFlow\Context::getInstance()->omniBaseURL;
        
        if (!mkdir($scrPath.'\temp'))
                echo 'failed to create folder temp';
        
        echo $scrPath;
                
        foreach($rows as $row) {
        
            $proc=new BPMN\Process($row['processId']);
            $file1=$proc->getFileName();
            $file2=$proc->getExtensionFileName();
            $file3=$proc->getImageFileName();
            
            $target=  $scrPath. "\\temp\\".$row['processName'];
            $file1t=$target.'.bpmn';
            $file2t=$target.'.xml';
            $file3t=$target.'.svg';
            
            copy($file1,$file1t);
            copy($file2,$file2t);
            copy($file3,$file3t);
            
            echo '<br />'.$file1t.' '.$file2t. ' '.$file3t.' copied';
        }    
    }
    public function Action_export($req) {
		$processId=$req["processId"];
		$proc=BPMN\Process::LoadProcess($processId,true);

            $scrPath=  Config::getConfig()->scriptPath;
            $bUrl=\OmniFlow\Context::getInstance()->omniBaseURL;
                
            $file1=$proc->getFileName();
            $file2=$proc->getExtensionFileName();
            $file3=$proc->getImageFileName();
            $file1=  str_replace($scrPath, '', $file1);
            $file2=  str_replace($scrPath, '', $file2);
            $file3=  str_replace($scrPath, '', $file3);
            
            
            
		$v=new ProcessListView();
                $v->header();
                
            $h1= Helper::getAjaxUrl(array("command"=>'process.download',"file"=>$file1));
            $h2= Helper::getAjaxUrl(array("command"=>'process.download',"file"=>$file2));
            $h3= Helper::getAjaxUrl(array("command"=>'process.download',"file"=>$file3));
                
            echo "<a href='$h1'>BPMN File</a>";
            echo "<a href='$h2'>Extension File</a>";
            echo "<a href='$h3'>SVG File</a>";
                
                
                $v->endPage();
                
    }		
    public function Action_delete($req) {
		$processId=$req["processId"];
                
                BPMN\Process::Delete($processId);
                
                $this->Action_show($req);
                
    }		
    public function Action_register($req) {
		$processId=$req["processId"];
		$proc=BPMN\Process::LoadProcess($processId,true);
		$db=new ProcessModel();
	        
		$v=new ProcessListView();
                $v->header();
                $db->unRegister($file);
                $db->Register($proc);
                $v->endPage();
	                
    }		
    public function Action_unregister($req) {
        $file=$req["file"];
        $proc=BPMN\Process::Load($file,true);
	$db=new ProcessModel();
        
        $v=new ProcessListView();
        $v->header();
        $db->unRegister($file);
        $v->endPage();
        
    }		

    public function Action_saveJson($req,$saveToXML=true) {
//		header('Content-Type: application/json');

		// $file=$req["file"];
		// $proc=BPMN\Process::Load($file,false);
                $processId=$req['processId'];
                $proc=BPMN\Process::LoadProcess($processId,false);        
        
		$json=$req["json"];
                
                if (is_string($json)) {

                    $jsonData=html_entity_decode($json);

                    Context::Log(INFO,'json data after html entity decode'.$jsonData);
                    // to preserve linefeeds and in xml place &#xD; instead 
                    $jsonData = str_replace("\\n", "~~n~~",$jsonData);

    /*                
                //    $jsonData = str_replace("\\", "",$jsonData);


                    $jsonData = str_replace('{\\"','{"',$jsonData);
                    $jsonData = str_replace('\\":','":',$jsonData);
                    $jsonData = str_replace(':\\"',':"',$jsonData);
                    $jsonData = str_replace(',\\"',',"',$jsonData);
                    $jsonData = str_replace('\\",','",',$jsonData);
                    $jsonData = str_replace('\\"}','"}',$jsonData); */
                    Context::Log(INFO,'json data after replace'.$jsonData);
                    $jsonData=  json_decode($jsonData,true);

                    $jsonError=  Helper::getJsonError();
                    if ($jsonError!='')
                    {
                        Context::Log(INFO,'json_decode '.var_export($jsonData,true). ' Json Error:'.$jsonError);
                        Context::Log(INFO,'json '.var_export($json,true));
                        echo 'Error'.$jsonError;
                        http_response_code(701);
                        return;
                    }
                } else {
                    $jsonData=$json;
                }

                ProcessExtensions::LoadExtensionFromJson($proc, $jsonData);
                if ($saveToXML)
                    ProcessExtensions::saveExtensions($proc);
                
                $proc->Update();
                
                return $proc;
                
	}

    public function Action_show($req) {
        
        
		$actions['modeler.edit']='Model';
		$actions['process.describe']='Design';
		$actions['process.test']='Test';
		$actions['process.export']='Export';

                if (Context::getuser()->can('model'))
                    $actions['process.delete']='Delete';
                
                $list=  BPMN\Process::getList();
		$v=new ProcessListView();
                
                $localMenus=array();
                if (Context::getuser()->can('model')) {
                    $localMenus[]=array("modeler.import", "Import ...","");
                    $localMenus[]=array("modeler.new", "New Process","newModel();");
                }
                
                $v->header(true,false,$localMenus);
//		$v->ProcessList($this->getProcessTypes(),$actions);
                $v->ProcessList($list,$actions);
                $v->endPage();

}

}
