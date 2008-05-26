<?php

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/format.php";
include_once "./include/feeds.php";

$tag = (empty($_GET["tag"])) ? "" : $_GET["tag"];

$data = wc_db_get_post_list(0, 30, true, "", $tag);

$WCDATA["posts"] = $data[0];

foreach($WCDATA["posts"] as &$post){
    wc_format_post($post);
}
unset($post);

$WCDATA["title"] = $WC["default_title"];

if(!empty($tag)) {
    $WCDATA["title"].= " (Tag: $tag)";
}

$WCDATA["description"] = $WC["default_description"];

$feed_type = (empty($_GET["type"])) ? "rss" : $_GET["type"];

$url = wc_get_url("feed", $feed_type, $tag);

switch($feed_type){
    case "atom":
        $ct = "text/xml";
        $output = wc_feed_make_atom($WCDATA["posts"], $url, $WCDATA["title"], $WCDATA["description"]);
        break;
    case "json":
        $ct = "application/json";
        $output = wc_feed_make_json($WCDATA["posts"], $url, $WCDATA["title"], $WCDATA["description"]);
        break;
    default:
        $ct = "text/xml";
        $output = wc_feed_make_rss($WCDATA["posts"], $url, $WCDATA["title"], $WCDATA["description"]);
}

header("Content-Type: $ct");
echo $output;

?>
