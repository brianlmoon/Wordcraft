<?php

include_once "./include/common.php";
include_once "./include/database.php";
include_once "./include/url.php";

$success = false;

define("WC_LB_SUCCESS",         "THANKS");
define("WC_LB_ERROR_NOTFOUND",  32);
define("WC_LB_ERROR_NOSOURCE",  16);
define("WC_LB_ERROR_NOLINK",    17);
define("WC_LB_ERROR_DUP",       48);
define("WC_LB_ERROR_DENIED",    49);

$pingback_error_strings = array(
    32 => "Hmm, I don't know of a page on my site that has that URL.",
    16 => "Well, I tried, but I could not find the URL that you claim links to me.",
    17 => "I checked out that page.  It does not look like it links to me.",
    48 => "Yeah, I already know about that link.  Persistent aren't we?",
    49 => "I'm sorry Dave, I'm afraid I can't do that."
);


function handle_linkback($remote, $local, $title="", $except="") {

    global $WC, $WCDATA;

    // verify the $local url is on this site
    if(strpos($local, $WC["base_url"])===false){
        return WC_LB_ERROR_NOTFOUND;
    }

    // check for regular urls
    $q = parse_url($local, PHP_URL_QUERY);
    if($q){
        parse_str($q);
        if(isset($post_id)){
            $post = wc_db_get_post($post_id);
        }
    }

    // check for sef urls if no $post
    if(empty($post)){
        $uri = str_replace($WC["base_url"]."/", "", $local);
        if($uri){
            $post = wc_db_get_post($uri);
        }
    }

    // if no post found, return error
    if(empty($post)){
        return WC_LB_ERROR_NOTFOUND;
    }

    // check that we have not gotten this already
    list($comments, $count) = wc_db_get_comments($post["post_id"]);
    foreach($comments as $comment){
        if($comment["url"] == $remote){
            return WC_LB_ERROR_DUP;
        }
    }


    // get the remote page to run some checks
    $remote_page = @file_get_contents($remote);

    if(empty($remote_page)){
        return WC_LB_ERROR_NOSOURCE;
    }

    // replace any &amp; with & to make the lookup easy
    $remote_page = str_replace("&amp;", "&", $remote_page);

    // normalize spaces
    $remote_page = preg_replace("!\s+!s", " ", $remote_page);

    // do a simple check before we go to a lot of trouble
    if(strpos($remote_page, $local)===false){
        return WC_LB_ERROR_NOLINK;
    }

    // for deeper inspection, only look at the body of the page
    if(!preg_match('!<body[^>]*>.+?</body>!is', $remote_page, $match)){
        print_var($match);
        return WC_LB_ERROR_NOSOURCE;
    }

    $remote_body = $match[0];

    /**
     * We are only going to acknowledge links in plain HTML
     */

    // strip html comments
    $remote_body = preg_replace('|<!--.+?-->|s', "", $remote_body);

    // strip script tags
    $remote_body = preg_replace('|<script[^>]*>.+?</script>|is', "", $remote_body);

    // now pull out all the anchor tags
    if(!preg_match_all('!<a\s+[^>]*>!i', $remote_page, $matches)){
        return WC_LB_ERROR_NOLINK;
    }

    $found = false;
    foreach($matches[0] as $anchor){
        if(preg_match('!href="(.+?)"|href=\'(.+?)\'|href=([^ >]+)!', $anchor, $match)){
            $url = max($match[1], $match[2], $match[3]);
            $url = str_replace("&amp;", "&", $url);
            if($local==$url){
                $remote_body = str_replace($anchor, "[wctag]", $remote_body);
                $found = true;
                break;
            }
        }
    }

    if(!$found){
        return WC_LB_ERROR_NOLINK;
    }


    // ok, lets start making a comment

    $title = str_replace(array("http://","https://"), "", $remote);
    if(strlen($title) > 50){
        $title = substr($title, 0, 47)."...";
    }

    $excerpt = "Linkback: $remote";

    $comment = array(
        "post_id"    => $post["post_id"],
        "name"       => $title,
        "url"        => $remote,
        "comment"    => $excerpt,
        "ip_address" => $_SERVER["REMOTE_ADDR"],
        "status"     => "APPROVED",
        "linkback"   => 1
    );

    $success = wc_db_save_comment($comment);

    if($success){
        $comment = wc_db_get_comment($success);

        // email the comment
        if(($WC["email_comment"]=="all") ||
           ($WC["email_comment"]=="spam" && $comment["status"]=="SPAM")){

            $subject = "[".$WC["default_title"]."] Link to $post[subject]";
            $body = "There is a new link back to your post \"$post[subject]\"\n";
            $body.= wc_get_url("post", $post["post_id"], $post["uri"])."#comments\n\n";
            $body.= "URL    : $comment[url]\n";
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

        return WC_LB_SUCCESS;

    } else {

        return WC_LB_ERROR_DENIED;
    }

}

// this is a pingback, look for xml data

$xml = file_get_contents('php://input');

if(!empty($xml)){
    if(preg_match('!<methodName>\s*pingback.ping\s*</methodName>!s', $xml) &&
       preg_match_all('!<param>\s*<value>\s*<string>\s*(.+?)\s*</string>\s*</value>\s*</param>!s', $xml, $match)){

        if(count($match[1])==2){

            $sourceURI = $match[1][0];
            $targetURI = $match[1][1];

            $success = handle_linkback($sourceURI, $targetURI);

            if($success==WC_LB_SUCCESS){
                $response = '<?xml version="1.0"?>';
                $response.= '<methodResponse>';
                $response.= '<params>';
                $response.= '<param>';
                $response.= '<value><string>You are made of awesome and win! Thanks for the link!</string></value>';
                $response.= '</param>';
                $response.= '</params>';
                $response.= '</methodResponse>';
            } else {
                $response = '<?xml version="1.0"?>';
                $response.= '<methodResponse>';
                $response.= '<fault>';
                $response.= '<value>';
                $response.= '<struct>';
                $response.= '<member>';
                $response.= '<name>faultCode</name>';
                $response.= '<value><int>'.$success.'</int></value>';
                $response.= '</member>';
                $response.= '<member>';
                $response.= '<name>faultString</name>';
                $response.= '<value><string>'.$pingback_error_strings[$success].'</string></value>';
                $response.= '</member>';
                $response.= '</struct>';
                $response.= '</value>';
                $response.= '</fault>';
                $response.= '</methodResponse>';
            }
file_put_contents("/tmp/db.log", $response."\n", FILE_APPEND);

            header("Content-Type: text/xml");
            echo $response;
            exit();

        }

    }

}

if($success===false){
    header('HTTP/1.x 400 Bad Request');
    header("Status: 400 Bad Request");
}

?>
