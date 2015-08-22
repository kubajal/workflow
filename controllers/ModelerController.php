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
class ModelerController extends Controller{
    
    public function Action_new($req)
    {
        
        $file=$req["file"];
        $xmlFile = Config::getConfig()->processPath.'/'.$file;
        $bpmn= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<bpmn2:definitions xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:bpmn2=\"http://www.omg.org/spec/BPMN/20100524/MODEL\" xmlns:bpmndi=\"http://www.omg.org/spec/BPMN/20100524/DI\" xmlns:dc=\"http://www.omg.org/spec/DD/20100524/DC\" xmlns:di=\"http://www.omg.org/spec/DD/20100524/DI\" xsi:schemaLocation=\"http://www.omg.org/spec/BPMN/20100524/MODEL BPMN20.xsd\" id=\"sample-diagram\" targetNamespace=\"http://bpmn.io/schema/bpmn\">\n  <bpmn2:process id=\"Process_1\" isExecutable=\"false\">\n    <bpmn2:startEvent id=\"StartEvent_1\"/>\n  </bpmn2:process>\n  <bpmndi:BPMNDiagram id=\"BPMNDiagram_1\">\n    <bpmndi:BPMNPlane id=\"BPMNPlane_1\" bpmnElement=\"Process_1\">\n      <bpmndi:BPMNShape id=\"_BPMNShape_StartEvent_2\" bpmnElement=\"StartEvent_1\">\n        <dc:Bounds height=\"36.0\" width=\"36.0\" x=\"412.0\" y=\"240.0\"/>\n      </bpmndi:BPMNShape>\n    </bpmndi:BPMNPlane>\n  </bpmndi:BPMNDiagram>\n</bpmn2:definitions>";
        
	file_put_contents($xmlFile,$bpmn);

        $this->Action_edit($req);
        
    }
    public function Action_edit($req)
    {
        
	$file=$req["file"];
        

        $v=new ModlerView();
        
        $localMenus=array();
        $localMenus[]=array("process.describe&file=".$file, "Proceed to Design","");
        $localMenus[]=array("local.saveModel", "Save Model","window.saveDiagramFunct();return;");

        $v->header(true,true,$localMenus);
        
        $v->showEditor($file);
        $v->endPage();
    }
    public function Action_saveDiagram($req)
    {
        $file=$req['file'];
        $xmlFile = Config::getConfig()->processPath.'/'.$file;
        $svgFile = str_replace(".bpmn", ".svg", $xmlFile);
//        $xmlFile = str_replace(".bpmn", "_new.bpmn", $xmlFile);
        $svg = str_replace('\"','"', $req['svg']);
        $bpmn = str_replace('\"','"', $req['bpmn']);
       	file_put_contents($svgFile,$svg);
	file_put_contents($xmlFile,$bpmn);
        Context::Debug("Saving $file .. $xmlFile $svgFile");

    }
    
    public function Action_import($req)
    {
        $v=new Views();
        $v->header();
 ?>            
    <form enctype="multipart/form-data" action="index.php?action=modeler.upload" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Choose a BPMN file to upload:  <input name="bpmnfile" type="file" /><br />
    <input type="submit" value="Upload Files" />
    </form>            
<?php
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
            $filename=$this->uploadFile("bpmnfile");
            if ($filename===false)
                return;
            
            $req['file']=$filename;
            $this->Action_edit($req);
            }  
    }
    function uploadFile($fieldName)
    {
        $uploadDir=Config::getConfig()->processPath.'/';
        
           if (empty($_FILES[$fieldName])) {
               return false; 
           }
            $file = $_FILES[$fieldName];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                echo "<p>An error occurred.</p>";
                return false;
            }
            // ensure a safe filename
            $name = preg_replace("/[^A-Z0-9._-]/i", "_", $file["name"]);
        echo $name;
            // don't overwrite an existing file
            $i = 0;
            $parts = pathinfo($name);
            while (file_exists($uploadDir . $name)) {
                $i++;
                $name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
            }

                // preserve file from temporary directory
                $success = move_uploaded_file($file["tmp_name"],
                    $uploadDir . $name);
                if (!$success) { 
                    echo "<p>Unable to save file.</p>";
                    exit;
                }

                // set proper permissions on the new file
                chmod($uploadDir . $name, 0644);
                
            return $name;
    }
    

}
