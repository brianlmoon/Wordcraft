<?php

/**
 * Responsible for handling the rewritten URLs when Search Engine Friendly
 * URLs are turned on.
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

$uri = (isset($_GET["uri"])) ? $_GET["uri"] : "";

if(empty($uri)){
    wc_output("notfound");
    return;
}

$uri_data = wc_db_lookup_uri($uri);

if(empty($uri_data)){
    wc_output("notfound");
    return;
}

if(isset($uri_data["current_uri"])){
    $new_url = wc_get_url($uri_data["type"], array($uri_data["object_id"], $uri_data["current_uri"]), false);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $new_url");
    exit();
}

if($uri_data["type"]=="page"){
    include_once "./page.php";
} elseif($uri_data["type"]=="post"){
    include_once "./post.php";
} else {
    // not sure how I would get here
    wc_output("notfound");
    return;
}

?>
