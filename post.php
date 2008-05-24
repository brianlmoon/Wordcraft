<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";
include_once "./include/url.php";

$post_id = (isset($_GET["post_id"])) ? (int)$_GET["post_id"] : 0;
$post_uri = (isset($_GET["post_uri"])) ? $_GET["post_uri"] : "";

if(empty($post_id) && empty($post_uri)){
    wc_output("notfound");
    return;
}

if($post_id){
    $WCDATA["post"] = wc_db_get_post($post_id);
} else {
    $WCDATA["post"] = wc_db_get_post($post_uri);
}

if(empty($WCDATA["post"])){
    wc_output("notfound");
    return;
}

wc_format_post($WCDATA["post"]);

list($WCDATA["comments"], $comment_total) = wc_db_get_comments($post_id);

foreach($WCDATA["comments"] as &$comment){
    wc_format_comment($comment);
}
unset($comment);

$WCDATA["title"] = strip_tags($WCDATA["post"]["subject"]);
$WCDATA["description"] = preg_replace('!\s+!', " ", substr(strip_tags($WCDATA["post"]["body"]), 0, 300));

$WCDATA["comment_url"] = wc_get_url("comment");

wc_output("post", $WCDATA);

?>
