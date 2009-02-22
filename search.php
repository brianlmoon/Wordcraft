<?php

/**
 * Simple search for posts
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";

$display = 10;

if(isset($_GET["s"])){
    $start = (int)$_GET["s"];
} else {
    $start = 0;
}

$query = (isset($_GET["q"])) ? trim((string)$_GET["q"]) : "";

if(empty($query)){
    wc_output("notfound");
    return;
}

list($WCDATA["posts"], $total_posts) = wc_db_get_post_list($start, $display, true, $query);

if($total_posts<1){
    wc_output("notfound");
    return;
}

foreach($WCDATA["posts"] as &$post){
    wc_format_post($post);
}
unset($post);

$WCDATA["title"] = "Posts containing `".htmlspecialchars($query)."` - ".$WC["default_title"];
$WCDATA["description"] = "Posts containing `".htmlspecialchars($query)."`. ".$WC["default_description"];

$WCDATA["feed_url"] = wc_get_url("feed", "rss", "", $query);

if($total_posts > $start + $display) {
    $s = $start + $display;
    $WCDATA["older_url"] = wc_get_url("tag", $tag)."&s=$s";
}

if(($start > 0)){
    $WCDATA["newer_url"] = wc_get_url("tag", $tag);
    $s = $start - $display;
    if($s>0){
        $WCDATA["newer_url"].="&s=$s";
    }
}


wc_output("post_list", $WCDATA);

?>
