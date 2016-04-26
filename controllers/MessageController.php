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
class MessageController extends Controller{

/*
 *  receive a message from external system
 * 
 */
function Action_receive($req)
{
    MessageEngine::Recieve($req);
}
/*
 *  simulate a messaget as if it is sent from external system
 *  prompt user for parameters
 */
function Action_simulate($req)
{

}
/*
 *  list global message are can be simulated
 * 
 */
function Action_list($req)
{
            $rows=  OmniModel::getInstance()->listMessages();
	
            
            $v=new Views();
		$v->header();
                $v->listMessages($rows);
                $v->endPage();
}

}
