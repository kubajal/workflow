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
    
public function displayOld()
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
		
	}
	
	
	echo "
        <div class='form'>
        <form name='ajaxform' id='ajaxform' action='$url' method='post'>
	<br/>
	Case Id:
	<input type='text' name='caseId' value='$_caseId'></input>
	Process Name:
	<input type='text' name='file' value='$file'></input>
	<br/>
	<TEXTAREA NAME='script' ROWS=20 COLS=150>$_script</TEXTAREA>
	<input type='submit' /></form>";
        
        echo "<div id='results'>";
        
	if (isset($_POST['script']))
	{
            $ret=$this->executeScript($_script,$_case);
            
	}
        
        echo "</div>";

        
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
public function display()
{

	$_caseId='';
	$_script="";
        $_data=null;
        $scripts=null;
        
        $url=Helper::getUrl(array('action'=>'designer.debugScript'));
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
		
	}
	
	
	echo "
        <div class='form'>
        <form name='ajaxform' id='ajaxform' action='$url' method='post'>
	<br/>
	Case Id:
	<input type='text' name='caseId' value='$_caseId'></input>
	Process Name:
	<input type='text' name='file' value='$file'></input>
	<br/>
	<TEXTAREA NAME='script' ROWS=10 COLS=150>$_script</TEXTAREA>
            
	<input type='hidden' name='action' value='omni_ajax_call'></input>
	<input type='hidden' name='command' value='designer.debugScript'></input>

	<input type='submit' /></form>";
        
        echo "<div id='results'></div>";
        
        ?>
<script>
//callback handler for form submit
jQuery("#ajaxform").submit(function(e)
{
      jQuery('#results').html("executing ...");

    var postData = jQuery(this).serializeArray();

    var formURL = getAjaxUrl();
    jQuery.ajax(
    {
        url : formURL,
        type: "POST",
        data : postData,
        success:function(data, textStatus, jqXHR) 
        {
            jQuery('#results').html(data);
        },
        error: function(jqXHR, textStatus, errorThrown) 
        {
            jQuery('#results').html(textStatus);
        }
    });
    e.preventDefault(); //STOP default action
//    e.unbind(); //unbind. to stop multiple form submit.
});
 
jQuery("#ajaxform").submit(); //Submit  the FORM
</script>
<?php

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

public function execute()
{
    echo 'Results:<br/>';
    	if ($_POST['caseId']!=='')
        {
		$_caseId=$_POST['caseId'];
                
                $_case=  WFCase\WFCase::LoadCase($_caseId);
            echo 'case '.$_case->caseId;
        }
        else
        {
		$files=$_POST['file'];
               	$proc=BPMN\Process::Load($file);
                $_case= \OmniFlow\WFCase\WFCase::SampleCaseForProcess($proc);
            echo 'proces '.$proc->name;
        }
    	if (isset($_POST['script']))
	{
                $script=html_entity_decode($_POST['script']);
                $script= str_replace("\\", "",$script);
                echo $script;

                $ret=$this->executeScript($script,$_case);
        }
        print_r($_case->values);


}
public function executeScript($_script,$_case)
{
        $ret=ScriptEngine::Evaluate($_script,$_case);
            
        if ($ret->result===true)
            echo '<br />Final result: True';
        elseif ($ret->result===false)
            echo '<br />Final result: False';
        else
            echo '<br />Final result: Unknown';
            
        echo "<div class='results'>";
            
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
        
        return $ret;
}
}



}	// end of namespace

?>