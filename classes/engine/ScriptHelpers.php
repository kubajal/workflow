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

/**
 * Description of ScriptHelpersEngine
 *
 * @author ralph
 */
class ExpressionFunctionHelper
{
        static $scriptEngine;
	static $variables=array();
	public static function getVar($scriptEngine,$name)
	{
		if (isset(self::$variables[$name]))
		{
			return self::$variables[$name];
		}
		else
		return null;
	}
	static function setVar($scriptEngine,$name,$val)
	{
            $scriptEngine->vars[$name]=$val;
            self::$variables[$name]=$val;
	}
	static function log($scriptEngine,$exp)
	{
            if (is_array($exp))
            {
                return print_r($exp,true);
            }
            elseif (is_object($exp))
            {
                return 'object:'.var_export($exp,true);
                
            }
            elseif (is_string($exp))
            {
                return 'string:'.htmlspecialchars($exp);
                
            }
            else
                return htmlspecialchars($exp);
	}
        /* Direct output to the variable */
	static function setOutput($scriptEngine,$var)
        {
            $scriptEngine->outputVar=$var;
        }
	static function output($scriptEngine,$exp)
	{
            if ($scriptEngine===null)
                $scriptEngine=self::$scriptEngine;
            $str="";
            if (is_array($exp))
            {
                $str=print_r($exp,true);
            }
            elseif (is_object($exp))
            {
                $str=var_export($exp,true);
                
            }
            else
                $str=$exp;
            
            $scriptEngine->out($str);
            return $str;
            
	}
	static function returnFunct($scriptEngine,$exp)
	{
            $scriptEngine->result=$exp;
            $scriptEngine->returning=true;
            return $exp;
	}
        static function declareFunct($scriptEngine,$name)
        {
            $scriptEngine->vars[$name]=null;
        }
}

