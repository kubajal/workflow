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

function Action_dashboard($req)
{
        Context::setSession('returnTo','task.dashboard');
    
        $v=new DashboardView();
        if (!isset($req['_returning']))
            $v->header();
    
        $data=array();
        
        $tasks=OmniModel::getInstance()->listTasks();
        $data['tasks']=$tasks;
        
        $starts=ProcessModel::getInstance()->listStartEvents();
        $data['events']=$starts;
        
        Context::setSession('returnTo','task.dashboard');
        $v->Show($data);
        $v->endPage();
}

function Action_list($req)
{
        $rows=OmniModel::getInstance()->listTasks();

        Context::setSession('returnTo','task.list');
        $v=new TaskView();
        if (!isset($req['_returning']))
            $v->header();
        $v->listTasks($rows,'task.list');
        $v->endPage();

}
function Action_saveForm($req)
{
        $caseId=$_POST["_caseId"];
        
	$v=new CaseView();
        $v->header();
        
        $case=ActionManager::saveForm($_POST);
        
        QueueEngine::checkQueue();

        Context::Debug("saveForm completed. displaying case");
        if (!$this->checkReturn())
        {
            $case=WFCase\WFCase::LoadCase($caseId);
            $proc=$case->proc;
            $imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
            $v->ShowCase($case,$imageFile,true);
            $v->endPage();
        }

}
function Action_execute($req)
{
    	$postForm=false;
	if (isset($req['FormProcessed']))
        {
                $postForm=true;
        }
		
	Views::header();
	$caseId=$req["caseId"];
	$id=$req["id"];
    
	$case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
	
	$item = $case->getItem($id);
        
                
	$taskId = $item->processNodeId;
	$task = $proc->getItemById($taskId);

        
        
        // Todo: Move all this logic to ProcessItem->Run
        
        $task->Run($item);
        
        /*
        $item->UserTake();
    
	$actionView=$item->getActionView($postForm);
                
                Context::Log(INFO,"actionView:$actionView");

		if ($actionView===true)
			return;	
                    ActionManager::defaultForm($item);
        */
        Views::endPage();            
	
}
function Action_release($req)
{
    	$postForm=false;
		
	Views::header();
	$caseId=$req["case"];
	$id=$req["item"];
    
	$case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$item = $case->getItem($id);
                
        $item->UserRelease();

        if (!$this->checkReturn())
        {
            Views::endPage();            
        }
	
}
function checkReturn()
{
        $ret=Context::getSession('returnTo');
        if ($ret=='task.list')
        {
            $req['_returning']='yes';
            $this->Action_list($req);
            return true;
        }
        elseif ($ret=='task.dashboard')
        {
            $req['_returning']='yes';
            $this->Action_dashboard($req);
            return true;
        }
    return false;
}
}
