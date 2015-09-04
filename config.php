<?php
namespace OmniFlow;

class Config {
	

	var $dbtype = 'mysql';
	var $host = '127.0.0.1';
	var $user = 'root';
	var $db = 'wordpress';
	var $password = '';
	var $scriptPath=__DIR__;
	var $processPath;
	static $pageUrl;
	static $configInstance;
	
	public function __construct()
	{
		$this->processPath=$this->scriptPath.'/processes';
	}
	public static function getConfig()
	{
		if (Config::$configInstance==null)
			Config::$configInstance=new Config();
			
		return Config::$configInstance;
		
	}
}
