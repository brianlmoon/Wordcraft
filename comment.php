<?php

include_once "./include/common.php";
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

    if(empty($_POST["your_name"])){
        wc_output("error", array("error"=>"Please fill in your name."));
        exit();
    }

    if(empty($_POST["your_comment"])){
        wc_output("error", array("error"=>"Please fill in a comment."));
        exit();
    }

    if($WC["use_captcha"]){
        $success = false;

        session_start();

        if(isset($_SESSION["captcha"])){

            if(strtolower($_POST[$_SESSION["captcha"]["input_fieldname"]])==strtolower($_SESSION["captcha"]["answer"])){
                $success = true;
            }
        }

        if(!$success){
            wc_output("error", array("error"=>"Spam prevention failed.  Please check your input again."));
            exit();
        }
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

if(!empty($WC["akismet_key"])){

    $akismet_answer = wc_akismet_request( $comment, "comment-check" );

    if($akismet_answer=="true"){
        $comment["status"] = "SPAM";
    }
}

$success = wc_db_post_comment($comment);

if($success){
    $post_id = (int)$_POST["post_id"];
    $post = wc_db_get_post($post_id);
    $post_url = wc_get_url("post", $post_id, $post["uri"]);
    header("Location: $post_url#add_comment");
    exit();
} else {
    wc_output("error", array("error"=>"Your comment could not be saved."));
}

?>
