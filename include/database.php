<?php

/**
 * This file controls all databsae access
 *
 * @author     Brian Moon <brianm@phorum.org>
 * @copyright  1997-Present WC Blog Dev Team
 *
 */

require_once dirname(__FILE__)."/config.php";
require_once dirname(__FILE__)."/WCDB.php";

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

// if init has not loaded, quit the file
if(!defined("WC")) exit("common.php not loaded");

/**
 * Generate the table names using the prefix
 */
$WC["settings_table"] = $WC["db_prefix"]."_settings";
$WC["pages_table"] = $WC["db_prefix"]."_pages";
$WC["posts_table"] = $WC["db_prefix"]."_posts";
$WC["users_table"] = $WC["db_prefix"]."_users";
$WC["comments_table"] = $WC["db_prefix"]."_comments";
$WC["tags_table"] = $WC["db_prefix"]."_tags";

/**
 * Create a new database object for these functions to use
 */
$WCDB = new WCDB($WC["db_server"], $WC["db_name"], $WC["db_user"], $WC["db_password"]);


/**
 * Gets the application settings.
 *
 * @return  array
 *
 */
function wc_db_get_settings(){

    global $WC, $WCDB;

    $sql = "select * from {$WC["settings_table"]}";

    $WCDB->query($sql);

    $settings = array();

    while($rec = $WCDB->fetch()){
        if($rec["S"]){
            $settings[$rec["name"]] = json_decode($rec["data"]);
        } else {
            $settings[$rec["name"]] = $rec["data"];
        }
    }

    return $settings;
}


/**
 * Save the application settings.
 *
 * @param   $settings Array of settings to save.
 * @return  void
 *
 */
function wc_db_save_settings($settings){

    global $WC, $WCDB;

    if(empty($settings)) return false;

    $success = false;

    $clean_arr = array();

    foreach($settings as $name=>$data){

        switch($name){
            case "session_days":
                $clean_arr[] = array("name"=>$name, "type"=>"V", "data"=>(int)$data);
                break;

            case "base_url":
            case "session_secret":
            case "session_path":
            case "session_domain":
            case "date_format_long":
            case "date_format_short":
            case "template":
            case "default_title":
            case "default_description":
            case "akismet_key":
            case "email_comment":
                $clean_arr[] = array("name"=>$name, "type"=>"V", "data"=>$WCDB->escape($data));
                break;

            case "use_rewrite":
            case "use_captcha":
            case "use_akismet":
            case "moderate_all":
            case "allow_comments":
            case "send_linkbacks":
                $clean_arr[] = array("name"=>$name, "type"=>"V", "data"=>(bool)$data);
                break;

            default:
                trigger_error("Invalid field $name sent to ".__FUNCTION__.".", E_USER_WARNING);
                continue;
        }

    }

    $success = true;

    foreach($clean_arr as $setting){

        $sql = "replace into {$WC["settings_table"]} set
                    name = '$setting[name]',
                    type = '$setting[type]',
                    data = '$setting[data]'";

        if(!$WCDB->query($sql)){
            $success = false;
        }

    }

    return $success;
}


/**
 * Checks the given cookie against the database for a user
 *
 * @param   $cookie     The cookie value to check
 * @return  mixed
 *
 */
function wc_db_check_cookie($cookie){

    global $WCDB, $WC;

    $user = array();

    if(strpos($cookie, ":")!==false){
        list($user_id, $session_id) = explode(":", $cookie);

        $user_id = $WCDB->escape($user_id);
        $session_id = $WCDB->escape($session_id);

        $sql = "select * from {$WC['users_table']} where session_id='$session_id' and user_id='$user_id'";

        $user = $WCDB->query_fetch($sql, WC_DB_FETCH_ASSOC);
    }

    return $user;

}


/**
 * Checks a user name and password
 *
 * @param   $user_name  The user name to check
 * @param   $password   The password to check
 * @return  bool
 *
 */
function wc_db_check_login($user_name, $password) {

    global $WCDB, $WC;

    $user_name = $WCDB->escape($user_name);
    $password = md5($password).sha1($password);

    $sql = "select user_id from {$WC['users_table']} where user_name='$user_name' and password='$password'";

    return (int)$WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "user_id");

}


/**
 * Saves a post to the database.
 * To save an existing post, put the post_id in the array.
 *
 * @param   $post    Array of post elements to save
 * @return  bool
 *
 */
function wc_db_save_post(&$post){

    global $WCDB, $WC;

    $clean_arr = array();
    $result = false;

    if(isset($post["post_id"]) && empty($post["post_id"])) unset($post["post_id"]);

    foreach($post as $field=>$value){

        switch($field){
            case "post_id":
            case "user_id":
            case "allow_comments":
                $clean_arr[$field] = (int)$value;
                break;

            case "tags":
                break;

            case "subject":
            case "body":
            case "post_date":
            case "uri":
                $clean_arr[$field] = $WCDB->escape($value);
                break;

            default:
                trigger_error("Invalid field $field sent to ".__FUNCTION__.".", E_USER_WARNING);
                continue;
        }

    }

    if(!empty($post["post_id"])){

        // build an update
        $sql = "update {$WC['posts_table']} set ";
        foreach($clean_arr as $field=>$value){
            if($field!="post_id"){
                $sql.= "$field = '$value',";
            }
        }
        $sql = substr($sql, 0, -1); // trim the last comma
        $sql.= " where post_id=".$post["post_id"];

        $result = $WCDB->query($sql);

        if($result && isset($post["tags"])){

            $sql = "delete from {$WC['tags_table']} where post_id=".$post["post_id"];
            $WCDB->query($sql);

            $tag_arr = explode(",", $post["tags"]);
            foreach($tag_arr as $tag){
                $tag = trim($tag);
                if(empty($tag)) continue;
                $sql = "insert into {$WC['tags_table']} values (".$post["post_id"].", '".$WCDB->escape($tag)."')";
                $WCDB->query($sql);
            }
        }

    } else {

        // build an insert

        if(isset($post["post_id"])) unset($post["post_id"]);

        if(!isset($post["body"])) $post["body"] = "";

        foreach($clean_arr as $field=>$value){
            $fields.="$field,";
            $values.="'$value',";
        }
        $fields = substr($fields, 0, -1); // trim the last comma
        $values = substr($values, 0, -1); // trim the last comma
        $sql = "insert into {$WC['posts_table']} ($fields) values ($values)";

        $post_id = $WCDB->query_fetch($sql, WC_DB_FETCH_INSERT_ID);
        $post["post_id"] = $post_id;

        if($post_id && isset($post["tags"])){

            $result = $post_id;

            $tag_arr = explode(",", $post["tags"]);
            foreach($tag_arr as $tag){
                $tag = trim($tag);
                if(empty($tag)) continue;
                $sql = "insert into {$WC['tags_table']} values (".$post_id.", '".$WCDB->escape($tag)."')";
                $WCDB->query($sql);
            }
        }

    }

    return (bool)$result;
}


/**
 * Fetch a single post from the database
 *
 * @param   $post_id    The id or uri of the post to fetch
 * @return  array
 *
 */
function wc_db_get_post($identifier) {

    global $WCDB, $WC;

    if(is_numeric($identifier)){
        $where = "post_id = $identifier";
    } else {
        $where = "uri = '".$WCDB->escape($identifier)."'";
    }

    $sql = "select {$WC['posts_table']}.*, {$WC['users_table']}.user_name from {$WC['posts_table']} inner join {$WC['users_table']} using (user_id) where $where";

    $post = $WCDB->query_fetch($sql, WC_DB_FETCH_ASSOC);

    if(!empty($post)){

        if(empty($post_id)) $post_id = $post["post_id"];

        $sql = "select tag from {$WC['tags_table']} where post_id=".$post_id;

        $WCDB->query($sql);

        $post["tags"] = array();

        while($row = $WCDB->fetch()){
            $post["tags"][] = $row["tag"];
        }

        $post["tags_text"] = implode(", ", $post["tags"]);

        // get comment count
        $sql = "select count(*) as count from {$WC['comments_table']} where post_id=$post_id and status='APPROVED'";

        $post["comment_count"] = (int)$WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "count");

    }

    return $post;

}

/**
 * Function description
 * The funtion returns an array where the first element
 * is the posts and the second is the total.
 *
 * @param   $var    desctription
 * @return  mixed
 *
 */
function wc_db_get_post_list($start=false, $limit=false, $bodies=false, $filter="", $tag="", $post_ids=false) {

    global $WCDB, $WC;

    $bodies = (bool)$bodies;

    if(!$bodies){
        $fields = "post_id, subject, post_date, user_id";
    } else {
        $fields = "*";
    }


    $sql = "select SQL_CALC_FOUND_ROWS $fields from {$WC['posts_table']} ";

    $where = array();

    if($tag){
        $tag = $WCDB->escape((string)$tag);

        $sql.= "inner join {$WC['tags_table']} on
                    {$WC['posts_table']}.post_id={$WC['tags_table']}.post_id and
                    {$WC['tags_table']}.tag='$tag' ";

    }

    if($filter) {

        $words = preg_split('!\s+!', $filter);

        $match = $WCDB->escape("+".implode(" +", $words));

        $where[] = "match (subject, body) against ('$match' in boolean mode)";
    }

    if($post_ids!=false && is_array($post_ids)){
        $WCDB->escape($post_ids, "int");
        if(count($post_ids)){
            $where[] = "post_id in (".implode(",", $post_ids).")";
        }
    }

    if(count($where)){
        $sql.= " where ".implode(" and ", $where);
    }

    $sql.= "order by post_date desc";

    if(is_numeric($start) && is_numeric($limit)){
        $sql.= " limit $start, $limit";
    }

    $WCDB->query($sql);

    while($row = $WCDB->fetch()){

        // seed comment count and tags
        $row["comment_count"] = 0;
        $row["tags"] = array();

        $posts[$row["post_id"]] = $row;

        $user_ids[$row["post_id"]] = $row["user_id"];
    }

    $sql = "select found_rows() as total";
    $total = $WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "total");

    if(!empty($posts)){

        // get users
        $sql = "select user_id, user_name from {$WC['users_table']} where user_id in (".implode(",", $user_ids).")";
        $WCDB->query($sql);

        while($row = $WCDB->fetch()){
            $usernames[$row["user_id"]] = $row["user_name"];
        }

        foreach($user_ids as $post_id=>$user_id){
            $posts[$post_id]["user_name"] = $usernames[$user_id];
        }

        // get tags
        $sql = "select post_id, tag from {$WC['tags_table']} where post_id in (".implode(",", array_keys($posts)).")";

        $WCDB->query($sql);


        while($row = $WCDB->fetch()){
            $posts[$row["post_id"]]["tags"][] = $row["tag"];
        }


        // get comment count
        $sql = "select post_id, count(*) as count from {$WC['comments_table']} where post_id in (".implode(",", array_keys($posts)).") and status='APPROVED' group by post_id";

        $WCDB->query($sql);

        while($row = $WCDB->fetch()){
            if(!empty($row["post_id"])){
                $posts[$row["post_id"]]["comment_count"] = $row["count"];
            }
        }

    }

    return array($posts, $total);

}


/**
 * Deletes a post
 *
 * @param   $post_id    The id of the post to be deleted.
 * @return  bool
 *
 */
function wc_db_delete_post($post_id) {

    global $WCDB, $WC;

    $post_id = (int)$post_id;

    $sql = "delete from {$WC['posts_table']} where post_id=$post_id";
    $res = $WCDB->query($sql);

    if($res != false){
        $sql = "delete from {$WC['tags_table']} where post_id=$post_id";
        $WCDB->query($sql);
    }

    return (bool)$res;

}


/**
 * Gets a list of users from the database
 * The funtion returns an array where the first element
 * is the users and the second is the total.
 *
 * @param   $start  The position in the list to start from
 * @param   $limit  The maximum number of users to return
 * @param   $filter Options filter value to run against the user list
 * @return  mixed
 *
 */
function wc_db_get_user_list($start, $limit, $filter="") {

    global $WCDB, $WC;

    $start = (int)$start;
    $limit = (int)$limit;
    $filter = (string)$filter;

    $sql = "select SQL_CALC_FOUND_ROWS * from {$WC['users_table']} ";

    if($filter) {
        $words = preg_split('!\s+!', $filter);

        $match = $WCDB->escape("+".implode(" +", $words));

        $sql.= "where
                    match (user_name, first_name, last_name, email)
                    against ('$match' in boolean mode) ";
    }

    $sql.= " order by user_name desc limit $start, $limit";

    $WCDB->query($sql);

    while($row = $WCDB->fetch()){

        $users[$row["user_id"]] = $row;

    }

    $sql = "select found_rows() as total";
    $total = $WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "total");

    return array($users, $total);

}

/**
 * Fetch a single user from the database
 *
 * @param   $user_id    The id of the user to fetch
 * @return  array
 *
 */
function wc_db_get_user($user_id) {

    global $WCDB, $WC;

    $user_id = (int)$user_id;

    $sql = "select * from {$WC['users_table']} where user_id=".$user_id;

    $user = $WCDB->query_fetch($sql, WC_DB_FETCH_ASSOC);

    return $user;

}


/**
 * Saves a user to the database.
 * To save an existing user, put the user_id in the array.
 *
 * @param   $user    Array of user elements to save
 * @return  bool
 *
 */
function wc_db_save_user($user){

    global $WCDB, $WC;

    $clean_arr = array();
    $result = false;

    if(isset($user["user_id"]) && empty($user["user_id"])) unset($user["user_id"]);

    foreach($user as $field=>$value){

        switch($field){

            case "user_id":
                $clean_arr[$field] = (int)$value;
                break;

            case "user_name":
            case "first_name":
            case "last_name":
            case "email":
            case "about":
            case "session_id":
                $clean_arr[$field] = $WCDB->escape($value);
                break;

            case "password":
                $value = md5($value).sha1($value);
                $clean_arr[$field] = $WCDB->escape($value);
                break;

            default:
                trigger_error("Invalid field $field sent to ".__FUNCTION__.".", E_USER_WARNING);
                break;
        }

    }

    if(!empty($user["user_id"])){

        // build an update
        $sql = "update {$WC['users_table']} set ";
        foreach($clean_arr as $field=>$value){
            if($field!="user_id"){
                $sql.= "$field = '$value',";
            }
        }
        $sql = substr($sql, 0, -1); // trim the last comma
        $sql.= " where user_id=".$user["user_id"];

        $result = $WCDB->query($sql);

    } else {

        // build an insert

        if(isset($user["user_id"])) unset($user["user_id"]);

        if(!isset($user["body"])) $user["body"] = "";

        foreach($clean_arr as $field=>$value){
            $fields.="$field,";
            $values.="'$value',";
        }
        $fields = substr($fields, 0, -1); // trim the last comma
        $values = substr($values, 0, -1); // trim the last comma
        $sql = "insert into {$WC['users_table']} ($fields) values ($values)";

        $result = $WCDB->query_fetch($sql, WC_DB_FETCH_INSERT_ID);

    }

    return (bool)$result;
}


/**
 * Deletes a user
 *
 * @param   $user_id    The id of the user to be deleted.
 * @return  bool
 *
 */
function wc_db_delete_user($user_id) {

    global $WCDB, $WC;

    $user_id = (int)$user_id;

    $sql = "delete from {$WC['users_table']} where user_id=$user_id";
    $res = $WCDB->query($sql);

    return (bool)$res;

}


/**
 * Gets a tag list from the database
 * Returned in order of most posts
 *
 * @param   $limit  Maximum number of tags to get
 * @return  array
 *
 */
function wc_db_get_tags($limit=0) {

    global $WCDB, $WC;

    $tags = array();

    $limit = (int)$limit;

    $sql = "select tag, count(*) as post_count from {$WC['tags_table']} group by tag order by post_count desc";
    if($limit) $sql.= " limit $limit";

    $WCDB->query($sql);

    while($rec = $WCDB->fetch()){
        $tags[] = $rec;
    }

    return $tags;
}


/**
 * Posts a comment to the database
 * To save an existing comment, put the comment_id in the array.
 *
 * @param   $comment    Array contianing comment data
 * @return  mixed
 *
 */
function wc_db_save_comment($comment) {

    global $WCDB, $WC;

    // these are required
    if(!isset($comment["comment_id"])){
        if(empty($comment["comment"])) return false;
        if(empty($comment["post_id"])) return false;
        if(empty($comment["name"])) return false;

        if(empty($comment["comment_date"])) $comment["comment_date"] = date("Y-m-d H:i:s");
    }

    foreach($comment as $field=>$value){

        switch($field){

            case "post_id":
            case "comment_id":
                $clean_arr[$field] = (int)$value;
                break;

            case "name":
            case "email":
            case "url":
            case "ip_address":
            case "comment":
            case "comment_date":
                $clean_arr[$field] = $WCDB->escape($value);
                break;

            case "status":
                if($value!='APPROVED' && $value!='UNAPPROVED' && $value!='SPAM'){
                    $value = 'UNAPPROVED';
                }
                $clean_arr[$field] = $value;
                break;

            default:
                trigger_error("Invalid field $field sent to ".__FUNCTION__.".", E_USER_WARNING);
                break;
        }

    }

    if(isset($clean_arr["comment_id"])){

        $fields = "";

        foreach($clean_arr as $field=>$value){
            if($field!="comment_id"){
                if(is_numeric($value)){
                    $fields.="$field = $value,";
                } else {
                    $fields.="$field = '$value',";
                }
            }
        }
        $fields = substr($fields, 0, -1); // trim the last comma

        $sql = "update {$WC["comments_table"]} set $fields where comment_id=".$clean_arr["comment_id"];
        $success = $WCDB->query($sql);

    } else {

        $fields = "";
        $values = "";
        foreach($clean_arr as $field=>$value){
            $fields.="$field,";
            $values.="'$value',";
        }
        $fields = substr($fields, 0, -1); // trim the last comma
        $values = substr($values, 0, -1); // trim the last comma
        $sql = "insert into {$WC["comments_table"]} ($fields) values ($values)";

        $comment_id = $WCDB->query_fetch($sql, WC_DB_FETCH_INSERT_ID);

    }

    return $comment_id;
}


/**
 * Deletes a comment from the database
 *
 * @param   $comment_id    Comment's id to delete
 * @return  bool
 *
 */
function wc_db_delete_comment($comment_id) {

    global $WCDB, $WC;

    // these are required
    $comment_id = (int)$comment_id;
    if(empty($comment_id)) return false;

    $sql = "delete from {$WC["comments_table"]} where comment_id=$comment_id";
    return (bool)$WCDB->query($sql);

}


function wc_db_get_comment($comment_id) {

    global $WCDB, $WC;

    $comment_id = (int)$comment_id;

    $sql = "select * from {$WC["comments_table"]} where comment_id=$comment_id";

    $comment = $WCDB->query_fetch($sql, WC_DB_FETCH_ASSOC);

    return $comment;

}


function wc_db_get_comments($post_id=false, $start=false, $limit=false, $filter=false, $status=false) {

    global $WCDB, $WC;

    $sql = "select SQL_CALC_FOUND_ROWS * from {$WC["comments_table"]} ";

    $order_by = "comment_id desc";

    $where = array();
    if(is_numeric($post_id)){
        $where[] = "post_id=$post_id";
        $order_by = "comment_date";
    }

    if($filter) {

        $words = preg_split('!\s+!', $filter);

        $match = $WCDB->escape("+".implode(" +", $words));

        $where[] = "match (name, comment, email) against ('$match' in boolean mode)";
    }

    if($status!==false){
        $where[] = "status='".$WCDB->escape($status)."'";
    }

    if(!empty($where)){
        $sql.= " where ".implode(" and ", $where);
    }

    $sql.= " order by $order_by";

    if(is_numeric($start) && is_numeric($limit)){
        $sql.= " limit $start, $limit";
    }

    $comments = $WCDB->query_fetch($sql, WC_DB_FETCH_ALL_ASSOC);

    $sql = "select found_rows() as total";
    $total = (int)$WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "total");

    if(empty($comments)) $comments = array();

    return array($comments, $total);
}


/**
 * Fetch a single page from the database
 *
 * @param   $identifier    The id or uri of the page to fetch
 * @return  array
 *
 */
function wc_db_get_page($identifier) {

    global $WCDB, $WC;

    if(is_numeric($identifier)){
        $where = "page_id = $identifier";
    } else {
        $where = "uri = '".$WCDB->escape($identifier)."'";
    }

    $sql = "select {$WC['pages_table']}.* from {$WC['pages_table']} where $where";

    $page = $WCDB->query_fetch($sql, WC_DB_FETCH_ASSOC);

    return $page;

}


/**
 * Saves a page to the database.
 * To save an existing page, put the page_id in the array.
 *
 * @param   $page    Array of page elements to save
 * @return  bool
 *
 */
function wc_db_save_page($page){

    global $WCDB, $WC;

    $clean_arr = array();
    $result = false;

    if(isset($page["page_id"]) && empty($page["page_id"])) unset($page["page_id"]);

    foreach($page as $field=>$value){

        switch($field){
            case "page_id":
                $clean_arr[$field] = (int)$value;
                break;

            case "nav_label":
            case "title":
            case "body":
            case "uri":
                $clean_arr[$field] = $WCDB->escape($value);
                break;

            default:
                trigger_error("Invalid field $field sent to ".__FUNCTION__.".", E_USER_WARNING);
                continue;
        }

    }

    if(!empty($page["page_id"])){

        // build an update
        $sql = "update {$WC['pages_table']} set ";
        foreach($clean_arr as $field=>$value){
            if($field!="page_id"){
                $sql.= "$field = '$value',";
            }
        }
        $sql = substr($sql, 0, -1); // trim the last comma
        $sql.= " where page_id=".$page["page_id"];

        $result = $WCDB->query($sql);

    } else {

        // build an insert

        if(isset($page["page_id"])) unset($page["page_id"]);

        if(!isset($page["body"])) $page["body"] = "";

        foreach($clean_arr as $field=>$value){
            $fields.="$field,";
            $values.="'$value',";
        }
        $fields = substr($fields, 0, -1); // trim the last comma
        $values = substr($values, 0, -1); // trim the last comma
        $sql = "insert into {$WC['pages_table']} ($fields) values ($values)";

        $result = $WCDB->query_fetch($sql, WC_DB_FETCH_INSERT_ID);

    }

    return (bool)$result;
}


/**
 * Deletes a page
 *
 * @param   $page_id    The id of the page to be deleted.
 * @return  bool
 *
 */
function wc_db_delete_page($page_id) {

    global $WCDB, $WC;

    $page_id = (int)$page_id;

    $sql = "delete from {$WC['pages_table']} where page_id=$page_id";
    $res = $WCDB->query($sql);

    return (bool)$res;

}


/**
 * Function description
 * The funtion returns an array where the first element
 * is the pages and the second is the total.
 *
 * @param   $var    desctription
 * @return  mixed
 *
 */
function wc_db_get_page_list($start=false, $limit=false, $bodies=false, $filter="", $tag="", $page_ids=false) {

    global $WCDB, $WC;

    $bodies = (bool)$bodies;

    if(!$bodies){
        $fields = "page_id, title";
    } else {
        $fields = "*";
    }


    $sql = "select SQL_CALC_FOUND_ROWS $fields from {$WC['pages_table']} ";

    $where = array();

    if($filter) {

        $words = preg_split('!\s+!', $filter);

        $match = $WCDB->escape("+".implode(" +", $words));

        $where[] = "match (title, body) against ('$match' in boolean mode)";
    }

    if($page_ids!=false && is_array($page_ids)){
        $WCDB->escape($page_ids, "int");
        if(count($page_ids)){
            $where[] = "page_id in (".implode(",", $page_ids).")";
        }
    }

    if(count($where)){
        $sql.= " where ".implode(" and ", $where);
    }

    $sql.= "order by title";

    if(is_numeric($start) && is_numeric($limit)){
        $sql.= " limit $start, $limit";
    }

    $WCDB->query($sql);

    while($row = $WCDB->fetch()){

        $pages[$row["page_id"]] = $row;

    }

    $sql = "select found_rows() as total";
    $total = $WCDB->query_fetch($sql, WC_DB_FETCH_VALUE, "total");

    return array($pages, $total);

}


/**
 * Gets a page list from the database
 * Returned in order of nav_label
 *
 * @param   $limit  Maximum number of pages to get
 * @return  array
 *
 */
function wc_db_get_nav_pages($limit=0) {

    global $WCDB, $WC;

    $pages = array();

    $limit = (int)$limit;

    $sql = "select page_id, nav_label, uri from {$WC['pages_table']} order by nav_label";
    if($limit) $sql.= " limit $limit";

    $WCDB->query($sql);

    while($rec = $WCDB->fetch()){
        $pages[] = $rec;
    }

    return $pages;
}

?>
