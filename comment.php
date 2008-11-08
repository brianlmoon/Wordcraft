<?php

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/output.php";
include_once "./include/format.php";
include_once "./include/spam.php";

if(empty($_POST)){
    header('HTTP/1.1 405 Method Not Allowed');
    header('Status: 405 Method Not Allowed');
    exit();
}

$post = wc_db_get_post($_POST["post_id"]);
wc_format_post($post);

if(!$post["allow_comments"]){
    wc_output("error", array("error"=>"Comments are disabled on this post."));
}

// check if this is a logged in author
$user = wc_db_check_cookie($_COOKIE["wc_admin"]);

if($user) {
    if(empty($user["first_name"]) && empty($user["last_name"])){
        $comment_name = $user["user_name"];
    } else {
        $comment_name = trim($user["first_name"]." ".$user["last_name"]);
    }

    $comment_email = $user["email"];
    $comment_url = wc_get_url("main");
    $comment_status = "APPROVED";

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

    if($WC["moderate_all"]){
        $comment_status = "UNAPPROVED";
    } else {
        $comment_status = "APPROVED";
    }

    $score = wc_score_user_submission($_POST["your_comment"]);

    if($score < 0){

        if($score < -20){
            header('HTTP/1.1 403 Forbidden');
            header('Status: 403 Forbidden');
            exit();
        }

        $comment_status = "SPAM";

    } elseif($WC["use_akismet"] && !empty($WC["akismet_key"])){

        $akismet_answer = wc_akismet_request( $comment, "comment-check" );

        if($akismet_answer=="true"){
            $comment_status = "SPAM";
        }
    }


}



$comment = array(
    "post_id"    => $_POST["post_id"],
    "name"       => $comment_name,
    "email"      => $comment_email,
    "url"        => $comment_url,
    "comment"    => $_POST["your_comment"],
    "ip_address" => $_SERVER["REMOTE_ADDR"],
    "status"     => $comment_status
);


$success = wc_db_save_comment($comment);

if($success){
    $comment = wc_db_get_comment($success);

    // email the comment
    if(empty($user) &&
       (($WC["email_comment"]=="all") ||
       ($WC["email_comment"]=="spam" && $comment["status"]=="SPAM"))){

        $subject = "[".$WC["default_title"]."] Comment on $post[subject]";
        $body = "There is a new comment on your post \"$post[subject]\"\n";
        $body.= wc_get_url("post", $post["post_id"], $post["uri"])."#comments\n\n";
        $body.= "Author : $comment[name] (IP: $comment[ip_address] )\n";
        $body.= "E-mail : $comment[email]\n";
        $body.= "URL    : $comment[url]\n";
        $body.= "Status : $comment[status]\n";
        $body.= "Score  : $score\n";
        $body.= "Comment: \n\n$comment[comment]\n\n";
        $body.= "Delete:  $WC[base_url]/admin/comment_moderate.php?mode=delete&comment_id=$comment[comment_id]\n";
        if($comment["status"]!="SPAM"){
            $body.= "Spam:    $WC[base_url]/admin/comment_moderate.php?mode=spam&comment_id=$comment[comment_id]\n";
        }
        if($comment["status"]=="APPROVED"){
            $body.= "Hide:    $WC[base_url]/admin/comment_moderate.php?mode=hide&comment_id=$comment[comment_id]\n";
        } else {
            $body.= "Approve: $WC[base_url]/admin/comment_moderate.php?mode=approve&comment_id=$comment[comment_id]\n";
        }

        $author = wc_db_get_user($post["user_id"]);

        mail($author["email"], $subject, $body, "From: $author[email]\r\nReply-To: $author[email]");
    }

    if($comment["status"]=="APPROVED"){
        $post_id = (int)$_POST["post_id"];
        $post = wc_db_get_post($post_id);
        $post_url = wc_get_url("post", $post_id, $post["uri"]);
        header("Location: $post_url#add_comment");
        exit();
    } else {
        $WCDATA["message"] = "Your comment has been held for moderation.  Please don't be offended.  It is not personal.";
        wc_output("message", $WCDATA);
    }
} else {
    wc_output("error", array("error"=>"Your comment could not be saved."));
}

?>
