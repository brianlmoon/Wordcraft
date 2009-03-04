<?php

/**
 * Functions for formatting the data of various things in Wordcraft
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

// if init has not loaded, quit the file
if(!defined("WC")) return;

include_once dirname(__FILE__)."/url.php";


/**
 * Formats the data of a post
 *
 * @param   array   $post   Either a single post or an array of posts
 * @param   bool    $multi  If passing in more than one post, set to true
 * @return  mixed
 *
 */
function wc_format_post(&$post, $multi=false) {

    global $WC;

    if(!$multi){
        $post = array($post);
    }
    if($post == null){
        print_var(debug_print_backtrace());
    }
    foreach($post as &$p){

        $p["post_date"] = strftime($WC["date_format_long"], strtotime($p["post_date"]));

        $p["subject"] = htmlspecialchars($p["subject"], ENT_COMPAT, "UTF-8");

        $p["url"] = wc_get_url("post", $p["post_id"], $p["uri"]);

        if(!empty($p["tags"])){
            $tmp_tags = $p["tags"];
            unset($p["tags"]);
            foreach($tmp_tags as $tag){
                $p["tags"][] = array(
                    "tag" => $tag,
                    "url" => wc_get_url("tag", $tag)
                );
            }
        }
    }

    if(!empty($WC["hooks"]["format_post"])){
        $post = wc_hook("format_post", $post);
    }

    if(!$multi){
        $post = array_shift($post);
    }

}


/**
 * Formats the data of a comment
 *
 * @param   array   $comments   A single comment or an array of comments
 * @param   bool    $multi  If passing in more than one post, set to true
 * @return  mixed
 *
 */
function wc_format_comment(&$comment, $multi=false) {

    global $WC;

    if(!$multi){
        $comment = array($comment);
    }

    foreach($comment as &$c){

        $c["comment"] = htmlspecialchars($c["comment"], ENT_COMPAT, "UTF-8");

        // testing out support for perserving leading space
        if(preg_match_all('!\n +!', $c["comment"], $matches)){
            $searches = array_unique($matches[0]);
            foreach($searches as $search){
                $repl = str_replace(" ", "&nbsp;", $search);
                $c["comment"] = str_replace($search, $repl, $c["comment"]);
            }
        }

        $c["comment"] = nl2br($c["comment"]);

        $c["comment"] = preg_replace("/((http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]+)/i", "<a href=\"$1\" rel=\"nofollow\">$1</a>", $c["comment"]);

        $c["name"] = htmlspecialchars($c["name"], ENT_COMPAT, "UTF-8");
        $c["url"] = htmlspecialchars($c["url"], ENT_COMPAT, "UTF-8");
        $c["email"] = htmlspecialchars($c["email"], ENT_COMPAT, "UTF-8");
        $c["status"] = ucfirst(strtolower($c["status"]));

        $c["gravatar"] = "http://www.gravatar.com/avatar/".md5(strtolower(trim($c["email"]))).".jpg?r=pg&d=".urlencode($WC["base_url"]."/resources/transparent.png")."&s=75";
    }

    if(!empty($WC["hooks"]["format_comment"])){
        $comment = wc_hook("format_comment", $comment);
    }

    if(!$multi){
        $comment = array_shift($comment);
    }

}

?>
