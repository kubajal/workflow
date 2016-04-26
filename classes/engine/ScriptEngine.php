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

include_once 'ScriptHelpers.php';
include_once "sandbox.php";
/**
 * Description of ScriptEngine
 *
 * @author ralph
 */
class ScriptDebugLine
{
   var $line;
   var $stmt;
   var $ret;
   var $err;
}

class ScriptEngine
{
        var $script;
	var $language;
        var $vars;
        var $output="";
        var $outputMode=false;
        var $outputVar='';
        var $result;
        var $returning=false;
        var $scriptBlocks; //tree 
        var $debugLines=Array();
        var $messages=Array();

   function out($text)
   {
       if ($this->outputVar==='')
        $this->output.=$text;
       else {
           $this->vars[$this->outputVar].=$text;
       }

   }
   function addDebugLine($line,$stmt,$ret,$err=null)
   {
       $out=new ScriptDebugLine();
       $out->line=$line;
       $out->stmt=$stmt;
       $out->ret=$ret;
       $out->err=$err;
       $this->debugLines[]=$out;
   }

public static function Evaluate($script,$case,$caseItem=null,$notification=null,$toUser=null)
{
        Context::Debug("ScriptEngine.Evaluate $script");
    
	$lang=new ScriptEngine();
	$lang->init($case,$caseItem,$notification,$toUser);
        return $lang->execute($script);
}
private function execute($script)
{
        Context::Debug("ScriptEngine.Execute $script");
            
        
        $vars=$this->vars;
        $this->script=$script;//$this->stripComments($script);
        
        $rootNode=$this->Parse($this->script);
        
        $ret=$this->executeNode($rootNode);
        
        $this->result=$ret;
        
        return $this;
        
}
function getNode($node,$childName)
{
    foreach($node->children as $child)
    {
        if ($child->type==$childName)
            return $child;
    }
    return null;
}
function debug($msg)
{
//    echo $msg;
}
function executeNode($node,$takesControl=false,$type='')
{
    if ($type=='')
    {
        if ($node instanceof \Symfony\Component\ExpressionLanguage\Token) 
           {
             $this->debug( $node->__toString());
             $type='token';
           }
       else
         {
           $this->debug($node->type);
           $type=$node->type;
         }
    }
    switch($type)
    {
        case 'break':
            $this->break=true;
            break;
        case 'continue':
            $this->continue=true;
            break;
        
        case 'if':
            {
                $condition=$this->getNode($node,'condition');
                $block=$this->getNode($node,'do');
                $expr=$this->getNode($condition,'expression');
                $isTrue=$this->executeNode($expr);
                if ($isTrue)
                    $ret=$this->executeNode($block,false,'block');
                else 
                {
                    $block=$this->getNode($node,'else');
                    if ($block!==null)
                        $this->executeNode($block,false,'block');
                }
                break;
            }
        case 'while':
            {
                while(1)
                {
                    $condition=$this->getNode($node,'condition');
                    $block=$this->getNode($node,'do');
                    $expr=$this->getNode($condition,'expression');
                    $isTrue=$this->executeNode($expr);
                    if ($isTrue)
                    {
                        $r=$this->executeNode($block,false,'block');
                        if ($this->break)
                        {
                            $this->break=false;
                            break;
                        }
                        if ($this->continue)
                        {
                            $this->continue=false;
                        }
                        if ($r!==null) // null means didn't execute
                            $ret=$r;
                        if ($this->returning)
                            return $ret;


                    }
                    else 
                    {
                        break;
                    }
                }
                break;
            }
           
        case 'foreach':
            {
                $arrName=$this->getNode($node,'collection')->value;
                $varName=$this->getNode($node,'var')->value;
                $block=$this->getNode($node,'do');

                $oldValue=$this->vars[$varName];
                $arr=$this->vars[$arrName];
                
                foreach($arr as $v)
                {
                    $this->vars[$varName]=$v;
                    $r=$this->executeNode($block,false,'block');

                    if ($this->break)
                    {
                        $this->break=false;
                        break;
                    }
                    elseif ($this->continue)
                    {
                        $this->continue=false;
                    }
                    if ($r!==null) // null means didn't execute
                        $ret=$r;
                    if ($this->returning)
                        break;

                }
                $this->vars[$varName]=$oldValue;
                if ($this->returning)
                    return $ret;                
                break;
            }
        case 'mode_text':
            $this->out($node->value);
            break;
        case 'template':
            $this->outputMode=true;
        case 'block':
            foreach($node->children as $child)
            {
                $r=$this->executeNode($child);
                if ($this->continue)
                {
                    break;  // no more children but parent will continue next record
                }
                if ($this->break)
                {
                    break;
                }
                if ($r!==null) // null means didn't execute
                    $ret=$r;
                if ($this->returning) {
                    return $ret;
                }
            }
            if ($type=='template')  // finish template 
                $this->outputMode=false;
            
            if ($this->outputMode)
                $this->out($ret);
            
            
            return $ret;
            break;
        case 'statement':
        case 'expression':
            {
                $n=count($node->children);
                $left='';
                $str="";
                for($i=0;$i<count($node->children);$i++)
                {
                    $token=$node->children[$i];
                    if (($token->value =='=') && $i==1)
                    {
                        $left=$str;
                        $str="";
                    }
                    else {
                        $val=$token->value;
                        if ($token->type ==='string')
                            $val="'".$val."'";
                        $str.=$val;
                    }
                }
                $ret=$this->executeExpression($str);
                if (strlen($left) > 0)
                {
                    $this->setVariableFromScript($left, $ret);
                }

                $isTrue=$this->isTrue($ret);
                return $ret;// $isTrue;                
            }
            break;
    }
}
public function setVariableFromScript($varname,$value) 
{
        $this->vars[$varname]=$value;
}
// -------------------------------------------------------
public static function Validate($proc)
{
    
	$lang=new ScriptEngine();

        
//     	$case=ProcessSvc::StartProcess($processName);
//        $process=$case->proc;
        
        $case= \OmniFlow\WFCase\WFCase::SampleCaseForProcess($proc);
	$lang->Init($case);
        
        $msgs=Array();
        $scripts=$proc->getAllScripts();
        
        foreach($scripts as $scr)
        {
            $scr['script']=str_replace("~~n~~", "\n", $scr['script']);
            $script=$scr['script'];
            
            try
            {
            
            $lang=$lang->execute($script);
            
            foreach($lang->messages as $out)
                {
                    {
                        $msg="Script for ".$scr['nodeId']."-".$scr['type']." has an error <br/>Script:'".$scr['script']."'".$out;
                        Context::Log(VALIDATION_ERROR, $msg);

                    }
                }
            $lang->messages=Array();
            
            }
            catch (\Exception $ex) {
                $msg="Script for ".$scr['nodeId']."-".$scr['type']." has an error <br/>Script:'".$script."'".$ex->getMessage();
                Context::Log(VALIDATION_ERROR, $msg);
            }
        }

        
}
        
private function init($case,$caseItem=null,$notification=null,$toUser=null)
{
        
	$this->language = new \Symfony\Component\ExpressionLanguage\ExpressionLanguage();

        ExpressionFunctionHelper::$scriptEngine=$this;
        
        {
        /*
         * function: log(expression);
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression) {return ExpressionFunctionHelper::log($this,$expression);};
	$this->language->register('log', $compiler, $evaluator);
        }
        {
        /*
         * function: output(expression);
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression) {return ExpressionFunctionHelper::output($this,$expression);};
	$this->language->register('output', $compiler, $evaluator);
        }
        {
        /*
         * function: setOutput(expression);
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression) {
          
            return ExpressionFunctionHelper::setOutput($this,$expression);
            
        };
	$this->language->register('setOutput', $compiler, $evaluator);
        }
        {
        /*
         * function: return(expression);
         * 
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression) {return ExpressionFunctionHelper::returnFunct($this,$expression);};
	$this->language->register('return', $compiler, $evaluator);
        }

        
        if ($case!==null)
            $sbCase=new \OmniFlow\Sandbox\WFCase($case);
        $sbUser=new \OmniFlow\Sandbox\User();
	$this->vars=$case->values;
        $this->vars['String']=new \OmniFlow\Sandbox\String($this);
        $this->vars['Date']=new \OmniFlow\Sandbox\Date($this);
        $this->vars['Web']=new \OmniFlow\Sandbox\Web($this);
        $this->vars['Email']= new EmailEngine();
        $this->vars['_case']=$sbCase;
        $this->vars['_user']=$sbUser;
        $this->vars['_context']= new \OmniFlow\Sandbox\Context($this);
        
        if ($caseItem!==null) {
            $this->vars['_caseItem']=$caseItem->__toArray();
        }
        if ($notification!==null) {
            $this->vars['notification']=$notification;
        }
        if ($toUser!==null) {
            $this->vars['toUser']=$toUser;
        }
        
}
/*
 * 
 *      <before>{block1}after{block2}after
 * array:
 *      before  , none
 *      block1  , token
 *      after   , none
 *      block2  , token
 */
function isTrue($ret)
{
        if ($ret==true)
            return true;
        else
            return false;
    
}

function getReturn($ret)
{
    if (is_string($ret))
        return $ret;
    elseif (is_bool($ret))
    {
        if ($ret==true)
            return 'true';
        else
            return 'false';
    }
    elseif( (is_numeric($ret)))
    {
        return 'number: '.$ret;
    }
    elseif( (is_array($ret)))
    {
        return print_r($ret,true);
    }
    elseif( (is_object($ret)))
    {
        return 'object';
    }

}
function Parse($expression)
{
    $lang=$this->language;
    //$ret=$this->language->evaluate(script, $this->vars);
    $values=$this->vars;
    
//    public function parseScript($expression, $names)
    {

       $tokens=$lang->getLexer()->tokenize((string) $expression);
       
       $parser = new \Symfony\Component\ExpressionLanguage\ScriptParser($lang->functions);
            
       return $parser->parseScript($tokens, array_keys($values) ,$tokens->tokens);
            
    }
    
}
function executeExpression($line)
{
    
                if (trim($line)=='')
                    return false;
		try
		{
			$ret=$this->language->evaluate($line, $this->vars);
                        $dRet=$this->getReturn($ret);
                        $this->addDebugLine(0,$line,$dRet);
                        return $ret;
		}
		catch(\Exception $exc)
		{
                    $this->messages[]="Error at line $line {$exc->getMessage()}";
                        $this->addDebugLine(0,$line,"",$exc->getMessage());
                        
		}
    
}
function stripComments($text)
{
    $this->debug('<hr /> Before:'.strlen($text).$text);
    $text = preg_replace('!/\*.*?\*/!s', '', $text);    
    $this->debug('<hr /> After:'.strlen($text).$text);
    return $text;
}

}
