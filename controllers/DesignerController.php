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
    public function Action_debugScript($req)
    {
         $v=new ExpressionDebugSymfonyView();
        $v->execute();
    }

    public function Check_editTimer($req)
    {
        return true;
    }
    public function Action_editTimer($req)
    {
	$v2=new EditTimerView();
	$v2->header();
	$v2->display();
        $v2->endPage();
        
    }
}
 
 
