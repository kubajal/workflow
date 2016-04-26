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
class TaskController extends Controller{

function Action_dashboard($req)
{
        Context::setSession('returnTo','task.dashboard');
    
        $v=new DashboardView();
        if (!isset($req['_returning']))
            $v->header();
    
        $data=array();

        $tasks=OmniModel::getInstance()->listTasks(Context::getUser()->isAdmin());
        $data['tasks']=$tasks;

        $recents=OmniModel::getInstance()->listRecents(Context::getUser()->isAdmin());
        $data['recents']=$recents;
        
        $starts=ProcessModel::getInstance()->listStartEvents();
        $data['events']=$starts;
        
        Context::setSession('returnTo','task.dashboard');
        $v->Show($data);
        $v->endPage();
}

function Action_list($req)
{
        $rows=OmniModel::getInstance()->listTasks();

//        Context::setSession('returnTo','task.list');
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
        
        Context::setSession('returnTo','task.dashboard');
        

        WFCase\WFCaseItemStatus::$Notes='Form saved from url';
        
        //$case=ActionManager::saveForm($_POST);
        $post=$_POST;
        {
		Context::Log(INFO,' saveForm: '.print_r($post,true));
		$caseId=$post['_caseId'];
		$id=$post['_itemId'];
		$item=CaseSvc::LoadCaseItem($caseId, $id);
                
                if ($item->status===\OmniFlow\enum\StatusTypes::Completed)
                {
                  throw new \Exception("Task is already Completed.");
                }
		if (isset($post['_complete']))
			{
        		TaskSvc::SaveData($item, $post,  enum\StatusTypes::Completed);
			}
                else {
        		TaskSvc::SaveData($item, $post);
	
                 }
        }
        
        $this->doBatch();

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
		
	$v=new CaseView();
        $v->header();

        $caseId=$req["caseId"];
	$id=$req["id"];
    
        try
        {
            $caseItem=TaskSvc::Invoke($caseId, $id);
        }
        catch(\Exception $exc)
        {
            echo $exc->getMessage();
        }

        if (!$this->checkReturn())
        {
            $v->endPage();
        }
	
}
function Action_release($req)
{
    	$postForm=false;
		
        Context::setSession('returnTo','task.dashboard');
        
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
