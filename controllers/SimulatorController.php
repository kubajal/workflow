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
class SimulatorController extends Controller{

// TODO
public function Action_setUser($req)
{   
		$v=new Views();
                $v->header();
		$v->listEvents($rows);		
                $v->endPage();
}
// TODO
public function Action_startRecording($req)
{	
	
		$v=new Views();
                $v->header();
		$v->listMessages($rows);
                $v->endPage();
	
}
// TODO
public function Action_stopRecording($req)
{	
	
		$v=new Views();
                $v->header();
		$v->listMessages($rows);
                $v->endPage();
	
}
    
    
}
