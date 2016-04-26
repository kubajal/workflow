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

namespace OmniFlow\Sandbox;

/**
 * Description of ScriptHelpersEngine
 *
 * @author ralph
 */

class WFCase
{
    var $caseId;
    var $title;
    var $currentItemId;
    private $case;

    public function getCurrentItem()
    {
        return $case->getItem($this->currentItemId)->__toArray();;
    }
    public function getItem($itemId)
    {
        $items=array();
        foreach($this->case->items as $item)
        {
            if ($item->id==$itemId)
            return $item->__toArray();
        }
        return Array();

    }
    public function getItems()
    {
        $items=array();
        foreach($this->case->items as $item)
        {
            $items[]=$item->__toArray();
        }
        return $items;

    }
    
    public function setTitle($title) 
    {
        $this->case->title=$title;
    }
    
    public function setItemPriority($itemId,$priority) 
    {
        $item=$this->case->getItem($itemId);
        $item->priority=$priority;
    }
    public function setItemDeadline($itemId,$deadline) 
    {
        $item=$this->case->getItem($itemId);
        $item->deadline=$deadline;
    }
    public function setCaseStatus($status) 
    {
        $this->case->caseStatus=$status;
    
    }
    public function __construct(WFCase\WFCase $case) {
        $this->case=$case;
        $this->title=$case->title;
        $this->caseId=$case->caseId;
    }
    
}
class User
{
    
    public function __construct() {
        $user=  \OmniFlow\Context::getInstance()->user;
        $arr=$user->__toArray();
        foreach($arr as $k=>$v)
        {
            $this->$k=$v;
        }
    }
    
}
/*
 *     site title
    emailFooter
    linkToCase
    linkToItem
    linkToDashboard
 */
/*
 *  Data object is an array of data eleemnts used as input/output
 *  it is saved as part of the Context object
 */
class Data
{
    
    private $language;
    private $context;
    private $var;   // variableName either input or output
    
    public function __construct($lang,$context,$var) {
        $this->language=$lang;
        $this->context=$context;
        $this->var=$var;
    }
    public function asArray()
    {
        if ($this->var =='outputData')
        {
           return $this->context->outputData;
        } else {
            
            return $this->context->inputData;
        }
        
    }
    public function get($key)
    {
        $arr=$this->asArray();
        if (isset($arr[$key]))
            return $this->context->{$this->var}[$key];
        else
            return null;
    }
    public function set($key,$value)
    {
        if ($this->var =='outputData')
        {
            $this->context->outputData[$key]=$value;
        } else {
            
            $this->context->inputData[$key]=$value;
        }
        
    }
}
class Context
{
    
    private $language;
    private $context;

    public function __construct($lang) {
        $this->language=$lang;
        $this->context=  \OmniFlow\Context::getInstance();
        $arr=$this->context->__toArray();
        $this->inputData=new Data($lang,$this->context,'inputData');
        $this->outputData=new Data($lang,$this->context,'outputData');
        
        foreach($arr as $k=>$v)
        {
            $this->$k=$v;
        }
    }
    public function getInputData()
    {
        return $this->inputData;
    }
    public function getOutputData()
    {
        return $this->outputData;
    }
    public function linkToDashboard()
    {
	return Helper::getUrl(array('action'=>'action=task.dashboard')); 
    }
    public function linkToCaseItem($caseId,$itemId)
    {
	return Helper::getUrl(array('action'=>'task.execute','caseId'=>$caseId,'id'=>$itemId)); 
    }
    public function linkToCase($caseId)
    {
	return Helper::getUrl(array('action'=>'case.view','caseId'=>$caseId)); 
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
class String
{
    var $language;
    public function __construct($lang) {
        $this->language=$lang;
    }
    function size($string)
    {
        return strlen($string);
    }
    function test($subject)
    {
        $pattern="/tr\('(.*?)'/";
        $match=null;
        $ret=  preg_match($pattern,$subject,$match);

   $userinfo = "Name: (John Poul)> <br> Title: (PHP Guru)";
   preg_match_all ("/\((.*)\)/U", $userinfo, $pat_array);
   
   print $pat_array[0][0]." <br> ".$pat_array[0][1]."\n";
   
        print_r($match);

        echo '<hr />';
        
// get host name from URL
    preg_match('@^(?:http://)?([^/]+)@i',"http://www.php.net/index.html", $matches);

        print_r($matches);
        return $ret;
        
    }
    function preg_match_all($pattern,$subject,$matchVar=null)
    {
        $match=null;
        $ret=  preg_match_all($pattern,$subject,$match);

        if ($matchVar!==null) {
            $this->language->vars[$matchVar]=$match;
        }
            
        return $ret;
    }
    function preg_match($pattern,$subject,$matchVar=null)
    {
        $match=null;
        $ret=preg_match($pattern,$subject,$match);

        if ($matchVar!==null) {
            $this->language->vars[$matchVar]=$match;
        }
            
        return $ret;
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
class Date
{
    var $language;
    public function __construct($lang) {
        $this->language=$lang;
    }

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
class Web
{
    var $result;
    var $params=Array();
    var $language;
    public function __construct($lang) {
        $this->language=$lang;
    }
    
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
