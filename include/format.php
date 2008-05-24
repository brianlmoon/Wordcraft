<?php

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

// if init has not loaded, quit the file
if(!defined("WC")) return;

include_once dirname(__FILE__)."/url.php";

function wc_format_post(&$post) {

    global $WC;

    $post["post_date"] = strftime($WC["date_format_long"], strtotime($post["post_date"]));

    $post["subject"] = htmlspecialchars($post["subject"]);

    $post["url"] = wc_get_url("post", $post["post_id"]);

    if(isset($post["tags"])){
        $tmp_tags = $post["tags"];
        unset($post["tags"]);
        foreach($tmp_tags as $tag){
            $post["tags"][] = array(
                "tag" => $tag,
                "url" => wc_get_url("tag", $tag)
            );
        }
    }
}


function wc_format_comment(&$comment) {

    $comment["comment"] = strip_tags($comment["comment"]);
    $comment["comment"] = htmlspecialchars($comment["comment"]);

    $comment["comment"] = nl2br($comment["comment"]);

    $comment["comment"] = preg_replace("/((http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]+)/i", "<a href=\"$1\" rel=\"nofollow\">$1</a>", $comment["comment"]);

    $comment["name"] = htmlspecialchars($comment["name"]);
    $comment["url"] = htmlspecialchars($comment["url"]);
    $comment["email"] = htmlspecialchars($comment["email"]);
    $comment["status"] = ucfirst(strtolower($comment["status"]));
}

?>
