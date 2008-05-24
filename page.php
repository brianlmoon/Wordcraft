<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";

$page_id = (isset($_GET["page_id"])) ? (int)$_GET["page_id"] : 0;

if(empty($page_id)){
    wc_output("notfound");
    return;
}

$WCDATA["page"] = wc_db_get_page($page_id);

if(empty($WCDATA["page"])){
    wc_output("notfound");
    return;
}

$WCDATA["title"] = strip_tags($WCDATA["page"]["title"]);
$WCDATA["description"] = preg_replace('!\s+!', " ", substr(strip_tags($WCDATA["page"]["body"]), 0, 300));

wc_output("page", $WCDATA);

?>
