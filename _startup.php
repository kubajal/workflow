<?php
//namespace OmniFlow;


define( 'OMNIWORKFLOW_PATH', __DIR__ );

include_once __DIR__."/lib/Log4PHP/Logger.php";
include_once __DIR__."/config.php";
include_once __DIR__."/classes/Helper.php";

spl_autoload_register('omniFlow_AutoLoader');

//include_once __DIR__."/classes/Classes.php";
include_once __DIR__."/svc/API.php";
include_once __DIR__."/classes/Engine/ProcessEngine.php";
include_once __DIR__."/classes/DB.php";
include_once __DIR__."/classes/Data.php";
include_once __DIR__."/classes/meta.php";
include_once __DIR__."/classes/Describer.php";
include_once __DIR__."/custom.php";

use OmniFlow\Context as Context;
use OmniFlow\enum;

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function omniFlow_AutoLoader($className)
{
	$className=str_replace('\\', '/', $className);
        if (strrpos($className, '/')!==false)
        {
            $class= substr($className,strrpos($className, '/')+1);
            $folder=substr($className,0,strrpos($className, '/'));
            
           if ($folder=='OmniFlow/BPMN')
           {
                include __DIR__.'/classes/BPMN/' . $class . '.php';
                return;
           }
           elseif ($folder=='OmniFlow/WFCase')
           {
                include __DIR__.'/classes/WFCase/' . $class . '.php';
                return;
           }
           elseif ($folder=='OmniFlow/enum')
           {
                include __DIR__.'/classes/enum/' . $class . '.php';
                return;
           }
           
        }
        
	if (substr($className,0,5) === "Twig_")
	{
            $file = dirname(__FILE__).'/lib/'.str_replace(array('_', "\0"), array('/', ''), $className).'.php';
            
//            echo 'file:'.$file;
            if (is_file($file))
                            {
                require $file;
                            return;
                            }
	}
	
       if (strpos($folder,'Symfony/Component/ExpressionLanguage')!==false)
        {
//                echo '<br />Folder before:'.$folder;
                $folder=str_replace('Symfony/Component/ExpressionLanguage','',$folder);
			
                        if ($folder!='')
                                $folder=$folder.'/';

                        $path=__DIR__.'/lib/expression/'. $folder. $class . '.php';
//			echo '<br >including '.$path;
                        include $path;
                return;
        }

        if (strrpos($className, '/')!==false)
            $className= substr($className,strrpos($className, '/')+1);
	

        if (endsWith($className, "View"))
        {
            include_once __DIR__.'/views/' . $className . '.php';
            return;
        }
        elseif (endsWith($className, "Controller"))
        {
            include_once __DIR__.'/controllers/' . $className . '.php';
            return;
        }
        elseif (endsWith($className, "Model"))
        {
            include_once __DIR__.'/models/' . $className . '.php';
            return;
        }
        elseif (endsWith($className, "Engine"))
        {
            include_once __DIR__.'/classes/engine/' . $className . '.php';
            return;
        }
	else 
	{
            $path =__DIR__.'/classes/' . $className . '.php';
            include $path;
	}
}


OmniFlow\Logger::$debug =false;

date_default_timezone_set('America/Toronto');

register_shutdown_function( __NAMESPACE__."\\fatal_handler" );

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
        }

//    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

function fatal_handler()
{
	//	return;
	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$error = error_get_last();

	if( $error !== NULL) {
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];

		OmniFlow\Logger::Error($errstr);
		Context::Log(INFO,var_export($error,true));

		//		error_mail(format_error( $errno, $errstr, $errfile, $errline));
	}
}



