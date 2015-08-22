<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
            
            DB::startTransaction();

            self::executeQueueItem($nextItem);
        
            DB::commit();
            
            } 
            catch (Exception $ex) {
                Context::Log(ERROR, $ex->message);    
            }
        }
        EventEngine::Check();
    }
    public static function executeQueueItem($nextItem)
    {
        
        if ($nextItem["type"]=='Node')
        {
//        Context::Log(INFO, "processItem ".print_r($nextItem,true));
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
        }
    }
    public static function hasEnoughResources()
    {
        return true;
    }
}
