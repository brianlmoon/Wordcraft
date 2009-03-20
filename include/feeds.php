<?php

/**
 * Contains functions to generate feeds of different types
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */


/**
 * Creates an RSS feed
 *
 * @param   array   $posts              Array of posts to be put into the feed
 * @param   array   $feed_url           The home URL for the site that created the feed
 * @param   array   $feed_title         The title of this feed
 * @param   array   $feed_description   The description of this feed
 * @return  mixed
 *
 */
function wc_feed_make_rss($posts, $feed_url, $feed_title, $feed_description) {

    global $WC;

    $buffer = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $buffer.= "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
    $buffer.= "    <channel>\n";
    $buffer.= "        <title>".htmlspecialchars($feed_title, ENT_COMPAT, "UTF-8")."</title>\n";
    $buffer.= "        <description>".htmlspecialchars($feed_description, ENT_COMPAT, "UTF-8")."</description>\n";
    $buffer.= "        <link>".htmlspecialchars($feed_url, ENT_COMPAT, "UTF-8")."</link>\n";
    $buffer.= "        <lastBuildDate>".htmlspecialchars(date("r"), ENT_COMPAT, "UTF-8")."</lastBuildDate>\n";
    $buffer.= "        <generator>".htmlspecialchars("Wordcraft ".WC, ENT_COMPAT, "UTF-8")."</generator>\n";

    foreach($posts as $post) {

        $title = strip_tags($post["subject"]);
        $date = date("r", $post["post_date"]);
        $body = strtr($post['body'], "\001\002\003\004\005\006\007\010\013\014\016\017\020\021\022\023\024\025\026\027\030\031\032\033\034\035\036\037", "????????????????????????????");

        $buffer.= "        <item>\n";
        $buffer.= "            <guid>".htmlspecialchars($post["url"], ENT_COMPAT, "UTF-8")."</guid>\n";
        $buffer.= "            <title>".htmlspecialchars($title, ENT_COMPAT, "UTF-8")."</title>\n";
        $buffer.= "            <link>".htmlspecialchars($post["url"], ENT_COMPAT, "UTF-8")."</link>\n";
        $buffer.= "            <description><![CDATA[$body]]></description>\n";
        $buffer.= "            <dc:creator>".htmlspecialchars($post['user_name'], ENT_COMPAT, "UTF-8")."</dc:creator>\n";
        $buffer.= "            <pubDate>".htmlspecialchars($date, ENT_COMPAT, "UTF-8")."</pubDate>\n";

        foreach($post["tags"] as $tag){
            $buffer.= "            <category>".htmlspecialchars($tag["tag"], ENT_COMPAT, "UTF-8")."</category>\n";
        }

        $buffer.= "        </item>\n";
    }

    $buffer.= "    </channel>\n";
    $buffer.= "</rss>\n";

    return $buffer;
}


/**
 * Creates an ATOM feed
 *
 * @param   array   $posts              Array of posts to be put into the feed
 * @param   array   $feed_url           The home URL for the site that created the feed
 * @param   array   $feed_title         The title of this feed
 * @param   array   $feed_description   The description of this feed
 * @return  mixed
 *
 */
function wc_feed_make_atom($posts, $feed_url, $feed_title, $feed_description) {

    global $WC;

    $buffer = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $buffer.= "<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";
    $buffer.= "    <title>".htmlspecialchars($feed_title, ENT_COMPAT, "UTF-8")."</title>\n";
    $buffer.= "    <subtitle>".htmlspecialchars($feed_description, ENT_COMPAT, "UTF-8")."</subtitle>\n";
    $buffer.= "    <link rel=\"self\" href=\"".htmlspecialchars($feed_url, ENT_COMPAT, "UTF-8")."\" />\n";
    $buffer.= "    <id>".htmlspecialchars($feed_url, ENT_COMPAT, "UTF-8")."</id>\n";
    $buffer.= "    <updated>".htmlspecialchars(date("c"), ENT_COMPAT, "UTF-8")."</updated>\n";
    $buffer.= "    <generator>".htmlspecialchars("Wordcraft ".WC, ENT_COMPAT, "UTF-8")."</generator>\n";

    foreach($posts as $post) {

        $title = strip_tags($post["subject"]);
        $body = strtr($post['body'], "\001\002\003\004\005\006\007\010\013\014\016\017\020\021\022\023\024\025\026\027\030\031\032\033\034\035\036\037", "????????????????????????????");

        $buffer.= "    <entry>\n";
        $buffer.= "        <title type=\"html\">$title</title>\n";
        $buffer.= "        <link href=\"".htmlspecialchars($post["url"], ENT_COMPAT, "UTF-8")."\" />\n";
        $buffer.= "        <published>".date("c", $post["post_date"])."</published>\n";
        $buffer.= "        <updated>".date("c", $post["post_date"])."</updated>\n";
        $buffer.= "        <id>".htmlspecialchars($post["url"], ENT_COMPAT, "UTF-8")."</id>\n";
        $buffer.= "        <author>\n";
        $buffer.= "            <name>".htmlspecialchars($post["user_name"], ENT_COMPAT, "UTF-8")."</name>\n";
        $buffer.= "        </author>\n";
        $buffer.= "        <summary type=\"html\"><![CDATA[$body]]></summary>\n";

        foreach($post["tags"] as $tag){
            $buffer.= "        <category term=\"".htmlspecialchars($tag["tag"], ENT_COMPAT, "UTF-8")."\" />\n";
        }

        $buffer.= "    </entry>\n";
    }

    $buffer.= "</feed>\n";

    return $buffer;

}


/**
 * Creates a JSON feed
 *
 * @param   array   $posts              Array of posts to be put into the feed
 * @param   array   $feed_url           The home URL for the site that created the feed
 * @param   array   $feed_title         The title of this feed
 * @param   array   $feed_description   The description of this feed
 * @return  mixed
 *
 */
function wc_feed_make_json($posts, $feed_url, $feed_title, $feed_description) {

    global $WC;

    $array = array(
        "title" => $feed_title,
        "description" => $feed_description,
        "url" => $feed_url,
        "updated" => date("r"),
        "posts" => array()
    );

    foreach($posts as $post) {

        $array["posts"][] = array(
            "title" => $post["subject"],
            "url" => $post["url"],
            "tags" => $post["tags"],
            "published" => date("r", $post["post_date"]),
            "author" => $post["user_name"],
            "post" => $post['body']
        );
    }

    return json_encode($array);

}
?>
