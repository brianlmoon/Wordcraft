<?php

require_once "../include/common.php";

if(isset($_COOKIE["wc_admin"])){

    list($user_id, $session_id) = explode(":", $_COOKIE["wc_admin"]);

    setcookie("wc_admin", "", time()-86400, $WC["session_path"], $WC["session_domain"]);

    wc_db_save_user(array("user_id"=>$user_id, "session_id"=>""));
}

header("Location: index.php");
exit();

?>
