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

 spl_autoload_register('Symfony\Component\ExpressionLanguage\defaultAutoLoader');

use Symfony\Component\ExpressionLanguage\ParserCache\ArrayParserCache;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

/**
 * Allows to compile and evaluate expressions written in your own DSL.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExpressionLanguage
{
    /**
     * @var ParserCacheInterface
     */
    private $cache;
    private $lexer;
    private $parser;
    private $compiler;

    public $functions = array();

    /**
     * @param ParserCacheInterface                  $cache
     * @param ExpressionFunctionProviderInterface[] $providers
     */
    public function __construct(ParserCacheInterface $cache = null, array $providers = array())
    {
        $this->cache = $cache ?: new ArrayParserCache();
        $this->registerFunctions();
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    /**
     * Compiles an expression source code.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $names      An array of valid names
     *
     * @return string The compiled PHP source code
     */
    public function compile($expression, $names = array())
    {
        return $this->getCompiler()->compile($this->parse($expression, $names)->getNodes())->getSource();
    }

    /**
     * Evaluate an expression.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $values     An array of values
     *
     * @return string The result of the evaluation of the expression
     */
    public function evaluate($expression, $values = array())
    {
        return $this->parse($expression, array_keys($values))->getNodes()->evaluate($this->functions, $values);
    }
    public function evaluateScript($expression, $values = array())
    {
		
        return $this->parseScript($expression, array_keys($values))->getNodes()->evaluate($this->functions, $values);
    }
    public function parseScript($expression, $names)
    {
        if ($expression instanceof ParsedExpression) {
            return $expression;
        }

        $key = $expression.'//'.implode('-', $names);

        if (null === $parsedExpression = $this->cache->fetch($key)) {
            
//            $nodes = $this->getParser()->parse($this->getLexer()->tokenize((string) $expression), $names);
            
            $tokens=$this->getLexer()->tokenize((string) $expression);
            $parser=$this->getParser();
            
            $this->parser = new ScriptParser($this->functions);
            
            $nodes = $this->parser->parseScript($tokens, $names ,$tokens->tokens);
            
            $parsedExpression = new ParsedExpression((string) $expression, $nodes);

            $this->cache->save($key, $parsedExpression);
        }

        return $parsedExpression;
    }

    /**
     * Parses an expression.
     *
     * @param Expression|string $expression The expression to parse
     * @param array             $names      An array of valid names
     *
     * @return ParsedExpression A ParsedExpression instance
     */
    public function parse($expression, $names)
    {
        if ($expression instanceof ParsedExpression) {
            return $expression;
        }

        $key = $expression.'//'.implode('-', $names);

        if (null === $parsedExpression = $this->cache->fetch($key)) {
            
//            $nodes = $this->getParser()->parse($this->getLexer()->tokenize((string) $expression), $names);
            
            $tokens=$this->getLexer()->tokenize((string) $expression);
            $parser=$this->getParser();
            
            $nodes = $this->parser->parse($tokens, $names );
            
            $parsedExpression = new ParsedExpression((string) $expression, $nodes);

            $this->cache->save($key, $parsedExpression);
        }

        return $parsedExpression;
    }

    /**
     * Registers a function.
     *
     * @param string   $name      The function name
     * @param callable $compiler  A callable able to compile the function
     * @param callable $evaluator A callable able to evaluate the function
     *
     * @see ExpressionFunction
     */
    public function register($name, $compiler, $evaluator)
    {
//		echo "<br />Register $name  ";
        $this->functions[$name] = array('compiler' => $compiler, 'evaluator' => $evaluator);

//		print_r($this->functions[$name]);
		
										foreach($this->functions as $key=>$value)
								{
	//								echo "key:$key , ";
								}

//		echo "<br />Register $name  ";
		

    }

    public function addFunction(ExpressionFunction $function)
    {
        $this->register($function->getName(), $function->getCompiler(), $function->getEvaluator());
    }

    public function registerProvider(ExpressionFunctionProviderInterface $provider)
    {
        foreach ($provider->getFunctions() as $function) {
            $this->addFunction($function);
        }
    }

    protected function registerFunctions()
    {
        $this->register('constant', function ($constant) {
            return sprintf('constant(%s)', $constant);
        }, function (array $values, $constant) {
            return constant($constant);
        });
    }

    public function getLexer()
    {
        if (null === $this->lexer) {
            $this->lexer = new Lexer();
        }

        return $this->lexer;
    }

    private function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new Parser($this->functions);
        }

        return $this->parser;
    }

    private function getCompiler()
    {
        if (null === $this->compiler) {
            $this->compiler = new Compiler($this->functions);
        }

        return $this->compiler->reset();
    }
}

function defaultAutoLoader($className)
{
//	echo "<br />class $className <br />";

	$className=str_replace('\\', '/', $className);
        if (strrpos($className, '/')!==false)
			{
            $class= substr($className,strrpos($className, '/')+1);
			$folder=substr($className,0,strrpos($className, '/'));
			
//			echo '<br />Folder before:'.$folder;
			$folder=str_replace('Symfony/Component/ExpressionLanguage','',$folder);
			
//			echo '<br />Folder:'.$folder;
			if ($folder!='')
				$folder=$folder.'/';
				
			$path=__DIR__.'/'. $folder. $class . '.php';
//			echo '<br >including '.$path;
			include $path;
			}
	
}

