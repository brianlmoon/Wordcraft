<?php

require_once "../include/common.php";

if(empty($WC["user"]) || !is_array($WC["user"]) || empty($WC["user"]["user_id"])) {

    include_once "./login.php";
    exit();
}

?>
