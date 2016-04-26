<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of FormView
 *
 *  Form Specs
 *  field:
 *      
 * @author ralph
 */
class FormView extends Views {

    
public static function defaultForm(WFCase\WFCaseItem $item,$edit,$privileges)
{
    $formView=new FormView();
    $formView->displayForm($item,$edit);
}
public function displayForm(WFCase\WFCaseItem $item,$edit,$privileges)
{
        Context::setSession('returnTo','');

        $form=new Form();
        $form->getHtml($item,$edit,$privileges);
		
}	
  
}
/*
 *  
 * Every field fieldId,fieldName,Title,help
 *  FieldDiv <div for the group
 *  Label <label; classes,text
 *  InputDiv <div for input; classes
 *  Input <input itself
 *      tag
 *      type
 *      value
 *  Help <div for the help; class, text
 * 
 */
class Form
{
  var $specs=Array();
  
  public function getHtml(WFCase\WFCaseItem $item,$edit,$privileges)
  {
        $task=$item->getProcessItem();
        $case=$item->case;
        $caseId=$case->caseId;
        $id=$item->id;
        $proc=$case->proc;
        
        $releaseStatement= in_array('R', $privileges);
                
        $this->specs['js_checkDisplay']='// Scripts are here
                ';
        $this->specs['js_validateData']='';
        


        if ($task==null)
        {
            Context::Error("Case Item is not consistent with process. can not locate task $item->processNodeId");
            return false;
        }
	
                
        $title =$proc->title.'-'.$task->label.' for case '.$case->caseId;
        
        $this->header($title,$caseId,$id);
        
        foreach($task->dataElements as $var)
        {
            $dataElement=$var->getDataElement($case->proc);
            if ($dataElement==null)
                continue;

                $name=$dataElement->name;
                $edit=$var->canEdit();
                $view=$var->canView();

                Context::Log(INFO,"default form field name: $name type: $type".print_r($var,true).'edit:'.$edit.'view:'.$view);
                if ((!$view) && (!$edit))
                    continue;

                $val=$case->getValue($name);
                
                $field=new Field();
                $htm=$field->getHtml($this,$var,$dataElement,$val);

                echo "
                 
                 ".$htm;
//                $this->Field($fld,$label,$input,$help);
        }
        $this->footer($caseId,$id,$releaseStatement);
  }
  private function header($title,$caseId,$id)
  {
     $h1=Helper::getUrl(array("action"=>"task.saveForm","case"=>$caseId,"item"=>$id));
      
?>	
   <div class='formBox' style='margin: 0px;
                    border: rgb(205, 77, 194);
                    border-width: 2px;
                    background-color: azure;
                    padding: 15px;' >
   <h2 class='formTitle' style='margin-bottom: 15px;'><?php echo $title; ?></h2>
   <div >
    <form name='dataform' id='dataform' class='form-horizontal' action='<?php echo $h1; ?>' method='post'>
	 <input type='hidden' name='_caseId' value='<?php echo $caseId; ?>' />
	 <input type='hidden' name='_itemId' value='<?php echo $id; ?>' />
<?php         
  }
  private function footer($caseId,$id,$releaseStatement)
  {
        $h2=Helper::getUrl(array("action"=>"task.release","case"=>$caseId,"item"=>$id));
      
?>
<!-- footer -->         
         <div class="clearfix">
<?php			 
        echo '</div>
            <div class="form-section clearfix">';
        Field::OtherField("complete", "Complete Task?","<input type='checkbox' name='_complete'  checked/>","Proceed to next task in the workflow");
        Field::OtherField("save", "","<input type='submit'  value='Save' />","");

        echo "</form>";
        if ($releaseStatement)  {
            echo "
        <hr /> This task is assigned to YOU. <a href='$h2'>Release me from this task please.</a> <br/>";
        }
        echo "
        
         </div></div>";
     ?>
         <div id='messages' />
        <br /> 
        <br /> 
<script>
jQuery('.date').datepicker();
jQuery( document ).ready(function() {
    checkDisplay();
});
function valueOf(fieldId)
{
    var fld=jQuery('#'+fieldId);
    var type=fld.attr('type')
    var val;
    if (type=='radio') {
        fld=jQuery('[name='+fieldId+']:checked');
        val=fld.val();
    }
    else if (type=='checkbox') {
        var checked=fld.attr('checked');
        if (checked=='checked')
            val=fld.val();
        else
            val='';
    }
    else
        val = fld.val();
    
    return val;
    
}
function validateData()
{
    <?php
        echo $this->specs['js_validateData'];

    ?>
}
function checkDisplay()
{
    <?php
        echo $this->specs['js_checkDisplay'];
    ?>
  /*
   * // check edit control
  if (valueOf('Radio')=='Yes')
      jQuery('#fg_edit').show();
  else 
      jQuery('#fg_edit').hide(); */
}
function msg(msg)
{
    jQuery('#messages').html(msg);
}
jQuery('#dataform').change(function() {
    
  validateData();
  checkDisplay();
 /* var txt='Handler for .change() called.'+
  ' radio value '+ valueOf('Radio') +
  ' checkbox value '+ valueOf('CheckBox1') +
  ' value of select '+ valueOf('select') ;
  ' value of edit '+ valueOf('edit') ; 
  msg(txt); */
   return true;
});

</script>
        
        <br />
        </div>
<?php
      
}
   public static function parseOption($strOption) 
   {
       if (strlen(trim($strOption))==0)
           return Array();
       $l=  strlen($strOption);
       $cursor=0;
       $cursor=strpos($strOption,'(');
       $p2=strpos($strOption,')');
       
       $fun=substr($strOption,0,$cursor);
       $res=Array();
       $res[]=$fun;
//       $cursor++;
       $token="";
       $delimiters="(";
//       $ignoreSpaces=true;
//       $openText=false;
       $mode='D';        // Quote, Delimiters , Text , End
       while($cursor<$l) {  // check parameters
           $c=substr($strOption,$cursor,1);
           if (' ' === $c && ($mode=='D' || $mode=='T') || ($mode=='E')) {
                    ++$cursor;
                    continue;
           }

           
           $p=strpos($delimiters,$c);
           
           if ($p!==false) {
               // handle delimiters 
                switch ($c)
                    {
                        case '(' :
                            $text='';
                            $delimiters="'\",)";
                            $mode='T';
                            break;
                        case ',' :
                            $res[]=$text;
                            $text='';
                            $delimiters="'\",)";
                            $mode='T';
                            break;
                        case '"' :
                        case "'" :
                            if ($mode=='Q')
                            { // end quote
                                $res[]=$text;
                                $text='';
                                $mode='D'; 
                                $delimiters=",)";
                            }
                            else
                            {   // start quote
                                $text='';
                                $mode='Q'; 
                                $delimiters=$c;
                            }
                            break;
                        case ')' :
                            if ($mode=='T')
                                $res[]=$text;
                            $delimiters="";
                            $mode='E';
                            break;
                    }

                } elseif ($mode == 'T' || $mode=='Q')  {
                    $text.=$c;
                } else {
                    echo "Error expecting $delimiters";
                    return false;
                }
                ++$cursor;
                continue;
       }
       return $res;
       
   }

}
class Field
{
   var $form;
   var $id;
   var $name;
   var $title;
   var $help;
   var $isReadOnly=false;
   var $type;
   var $specs=Array();
   var $dataElement;
   var $val;
   
   const FieldGroupDiv='fieldDiv';
   const FieldLabel='fieldLabel';
   const FieldDiv='fieldDiv';
   const Field='field';
   const Help='help';
   
   const LABEL_GRID='col-xs-9 col-md-3';
   const FIELD_GRID='col-xs-9 col-md-9';
   
public static function OtherField($field,$label,$input,$help="")
{
echo "
<div class='form-group' id='fg_$field' style='clear:both;'>
  <label class='control-label ".Field::LABEL_GRID."' for='$field'>$label</label>
  <div class='".Field::FIELD_GRID."'>
     $input ";
if ($help!=='' || $help!==null)
   echo "<span class='help-block'>$help</span>";
echo "
  </div>        
</div>
";
    
}

   public function getHtml($form,$var,$dataElement,$val) 
   {
       $this->init($form,$var,$dataElement,$val);
       $this->doInput($val);
       
        $options=  explode(';',$dataElement->options);

        
        
        Context::debug("field options".$dataElement->options.print_r($options,true));

       
        foreach ($options as $option) {
           $this->processOption($option);
        }
       
       $out=$this->generateCode();
       return $out;
   }
   public function init($form,$var,$dataElement,$val) {
       
        $this->form=$form;
        $this->dataElement=$dataElement;
        
        $this->val=$val;
        $fldName=$var->field;
        $this->name=$dataElement->name;
        if ($fldName=='')
            $fldName=$this->name;
        
        $this->title=$dataElement->title;
        if ($this->title=='')
            $this->title=$this->name;
        
        $this->help=$dataElement->description;
        if ($var->canEdit())
            $this->isReadOnly=false;
        else
        {
            $this->isReadOnly=true;
        }
        
        $this->type=$dataElement->dataType;        
        
        // specs 
        $FieldLabel=Array(
                        'tag'=>'label',
                        'for'=>$fldName,
                        'class'=>'control-label '.Field::LABEL_GRID,
                        '_text'=>$this->title);
        $Field=Array('tag'=>'input','class'=>'form-control',
                    'name'=>$fldName,'id'=>$fldName);


        
        if ($this->isReadOnly==false)
            $Field['placeholder']=$this->title;
        else
            $Field['readonly']=null;
        
        if ($dataElement->req)
            $Field['required']=null;
            

        $help=Array('tag'=>'span','class'=>'help-block','_text'=>$this->help);
        
        if ($this->help=='')
            $help=null;
        
        $FieldDiv=Array(
                        'tag'=>'div',
                        'class'=>Field::FIELD_GRID,
                        Field=>$Field,
                        Help=>$help);
        
        
        
        
        $this->specs=Array(
                        'tag'=>'div',
                        'class'=>'form-group clearfix',
                        'id'=>'fg_'.$fldName,
                         FieldLabel=>$FieldLabel,
                         FieldDiv=>$FieldDiv
                    );
        
   }
           
   public function generateCode($specs=null) {
       
    if ($specs===null)
        $specs=$this->specs;
    $tag="";
    $attr="";
    $contents="";
    $out="";
    foreach($specs as $key=>$val)
    {
        if (is_array($val)) {
            $contents.=$this->generateCode($val);
        }
        elseif ($key=='tag') {
            $tag=$val;
        }
        elseif (substr($key,0,1)=='_') {
            $contents.=$val;
        }
        else {
            if ($val===null)
                $attr.=" ".$key;
            else
                $attr.=" ".$key."='".$val."' ";
        }
    }
    $out="";
    if ($tag!='')
        $out="<$tag $attr>$contents</$tag>";
    else 
        $out=$contents;
        
    return $out;
//    $input=$this->getInput();
       
   }
   public function doInput()
   {
       
        $inpt=$this->specs[FieldDiv][Field];
                
            switch($this->type)
            {
                case 'text':
                    $inpt['tag']='textArea';
                    $inpt['_val']=$this->val;
                    break;
                case 'Boolean':
                    $checked='';
                    if ($this->dataElement->validValues!=='') {

                        if ($this->isReadOnly) {
                            $inpt['type']='text';
                            $inpt['value']=$this->val;
                        }
                        else {
                            
                            $values=explode('\r',$this->dataElement->validValues);
                            $values= preg_split ('/$\R?^/m', $this->dataElement->validValues);
                            
                            $arrObject = new \ArrayObject($inpt);
                            $std = $arrObject->getArrayCopy();
                            $std=new \ArrayObject($std);
                            $inpt=Array();

                            foreach($values as $sval) {
                                
                                $rec=$std->getArrayCopy();
                                
                                if ($this->val==$sval)
                                    $rec['checked']=null;
                                $rec['class']='col-xs-1';
                                $rec['type']='radio';
                                $rec['value']=$sval;
                                $rec['_text']="<span class='".Field::LABEL_GRID."'>$sval</span>";
                                $inpt[]=$rec;
                            }
                        }
                    }
                    else { // simple checkbox 
                            $checked='';
                         if ($this->val=='Yes') {
                             $inpt['checked']=null;
                             $checked='checked';
                         }
                         
                         if ($this->isReadOnly) {
                             // hide the original input and display a fake one with the value
                            $inpt['_text']="<input type='checkbox' disabled readonly $checked value='Yes' />";
                            $inpt['type']='hidden';
                            $inpt['value']='Yes';
                         }
                         else {
                            $inpt['type']='checkbox';
                            $inpt['value']='Yes';
                         }
                         
                         //$input=" <input type='checkbox' $std value='Yes' $checked $readOnly />";
                    }
                     break;
                case 'Radio':
                        $values=$this->dataElement->validValues;
                        if ($values==='') {
                            $values=Array('Yes','No');
                        }
                        else {
                            $values=explode('\r',$this->dataElement->validValues);
                            $values= preg_split ('/$\R?^/m', $this->dataElement->validValues);
                            
                        }
                    
                        if ($this->isReadOnly) {
                            $inpt['type']='text';
                            $inpt['value']=$this->val;
                        }
                        else {
                            
                            
                            $arrObject = new \ArrayObject($inpt);
                            $std = $arrObject->getArrayCopy();
                            $std=new \ArrayObject($std);
                            $inpt=Array();

                            foreach($values as $sval) {
                                
                                $rec=$std->getArrayCopy();
                                
                                if ($this->val==$sval)
                                    $rec['checked']=null;
                                $rec['class']='col-xs-1';
                                $rec['type']='radio';
                                $rec['value']=$sval;
                                $rec['_text']="<span class='".Field::LABEL_GRID."'>$sval</span>";
                                $inpt[]=$rec;
                            }
                        }
                        break;
                case 'Checkbox':
                        $values=$this->dataElement->validValues;
                        if ($values==='') {
                            $values=Array('Yes','No');
                        }
                        else {
                            $values=explode('\r',$this->dataElement->validValues);
                            $values= preg_split ('/$\R?^/m', $this->dataElement->validValues);
                            
                        }
                        if ($this->isReadOnly) {
                            $inpt['type']='text';
                            $inpt['value']=$this->val;
                        }
                        else {
                            if ($this->val==$values[0]) {
                                $inpt['checked']=null;
                            }
                            $inpt['class']='form-checkbox';
                            $inpt['type']='checkbox';
                            $inpt['value']=$values[0];
                         }
                         //$input=" <input type='checkbox' $std value='Yes' $checked $readOnly />";
                     break;
                case 'Select':
                    {
                        if ($this->isReadOnly)
                             $inpt['disabled']=null;
                        
                        $values=explode('\r',$this->dataElement->validValues);
                        $values= preg_split ('/$\R?^/m', $this->dataElement->validValues);
                        $valInput=""; //<select name='$fld' id='$fld' $readOnly>";
                         $inpt['tag']='select';
                        foreach($values as $sval)
                        {
                                $sel="";
                                if ($this->val==$sval)
                                        $sel="selected";
                                $valInput.="<option value='$sval' $sel>$sval</option>";
                        }
                         $inpt['_text']=$valInput;
                        break;
                    }
                case 'Date':
                        $inpt['type']='text';
                        $inpt['value']=$this->val;
                    if ($this->isReadOnly)  {
                    }
                    else {
                        $inpt['class']='date';
                        //$input="<input type='text' class='date' value='$val' id='$fld' name='$fld' $readOnly>";
                    }
                    break;
                case 'File':
                        $inpt['type']='file';
                        $inpt['class']='input-file';
                    		
                    //$input="<input  id='$fld' name='$fld' class='input-file' type='file' $readOnly>";
                    break;
                default: 
                        $inpt['type']='text';
                        $inpt['value']=$this->val;
                    //$input="<input type='text' $std value='$val' $readOnly>";
                    break;
                }
                
        $this->specs[FieldDiv][Field]=$inpt;                
//       return $input;
   }
   private function processOption($optionString)
   {
       $opt=Form::parseOption($optionString);
       if (empty($opt))
           return;
       $option=$opt[0];
       
       {
            Context::debug("field option".$option.print_r($opt,true));
           switch ($option)
           {
               case 'class':    // section,class
               case 'addClass': // section,class
               case 'required': // condition
               case 'mask':     // maskString
               case 'group':     // name of group
                   break;
               case 'displayIf':     //displayIf(condition,action,elseAction)
                   $condition=$opt[1];
                   $id='fg_'.$this->name;
                   /*
        echo $this->specs['js_checkDisplay'];
    ?>
  // check edit control
  if (valueOf('Radio')=='Yes')
      jQuery('#fg_edit').show();
  else 
      jQuery('#fg_edit').hide();

                    */
                   $scr="if ($condition) {
                           jQuery('#$id').show();
                           } else {
                           jQuery('#$id').hide();
                           }";
                   $this->form->specs['js_checkDisplay'].=$scr;
                   
                   break;
               
                   
           }
       }
   }
}
