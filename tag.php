<?php

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";

$tag = (isset($_GET["tag"])) ? trim((string)$_GET["tag"]) : "";

if(empty($tag)){
    wc_output("notfound");
    return;
}

$data = wc_db_get_post_list(0, 10, true, "", $tag);

if($data[1]<1){
    wc_output("notfound");
    return;
}

$WCDATA["posts"] = $data[0];

foreach($WCDATA["posts"] as &$post){
    wc_format_post($post);
}
unset($post);

$WCDATA["title"] = $WC["default_title"];

$WCDATA["description"] = $WC["default_description"];

$WCDATA["feed_url"] = wc_get_url("feed", "rss", $tag);

wc_output("tag", $WCDATA);

?>
