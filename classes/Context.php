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

class User extends WFObject
{
    	var $id=null;
	var $name;
	var $email;
	var $clientId;	/* for multi tenants implementation */
        var $userCapabilities=array();

    public function can($capability)
    {
        $caps=$this->userCapabilities;
        
        if (isset($caps[$capability]))
        {
            return $caps[$capability];
        }
        else
        {
            return false;
        }
    }

    public function addCapability($capability)
    {
        $this->userCapabilities[$capability]=true;
    }
    
    public function isLoggedIn()
    {
        if ($this->id!==null)
            return true;
        else
            return false;
    }
}
class Context extends WFObject
{
	var $env; 		/*D:Development,T:Text,P:Production */
	var $user;
	var $roleId;
        var $fromWordPress;
        var $feedback=array();
        var $errors=array();
        var $dataToSave=array();
        var $omniBaseURL="";
        
    static $validitionErrorsCount;        
    
    protected static $_instance=null;
        

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
    public function TestData()
    {
        self::addDataToSave("CaseModel", "insert",$case);
        self::addDataToSave("CaseItemModel", "insert",$caseItem);
    }
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

    public static function getuser()
    {
        if (self::getInstance()->user==null)
            self::getInstance()->user=new User();
        
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
		\Logger::configure(array(
				'rootLogger' => array(
						'appenders' => array('default'),
				),
				'appenders' => array(
						'default' => array(
								'class' => 'LoggerAppenderFile',
								'layout' => array(
										'class' => 'LoggerLayoutSimple'
								),
								'params' => array(
										'file' => OMNIWORKFLOW_PATH.'/logs/my.log',
										'append' => true
								)
						)
				)
		));


		$logger = \Logger::getLogger("main");
	}

	/*if ($type==INFO)
		$logger->info($msg);
		elseif($type==LOG)
		$logger->info($msg);*/

	if($type==ERROR)
	{
		$logger->error($msg);
//		echo '<br /><div class="error" style="float: left;">Error: </div><div>'.$msg.'</div>';
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
