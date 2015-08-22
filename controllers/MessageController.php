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
