<?php

include_once "./include/config.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";
include_once "./include/akismet.php";

// check if this is a logged in author
$user = wc_db_check_cookie($_COOKIE["wc_admin"]);

if($user) {
    if(empty($user["first_name"]) && empty($user["last_name"])){
        $comment_name = $user["user_name"];
    } else {
        $comment_name = trim($user["first_name"]." ".$user["last_name"]);
    }

    $comment_email = "";
    $comment_url = "";

} else {

    print_var($_POST);

    if(empty($_POST["your_name"])){
        wc_output("error", array("error"=>"Please fill in your name."));
        exit();
    }

    if(empty($_POST["your_comment"])){
        wc_output("error", array("error"=>"Please fill in a comment."));
        exit();
    }

    $comment_name = $_POST["your_name"];
    $comment_email = $_POST["your_email"];
    $comment_url = $_POST["your_url"];

}



$comment = array(
    "post_id"    => $_POST["post_id"],
    "name"       => $comment_name,
    "email"      => $comment_email,
    "url"        => $comment_url,
    "comment"    => $_POST["your_comment"],
    "ip_address" => $_SERVER["REMOTE_ADDR"]
);

if(!empty($WC["akismet_key"])

    $akismet_answer = wc_akismet_http_request( $comment, "comment-check" );

    if($akismet_answer=="true"){
        $comment["status"] = "SPAM";
    }
}

$success = wc_db_post_comment($comment);

if($success){
    $post_id = (int)$_POST["post_id"];
    header("Location: post.php?post_id=".$post_id."#add_comment");
    exit();
} else {
    wc_output("error", array("error"=>"Your comment could not be saved."));
}

?>
