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
 * Description of Queue
 *
 * @author ralph
 */
class QueueEngine {
    static $queueItems=array();
    public static function addNodeToCase($method,array $objects)
    {
        Context::Debug("Queue::addNodeToCase $method");
        $qItem=
                array("type"=>'Node',
                "method"=>$method,
                "objects"=>$objects);

          self::$queueItems[]=$qItem;
        
//        self::executeQueueItem($qItem);
        
    }
    public static function addMessage($message,array $data)
    {
        Context::Debug("Queue::addMessage $message");
        $qItem=
                array("type"=>'Message',
                "method"=>$message,
                "objects"=>$data);

          self::$queueItems[]=$qItem;
        
//        self::executeQueueItem($qItem);
        
    }
    public static function addEvent()
    {
        
    }
    public static function getQueueItem()
    {
        if (count(self::$queueItems)==0)
            return null;
        
        $item=self::$queueItems[0];
        array_splice(self::$queueItems,0,1);
        return $item;

    }

    /*
     * Check if there is something to do
     */
    public static function checkQueue()
    {
        Context::Log(INFO, "checkQueue");
        
        while(self::hasEnoughResources())
        {
        try {
                
            $nextItem=self::getQueueItem();
            if ($nextItem==null)
                break;

            Context::Log(INFO, "checkQueue item");
            
            OmniModel::getInstance()->startTransaction();

            self::executeQueueItem($nextItem);
        
            OmniModel::getInstance()->commit();
            
            } 
            catch (Exception $ex) {
                Context::Log(ERROR, $ex->message);    
            }
        }
        EventEngine::Check();
    }
    public static function executeQueueItem($nextItem)
    {
      
        switch($nextItem['type'])
        {
            case 'Message':
                $msg=$nextItem["method"];
                $data=$nextItem["objects"];
                MessageEngine::Process($msg,$data);
                break;
            case 'Node':
                Context::Log(INFO, "Queue::executeQueueItem");
                $method=$nextItem["method"];
                $objects=$nextItem["objects"];

                $obj=null;
                $params=array();
    //            print_r($nextItem);


                foreach($objects as $o)
                {
                    if ($obj==null)
                        $obj=$o;
                    else
                        $params[]=$o;
                }

                call_user_func_array (  array( $obj,$method) , $params );

    //           $ret=$processItem->$method($case,"",$from);
                break;
        }
    }
    public static function hasEnoughResources()
    {
        return true;
    }
}
