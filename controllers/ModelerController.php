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
class ModelerController extends Controller{
    
    public function Action_new($req)
    {

        $name=$req['processName'];
        $title=$req['processTitle'];
        
            
        $proc=\OmniFlow\BPMN\Process::NewProcess($name,$title);
            
        $xmlFile=$proc->getFileName();
        $req['processId']=$proc->processId;
        
        $bpmn= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<bpmn2:definitions xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:bpmn2=\"http://www.omg.org/spec/BPMN/20100524/MODEL\" xmlns:bpmndi=\"http://www.omg.org/spec/BPMN/20100524/DI\" xmlns:dc=\"http://www.omg.org/spec/DD/20100524/DC\" xmlns:di=\"http://www.omg.org/spec/DD/20100524/DI\" xsi:schemaLocation=\"http://www.omg.org/spec/BPMN/20100524/MODEL BPMN20.xsd\" id=\"sample-diagram\" targetNamespace=\"http://bpmn.io/schema/bpmn\">\n  <bpmn2:process id=\"Process_1\" isExecutable=\"false\">\n    <bpmn2:startEvent id=\"StartEvent_1\"/>\n  </bpmn2:process>\n  <bpmndi:BPMNDiagram id=\"BPMNDiagram_1\">\n    <bpmndi:BPMNPlane id=\"BPMNPlane_1\" bpmnElement=\"Process_1\">\n      <bpmndi:BPMNShape id=\"_BPMNShape_StartEvent_2\" bpmnElement=\"StartEvent_1\">\n        <dc:Bounds height=\"36.0\" width=\"36.0\" x=\"412.0\" y=\"240.0\"/>\n      </bpmndi:BPMNShape>\n    </bpmndi:BPMNPlane>\n  </bpmndi:BPMNDiagram>\n</bpmn2:definitions>";
        
	file_put_contents($xmlFile,$bpmn);

        $this->Action_edit($req);
        
    }
    public function Action_edit($req)
    {
        $readOnly=true;
        
        if (Context::getuser()->can('model'))
            $readOnly=false;
                
	$processId=$req["processId"];
        $proc=  BPMN\Process::LoadProcess($processId,false);
        $file=  $proc->getFileName();

        $v=new ModlerView();
        
        $localMenus=array();
        $localMenus[]=array("process.describe&processId=".$processId, "Proceed to Design","");
        
        if (!$readOnly)
            $localMenus[]=array("local.saveModel", "Save Model","window.saveDiagramFunct();return;");

        $v->header(true,true,$localMenus);
        $v->showEditor($processId,$file,$proc->title);
        $v->endPage();
    }
    public function Action_saveDiagram($req)
    {
        $file=$req['file'];
        $processId=$req['processId'];
        $proc=BPMN\Process::LoadProcess($processId);
        
        $xmlFile = $proc->getFileName();
        $svgFile = $proc->getImageFileName();
        
//        $xmlFile = str_replace(".bpmn", "_new.bpmn", $xmlFile);
        $svg = str_replace('\"','"', $req['svg']);
        $bpmn = str_replace('\"','"', $req['bpmn']);
       	file_put_contents($svgFile,$svg);
	file_put_contents($xmlFile,$bpmn);
        Context::Debug("Saving $file .. $xmlFile $svgFile");

    }
    public function Action_installDemoModel($req)
    {
        $rows=ProcessModel::getInstance()->listProcesses();
        
        if (count($rows)>0)
            return;
        $req['process']='Employee Expenses';
        
        $this->Action_loadFromCatalog($req);
    }
    
    public function Action_loadFromCatalog($req)
    {
        Context::Debug("loadFromCatalog ".print_r($req,true));
            $path='http://workflow.omnibuilder.com/catalogue/';
            
            $name=$req['process'];
            
            $proc=\OmniFlow\BPMN\Process::NewProcess($name,$name);

            $name=  str_replace(' ', '%20', $name);
            
            $file=$path.$name.'.bpmn';
            if (!copy($file,$proc->getFileName()))
                echo "failed to copy $file";
        Context::Debug("loadFromCatalog - $file copied ");

            $file=$path.$name.'.xml';
            if (!copy($path.$name.'.xml',$proc->getExtensionFileName()))
                   echo "failed to copy $file";

        Context::Debug("loadFromCatalog - $file copied ");
            
            $file=$path.$name.'.svg';
            if (!copy($path.$name.'.svg',$proc->getImageFileName()))
                   echo "failed to copy $file";

        Context::Debug("loadFromCatalog - $file copied ");
            
            $processId=$proc->processId;
            $proc=BPMN\Process::LoadProcess($processId);        
            $proc->Update();

        Context::Debug("loadFromCatalog - loadProcess completed");
            
            $req['processId']=$proc->processId;
            
            $this->Action_edit($req);
    }
    public function Action_import($req)
    {
        $v=new Views();
        $v->header();
        
       $link=Helper::getUrl(array('action'=>'modeler.upload')); 
        
 ?>            
    <form enctype="multipart/form-data" action="<?php echo $link;?>" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Process Name: <input type="text" name="processName" />
    Process Short Description:<input type="text" name="processTitle" />
    <br />Choose a BPMN file to upload:  
     <input name="bpmnfile" type="file"  width="40%"/>
      You may also upload a Process Extension file (optional):  
     <input name="xmlfile" type="file" width="40%"/><br />
     
    <input type="submit" value="Upload Files" />
    </form>            
<?php
       $link=Helper::getUrl(array('action'=>'modeler.loadFromCatalog')); 
        
       $listData=file_get_contents("http://workflow.omnibuilder.com/catalogue/list.txt");
       
       $list=preg_split('/\r\n|\n|\r/',$listData);

       echo "<br />Or Select a Process to Load from our Catalogue:<table>";
       foreach($list as $item) {
           $parts=explode(";",$item);
           $name=$parts[0];
           $title=$parts[1];
           $hlink=$link."&process=$name";
           
           echo "<tr><td width='12%'><a href='$hlink'>$name</a></td><td>$title</td><td><img src='http://workflow.omnibuilder.com/catalogue/$name.png' style='height:150px;' /></td></tr>";
       }
       echo "</table>";
      
        $v->endPage();
        
    }    
    public function Action_upload($req)
    {
           if (!empty($_FILES["bpmnfile"])) {
                $uploadedfile = $_FILES["bpmnfile"];

                if ($uploadedfile["error"] !== UPLOAD_ERR_OK) {
                    echo "<p>An error occurred.</p>";
                    exit;
                }
                
            $name=$req['processName'];
            $title=$req['processTitle'];
            
            $proc=\OmniFlow\BPMN\Process::NewProcess($name,$title);
            
            $procFile=$proc->getFileName();
            
            $filename=$this->uploadFile("bpmnfile",$procFile);
            if ($filename===false)
                return;
            $procFile=$proc->getExtensionFileName();

            $filename=$this->uploadFile("xmlfile",$procFile);
            
            $req['processId']=$proc->processId;
            $this->Action_edit($req);
            }  
    }
    function uploadFile($uploadName,$fileName)
    {
           if (empty($_FILES[$uploadName])) {
               return false; 
           }
            $file = $_FILES[$uploadName];

            if ($file["error"] !== UPLOAD_ERR_OK) {
//                echo "<p>An error occurred.</p>";
                return false;
            }
            // ensure a safe filename
            //$name = preg_replace("/[^A-Z0-9._-]/i", "_", $file["name"]);
        echo $fileName;
            // don't overwrite an existing file
            $i = 0;
            $parts = pathinfo($fileName);
            while (file_exists($fileName)) {
                $i++;
                $fileName = $parts["filename"] . "-" . $i . "." . $parts["extension"];
            }

                // preserve file from temporary directory
                $success = move_uploaded_file($file["tmp_name"],
                     $fileName);
                if (!$success) { 
                    echo "<p>Unable to save file.</p>";
                    exit;
                }

                // set proper permissions on the new file
                chmod($fileName, 0644);
                
            return $fileName;
    }
    

}
