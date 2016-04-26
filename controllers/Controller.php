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


class Controller
{

    var $ajaxCall=false;
    
/*
 * 
 * 
 * 
 */
public function DisplayErrors()
{
        $msgs=Context::getInstance()->errors;
        foreach($msgs as $msg)
        {
            echo '<br />'.$msg;
        }


}
public function Action($req=null)
{
    
    $user=  Context::getUser();
    
    
 /*   if (!$user->isLoggedIn())
    {
        $this->login();
        return;
    } */
       try {
      
       $this->doAction($req);
       
       } 
    catch (\Exception $ex) {
        Context::Exception($ex);
        }
        
    $this->doBatch();
}
public function doBatch()
{
       try {
        Context::$batchMode=true;
       
        EventEngine::Check();
        QueueEngine::checkQueue();
        
       } 
        catch (\Exception $ex) {
        Context::Exception($ex);
       }
        Context::$batchMode=false;

}
public function login()
{

    $url=Context::getInstance()->loginURL;
    $page="<a href=$url>Please login</a>
<br />To login use the following userids
<table>
    <tr><td>user name</td><td>password</td><td>function</td></tr>
    <tr><td>analyst1</td><td>demo</td><td>To Model and Design Processes</td></tr>
    <tr><td>employee1</td><td>demo</td><td>To create an expense </td></tr>
    <tr><td>manager1</td><td>demo</td><td>To approve the expense</td></tr>
    <tr><td>accounting1</td><td>demo</td><td>To review and process the expense</td></tr>
</table>    ";
    echo $page;
 
}
/*
 *  Checks if user is authorized for this function
 *      default behaviour here that user must be login
 */

public function Check_default($req)
{
    $cont=  Context::getInstance();
    $user= Context::getuser();
    if ($user->id==null) {// not logged
        $this->login();
        return false;
    }
    return true;
}
public function doAction($req=null)
{
	if ($req==null)
		$req=$_REQUEST;
	
	
	Context::Log(INFO,'--------------------');
	Context::Log(INFO,"Controller".print_r($req,true)." user:".  var_export(Context::getuser(),true));
	
	if ((!isset($req["action"]) || ($req["action"]=='') ))
	{
            $req["action"]='task.dashboard';

	}

	$action=$req["action"];
        
        if ($action==='cron')
        {
           return EventEngine::Check();
        }
         

        $pos=strpos($action,'.');
        if ($pos !== false) {        
            $className=__NAMESPACE__.'\\'.ucwords(substr($action,0,$pos)).'Controller';
            $methodName='Action_'.substr($action,$pos+1);
            
            $checkMethodName='Check_'.substr($action,$pos+1);
            
            Context::Log(INFO, $className.' method:'.$methodName);
            $contr=new $className();
            
            if (method_exists($contr, $checkMethodName))
            {
                if (!$contr->$checkMethodName($req))
                    return;
            } else {
                if (!$contr->Check_default($req))
                    return;
                
            }
            
            if (method_exists($contr, $methodName))
            {
                $contr->$methodName($req);

                return;
            }
        }
        
        $methodName='Action_'.$action;
        if (method_exists($this, $methodName))
        {
            $this->$methodName();
            return;
        }
        
	$v=new Views();

	Views::header();
         echo 'No such action:'.$action;
         Context::debug('No such action:'.$action);
        Views::endPage();
        
}
function redirect($req)
{
    
}
}
?>