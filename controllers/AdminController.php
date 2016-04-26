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
class AdminController extends Controller{


public function Action_listEvents($req)
{
		$rows=OmniModel::getInstance()->listEvents();
		$v=new Views();
                $v->header();
		$v->listEvents($rows);		
                $v->endPage();
}
public function Action_listMessages($req)
{	
    
		$rows=OmniModel::getInstance()->listMessages();
	
		$v=new Views();
                $v->header();
		$v->listMessages($rows);
                $v->endPage();
	
}
public function Action_installDB($req)
{	
		$v=new Views();
                $v->header();
                $om=new OmniModel();
                $om->installDB();
                $v->endPage();
                
}
public function Action_resetCaseData($req)
{	
    
                $om=new OmniModel();
		$v=new Views();
                $v->header();
                $om->resetCaseData();
                $v->endPage();
                
}

    
    
}
