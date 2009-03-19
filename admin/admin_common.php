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

define("WC_ADMIN", 1);

require_once "../include/common.php";

if(!isset($WC["db_version"]) || $WC["db_version"] != WC_DB_VERSION){
    header("Location: upgrade.php");
    exit();
}

if(!defined("WC_INSTALLING") &&
   (empty($WC["user"]) || !is_array($WC["user"]) || empty($WC["user"]["user_id"]))) {

    require_once "./login.php";
    exit();
}

?>
