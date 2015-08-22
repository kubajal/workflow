<?php

/*
 * Copyright (C) 2015 ralph
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

namespace OmniFlow\BPMN;

/**
 * Description of DataElement
 *
 * @author ralph
 */
class DataElement extends \OmniFlow\WFObject
{
	var $name;
	var $id;
	var $title;
	var $description;
	var $dataType;
	var $validValues;
        var $req;
/*
	public static function fromXML($node)
	{
            $de=new DataElement();
            $de->__fromXML($node);
            return $de;
	}
	public static function fromArray($arr)
	{
            $de=new DataElement();
		$de->id=$arr['id'];
		$de->name=$arr['name'];
		$de->title=$arr['title'];
		$de->dataType=$arr['dataType'];
		$de->validValues=$arr['validValues'];
		$de->req=$arr['req'];
            return $de;
	}
        
        public function toXML($node)
        {
            $node->addAttribute('id',$this->id);
            $node->addAttribute('name',$this->name);
            $node->addAttribute('title',$this->title);
            $node->addAttribute('description',$this->description);
            $node->addAttribute('dataType',$this->dataType);
            $node->addAttribute('validValues',$this->validValues);
            $node->addAttribute('req',$this->req);

        }*/
}
