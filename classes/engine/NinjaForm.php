<?php
namespace  OmniFlow
{

class NinjaForms
{
	
	static function displayForm($formId)
	{
		if ( function_exists( 'ninja_forms_display_form' ) )
		{
//			echo 'about to display the form'.$formId;
			ninja_forms_display_form( $formId);
		}
		else
		{
//			echo 'ninja is not here';
			return false;
		}
		
	}
	static function ninja_forms_register(){
		add_action( 'ninja_forms_post_process', 
				'OmniFlow\\NinjaForms::postProcess' );
	}
	
	static function formDisplay()
	{
		//Declare $ninja_forms_loading as a global variable.
//		echo 'form display';
		global $ninja_forms_loading;

		if ($ninja_forms_loading==null)
			return;
		
		Context::Log(INFO,var_export($ninja_forms_loading,true));
		
		if (!isset($_REQUEST['caseId']))
			return;
			
		$caseId=$_REQUEST['caseId'];
		$id=$_REQUEST['id'];
		
		$item=CaseSvc::LoadCaseItem($caseId, $id);
		$case=$item->case;
		
		$variables=$item->getVariables();
		
//		echo 'displaying...';
		$form_id = $ninja_forms_loading->get_form_ID();
		
		$all_fields = $ninja_forms_loading->get_all_fields();

		foreach( $all_fields as $field_id=>$fieldValue){
			
			foreach($variables as $var)
			{
				if ($var->field==$field_id)
				{
					$val=$case->getValue($var->name);
					$ninja_forms_loading->update_field_value( $field_id, $val );
				}
			}
			
		}
		
		//$all_fields will be an array in the format of: array( 'field_id' => #, 'user_value' => 'Submitted Value' );
	}

	static function formProcessed(){
		global $ninja_forms_processing;
		
		if ($ninja_forms_processing==null)
			return;
		
		$_REQUEST['FormProcessed']="Yes";
		
		$formData=$ninja_forms_processing;

	
		$form=$formData->data['form'];
//		$url=$formData->data['form']['form_url'];
		echo '<br /> form url'.$url;
		

		$urlPara=explode('&',parse_url($url, PHP_URL_QUERY));
		$tags=array();
		foreach($urlPara as $para)
		{
			$arr=explode('=',$para);
			$tag=$arr[0];
			$tags[$tag]=$arr[1];			
		}
		$caseId=$tags['caseId'];
		$id=$tags['id'];
		
		//Get all the user submitted values
		$all_fields = $ninja_forms_processing->get_all_fields();
		
		TaskSvc::SetStatus($caseId, $id,null,$all_fields);
	
		if( is_array( $all_fields ) ){ //Make sure $all_fields is an array.
			//Loop through each of our submitted values.
			foreach( $all_fields as $field_id => $user_value ){
				//Update an external database with each value
				
			}
		}
	}
}
}