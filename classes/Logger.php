<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of Logger
 *
 * @author ralph
 */
class Logger 
{
    static $debug=true;
    static $logFileName="";
    static function StartSession($fileName)
    {
            Logger::$logFileName=$fileName;
    }
    static function StartSection($name)
    {

    }
    static function Debug($msg)
    {

            if (!Logger::$debug)
                    return;

            if (Logger::$logFileName=="")
                    echo '<span class="debug"><br />'.$msg.'</span>';
            else
                    file_put_contents (Logger::$logFileName , $msg , FILE_APPEND );


    }

    static function Log($msg)
    {

            if (Logger::$logFileName=="")
                    echo '<span class="debug"><br />'.$msg.'</span>';
            else
                    file_put_contents (Logger::$logFileName , $msg , FILE_APPEND );


    }
    static function Error($msg)
    {
            echo '<br />'.$msg;
    }
}
	

