<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ExpressionLanguage;

/**
 * Parsers a token stream.
 *
 * This parser implements a "Precedence climbing" algorithm.
 *
 * @see http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm
 * @see http://en.wikipedia.org/wiki/Operator-precedence_parser
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ParserNode
{
    var $type;
    var $value;
    var $parent;
    var $children=Array();

    public function __construct($type,$parent,$title='')
    {
        $this->type=$type;
        $this->parent=$parent;
        $this->value=$title;
        if ($parent!==null)
            $parent->children[]=$this;
    }
    
}
class ScriptParser extends Parser
{
    var $tokens;
    var $pos=-1;
    var $searchPos=-1;
    var $rootNode=null;

    public function __construct(array $functions)
    {
        parent::__construct($functions);
    }

    /**
     * Converts a token stream to a node tree.
     *
     * The valid names is an array where the values
     * are the names that the user can use in an expression.
     *
     * If the variable name in the compiled PHP code must be
     * different, define it as the key.
     *
     * For instance, ['this' => 'container'] means that the
     * variable 'container' can be used in the expression
     * but the compiled code will use 'this'.
     *
     * @param TokenStream $stream A token stream instance
     * @param array       $names  An array of valid names
     *
     * @return Node A node tree
     *
     * @throws SyntaxError
     */
    public function parse(TokenStream $stream, $names = array())
    {
        $this->stream = $stream;
        $this->names = $names;

       $node = $this->parseScript();

//oldCode:        $node = $this->parseExpression();
        if (!$stream->isEOF()) {
            throw new SyntaxError(sprintf('Unexpected token "%s" of value "%s"', $stream->current->type, $stream->current->value), $stream->current->cursor);
        }

        return $node;
    }
//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
/* Search function
 * 
 */
 /*
  * get nextTag
  * sets the searchPos
  * false if no more tags
  */
function nextToken($justSearching=false)
{
    $n=count($this->tokens);
    if ($this->searchPos == ($n-1))
        return null;

    $this->pos++;
    $this->searchPos++;
    $token=$this->tokens[$this->searchPos];
    
     
    if ($token->type==Token::EOF_TYPE)
        return null;
    else
        return $token;
}
function putTokenBack()
{
    $this->pos--;
    $this->searchPos--;
}
function nextTokenIs($type,$tag)
{
    $token=$this->nextToken();
    
    if ($token->value ==$tag)
        return true;
    throw new \Exception("expecting $tag");

}
function searchFor($type,$tag)
{
    while(($token=$this->nextToken())!=null)
    {
        if (($token->type==$type) && ($token->value==$tag))
            return $token;
    }
    return false;
}
/* Do Functions
 *  process a piece of code
 */
function doBlock($parent)
{
    
    $node=new ParserNode('block',$parent);    
    $statementNode=null;
    while(($token=$this->nextToken())!=null)
    {
       $tag=$token->value;
       

       if (($token->type=='name') || ($token->type=='punctuation')) { 
       
            switch($tag)
            {
                case '}':
                    // end block
                    return $node;
                    break;
                case 'if':
                     $statementNode=null;
                    $this->doIf($node);
                    break;
                case 'continue':
                    $node=new ParserNode('continue',$parent);
                    break;
                case 'break':
                    $node=new ParserNode('break',$parent);
                    break;
                
                case 'while':
                     $statementNode=null;
                    $this->doWhile($node);
                    break;
                case 'foreach':
                     $statementNode=null;
                    $this->doForEach($node);
                    break;
                case 'template':
                     $statementNode=null;
                    $this->doTemplate($node);
                    break;
                case '{':
                     $statementNode=null;
                    $this->doBlock($node);
                    break;
     /*           case '(':
                    if ($statementNode==null)
                         $statementNode=new ParserNode('statement',$node);    
                    $this->doExpression($statementNode);
                    break; */
                case ';':
                    $statementNode=new ParserNode('statement',$node);    
                    break;
                default:
                    if ($statementNode==null)
                        $statementNode=new ParserNode('statement',$node);    
                    $this->doToken($statementNode);
                    break;
            }
       }
       else {
           
        if ($statementNode==null)
            $statementNode=new ParserNode('statement',$node);    
        $this->doToken($statementNode);
           
       }
               
               
    }
    return $node;
}
function currentToken()
{
    return $this->tokens[$this->pos];
}
function doToken($parent) // just consume the token
{

    $token=$this->currentToken();
    $parent->children[]=$token;
}
function doExpression($parent)
{
    
    $node=new ParserNode('expression',$parent);    
    
    while(($token=$this->nextToken())!=null)
    {
       $tag=$token->value;
       if (($token->type=='name') || ($token->type=='punctuation')) { 
            switch($tag)
            {
                case ')':
                    // end block
                    return $node;
                    break;
                case '(':
                    $this->doExpression($node);
                    break;
                default :
                {
                   $this->doToken($node);
                }

             }
       }
       else
           $this->doToken($node);
           
               
    }
    return $node;
}

function doTemplate($parent)
{
    /*
     * syntax
     *  template (array as record)
     *  {
     *   text is here 
     *      {variable}
     *   text is here 
     *          {variable}
     * 
     *  }
     */
    $node=new ParserNode('template',$parent);

    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'('))
    {
        $header=new ParserNode('header',$node);
        
        $token=$this->nextToken();
        $this->doToken(new ParserNode('source',$header));
        
        $token=$this->nextToken();
        $this->doToken(new ParserNode('AS',$header));
        
        $token=$this->nextToken();
        $this->doToken(new ParserNode('variableName',$header));
    }
    $this->nextTokenIs(Token::PUNCTUATION_TYPE,')');
            
    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
    {
        $do=new ParserNode('body',$node);
        $this->doBlock($do);
    }
        
}
function doWhile($parent)
{
    
    $node=new ParserNode('while',$parent);

    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'('))
    {
        $condit=new ParserNode('condition',$node,"condition of while");
        $this->doExpression($condit);
    }
    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
    {
        $do=new ParserNode('do',$node,"do while");
        $this->doBlock($do);
    }
        
}
function doForEach($parent)
{
    
    $node=new ParserNode('foreach',$parent);

        if (!$this->nextTokenIs(Token::PUNCTUATION_TYPE,'('))
            {

            }

        $token=$this->nextToken();
         $collection=new ParserNode('collection',$node);
         $collection->value=$token->value;

        $token=$this->nextToken();  // as 
           if ( ($token->type=='name') &&  ($token->value==='as'))
           {
           }
         else {

           }
           
        $token=$this->nextToken();
         $var=new ParserNode('var',$node);
         $var->value=$token->value;
         
           
        if (!$this->nextTokenIs(Token::PUNCTUATION_TYPE,')'))
            {

            }



        if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
        {
            $do=new ParserNode('do',$node,'foreach block');
            $this->doBlock($do);
        }

}


function doIf($parent)
{
    
    $node=new ParserNode('if',$parent);

    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'('))
    {
        $condit=new ParserNode('condition',$node,"if condition");
        $this->doExpression($condit);
    }
    if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
    {
        $do=new ParserNode('do',$node," ifTrue block");
        $this->doBlock($do);
    }
        
    while(($token=$this->nextToken())!=null)
    {
       $tag=$token->value;

       switch($tag)
       {
           case 'elseif':
               if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'('))
                {
                    $condit=new ParserNode('elseif',$node,"else if condition");
                    $this->doExpression($condit);
                }
               if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
                {
                    $do=new ParserNode('block',$node,"else if block");
                    $this->doBlock($do);
                }
                break;
           case 'else':
               if ($this->nextTokenIs(Token::PUNCTUATION_TYPE,'{'))
                {
                    $do=new ParserNode('else',$node);
                    $this->doBlock($do);
                }
               break 2;
           default:
           {
               $this->putTokenBack();
               break 2;
           }
       }
    }
}
/*
 * entry point
 */    
 
function debug($msg)
{
    // ` $msg;
}
 public function parseScript(TokenStream $stream, $names = array(),$tokens)
    {
     
      $this->stream = $stream;
      $this->tokens=$tokens;
      $this->names = $names;     
      
     foreach($tokens as $token)
     {
         $this->debug('<br />'.$token->__toString());
     }
      
      
      $this->rootNode=$this->doBlock(null);

      $this->processNode(0,$this->rootNode,function ($level,$node)
      {
/*          $this->debug( '<br />'.$level);
          for($i=0;$i<$level;$i++)
          {
              $this->debug( '.');
          }
          if ($node instanceof Token) 
          {
              $this->debug( 'token:'.$node->__toString());
          }
          else
              $this->debug( 'node:'.$node->type); */
      });
      
    return $this->rootNode;
      
    }
function processNode($level,$node,$action)
{
    $action($level,$node);
    if ($node instanceof ParserNode) 
    {
        foreach($node->children as $child)
        {
            $this->processNode($level+1,$child,$action);
        }
    }
}

}
