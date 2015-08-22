<?php
require_once 'autoload.php';

Twig_Autoloader::register();
/*

$loader = new Twig_Loader_Filesystem('views/'); 

$twig = new Twig_Environment($loader); 

echo $twig->render('page.html', array('text' => 'Hello world!')); 

$loader = new Twig_Loader_Array(array(
    'index.html' => "
<br />1	Hello {{ name }}!
<br />2 {% set shape = 'square' %}
<br />3 {% set shape2 = 'cirlce' %}
<br />4 <h1>{{ text }}</h1>
<br />5 {% if shape is defined %}
<br />6  {{ shape }}
<br />7 {% endif %}
<br />8 {{ shape|upper }}	
<br />9 {{ foo ? 'yes' : 'no' }}
	",
));
*/


//echo $twig->render('index.html', array('name' => 'Fabien','text'=>'Hello World!'));

$script="
<br />0	Hello {{id}}
<br />1	Hello {{ name }}!
<br />2 {% set shape = 'square' %}
<br />3 {% set shape2 = 'cirlce' %}
<br />4 <h1>{{ text }}</h1>
<br />5 {% if shape is defined %}
<br />6  {{ shape }}
<br />7 {% endif %}
<br />8 {{ shape|upper }}	
<br />9 {{ foo ? 'yes' : 'no' }}
<br />11 {{Test.go(shape)}}
"; 
$t=new TwigScript();
$t->run($script);

class Tester
{
	public function go($string)
	{
		return 'Go '.$string;
	}
}
class TwigScript
{
var $function;

function run($script)
{

$loader = new Twig_Loader_Array(array(
    'index.html' =>  $script , 'test.html'=> $script));

	
$twig = new Twig_Environment($loader);

$this->function = new Twig_SimpleFunction('return', function ($name) {
	$arguments=$this->function->getArguments();
    return 'return:'.$name.print_r($arguments);
});
$twig->addFunction($this->function);
	
$tst = new Tester();

echo $twig->render('index.html', array('name' => 'Fabien','id'=>5,'text'=>'Hello World!','Test'=>$tst));

return;
echo $twig->render('test.html', array('name' => 'Ralph','id'=>5,'text'=>'Hello World!'));

$stream=$twig->tokenize($script);

var_dump($stream);

$nodes = $twig->parse($stream);
print_r($nodes);
$php = $twig->compile($nodes);
echo '<hr/>'.$php;
}
}
