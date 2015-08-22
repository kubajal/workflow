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

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
        }

    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

/**
 * Description of Parser
 *
 * @author ralph
 */

class ParserTag
{
    var $tag;
    var $parents=Array();
    public function __construct($tag,$parents=Array())
    {
        $this->tag=$tag;
        $this->parents=$parents;
    }
}
class ParserToken
{
    var $id;
    var $tag;
    var $startPos;
    var $startHeader;
    var $endHeader;
    var $startBody;    
    var $endPos;
    var $parent=null;
    var $children=Array();
    var $subs=Array();
    var $isSub=false;
    static $lastTokenId=0;

    public function __toString() {
        $subs="";
        $pid="";
        if ($this->parent)
            $pid=$this->parent->id;
        foreach($this->subs as $sub)
        {
            $subs.' sub: '.$sub->id;
        }
        return "id:$this->id tag:$this->tag startPos: $this->startPos endPos: $this->endPos parent: $pid"
                . " isSub: $this->isSub Subs: $subs";
    }
    public function __construct() {
        $this->id=self::$lastTokenId++;
    }
    function getBody($script)
    {
        $end=$this->endPos;
        
        return substr($script,$this->startBody,$end-$this->startBody);
        
    }
    function getHeader($script)
    {
        return substr($script,$this->startHeader,$this->endHeader-$this->startHeader);
        
    }
}
class Parser {
    
    var $engine;
    
    function __construct(ScriptEngine $engine) {
        $this->engine=$engine;
    }
    function debug($str)
{
    echo $str;
}
function matchTags($tags,$str)
{
    echo 'match tags '.$str;
    
    $stackTokens=Array(); // pending tokens
    $rootToken=new ParserToken();
            $rootToken->tag='';
            $rootToken->endHeader=0;
            $rootToken->startPos=0;
            $rootToken->startBody=0;
            $rootToken->endPos=strlen($str);
    $this->stack=Array($rootToken);
    
    $p=0;
    $this->processedPos=0;
    while(true)
    {
        $tag=null;
        $type='';
        $header='';
        $sPos=0;
        
        // get next tag
        if (!$this->findTag($str,$p,$tags,$tag,$type,$sPos))
            break;
        echo "<br /> type $type";
        $currentToken=$this->stack[count($this->stack)-1];
        
        $properToken=$currentToken;
        if ($currentToken->isSub) 
            $properToken=$currentToken->parent;
        
        
        if (($type=='start') && count($tags[$tag]->parents)>0)
        {
            $parentOk=false;
            
            
            foreach($tags[$tag]->parents as $parent)
            {
                if ($parent==$properToken->tag)
                {
                    $parentOk=true;
                    break;
                }
            }
            if (!$parentOk)
            {
                echo 'ERROR invalid sub:'.$tag;
                return null;
            }
            echo 'sub tag';
            
            if (count($properToken->subs)>0)
            { // has subs need to close last sub
               $lastSub = $properToken->subs[count($properToken->subs)-1];
               $this->processEndToken($lastSub, $sPos, $p,$str);
            }
            else
            {
                $this->processEndToken($properToken, $sPos, $p,$str,true);
            }
            $type='sub';
            
            $token=new ParserToken();
            $token->tag=$tag;
            $token->startPos=$sPos;
            //$token->processedPos=$p;
            $token->parent=$properToken;
            $token->isSub=true;
            $properToken->subs[]=$token;
            $this->stack[]=$token;
            $this->findEndHeader($str,$p,$token);
            $this->processedPos=$p;
            
        }
        else if ($type=='start')
        {
            // handle non-tagged scripts
            {

                //$processed=$currentToken->processedPos;
                $txt=substr($str,$this->processedPos,$sPos-$this->processedPos);
                if (trim($txt)!=='')
                {
                    echo '<br />adding a new token for non-tagged'.$txt;
                    // add before text
                    $token=new ParserToken();
                    $token->tag='';
                    $token->startPos=$this->processedPos;
                    $token->startBody=$this->processedPos;
                    $token->endPos=$sPos;
                    //$token->processedPos=$sPos;
                    $token->parent=$currentToken;
                    $currentToken->children[]=$token;
                }
            }
            
            $token=new ParserToken();
            $token->tag=$tag;
            $token->startPos=$sPos;
            //$token->processedPos=$p;
            $token->parent=$currentToken;
            $currentToken->children[]=$token;
            $this->stack[]=$token;
            $this->findEndHeader($str,$p,$token);
            $this->processedPos=$p;
        }
        else // verify that the tag is last in stack
        {
            
            if ($currentToken->tag === $tag)
            {
                ;
            }
            elseif (($currentToken->isSub) && $currentToken->parent->tag ===$tag)
            {
                ;
            }
            else
            {
                echo "ERROR not well formed";
                return;
            }
            
            if ($type=='end')
            {
                if (count($properToken->subs)>0)
                { // has subs need to close last sub
                   $lastSub = $properToken->subs[count($properToken->subs)-1];
                   $this->processEndToken($lastSub, $sPos, $p,$str);
                }
                else 
                    $this->processEndToken($properToken, $sPos, $p,$str);
            }
        }
        
    }
    echo 'done.'. count($this->stack);
    
    $this->processEndToken($rootToken, strlen($str),strlen($str), $str);
    if (count($this->stack)>0)
    {
        echo '<br /> ERROR stack is not empty = stack follows:';
        foreach($this->stack as $token)
        {
            echo '<br />'.$token->__toString();
        }
        
    }
    
    echo '<br />finished';
    return $rootToken;
}
function processEndToken($token,$sPos,$p,$script,$keepOnStack=false)
{
        $token->endPos=$sPos;
        // any left over 
        if ($this->processedPos <$token->endPos)
        {
           $txt=substr($script,$this->processedPos ,$token->endPos-$this->processedPos);
                echo '<br />adding at tail end new token for non-tagged'.$txt;
                // add before text
                $ntoken=new ParserToken();
                $ntoken->tag='';
                $ntoken->startPos=$this->processedPos;
                $ntoken->startBody=$this->processedPos;
                $ntoken->endPos=$sPos;
                $ntoken->parent=$token;
                $token->children[]=$ntoken;
        }
        if (!$keepOnStack)
        {
            $lastStack=$this->stack[count($this->stack)-1];
            if ($lastStack->id==$token->id)
                array_pop($this->stack);
            else
                echo "ERROR not the last on stack to remove";
        }
        $this->processedPos=$p;

}
function findEndHeader($str,&$p,$token)
{
    
        $tag=$token->tag;
        $endHeader='}}';
        if ($endHeader!==null)
        {
            // get header
            $len=strlen($endHeader);
            $l=strpos($str,$endHeader,$p);
            if ($l===false)
                echo "ERROR";

            $token->startHeader=$p;
            $token->endHeader=$l;
            $token->startBody=$l+$len;
            
            $p=$l+$len;
        }
}
function findTag($str,&$p,$tags,&$rtag,&$rtype,&$startPos)
{
    $l=strpos($str,'{{',$p);
    echo '<br /> findTag'.$p.' at'.$l.substr($str,$l-3,10);
    if ($l===false)
            return false;
    $startPos=$l;
    
    
    foreach($tags as $tag)
    {
        $len=strlen($tag->tag);
        echo "checking $len |".substr($str,$l+2,$len)."| vs |".$tag->tag."|";
        if (substr($str,$l+2,$len)==($tag->tag))
        {
            $p=$l+2+$len;
            $rtype='start';
            $rtag=$tag->tag;
            echo 'found start';
            return true;
        }
    }
    foreach($tags as $tag)
    {
        $tagEnd="{{/".$tag->tag."}}";
        $len=strlen($tagEnd);
        if (substr($str,$l,$len)==$tagEnd)
        {
            $p=$l+$len;
            $rtype='end';
            $rtag=$tag->tag;
            echo 'found end';
            return true;
        }
    }
    // noting found 
    $p=$l;
    return true;
}
function matchBloc($script,$ltoken,$rtoken,$endHeader=null)
{
    $this->debug('<hr />'.$ltoken);
    
    $p=0;
    $len=strlen($ltoken);
    $len2=strlen($rtoken);
    $matches=array();
    $l2=-$len2;
    
    while(true)
    {
        $l1=0;
        $header="";
        $l=strpos($script,$ltoken,$p);
        if ($l===false)
            break;
        else
        {
            $e=$l+$len;
            if ($endHeader!==null)
            {
                // get header
                $len1=strlen($endHeader);
                $l1=strpos($script,$endHeader,$e);
                if ($l1===false)
                    echo "ERROR";
                $e=$l1+$len1;

                $start=$l+$len;
                $end=$l1;
                $header =substr($script,$start,$end-$start);
                $this->debug("<br />header: $start to $end ".$header);
                $this->debug('<br />parsing header ');
                
            }
            else {
                $l1=$l;
                $len1=$len;
            }

            $l2=strpos($script,$rtoken,$e);
            if ($l2===false)
                echo "ERROR";

        }
     //    {{blockxxxxx}}xxxxxxxxxxxxxxx{{/blcok}}
     //    ^           ^                ^
     //    l           l1               l2
   //len   xxxxxxx     
   //len2                               xxxxxxxxxx
   //len1              xx  
/*
123; {{block}} Line 1 Text inside block {_case.caseId} end of case id {{/block}}         
 */
        $before =substr($script,$p,$l-$p);
        $this->debug("<br />before $p to $l: $before");
        
        $start=$l1+$len1;
        $end=$l2;
        $inside =substr($script,$start,$end-$start);
        $this->debug("<br />block $start to $end : $inside");
        $matches[]=Array($before,null,null);
        $matches[]=Array($inside,$ltoken,$header);
        $p=$l2+$len2;
    }
    $tail =substr($script,$l2+$len2);
    $matches[]=Array($tail,null,null);
    $l2=$l2+$len2;
    $this->debug("<br />tail $l2 : $tail");
    return $matches;
}
function processToken($level,$token,$script,$action)
{
    $action($level,$token,$script);
    foreach($token->children as $child)
    {
        $this->processToken($level+1,$child,$script,$action);
    }
}
function parse(&$script)
{
    $tags=Array();
    $tags['block']=new ParserTag('block');
    $tags['if']=new ParserTag('if');
    $tags['elseif']=new ParserTag('elseif',Array('if'));
    $tags['else']=new ParserTag('else',Array('if'));
    
    $matches=$this->matchBloc($script, "/*", "*/");
    $blocks=Array();
    $script="";
    foreach($matches as $block)
    {
        if ($block[1]==null)    // not a comment
            $script.=$block[0];
    }
    

    $rootToken=$this->matchTags($tags,$script);
    if ($rootToken==null)
        return null;
    

    $this->processToken(0,$rootToken,$script,function ($level,$token,$script)
    {
        echo '<hr />'.$level.' token'.$token->tag;
        echo $token->getBody($script).
                ' |hdr:'.$token->getHeader($script).
                ' |Children:'.count($token->children);
        echo '<br />'.$token->__toString();
        
    });
            
    
    
    
    return $rootToken;
    
    
    foreach($parseTokens as $token)
    {
        if ($token->parent!==null)
        {
            $token->parent->children[]=$token;
        }
    }
    foreach($parseTokens as $token)
    {
        echo '<br /> token:';
        echo $token->tag->tag.'|'.$token->getBody($script).
                ' |hdr:'.$token->getHeader($script).
                ' |Children:'.count($token->children);

    }
    foreach($parseTokens as $token)
    {
        $n=count($token->cildren);
        if ($n>0)
        {
            $p=0;
            for($i=$n;$i>0;$i--)
            {
                
            }
        }
    }
            
    return $parseTokens;
    
}
function parse2($script)
{
    
     $parseTokens=$this->parse2($script);
    
    // now convert them to scriptBlocks
    return $blocks;
        
    
    $doc = new \DOMDocument();
    libxml_use_internal_errors(true);
    $doc->strictErrorChecking=false;
    $doc->loadHTML($script);
    libxml_clear_errors();
    foreach($doc->childNodes as $item)
    {
        try {
            
        echo '<br />item:'.$item->tagName.'-'.$doc->saveHTML($item);
            
        foreach($item->childNodes as $child)
        {
            echo '<br />child'.$child->tagName.'-'.$doc->saveHTML($child);
        foreach($child->childNodes as $gchild)
        {
            echo '<br />..grand child'.$gchild->tagName.'-'.$doc->saveHTML($gchild);
        }
        }
        
            
        } catch (\Exception $ex) {
            
        }
        
    }
    return;
    $web=new WebSandbox();
    print_r($web->XMLtoArray($script));
    return;
//    $parseTokens=$this->parse2($script);
    
    // now convert them to scriptBlocks
//    return $blocks;
    
    $lines = explode(PHP_EOL, $script);
    //print_r($lines);
    
    $matches=$this->matchBloc($script, "/*", "*/");
    $blocks=Array();
    $script="";
    foreach($matches as $block)
    {
        if ($block[1]==null)    // not a comment
            $script.=$block[0];
    }
    $matches=$this->matchBloc($script, "{{block", "{{/block}}","}}");
    $script="";
    foreach($matches as $block)
    {
        $token=$block[1];
        if ($token==null)    // not a block
        {
            $sblock=new ScriptBlock();
            $sblock->script=$block[0];
            $sblock->type='Script';
            $blocks[]=$sblock;
        }   
        elseif ($token=='{{block') {
            // convert block
            $str=$block[0];
            $vars=$this->matchBloc($str,'{','}');
            $str="output(";
            foreach($vars as $var)
            {
                if ($var[1]==null)
                {
                    $str.='"'.$var[0].'"';
                }
                else
                {
                    $str.='~'.$var[0].'~';
                }
            }
            $str.=")";
            $sblock=new ScriptBlock();
            $sblock->script=$str;
            $sblock->type='Template';
            $sblock->header=$block[2];
            $blocks[]=$sblock;
            
            
            }
        elseif ($token=='{{if') {
            // convert block
            $str=$block[0];
            $sblock=new ScriptBlock();
            $sblock->script=$str;
            $sblock->type='if';
            $sblock->header=$block[2];
            $blocks[]=$sblock;
            }
    }
    print_r($blocks);
    return $blocks;
}

}
