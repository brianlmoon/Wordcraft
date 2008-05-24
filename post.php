<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";
include_once "./include/url.php";

$post_id = (isset($_GET["post_id"])) ? (int)$_GET["post_id"] : 0;

if(empty($post_id)){
    wc_output("notfound");
    return;
}

$WCDATA["post"] = wc_db_get_post($post_id);

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

// check if this is a logged in author
$WCDATA["user"] = wc_db_check_cookie($_COOKIE["wc_admin"]);

$WCDATA["title"] = strip_tags($WCDATA["post"]["subject"]);
$WCDATA["description"] = preg_replace('!\s+!', " ", substr(strip_tags($WCDATA["post"]["body"]), 0, 300));

$WCDATA["comment_url"] = wc_get_url("comment");

wc_output("post", $WCDATA);

?>
