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
require_once "./admin_functions.php";

if(!isset($WC["db_version"]) || $WC["db_version"] != WC_DB_VERSION){
    header("Location: upgrade.php");
    exit();
}

if(!defined("WC_INSTALLING") &&
   (empty($WC["user"]) || !is_array($WC["user"]) || empty($WC["user"]["user_id"]))) {

    require_once "./login.php";
    exit();
}

if(count($_POST)){

    if(empty($_POST["secret"]) ||
       !in_array($_POST["secret"], $_SESSION["form_secrets"])){
        wc_admin_error("The form information you submitted is invalid.  Please go back and reload the page before submitting the form.");
    }
}

?>
