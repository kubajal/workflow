<?php

namespace OmniFlow\API;
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

/**
 * Description of API
 *
 * @author ralph
 */

/*
 *  Package/Resource/Action/
 *GET Workflow/Process/Load/id/5000
 *  GET Workflow/Process/Load/?id=5000&user
 *PUT workflow/Process/Save/?id=5000&data=...
 *  
 */

interface Process {

    function ListProcesses();
    function Import();
    function NewProcess();

    function Copy();
    function Model();
    function Design();
    function Export();
    function Delete();
    function Describe();
    function Test();
    function Start();

    function load();
    function getDesignData();
    function saveDesignData();
    function getSVGData();
   
}
interface WFCase {
    function View();

    function load();
}
interface Task {

    function View();
    function Execute();
    function Invoke();
    function Complete();
    function SetMessage();
    function Take();
    function Release();
    function Assign();
}
interface Monitor {

    function GetView();
            function Notifications();
            function Recent();
            function Workload();
            function Users();
    function SimulateUser();
}
