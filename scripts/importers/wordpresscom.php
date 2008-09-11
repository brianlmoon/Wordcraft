<?php

include dirname(dirname(dirname(__FILE__)))."/include/common.php";

$opts = getopt("hf:");

if(isset($opts["h"])){
    usage();
}

if(empty($opts["f"])){
    usage();
}

$input_file = $opts["f"];

if(!file_exists($input_file)){
    echo "File not found $input_file\n";
    usage();
}

$xml = @simplexml_load_file($input_file, 'SimpleXMLElement', LIBXML_NOCDATA);;

if(!$xml){
    echo "File $input_file does not appear to be valid Wordpress.com XML\n";
    usage();
}

$users = wc_db_get_user_list(0, 1000);

foreach($users as $user){
    if(empty($first_user_id)) $first_user_id = $user["user_id"];
    $names[$user["first_name"]." ".$user["last_name"]] = $user["user_id"];
}


foreach($xml->channel->item as $item){
    $content = $item->children("http://purl.org/rss/1.0/modules/content/");
    $dc      = $item->children("http://purl.org/dc/elements/1.1/");
    $wp      = $item->children("http://wordpress.org/export/1.0/");

    // skip auto save posts
    if($wp->status=="inherit") continue;

    $user_id = (isset($names[(string)$dc->creator])) ? (string)$names[$dc->creator] : $first_user_id;

    preg_match('!https*://[^/]+/(.+)$!', (string)$item->link, $match);
    $uri = $match[1];

    $published = ($wp->status=="publish");

    $allow_comments = ($wp->comment_status=="open");

    $tags = array();

    foreach($item->category as $cat) {
        $tags[] = (string)$cat;
    }

    $tags = implode(",", array_unique($tags));

    if($wp->post_type=="page"){

        $page_array = array(
            "nav_label" => (string)$item->title,
            "title"     => (string)$item->title,
            "body"      => nl2br((string)$content->encoded),
            "uri"       => $uri,
        );

        $success = wc_db_save_page($page_array);

    } else {

        $post_array = array(
            "user_id"        => $user_id,
            "subject"        => (string)$item->title,
            "body"           => nl2br((string)$content->encoded),
            "tags"           => $tags,
            "allow_comments" => $allow_comments,
            "published"      => $published,
            "uri"            => $uri,
            "post_date"      => date("Y-m-d H:i:s", strtotime((string)$item->pubDate))
        );


        $success = wc_db_save_post($post_array);

        if($success){

            $post_id = $post_array["post_id"];

            if(count($wp->comment)){

                $comments = $wp->comment;

                if(!isset($comments[0])){
                    $comments = array($comments);
                }

                foreach($comments as $wpcomment){

                    if($wpcomment->comment_approved=="spam") continue;



                    $status = ($wpcomment->comment_approved=="1") ? "APPROVED" : "UNAPPROVED";

                    $linkback = ($wpcomment->comment_type=="pingback");

                    $comment = array(
                        "post_id"      => $post_id,
                        "name"         => (string)$wpcomment->comment_author,
                        "email"        => (string)$wpcomment->comment_author_email,
                        "url"          => (string)$wpcomment->comment_author_url,
                        "comment"      => nl2br((string)$wpcomment->comment_content),
                        "ip_address"   => (string)$wpcomment->comment_author_IP,
                        "status"       => $status,
                        "comment_date" => date("Y-m-d H:i:s", strtotime((string)$wpcomment->comment_author_IP." +0000")),
                        "linkback"     => $linkback
                    );

                    $success = wc_db_save_comment($comment);


                }

            }
        }
    }
}

echo wordwrap("All done.  Just so you know, Wordpress.com has some odd stuff in some of their posts.  You will want to check your data.\n", 80);

return;

function usage() {
    echo "Usage: php ".basename(__FILE__)." -f [XML FILE]\n";
    exit();
}
?>
