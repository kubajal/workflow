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
 * Description of EmailEngine
 *
 * @author ralph
 */
/*
 *  example of how to user
 * 
 *      EMail.to="abc";
 *      Email.subject="aaa";
 *      Email.send();
 */
class EmailEngine
{
     // wp_mail( $to, $subject, $message, $headers, $attachments ); 
    var $to;
    var $subject;
    var $message;
    var $headers;
    var $attachments;
    public function send($to=null,$subject=null,$message=null,$headers=null,$attachments=null)
    {
        if ($to !==null) $this->to=$to;
        if ($subject !==null) $this->subject=$subject;
        if ($message !==null) $this->message=$message;
        if ($headers !==null) $this->headers=$headers;
        if ($attachments !==null) $this->attachments=$attachments;
        
        if (Context::getInstance()->sendEmail) {
            
            Context::debug("sending email to: $this->to , subject: $this->subject message: $this->message headers: $this->headers");
            $ret=wp_mail($this->to,$this->subject,$this->message,$this->headers,$this->attachments);
        }  else  {
            Context::debug("not sending email to: $this->to , subject: $this->subject message: $this->message headers: $this->headers");
            // todo log email message
            $ret=true;
        }
        return $ret;
    }
            

}
