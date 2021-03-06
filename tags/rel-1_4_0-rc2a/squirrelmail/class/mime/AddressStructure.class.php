<?php

/**
 * AddressStructure.class.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions needed to handle mime messages.
 *
 * $Id$
 */

class AddressStructure {
    var $personal = '',
        $adl      = '',
        $mailbox  = '',
        $host     = '',
        $group    = '';

    function getAddress($full = true) {
        $result = '';

        if (is_object($this)) {
            $email = ($this->host ? $this->mailbox.'@'.$this->host
	                          : $this->mailbox);
            if (trim($this->personal)) {
	        $addr = ($email ? '"' . $this->personal . '" <' .$email.'>'
		                : $this->personal);
                $best_dpl = $this->personal;
            } else {
                $addr = $email;
                $best_dpl = $email;
            }
            $result = ($full ? $addr : $best_dpl);
        }
        return $result;
    }
}

?>
