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
 * Lexes an expression.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * 
 * History:
 *  V1.1    Modified by RalphHanna   to allow for ';'
 */
class Lexer
{
    /**
     * Tokenizes an expression.
     *
     * @param string $expression The expression to tokenize
     *
     * @return TokenStream A token stream instance
     *
     * @throws SyntaxError
     */
    public function tokenize($expression)
    {
 //       $expression = str_replace(array("\r", "\n", "\t", "\v", "\f"), ' ', $expression);
        $cursor = 0;
        $tokens = array();
        $brackets = array();
        $end = strlen($expression);
        $mode='script';
        $textStart=0;

        while ($cursor < $end) {
            
            if ($mode=='text') {
            
                if (false !== strpos( substr($expression,$cursor,2),'>>')) {
                    // Template
                    $str=substr($expression,$textStart,$cursor-$textStart);
                    $tokens[] = new Token(Token::MODE_TEXT_TYPE, $str, $cursor);
                    $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '>>', $p );
                    $cursor+=2;
                    $mode='script';
                } elseif (false !== strpos( substr($expression,$cursor,2),'{{')) {
                    // script within
                    $str=substr($expression,$textStart,$cursor-$textStart);
                    $tokens[] = new Token(Token::MODE_TEXT_TYPE, $str, $cursor);
                    $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '{{', $p );
                    $cursor+=2;
                    $mode='script';
                }
                else {
                    ++$cursor;
                }
                    
            }
            else {
                $c=$expression[$cursor];
                $c= str_replace(array("\r", "\n", "\t", "\v", "\f"), ' ', $c);
                
                if (' ' == $c) {
                    ++$cursor;

                    continue;
                }
                if (false !== strpos( substr($expression,$cursor,2),'/*')) {
                    // Template
                    $p = strpos($expression,'*/',$cursor+2);
                    if ($p===false)
                    {
                        // Error
                        $cursor+=2;
                    }
                    else {
                        $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '/*', $cursor + 2);
                        $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '*/', $p);
                        $cursor=$p+2;
                    }

                } elseif (false !== strpos( substr($expression,$cursor,2),'}}')) {
                    // end of script back to template
                    $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '}}', $cursor );
                    $cursor+=2;
                    $textStart=$cursor;
                    $mode='text';
                } elseif (false !== strpos( substr($expression,$cursor,2),'<<')) {
                    // Template
                    $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '<<', $cursor );
                    $cursor+=2;
                    $mode='text';
                    $textStart=$cursor;
    /*                $p1=$cursor+2;
                    $p2 = strpos($expression,'>>',$cursor+2);
                    if ($p2===false)
                    {
                        // Error
                        $cursor+=2;
                    }
                    else {
                        $str=substr($expression,$p1,$p2-$p1);
                        $tokens[] = new Token(Token::MODE_TEXT_TYPE, $str, $p1);
                        $tokens[] = new Token(Token::MODE_SWITCH_TYPE, '>>', $p2);
                        $cursor=$p2+2;
                    }    */
                
                } elseif (preg_match('/[0-9]+(?:\.[0-9]+)?/A', $expression, $match, null, $cursor)) {
                    // numbers
                    $number = (float) $match[0];  // floats
                    if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                        $number = (int) $match[0]; // integers lower than the maximum
                    }
                    $tokens[] = new Token(Token::NUMBER_TYPE, $number, $cursor + 1);
                    $cursor += strlen($match[0]);
                } elseif (false !== strpos('([{', $expression[$cursor])) {
                    // opening bracket
                    $brackets[] = array($expression[$cursor], $cursor);

                    $tokens[] = new Token(Token::PUNCTUATION_TYPE, $expression[$cursor], $cursor + 1);
                    ++$cursor;
                } elseif (false !== strpos(')]}', $expression[$cursor])) {
                    // closing bracket
                    if (empty($brackets)) {
                        throw new SyntaxError(sprintf('Unexpected "%s"', $expression[$cursor]), $cursor);
                    }

                    list($expect, $cur) = array_pop($brackets);
                    if ($expression[$cursor] != strtr($expect, '([{', ')]}')) {
                        throw new SyntaxError(sprintf('Unclosed "%s"', $expect), $cur);
                    }

                    $tokens[] = new Token(Token::PUNCTUATION_TYPE, $expression[$cursor], $cursor + 1);
                    ++$cursor;
                } elseif (preg_match('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/As', $expression, $match, null, $cursor)) {
                    // strings
                    $tokens[] = new Token(Token::STRING_TYPE, stripcslashes(substr($match[0], 1, -1)), $cursor + 1);
                    $cursor += strlen($match[0]);
                } elseif (preg_match('/not in(?=[\s(])|\!\=\=|not(?=[\s(])|and(?=[\s(])|\=\=\=|\>\=|or(?=[\s(])|\<\=|\*\*|\.\.|in(?=[\s(])|&&|\|\||matches|\=\=|\!\=|\*|~|%|\/|\>|\||\!|\^|&|\+|\<|\-/A', $expression, $match, null, $cursor)) {
                    // operators
                    $tokens[] = new Token(Token::OPERATOR_TYPE, $match[0], $cursor + 1);
                    $cursor += strlen($match[0]);
    //            } elseif (false !== strpos('.,?:', $expression[$cursor])) {
    //*  V1.1    Modified by RalphHanna   to allow for ';='                
                } elseif (false !== strpos('.,?:=;', $expression[$cursor])) {
                    // punctuation
                    $tokens[] = new Token(Token::PUNCTUATION_TYPE, $expression[$cursor], $cursor + 1);
                    ++$cursor;
                } elseif (preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A', $expression, $match, null, $cursor)) {
                    // names
                    $tokens[] = new Token(Token::NAME_TYPE, $match[0], $cursor + 1);
                    $cursor += strlen($match[0]);
                } else {
                    // unlexable
                    throw new SyntaxError(sprintf('Unexpected character "%s"', $expression[$cursor]), $cursor);
                }
            }
        }

        $tokens[] = new Token(Token::EOF_TYPE, null, $cursor + 1);

        if (!empty($brackets)) {
            list($expect, $cur) = array_pop($brackets);
            throw new SyntaxError(sprintf('Unclosed "%s"', $expect), $cur);
        }

        return new TokenStream($tokens);
    }
}
