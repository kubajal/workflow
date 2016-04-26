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
 * Description of Context
 *
 * @author ralph
 */
global $logger;
const ERROR="Error";
const INFO = "Info";
const LOG = "Log";
const VALIDATION_ERROR="validationError";

class Context extends WFObject
{
	var $user;
        var $fromWordPress;
        var $errors=array();
        var $dataToSave=array();
        var $omniBaseURL="";
        var $processPath;
        var $site;
        // Environment  data
        var $invId;
        var $DevProd;
        var $clientId;
        
        var $loginURL;
        
        var $sendEmail=true;
        
        var $inputData=Array();
        var $outputData=Array();
        
	static $pageUrl;
        
        static $batchMode=false;

        
    static $validitionErrorsCount;        
    
    protected static $_instance=null;
    const ERROR="Error";
    const INFO = "Info";
    const LOG = "Log";
    const VALIDATION_ERROR="validationError";
        
    /*
     * Context object need to handle simulation
     *  set user
     *  recording mode
     */
    protected function __construct()
    {
        $config=new Config();
        $this->processPath=$config->processPath;
        $this->sendEmail=$config->sendEmail;
    }

    protected function __clone()
    {
        //Me not like clones! Me smash clones!
    }
    public static function inAdmin()
    {
        if (function_exists('is_admin'))
        {
            return is_admin();
        }
        return false;
    }
    /*
     *  Saving Data
     *  addDataToSave(modelObject,method,sourceObject
     */

    public static function SaveData()
    {
        
        foreach(self::getInstance()->dataToSave as $dataRec)
        {
            $cls=$dataRec[0];
            $method=$dataRec[1];
            $object=$dataRec[2];
            $ret= call_user_func_array(array($cls, $method), array($object));                    
        }
    }
    public static function addDataToSave($modelClass,$operation,$object)
    {
        //         self::addDataToSave("CaseItemModel", "insert",$caseItem);
        $arr=array($modelClass,$operation,$object);

        self::getInstance()->dataToSave[]=$arr;
        
    }
    public static function getSession($variable)
    {
           session_start();
           if (isset($_SESSION[$variable]))
               return $_SESSION[$variable];
           else {
               return null;
           }

    }
    public static function setSession($variable,$val)
    {
           session_start();
           $_SESSION[$variable]=$val;

    }
    public static function getSite()
    {
        if (self::getInstance()->site==null)
            self::getInstance()->site=new Site();
        
        return self::getInstance()->site;
    }
    public static function getuser()
    {
        if (self::getInstance()->user==null)
            self::getInstance()->user=new WFUser\User();
        
        return self::getInstance()->user;
    }
    public static function getInstance()
    {
        if (self::$_instance==null)
            self::$_instance=new Context();

        return self::$_instance;
    }
    public static function Exception(\Exception $ex)
    {
        
        $msg='System Error '.$ex->getMessage().' in file: '.$ex->getFile().
                ' at line '.$ex->getLine();
        self::Log(ERROR, $msg);
        
        if (!self::$batchMode)
            echo '<br />'.$msg;
        
    }
    public static function Error($msg)
    {
        self::Log(ERROR, $msg);
        self::getInstance()->errors[]=$msg;
        if (!self::$batchMode)
            echo "<br />ERROR $msg";
    }
    public static function Debug($msg)
    {
        self::Log(INFO, $msg);
    }
    static function Log($type,$msg)
    {
	global $logger;
	if ($logger==null)
	{
            $config=array(
                'appenders' => array(
                    'default' => array(
                        'class' => 'LoggerAppenderDailyFile',
                        'layout' => array(
                            'class' => 'LoggerLayoutPattern',
                            'params' => array(
                                'conversionPattern' => '%date{H:i:s,u} %-5level %msg%n'
                                )                           
                        ),
                        'params' => array(
                            'datePattern' => 'Y-m-d',
                            'file' => OMNIWORKFLOW_PATH.'/logs/'.'file-%s.log',
                        ),
                    ),
                ),
                'rootLogger' => array(
                    'appenders' => array('default'),
                ),
            );            

            \Logger::configure($config);
            
            $logger = \Logger::getLogger("main");
	}

	/*if ($type==INFO)
		$logger->info($msg);
		elseif($type==LOG)
		$logger->info($msg);*/

	if($type==ERROR)
	{
		$logger->error($msg);
		echo '<br /><div class="error" style="float: left;">Error: </div><div>'.$msg.'</div>';
	}
	if($type==VALIDATION_ERROR)
	{
            self::$validitionErrorsCount++;
		$logger->error($msg);
		echo '<br /><div class="validationError">Error:'.$msg.'</div>';
	}
	else
		$logger->info($msg);

    }


}
