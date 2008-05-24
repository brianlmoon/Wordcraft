<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";

$page_id = (isset($_GET["page_id"])) ? (int)$_GET["page_id"] : 0;
$page_uri = (isset($_GET["page_uri"])) ? $_GET["page_uri"] : "";

if(empty($page_id) && empty($page_uri)){
    wc_output("notfound");
    return;
}

if($page_id){
    $WCDATA["page"] = wc_db_get_page($page_id);
} else {
    $WCDATA["page"] = wc_db_get_page($page_uri);
}


if(empty($WCDATA["page"])){
    wc_output("notfound");
    return;
}

$WCDATA["title"] = strip_tags($WCDATA["page"]["title"]);
$WCDATA["description"] = preg_replace('!\s+!', " ", substr(strip_tags($WCDATA["page"]["body"]), 0, 300));

wc_output("page", $WCDATA);

?>
