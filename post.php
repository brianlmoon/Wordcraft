<?php

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";
include_once "./include/url.php";
include_once "./include/captcha/CaptchaJavascript.php";
include_once "./include/captcha/lib/iscramble.php";

$post_id = (isset($_GET["post_id"])) ? (int)$_GET["post_id"] : 0;
$post_uri = (isset($_GET["uri"])) ? $_GET["uri"] : "";

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

if(empty($post_id)){
    $post_id = $WCDATA["post"]["post_id"];
}

list($WCDATA["comments"], $comment_total) = wc_db_get_comments($post_id, "APPROVED");

foreach($WCDATA["comments"] as &$comment){
    wc_format_comment($comment);
}
unset($comment);

$WCDATA["title"] = strip_tags($WCDATA["post"]["subject"]);
$WCDATA["description"] = preg_replace('!\s+!', " ", substr(strip_tags($WCDATA["post"]["body"]), 0, 300));

$WCDATA["comment_url"] = wc_get_url("comment");

if($WC["use_captcha"]){
    $captcha = new CaptchaJavascript();
    $data = $captcha->generate_captcha();
    $WCDATA["captcha"] = iScramble_javascript();
    $WCDATA["captcha"].= $data["html_form"];
    $WCDATA["captcha"].= $data["html_after_form"];
    session_start();
    $_SESSION["captcha"] = $data;
}

if($WCDATA["post"]["allow_comments"]){
    header("X-Pingback: ".wc_get_url("pingback"));
}

wc_output("post", $WCDATA);

?>
