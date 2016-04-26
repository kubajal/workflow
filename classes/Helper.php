<?php

/*
 * Copyright (c) 2015, Omni-Workflow - Omnibuilder.com by OmniSphere Information Systems. All rights reserved. For licensing, see LICENSE.md or http://workflow.omnibuilder.com/license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmniFlow;

class Helper
{

	public static function HeaderInclude($file='')
	{
		if ($file=='')
		{
			if (function_exists('wp_enqueue_script'))
				return;
		}
                $scripts=array();
		$styles=array();
                
		$styles[]=array('bootstrap',"lib/bootstrap/bootstrap.min.css");
		$styles[]=array('bootstrap-theme',"lib/bootstrap/bootstrap-theme.min.css");
                $scripts[]=array('bootstrap',"lib/bootstrap/bootstrap.min.js",array( 'jquery' ));
                
		$scripts[]=array('dhtmlx',"dhtmlx/codebase/dhtmlx.js");
		$styles[]=array('dhtmlx',"dhtmlx/codebase/dhtmlx.css");
		$styles[]=array('dhtmlx-DS',"dhtmlx/codebase/datastore.js");
		$styles[]=array('dhtmlx-skin',"dhtmlx/skins/skyblue/dhtmlx.css");
		
//                if ($modeler)
                {
		$styles[]=array('diagram-js',"css/diagram-js.css");
		$styles[]=array('bpmn',"vendor/bpmn-font/css/bpmn-embedded.css");
		$styles[]=array('app',"css/app.css");
                }
		
//		$scripts[]=array('jquery',"js/jquery.min.js");
		$scripts[]=array('jquery-ui',"lib/jquery-ui/jquery-ui.min.js");
		$styles[]=array('jquery-ui',"lib/jquery-ui/jquery-ui.css");
		$styles[]=array('jquery-ui-theme',"lib/jquery-ui/jquery-ui.theme.css");
                
		$scripts[]=array('jquery-isloading',"js/jquery.isloading.min.js");
		$scripts[]=array('jquery-validate',"lib/jquery.validate.min.js");
                
		$styles[]=array('workflow',"css\workflow.css");
		$scripts[]=array('workflow',"js/workflow.js");
		$scripts[]=array('workflow-json',"js/jsonHelper.js");
		$scripts[]=array('workflow-editor',"js/processEditor.js");
		$scripts[]=array('workflow-case',"js/caseView.js");
                
		
		if ($file!='')  // From WordPress
		{
			foreach($scripts as $script)
			{
				wp_enqueue_script(
						$script[0],
						plugins_url( $script[1] , $file),array( 'jquery' ));
			}

			foreach($styles as $style)
			{
				wp_enqueue_style(
						$style[0],
						plugins_url( $style[1], $file));
			}
                        
		}
		else    // Without WordPress
		{
			foreach($scripts as $script)
			{
				echo "<script type='text/javascript' src='$script[1]'></script>";
			}

			foreach($styles as $style)
			{
				echo "<link rel='stylesheet' href='$style[1]' type='text/css'>";
			}

		}
	}

        public static function BasicJS()
        {
		if (Context::getInstance()->fromWordPress)
		{
                        
                    echo '<script>
                        var omni_base_url="'.Context::getInstance()->omniBaseURL.
                        '";</script>';
		}
		else    // Without WordPress
		{
			// no wordPress
			echo '
				<script>
                                var omni_base_url=""; 
				var ajax_object =null;					
				</script>';
		}
            
        }
	public static function ItemRef(BPMN\ProcessItem $processItem)
	{
		return "<a class='processItem' href='#' itemId='".$processItem->id."'>".
				$processItem->label."</a>";
	}
	public static function getClassName($object)
	{
		return self::className(get_class($object));

	}
	public static function className($className)
	{
		$className=str_replace('\\', '/', $className);
		$className= substr($className,strrpos($className, '/')+1);
		return $className;

	}
	/*
		Scenarios:

		1) from admin panel called with a page=...
		a) OmniFlow\Config::$pageUrl="page=omni-workflow";
		b) pageUrl is appended to the request_uri
		2) from front end http://localhost:8000/wordpress/omni-workflow-processes/
		first time OK
		but second calls it include the options
		3) from front end http://localhost:8000/wordpress/?page=omni-workflow-processes
		first time OK
		but second calls it include the options


		*/

	public static function getAjaxUrl($options)
	{
		//$url=strtok($_SERVER["REQUEST_URI"],'action=');
            
                $url = admin_url().'/admin-ajax.php?action=omni_ajax'; 
                $firstMarker="&";


		foreach($options as $opt=>$val)
		{
			$url.=$firstMarker.$opt.'='.$val;
		}
		return $url;
	}
	public static function getUrl($options)
	{
		//$url=strtok($_SERVER["REQUEST_URI"],'action=');
		$url=$_SERVER["REQUEST_URI"];


		if (Config::$pageUrl=="")
		{
			$pos = strpos($url, 'action=');

			if ($pos > 0) {		// no action go ahead
				$url=substr($url,0,$pos-1);
			}
			if (strpos($url, '?')>0)
				$firstMarker="&";
			else
				$firstMarker="?";
		}
		else
		{
			$pos = strpos($url, '?');

			if ($pos > 0) {		// no action go ahead
        			$url=substr($url,0,$pos);
			}
			$firstMarker="&";
			$url.='?'.Config::$pageUrl;
		}

		foreach($options as $opt=>$val)
		{
			$url.=$firstMarker.$opt.'='.$val;
			$firstMarker="&";
		}
		return $url;
	}
        
   static function getJsonError()
   {
        switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return  '';
        break;
        case JSON_ERROR_DEPTH:
            return  'Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            return  'Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            return  'Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            return  'Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            return  'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            return  'Unknown error';
        break;
    }

   }
public static function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = Helper::utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}   
}
