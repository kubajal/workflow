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
class CaseController extends Controller{

public function Action_list($req)
{

		$cases=CaseModel::getList();
                $v=new CaseView();
		$v->header();
		$v->ListCases($cases);
                $v->endPage();
}
public function Action_view($req)
{	
    
	$caseId=$req["caseId"];

        $case=WFCase\WFCase::LoadCase($caseId);
	$proc=$case->proc;
	$imageFile = str_replace(".bpmn", ".svg",$case->processFullName);
        $v=new CaseView();
		$v->header();
	$v->ShowCase($case,$imageFile);
                $v->endPage();
	
}
    
    
}
