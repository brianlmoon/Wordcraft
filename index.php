<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";

$data = wc_db_get_post_list(0, 10, true);

$WCDATA["posts"] = $data[0];

foreach($WCDATA["posts"] as &$post){
    wc_format_post($post);
}
unset($post);

$WCDATA["title"] = $WC["default_title"];

$WCDATA["description"] = $WC["default_description"];

wc_output("index", $WCDATA);

?>
