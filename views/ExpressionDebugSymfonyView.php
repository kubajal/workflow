<?php
namespace OmniFlow
/*
 * 
 * To Do:
 *  Restructure code  by having execution away from view
 * 
 * add sandBox objects
 *      Case
 *      Context
 *      User
 *      Process
 * 
 *      String
 *      Date
 * 
 *      Rule
 * 
 * Improve Syntax:
 *  Allow Comments
 * 
 * 
 */


{
    
 

//include_once('lib\Expression\ExpressionLanguage.php');

class ExpressionDebugSymfonyView extends Views
{
    
public function display()
{

	$_caseId='';
	$_itemId='';
	$_script="";
        $_data=null;
        $scripts=null;
        
        $url=Helper::getUrl(array('action'=>'designer.debugScripts'));
        echo "
        <div style='margin:20px;'>
        <style>
        .vars {float:left;width:40%;}
        .line {clear:both; border: 1px dotted; }
        .stmt {float:left; width:60%;max-width:400px;}
        .result {float:left; width:40%;}
        .scripts {float:left;}
        .output {border: 1px solid #AE00FF;    background-color: aliceblue; }
        .clear { clear: both; }
        </style>
                ";
	if (isset($_POST['script']))
	{
		$_script=  stripslashes($_POST['script']);
	}
	if (isset($_POST['caseId']) && $_POST['caseId']!=='' )
	{
            
            
		$_caseId=$_POST['caseId'];
                
                $_case=  WFCase\WFCase::LoadCase($_caseId);
                
		$_process=$_case->proc;
                
                $scripts=$_process->getAllScripts();
                
		$_data=$_case->values;
		
		if (isset($_POST['itemId']))
		{
			$_itemId=$_POST['itemId'];
			if ($_itemId!='')
			{
			$_item=$_case->getItem($_itemId);
			}
		}
	}
	
	
	echo "
        <div class='form'>
        <form name='ajaxform' id='ajaxform' action='$url' method='post'>
	<br/>
	Case Id:
	<input type='text' name='caseId' value='$_caseId'></input>
	Item Id:
	<input type='text' name='itemId' value='$_itemId'></input>
	<br/>
	<TEXTAREA NAME='script' ROWS=20 COLS=150>$_script</TEXTAREA>
	<input type='submit' /></form>";
        
        
	if (isset($_POST['script']))
	{
            $ret=ScriptEngine::Evaluate($_script,$_case);
            
	}
        
        echo "<div class='results'>";
        
        if ($ret->result===true)
            echo '<br />Final result: True';
        elseif ($ret->result===false)
            echo '<br />Final result: False';
        else
            echo '<br />Final result: Unknown';
            
            
        foreach($ret->debugLines as $msg)
        {
            $line=$msg->stmt;
             	echo "<div class='line'>
                    <div class='stmt'>Expression:'$line'</div>";
		echo "<div class='result'>";
            if ($msg->err)
            {
                echo "***ERROR** $msg->err";
            }
            else
                echo $msg->ret;
            
            echo '</div></div>';
            
        }
        
        
        echo "</div>
        <hr /><div class='clear' style='margin:20px;'></div>";
        
        echo 'Output:<br/><div class="output">'.$ret->output.'</div>';
        
        echo "
        <hr /><div class='clear' style='margin:20px;'></div>
        <div class='vars'>";
        
        if ($_data!==null)
        {
            echo "Case Variables<table style='line-height:1;'>";
            foreach($_data as $key=>$val)
            {
                echo "<tr><td>$key</td><td>$val</td></tr>";
            }
            echo "</table>";
        }

        echo "</div>";

       //   ------------ scripts -------------------
        echo '<div class="scripts">';
        if ($scripts!==null)
        {
            echo 'Process Scripts <table style="line-height:1;">';
            foreach($scripts as $script)
            {
                echo '<tr><td>'.$script['nodeId'].'</td><td>'.
                        $script['type'].'</td><td>'.
                        $script['script'].'</td></tr>';
            }
            echo '</table>';
        }
        echo '</div></div>';
        
        echo '<div class="clear"/>';
}
}


}	// end of namespace

?>