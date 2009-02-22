<?php

/**
 * Included in all admin scripts.  Checks that the user is logged in.
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

require_once "../include/common.php";

if(empty($WC["user"]) || !is_array($WC["user"]) || empty($WC["user"]["user_id"])) {

    include_once "./login.php";
    exit();
}

?>
