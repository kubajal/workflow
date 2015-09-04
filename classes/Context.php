<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
        var $feedback=array();
        var $errors=array();
        var $dataToSave=array();
        var $omniBaseURL="";
        
    static $validitionErrorsCount;        
    
    protected static $_instance=null;
        
    /*
     * Context object need to handle simulation
     *  set user
     *  recording mode
     */
    protected function __construct()
    {
    }

    protected function __clone()
    {
        //Me not like clones! Me smash clones!
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
    public static function feedback($msg)
    {
        self::getInstance()->feedback[]=$msg;
    }
    public static function Exception(\Exception $ex)
    {
        echo '<br />System Error '.$ex->getMessage().' in file: '.$ex->getFile().
                ' at line '.$ex->getLine();
        
    }
    public static function Error($msg)
    {
        self::Log(ERROR, $msg);
        self::getInstance()->errors[]=$msg;
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
