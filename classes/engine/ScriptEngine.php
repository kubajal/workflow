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

include_once 'ScriptHelpers.php';
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
        var $result;
        var $scriptBlocks; //tree 
        var $debugLines=Array();
        var $messages=Array();
        
   function addDebugLine($line,$stmt,$ret,$err=null)
   {
       $out=new ScriptDebugLine();
       $out->line=$line;
       $out->stmt=$stmt;
       $out->ret=$ret;
       $out->err=$err;
       $this->debugLines[]=$out;
   }

public static function Evaluate($script,$case)
{
    
	$lang=new ScriptEngine();
	$lang->Init($case);
        return $lang->Execute($script);
}
function Execute($script)
{
        $vars=$this->vars;
        $this->script=$this->stripComments($script);
        
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
    echo "ERROR expecting $childName";
    return null;
}
function debug($msg)
{
//    echo $msg;
}
function executeNode($node,$type='')
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
        case 'if':
            {
                $condition=$this->getNode($node,'condition');
                $block=$this->getNode($node,'do');
                $expr=$this->getNode($condition,'expression');
                $isTrue=$this->executeNode($expr);
                if ($isTrue)
                    $ret=$this->executeNode($block,'block');
                else 
                {
                    $block=$this->getNode($node,'else');
                    if ($block!==null)
                        $this->executeNode($block,'block');
                }
                break;
            }
           
        case 'block':
            foreach($node->children as $child)
            {
            $ret=$this->executeNode($child);
            }
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
                    $this->vars[$left]=$ret;
                }

                $isTrue=$this->isTrue($ret);
                return $isTrue;                
            }
            break;
    }
}

// -------------------------------------------------------
public static function Validate($processName)
{
    
	$lang=new ScriptEngine();

        
       	$case=ProcessSvc::StartProcess($processName);
        $process=$case->proc;

	$lang->Init($case);
        
        $msgs=Array();
        $scripts=$process->getAllScripts();
        
        foreach($scripts as $scr)
        {
            $script=$scr['script'];
            $lang=$lang->Execute($script);
        }
            foreach($lang->messages as $out)
            {
                {
                    $msg="Script for ".$scr['nodeId']."-".$scr['type']." has an error ".$out;
                    Context::Log(VALIDATION_ERROR, $msg);
                    
                }
            }
        
}
        
function Init($case)
{
        
	$this->language = new \Symfony\Component\ExpressionLanguage\ExpressionLanguage();

	$this->language->register('upper',
                function ($arg) {
        		return sprintf('strtoupper(%s)', $arg);
                        	}, 
                function (array $variables, $value) {
                            return strtoupper($value);
                            });
        {
	$compiler = function ($arg) {
		return sprintf('strtoupper(%s)', $arg);
		};
	$evaluator = function (array $variables, $value) {
		return ExpressionFunctionHelper::getVar($this,$value);
		};
	$this->language->register('get', $compiler, $evaluator);
        }
        {
        /*
         * function: set(variableName,value);
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $name,$value) {return ExpressionFunctionHelper::setVar($this,$name,$value);		};
	$this->language->register('set', $compiler, $evaluator);
        }

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
         * function: return(expression);
         * 
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression) {return ExpressionFunctionHelper::returnFunct($this,$expression);};
	$this->language->register('return', $compiler, $evaluator);
        }

        
        if ($case!==null)
            $sbCase=new CaseSandbox($case);
        $sbUser=new UserSandbox();
	$this->vars=$case->values;
        $this->vars['String']=new StringSandbox();
        $this->vars['_case']=$sbCase;
        $this->vars['_user']=$sbUser;
        $this->vars['_context']= new ContextSandbox();
        $this->vars['Web']= new WebSandbox();
        
        
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
