<?php

/*
 * Copyright (C) 2015 ralph
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
 * Description of ScriptHelpersEngine
 *
 * @author ralph
 */
class CaseSandbox
{
    var $caseId;
    var $title;
    var $items=array();
    var $statusList=array();
    
    public function __construct(WFCase\WFCase $case) {
        $this->caseId=$case->caseId;
        foreach($case->items as $item)
        {
            $this->items[]=$item->__toArray();
        }
    }
    
}
class UserSandbox
{
    
    public function __construct() {
        $user=Context::getInstance()->user;
        $arr=$user->__toArray();
        foreach($arr as $k=>$v)
        {
            $this->$k=$v;
        }
    }
    
}
class ContextSandbox
{
    
    public function __construct() {
        $context=Context::getInstance();
        $arr=$context->__toArray();
        foreach($arr as $k=>$v)
        {
            $this->$k=$v;
        }
    }
    
}

class ExpressionFunctionHelper
{
	static $variables=array();
	public static function getVar($language,$name)
	{
		if (isset(self::$variables[$name]))
		{
			return self::$variables[$name];
		}
		else
		return null;
	}
	static function setVar($language,$name,$val)
	{
            $language->vars[$name]=$val;
            self::$variables[$name]=$val;
	}
	static function log($language,$exp)
	{
            if (is_array($exp))
            {
                return print_r($exp,true);
            }
            elseif (is_object($exp))
            {
                return 'object:'.var_export($exp,true);
                
            }
            elseif (is_string($exp))
            {
                return 'string:'.htmlspecialchars($exp);
                
            }
            else
                return htmlspecialchars($exp);
	}
	static function output($language,$exp)
	{
            $str="";
            if (is_array($exp))
            {
                $str=print_r($exp,true);
            }
            elseif (is_object($exp))
            {
                $str=var_export($exp,true);
                
            }
            else
                $str=$exp;
            
            $language->output.=$str;
            return $str;
            
	}
	static function returnFunct($language,$exp)
	{
            $language->result=$exp;
            $language->returning=true;
            return $exp;
	}
        static function declareFunct($language,$name)
        {
            $language->vars[$name]=null;
        }
	static function ifEx($language,$exp,$true,$false)
	{
            var_dump($exp);
            var_dump($true);
            var_dump($false);
            
            return 'ifEx-exp:'.$exp.'-'.$true.'-'.$false;
	}
}

/*
 
Date

Examples:

  Date.now()

  Date.daysBetween(date1,date2)

  Date.workingDaysBetween(date1,date2)

  Date.hoursBetween(date1,date2)

 */

/* String

Examples:

  String.size(string1) 

  String.compare(string1,string2)

  String.search(string1,search)

  String.replace(string1,search,replace)

  String.startsWith(string,search)
 * 
 */
class StringSandbox
{
    function size($string)
    {
        return strlen($string);
    }
    function compare($s1,$s2)
    {
        return strcmp($s1,$s2);
    }
    function upper($s1)
    {
        return strtoupper($s1);
    }
    function lower($s1)
    {
        return strtolower($s1);
    }
    function search($s1,$s2)
    {
        return strstr($s1,$s2);
    }
    function replace($string,$s,$r)
    {
        return str_replace($s, $r, $string);
    }
}
class DateSandbox
{
    function setTimezone($zone)
    {
        date_default_timezone_set($zone);    
    }
    function now($format)
    {
        return date($format);
    }
    function getDate($format)
    {
        return getDate($format);
    }
    function daysBetween($d1,$d2)
    {
        
    }
}

class WebSandbox
{
    var $result;
    var $params=Array();
    
    function addParameter($name,$value)
    {
        $this->params[$name]=$value;
    }
    function invokeService($url,$method)
    {
            try
            {
                    $client = new \SoapClient($url);
                    $this->result=$client->$method($this->params);
                    
                    if (is_soap_fault($this->result)) 
                    {
                    return 'SoapFault: ';
//                        trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
                    }                    
                    return $this->result;
//                    return $this->examine("result",$this->result);
            }
            catch(\SoapFault $e){
                    return 'SoapFault: '.$exc->getMessage().var_export($exc);
                
            }
             catch (\Exception $exc)
            {
                    return 'exception: '.$exc->getMessage().var_export($exc);
            }
    }
    function XMLtoArray($val)
    {

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
        }

    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
        
        try {
        
        $xml = \simplexml_load_string($val);
        $json = json_encode($xml);
        $arr = json_decode($json,TRUE);

        return $arr;
        } catch (\Exception $ex) {
            return "Error ".$ex->getMessage();
        }
    }

    function examine($name,$val)
    {
                    if (is_object($val))
                    {
                    echo "<br />dumping $name object";
                    var_dump($val);
                            $props = get_object_vars ( $val);
                            foreach($props as $prop=>$value)
                            {
                                    $this->examine($prop,$value);
                            }
                    }
                    elseif (is_string($val))
                    {
                    if (strpos($val,"xml")==false)
                    {
                            echo "$name = $val";
                    }
                    else
                    {
                    echo '<br />a string (XML?):'.$val;
                            echo '<br/>';
                            $xml = simplexml_load_string($val);
                            $json = json_encode($xml);
                            $arr = json_decode($json,TRUE);
                            echo '<br/>...';
                            foreach($arr as $prop=>$value)
                            {
                                    $this->examine($prop,$value);
                            }
                            echo '<br/>...';
                    }
                    }

}
}
