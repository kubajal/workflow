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
include_once 'Parser.php';
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
class ScriptBlock
{
    var $type; // Template , Script , Node (has children)
    var $header;
    var $alternate;
    var $children;
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
        
   function addDebugLine($line,$stmt,$ret,$err=null)
   {
       $out=new ScriptDebugLine();
       $out->line=$line;
       $out->stmt=$stmt;
       $out->ret=$ret;
       $out->err=$err;
       $this->debugLines[]=$out;
   }

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
            foreach($lang->messages as $out)
            {
                if($out->err!==null)
                {
                    $msg="Script for ".$scr['nodeId']."-".$scr['type']." has an error ".$out->err.
                            "in <br />".$scr['script'];
                    Context::Log(VALIDATION_ERROR, $msg);
                    
                }
            }
        }
        
}
public static function Evaluate($script,$case)
{
    
	$lang=new ScriptEngine();
	$lang->Init($case);
        return $lang->Execute($script);
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
         * function: if(condition,trueAction,falseAction);
         * 
         */
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $expression,$true,$false) {return ExpressionFunctionHelper::ifEx($this,$expression,$true,$false);		};
	$this->language->register('if', $compiler, $evaluator);
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
        {
	$compiler = function ($arg) {	return sprintf('strtoupper(%s)', $arg);		};
	$evaluator = function (array $variables, $name) {return ExpressionFunctionHelper::declareFunct($this,$name);};
	$this->language->register('declare', $compiler, $evaluator);
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
function Execute($script)
{
        $vars=$this->vars;
//print_r($vars);

//           $this->executeLine($expression);
//return;
        $parser=new Parser($this);
        
        echo '<hr />'.$script;
        
        $this->Parse($script);
        
        return $this;
        
        $rootToken=$parser->parse($script);    
        $this->script=$script;

        if ($rootToken===null)
            return $this;
        
        $ret=$this->executeBlock($rootToken);
        $this->result=$ret;
        
        return $this;
        
}
function executeBlock($token,$type=null)
{
        if ($type===null)
        {
            if ($token->tag==='')
                $type='';
            else
                $type=$token->tag;
        }
        
        
        $script = $token->getBody($this->script);
        echo '<br />Executing token '.$type.' script '.$script.'<br />';
        
        $ret=false; // default unless set by a script
        
        if ($type=='')
        {
            if (count($token->children)>0)
            {
                foreach($token->children as $child)
                {
                    $ret=$this->executeBlock($child);
                }
            }
            else 
            {
                $lines=explode(';',$script);
                foreach($lines as $line)
                {
                    if (strlen(trim($line))>0)
                    {
                        $ret=$this->executeExpression($line);
                        if ($this->result!==null)
                            break;
                    }
                }
            }
        }
        elseif ($type=='block')
        {
            $ret=$this->executeTemplate($token);
        }
        elseif ($type=='if')
        {
            $ret=$this->executeIf($token);
        }
       
    return $ret;
}
function executeIf(ParserToken $block)
{
    $header=$block->getHeader($this->script);
    $script = $block->getBody($this->script);
    echo '<hr />Template '.$header.'<br />'.$script;
    if ($header != '')
    {
        echo '<hr />Template '.$header.' scrpt '.$script;
        {
            $condition=$header;
            echo '<br />condition:!'.$condition.'!';
            $isTrue=$this->isTrue($this->executeExpression($condition));
            echo "<br />Condition $condition isTrue $isTrue";
            
            
            if ($isTrue)
                {
                return $this->executeBlock($block,'');
                }
            else  // do the else
            {
                foreach($block->subs as $sub)
                {
                    if ($sub->tag =='elseif')
                    {
                        $isTrue=$this->isTrue($this->executeExpression($sub->getHeader($script)));
                        
                        if ($isTrue)
                        return $this->executeBlock($sub,'');
                    }
                    if ($sub->tag =='else')
                    {
                        return $this->executeBlock($sub,'');
                    }
                }
            }
            
        }
    }
}
function executeTemplate(ParserToken $block)
{
    $header=$block->getHeader($this->script);
    $script = $block->getBody($this->script);
    echo '<hr />Template '.$header.'<br />'.$script;
    if ($header != '')
    {
        echo '<hr />Template '.$header.' scrpt '.$script;
        parse_str($header,$keys);
        print_r($keys);
        if (isset($keys['if']))
        {
            $condition=$keys['if'];
            echo '<br />condition:!'.$condition.'!';
            $ret = $this->executeExpression($condition);
            $isTrue=$this->isTrue($ret);
            echo "<br />Condition $condition ret= $ret isTrue $isTrue";
            
            
            if ($isTrue)
                {
                return $this->executeBlock($block,'');
                }
            
        }
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
//    return $lang->parseScript($expression, array_keys($values))->getNodes()->evaluate($this->functions, $values);
    return $lang->parseScript($expression, array_keys($values));
    
        //->getNodes()->evaluate($this->functions, $values);
//    return $lang->parseScript($expression, array_keys($values))->getNodes()->evaluate($this->functions, $values);
    
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
                        $this->addDebugLine(0,$line,"",$exc->getMessage());
                        
		}
    
}
}
