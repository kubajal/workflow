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
 * Description of WFObject
 *
 * @author ralph
 */
class WFObject
{
        private function __getProperties()
        {
            $reflect = new \ReflectionClass($this);
            $props   = $reflect->getProperties(/*ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED*/);
                
            return $props;
        
        }
	public function __toArray()
	{
	
		$props   = $this->__getProperties();
	
		$data=Array();
		foreach ($props as $prop) {
                        if ($prop->isStatic())
                            continue;
			$name=$prop->getName();
			$val=$this->$name;
			if (!is_array($val) && !is_object($val))
			{
        			$data[$name]=$val;
	
			}
		}
		return $data;
	}

        public function __toXML($node)
        {
            foreach($this->__toArray() as $prop=>$val)
            {
                $node->addAttribute($prop,$val);
            }
        }

        public function __fromXML($node)
        {
            $props   = $this->__getProperties();
            foreach($props as $prop)
            {
                if ($prop->isStatic())
                    continue;
		$name=$prop->getName();
		$this->$name =XMLLoader::getAttribute($node,$name);
            }
            
        }
	
	public function __fromArray($data)
	{
		$props   = $this->__getProperties();

                foreach ($props as $prop) {

                    if ($prop->isStatic())
                            continue;
                    $name=$prop->getName();
                    
                    if (isset($data[$name]))
                    {
                       $val=$data[$name];
                        if ($val!=="null")
        			$this->$name=$val;
                        
                    }
		}
	
	}}
