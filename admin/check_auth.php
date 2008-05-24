<?php

require_once "../include/database.php";

if(isset($_COOKIE["wc_admin"])){

    $USER = wc_db_check_cookie($_COOKIE["wc_admin"]);

}

if(empty($USER) || !is_array($USER) || empty($USER["user_id"])) {

    include_once "./login.php";
    exit();
}

?>
