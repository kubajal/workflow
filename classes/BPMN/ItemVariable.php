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

namespace OmniFlow\BPMN;

/**
 * Description of ItemVariable
 *
 * @author ralph
 */
class ItemVariable extends \OmniFlow\WFObject
{
 	var $refId;
 	var $field;
        var $view;
        var $edit;
        var $options;
        
        public function canView()
        {
            if ($this->view==='1' || $this->view==='true')
                return true;
            else 
                
                return false;
        }
        public function canEdit()
        {
            if ($this->edit==='1' || $this->edit==='true')
                return true;
            else 
                
                return false;
        }
        public function getDataElement(Process $proc)
        {

            foreach($proc->dataElements as $de)
            {
                if ($this->refId==$de->id)
                    return $de;
            }
            Context::Log(ERROR, "data element not found for $this->refId");
            return null;
            
        }

}


