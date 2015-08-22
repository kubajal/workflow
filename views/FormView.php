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
 * @author ralph
 */
class FormView extends Views {
    
public static function defaultForm(WFCase\WFCaseItem $item)
{
    $form=new FormView();
    $form->displayForm($item);
}
public function displayForm(WFCase\WFCaseItem $item)
{
		$task=$item->getProcessItem();
		$case=$item->case;
		$caseId=$case->caseId;
		$id=$item->id;
		$proc=$case->proc;
	
                if ($task==null)
                {
                    Context::Error("Case Item is not consistent with process. can not locate task $item->processNodeId");
                    return false;
                }
	
                
                $title =$proc->title.'-'.$task->label.' for case '.$case->caseId;
		$h1=Helper::getUrl(array("action"=>"task.saveForm","case"=>$caseId,"item"=>$id));
?>	
   <div class='formBox' style='margin: 0px;
                    border: rgb(205, 77, 194);
                    border-width: 2px;
                    background-color: azure;
                    padding: 15px;' >
   <h2 class='formTitle' style=''><?php echo $title; ?></h2>
    <form name='ajaxform' id='ajaxform' class='form-horizontal' action='<?php echo $h1; ?>' method='post'>
	 <input type='hidden' name='_caseId' value='<?php echo $caseId; ?>' />
	 <input type='hidden' name='_itemId' value='<?php echo $id; ?>' />
<?php			 
		
	
        foreach($task->dataElements as $var)
        {
            $dataElement=$var->getDataElement($case->proc);
            if ($dataElement==null)
                continue;


                $fld=$var->field;
                $name=$dataElement->name;
                $label=$dataElement->title;
                $help=$dataElement->description;
                $edit=$var->canEdit();
                $view=$var->canView();
                $type=$dataElement->dataType;

                Context::Log(INFO,"default form field name: $name type: $type".print_r($var,true).'edit:'.$edit.'view:'.$view);
                if ((!$view) && (!$edit))
                    continue;
                
                 if ($label=='')
                    $label=$name;
                if ($fld=='')
                    $fld=$name;

                $val=$case->getValue($name);

                if ($dataElement->validValues!='')
                    $type='select';

                $std="class='form-control' id='$fld' placeholder='$label'";
                $input='';
                
            switch($type)
            {
                case 'text':
                    $input="<textArea $std>$val</textArea>";
                    break;
                case 'Boolean':
                    $checked='';
                    if ($val=='Yes')
                        $checked='checked';

                    $input="<input type='checkbox' $std value='Yes' $checked />";
                    break;
                case 'select':
                    {
                        Context::Debug("valid values ".$dataElement->validValues);
                        $values=explode('\r',$dataElement->validValues);
                        $values= preg_split ('/$\R?^/m', $dataElement->validValues);
                        $valInput="<select name='$fld' id='$fld'>";
                        foreach($values as $sval)
                        {
                                $sel="";
                                if ($val==$sval)
                                        $sel="selected";
                                $valInput.="<option value='$sval' $sel>$sval</option>";
                        }
                        $valInput.="</select>";

                        $input=$valInput;
                        break;
                    }
                case 'Date':
                    $input="<input type='text' class='date' value='$val' id='$fld'>";
                    break;
                case 'File':
                    		
                    $input="<input  id='$fld' class='input-file' type='file'>";
                    break;
                default: 
                    $input="<input type='text' $std value='$val' >";
                    break;
                }
                $this->Field($fld,$label,$input,$help);
        }
        self::Field("complete", "Consider this Task Complete","<input type='checkbox' name='_complete'  />","To proceed to next task in the workflow");
        self::Field("save", "","<input type='submit'  value='Save' />","");

        echo "
        <br /> 
        <br /> 
<script>
jQuery('.date').datepicker();

</script>
        
        <br />";
        echo "</div>";
}	
public function Field($field,$label,$input,$help="")
{
echo "
<div class='form-group'>
  <label class='control-label col-xs-3' for='$field'>$label</label>
  <div class='col-xs-9'>
     $input 
   <span class='help-block'>$help</span>			       
  </div>        
</div>
";
    
    
    /*
echo "
    
<div class='control-group' style='
        display:block;width:100%;overflow: auto;
          border: 1px dotted;
          margin:5px;padding: 7px;
        '>
  <label class='control-label' style='
        float:left;width:200px;
  	text-align: right;
	margin-right: 15px;
  for='$field'>$label</label>
    <span class='field_value' style='float:left;'>$value</span>
    <p class='help-block' 
        style='margin: 0 0 20px 220px;
        clear:left;'>$help</p>
</div>"; */
//    echo "<br /><span class='field_label' style='float:left;width:150px;'>$label</span>";
    
//    echo "<span class='field_value' style='float:left;'>$value</span>";
    
}

}
