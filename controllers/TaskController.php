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
class TaskController extends Controller{

function Action_list($req)
{
        $rows=DB::listTasks();

        $v=new TaskView();
        $v->header();
        $v->listTasks($rows);
        $v->endPage();

}
function Action_saveForm($req)
{
        $caseId=$_POST["_caseId"];
        $case=ActionManager::saveForm($_POST);
        
        QueueEngine::checkQueue();
        
        Context::Debug("saveForm completed. displaying case");
	$case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
	$v=new CaseView();
        $v->header();
	$v->ShowCase($case,$imageFile,true);
        $v->endPage();

}
function Action_execute($req)
{
	$postForm=false;
	if (isset($req['FormProcessed']))
        {
                $postForm=true;
        }
		
	$this->header();
	$caseId=$req["caseId"];
	$id=$req["id"];
    
	$case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
	
	$item = $case->getItem($id);
	$taskId = $item->processNodeId;
	$task = $proc->getItemById($taskId);
	$actionView=$item->getActionView($postForm);
                
                Context::Log(INFO,"actionView:$actionView");

		if ($actionView===true)
			return;	
                    ActionManager::defaultForm($item);
	
	
}}
