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
class AdminController extends Controller{


public function Action_listEvents($req)
{
		$rows=  OmniModel::listEvents();
		$v=new Views();
                $v->header();
		$v->listEvents($rows);		
                $v->endPage();
}
public function Action_listMessages($req)
{	
    
		$rows=DB::listMessages();
	
		$v=new Views();
                $v->header();
		$v->listMessages($rows);
                $v->endPage();
	
}
public function Action_resetCaseData($req)
{	
    
                $om=new OmniModel();
                $om->resetCaseData();
		$v=new Views();
                $v->header();
                $v->endPage();
                
}

    
    
}
