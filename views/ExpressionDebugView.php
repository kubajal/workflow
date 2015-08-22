<?php
namespace OmniFlow
{
//include_once('lib\Expression\ExpressionLanguage.php');

class ExpressionDebugView extends Views
{

public function test($p1,$p2)
{
    return "test with $p1 and p2";
}
public function display()
{

	$_caseId='';
	$_itemId='';
	$_script="";
        $_mode="";
        $_modeSelected="";
	if (isset($_POST['script']))
	{
		$_script=$_POST['script'];
	}
	if (isset($_POST['mode']))
	{
                echo 'Mode is on';
                $_mode=true;
                $_modeSelected="checked";
        }
        
	if (isset($_POST['caseId']) && $_POST['caseId']!=='' )
	{
		$_caseId=$_POST['caseId'];
                
                $_case=WFCase::LoadCase($_caseId);
                
		$_process=$_case->proc;
		$_data=$_case->values;
                print_r($_data);
		
		if (isset($_POST['itemId']))
		{
			$_itemId=$_POST['itemId'];
			if ($_itemId!='')
			{
			$_item=$_case->getItem($_itemId);
			}
		}
	}
	
	echo '<br /><hr />Output:';
	
	echo "<form name='ajaxform' id='ajaxform' action='index.php?action=designer.debugExpression' method='post'>
	<br/>
	Case Id:
	<input type='text' name='caseId' value='$_caseId'></input>
	Item Id:
	<input type='text' name='itemId' value='$_itemId'></input>
	Brief Mode:
	<input type='checkbox' name='mode' value='Yes' $_modeSelected></input>
	<br/>
	<TEXTAREA NAME='script' ROWS=20 COLS=150>$_script</TEXTAREA>
	<input type='submit' /></form>";
        echo 'mode:'.$_mode;
	
	if (isset($_POST['script']))
	{
            $_data['_case']=$_case;
            $_data['_context']=  Context::getInstance();
            $_data['_obj']=$this;
            $this->Evaluate($_script,$_data,$_mode);
	}
}
function Evaluate($script,$data,$brief)
{
    
	$lang=new FunctionExecutor();
	$lang->Init($data);
        $lang->Execute($script,$brief);
        return;
}
}

class FunctionExecutor
{
	var $language;
        var $returnFunction;
        var $returnValue;
        var $vars;
function Init($vars)
{
    $this->vars=$vars;
}
function Execute($script,$brief)
{


//    $tst = new Tester();
    $lines=explode("\n",$script);
    if ($brief)
    {
        $l=1;
        $nscript="";
        foreach($lines as $line)
        {
            $nline=str_replace("\r","",$line);
            if (trim($nline)==="")
                $nline="{#  #}";
            else
                $nline="{% $nline %}";
            echo "<br />line $l:.$nline";
            $nscript.=$nline."\n";
            $l++;
        }
        $nscript.="RETURN={{return}} \n";
        $script=$nscript;
    }

    $loader = new \Twig_Loader_Array(array(
    'index.html' =>  $script , 'test.html'=> $script));

	
    $twig = new \Twig_Environment($loader);

        $this->returnFunction= new \Twig_SimpleFunction('return', function ($value) {
            $arguments=$this->returnFunction->getArguments();
            $this->returnFunct($value);
            return 'function return: value:'.$value;
        });

    $twig->addFunction($this->returnFunction);
    
    
    try
    {
        $ret=$twig->render('index.html', $this->vars);
        echo "<hr />Script Results:$ret";
        if (strpos($ret,"RETURN=")===0)
        {
            $v=substr($ret,7,1);
            if ($v==='1')
                $success=true;
            else
                $success=false;
                
            echo "V=$v".$success;
            echo '<hr /> Condition is ';
            if ($success)
                echo "TRUE";
            else
                echo "FALSE";
        }
//        echo $this->examineValue($ret);
    }
    catch(\Twig_Error_Syntax $exc)
    {
        echo '<br />'.$this->getError($exc,$lines);
       
    }
//    echo '<hr />Return:'.$this->returnValue;
//    echo $this->examineValue($this->returnValue);
    return;


}
public function getError(\Twig_Error_Syntax $exc,$lines)
{
    $rp = new \ReflectionProperty('\Twig_Error_Syntax', 'lineno');
    $rp2 = new \ReflectionProperty('\Twig_Error_Syntax', 'message');
    
    $rp->setAccessible(true);
    $rp2->setAccessible(true);
    $msg=$rp2->getValue($exc);
    $lineno=$rp->getValue($exc);
    echo "<hr />Syntax Error $msg at Line # $lineno";
    echo "<br />".$lines[$lineno-1];
}
function returnFunct($value)
{
    $this->returnValue=$value;
}
function examineValue($ret)
{
    if (is_string($ret))
        return 'string:'.$ret;
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
function executeLine($line)
{
		try
		{
                	echo "<br />Expression:'$line' ";
			$ret=$this->language->evaluate($line, $this->vars);
			echo '-- ret:'.$this->displayReturn($ret);
		}
		catch(\Symfony\Component\ExpressionLanguage\SyntaxError $exc)
		{
			echo "***ERROR*** $exc->message";
		}

    
}
}
class ExpressionVars
{
    var $language;
    public function add($name)
    {
        return $this->language->vars[$name]=null;
        
    }
    public function get($name)
    {
        return $this->language->vars[$name];
        
    }
    public function set($name,$val)
    {
        $this->language->vars[$name]=$val;
    }
}
class ExpressionHelper
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
	static function debug($language,$exp)
	{
            echo '<br />'.$exp;
            return $exp;
	}
	static function returnFunct($language,$exp)
	{
            echo $exp;
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

}	// end of namespace

?>