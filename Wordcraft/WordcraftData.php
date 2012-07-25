<?php

namespace Wordcraft;

error_reporting(-1);

if(!defined("__DIR__")){
    define("__DIR__", dirname(__FILE__));
}

class WordcraftData {

    protected $properties;

    protected $original;

    public function __get($var) {
        $value = null;
        if(!isset($this->data_types[$var])){
            $trace = debug_backtrace();
            trigger_error(
            'Undefined property via __get(): ' . $var .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
            return;
        }
        if(isset($this->properties[$var])){
            $value = $this->properties[$var];
        }
        return $value;
    }

    public function __set($var, $value) {
        if(!isset($this->data_types[$var])){
            $trace = debug_backtrace();
            trigger_error(
            'Undefined property via __set(): ' . $var .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
            return;
        }

        if(is_array($this->data_types[$var])){
            if(isset($this->data_types[$var][0])){
                $filter = $this->data_types[$var][0];
                $flags = $this->data_types[$var][1];
            } else {
                $filter = $this->data_types[$var]["filter"];
                $flags = $this->data_types[$var];
                unset($flags["filter"]);
            }
        } else {
            $filter = $this->data_types[$var];
            $flags = null;
        }

        $val = filter_var($value, $filter, $flags);

        if(is_null($val)){
            $trace = debug_backtrace();
            trigger_error(
            "Invalid value for property $var via __set(): $var".
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
            return;
        }

        $this->properties[$var] = $val;
    }

    public static function validate_time($time) {
        $int_time = $time + 0;
        if("$int_time" != "$time"){
            $int_time = strtotime($time);
        }
        return $int_time;
    }

}

class Wordcraft extends WordcraftData {

    public $db;

    public $tables;

    public $hooks;

    protected $data_types = array(
        "akismet_key" => FILTER_SANITIZE_STRING,
        "allow_comments" => FILTER_VALIDATE_BOOLEAN,
        "base_url" => FILTER_VALIDATE_URL,
        "date_format_long" => FILTER_SANITIZE_STRING,
        "date_format_short" => FILTER_SANITIZE_STRING,
        "db_version" => FILTER_SANITIZE_NUMBER_INT,
        "default_description" => FILTER_UNSAFE_RAW,
        "default_title" => FILTER_UNSAFE_RAW,
        "email_comment" => FILTER_VALIDATE_BOOLEAN,
        "moderate_all" => FILTER_VALIDATE_BOOLEAN,
        "send_linkbacks" => FILTER_VALIDATE_BOOLEAN,
        "session_days" => FILTER_SANITIZE_NUMBER_INT,
        "session_domain" => FILTER_SANITIZE_STRING,
        "session_path" => FILTER_SANITIZE_STRING,
        "session_secret" => FILTER_SANITIZE_STRING,
        "template" => FILTER_SANITIZE_STRING,
        "use_akismet" => FILTER_VALIDATE_BOOLEAN,
        "use_captcha" => FILTER_VALIDATE_BOOLEAN,
        "use_rewrite" => FILTER_VALIDATE_BOOLEAN,
        "use_spam_score" => FILTER_VALIDATE_BOOLEAN,
    );

    public function __construct() {

        $ini = __DIR__."/config.ini";
        $config = parse_ini_file ( $ini , true ) ;

        $dsn = "{$config['database']['driver']}:host={$config['database']['host']};dbname={$config['database']['name']}";

        $this->db = new PDO($dsn, $config['database']["user"], $config['database']["password"]);

        $this->build_table_names($config["database"]["prefix"]);

        $sql = "select * from {$this->tables['settings']}";
        $sth = $this->db->prepare($sql);
        $sth->execute();

        while($row = $sth->fetch(PDO::FETCH_ASSOC)){
            $prop =$row["name"];
            if($row["type"] == "V"){
                $this->$prop = $row["data"];
            } else {
                $this->$prop = unserialize($row["data"]);
            }
        }

    }

    public static function init() {
        static $singleton;
        if(is_null($singleton)){
            $singleton = new Wordcraft();
        }
        return $singleton;
    }

    private function build_table_names($prefix) {
        $this->tables["settings"] = $prefix."_settings";
        $this->tables["pages"] = $prefix."_pages";
        $this->tables["posts"] = $prefix."_posts";
        $this->tables["users"] = $prefix."_users";
        $this->tables["comments"] = $prefix."_comments";
        $this->tables["tags"] = $prefix."_tags";
        $this->tables["uri_lookup"] = $prefix."_uri_lookup";
    }

    public function lookup_uri($uri) {

    }

    protected function error() {
        $wc = Wordcraft::init();
        $err_info = $wc->db->errorInfo();
        return $err_info[2];
    }

    public function plugin($hook, &$param1=null, &$param2=null, &$param3=null, &$param4=null, &$param5=null) {

        foreach($this->hooks[$hook] as $h){
            $func = $h["function"];
            $func($hook, $param1, $param2, $param3, $param4, $param5);
        }

    }

}

class WordcraftUser extends WordcraftData {

    private $wc;

    protected $data_types = array(
        "user_id" => FILTER_SANITIZE_NUMBER_INT,
        "user_name" => FILTER_SANITIZE_STRING,
        "first_name" => FILTER_SANITIZE_STRING,
        "last_name" => FILTER_SANITIZE_STRING,
        "email" => FILTER_VALIDATE_EMAIL,
        "about" => FILTER_UNSAFE_RAW,
        "password" => FILTER_UNSAFE_RAW
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}

class WordcraftPost extends WordcraftData {

    private $wc;

    protected $data_types = array(
        "post_id" => FILTER_SANITIZE_NUMBER_INT,
        "subject" => FILTER_SANITIZE_STRING,
        "body" => FILTER_UNSAFE_RAW,
        "post_date" => array(
            'filter'  => FILTER_CALLBACK,
            'options' => array("WordCraft", "validate_time")
        ),
        "user_id" => FILTER_SANITIZE_NUMBER_INT,
        "uri" => FILTER_SANITIZE_STRING,
        "allow_comments" => FILTER_VALIDATE_BOOLEAN,
        "published" => FILTER_VALIDATE_BOOLEAN,
        "tags" => array(
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY
        )
    );

    public function __construct($init_data=null) {
        $this->wc = Wordcraft::init();

        if(is_numeric($init_data)){
            /**
             * We have an id, load it from the db
             */
             $posts =  self::get(array(
                 "post_id" => $init_data,
                 "return_rows" => true
             ));

             if(count($posts)){
                 $init_data = current($posts);
             } else {
                 $init_data = false;
             }
        }

        if(is_array($init_data)){
            /**
             * We got an array, it is probably an array of post info
             * from a list call
             */
            foreach($init_data as $k=>$v){
                $this->$k = $v;
            }
        }

        $this->original = $this->properties;

        $key = "after:".__CLASS__.":".__FUNCTION__;
        if($this->wc->hooks[$key]){
            $this->wc->plugin($key, $this);
        }

    }

    public function save() {

        $key = "before:".__CLASS__.":".__FUNCTION__;
        if($this->wc->hooks[$key]){
            $this->wc->plugin($key, $this);
        }

        $success = true;

        $fields = array();
        $params = array();

        $save_tags = false;

        foreach($this->properties as $prop=>$val){

            if($prop != "post_id"){

                if($val != $this->original[$prop]){

                    if($prop == "tags"){
                        $save_tags = true;
                    } else {
                        $fields[] = "$prop = ?";
                        $params[] = $val;
                    }
                }
            }
        }

        if(count($fields) || $save_tags){

            $this->wc->db->beginTransaction();

            if(count($fields)){
                $sql = "update {$this->wc->tables['posts']} set ";
                $sql.= implode(",", $fields);
                $sth = $this->wc->db->prepare($sql);
                foreach($params as $key=>$param){
                    $sth->bindParam($key+1, $param);
                }
                if(!$sth->execute()){
                    $this->wc->db->rollBack();
                    $success = false;
                }
            }

            if($success && $save_tags){

                $dels = array_diff($this->original["tags"], $this->properties["tags"]);

                if($dels){
                    $sql = "delete from {$this->wc->tables['tags']} where post_id = ? and tag in (".implode(",", array_pad(array(), count($dels), "?")).")";
                    $sth = $this->wc->db->prepare($sql);
                    $sth->bindParam(1, $this->properties["post_id"], PDO::PARAM_INT);
                    foreach($dels as $key=>$param){
                        $sth->bindParam($key+2, $param);
                    }
                    if(!$sth->execute()){
                        $this->wc->db->rollBack();
                        $success = false;
                    }
                }

                if($success){
                    $adds = array_diff($this->properties["tags"], $this->original["tags"]);
                    if($adds){
                        foreach($adds as $a){
                            $sql = "insert into {$this->wc->tables['tags']} (post_id, tag) values (?, ?)";
                            $sth = $this->wc->db->prepare($sql);
                            $sth->bindParam(1, $this->properties["post_id"], PDO::PARAM_INT);
                            $sth->bindParam(2, $a);
                            if(!$sth->execute()){
                                $this->wc->db->rollBack();
                                $success = false;
                                break;
                            }
                        }
                    }
                }
            }

            if($success){
                $this->wc->db->commit();
                $key = "after:".__CLASS__.":".__FUNCTION__;
                if($this->wc->hooks[$key]){
                    $this->wc->plugin($key, $this);
                }
            }
        }


        return $success;
    }

    public static function get($filters = null){

        $wc = Wordcraft::init();

        $key = "before:".__CLASS__.":".__FUNCTION__;
        if($wc->hooks[$key]){
            $wc->plugin($key, $filters);
        }

        $bind_type = PDO::PARAM_STR;

        $list = array();

        $start = empty($filters["start"]) ? 0 : ((int)$filters["start"]) - 1;

        $sql = "select {$wc->tables['posts']}.* from {$wc->tables['posts']}";

        if(isset($filters["post_id"])) {

            $sql.= " where post_id = ?";
            $tokens[] = array($filters["post_id"], PDO::PARAM_INT);

        } else {

            if(isset($filters["tag"])){

                $sql.= " inner join {$wc->tables['tags']} on
                            {$wc->tables['posts']}.post_id = {$wc->tables['tags']}.post_id and
                            {$wc->tables['tags']}.tag = ?";
                $tokens = array($filters["tag"]);

            }

            if(isset($filters["search"])) {

                list($where, $tokens) = self::create_like_string(array("subject", "body"), $filters["search"]);

                $sql.= " where $where";

            }

            $sql.= " order by post_date desc limit $start, 20";

        }

        $sth = $wc->db->prepare($sql);

        if(!empty($tokens)){
            foreach($tokens as $key=>$tok){
                if(!is_array($tok)){
                    $sth->bindParam($key+1, $tok);
                } else {
                    $sth->bindParam($key+1, $tok[0], $tok[1]);
                }
            }
        }

        $sth->execute();

        $posts = array();

        while($row = $sth->fetch(PDO::FETCH_ASSOC)){
            $row["tags"] = array();
            $posts[$row["post_id"]] = $row;
        }

        $sql = "select * from {$wc->tables['tags']} where post_id in (".implode(",", array_keys($posts)).") order by post_id, tag";
        $sth = $wc->db->prepare($sql);
        $sth->execute();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)){
            $posts[$row["post_id"]]["tags"][] = $row["tag"];
        }

        $key = "after:".__CLASS__.":".__FUNCTION__;
        if($wc->hooks[$key]){
            $wc->plugin($key, $posts);
        }

        if(empty($filters["return_rows"])){
            foreach($posts as $p){
                $list[] = new WordcraftPost($p);
            }
        } else {
            $list = $posts;
        }

        return $list;

    }

    /**
     * Creates a LIKE clause for a query
     *
     * @param   $fields     Fields to be searched
     * @param   $search     The search string provided by the user
     * @return  string
     *
     */
    private static function create_like_string($fields, $search) {

        $return_tokens = array();

        // Surround with spaces so matching is easier.
        $search = " $search ";

        // Pull out all grouped terms, e.g. (nano mini).
        $paren_terms = array();
        if (strstr($search, '(')) {
            preg_match_all('/ (\-*\(.+?\)) /', $search, $m);
            $search = preg_replace('/ \-*\(.+?\) /', ' ', $search);
            $paren_terms = $m[1];
        }

        // Pull out all the double quoted strings,
        // e.g. '"iMac DV" or -"iMac DV".
        $quoted_terms = array();
        if (strstr( $search, '"')) {
            preg_match_all('/ (\-*".+?") /', $search, $m);
            $search = preg_replace('/ \-*".+?" /', ' ', $search);
            $quoted_terms = $m[1];
        }

        // Finally, pull out the rest words in the string.
        $norm_terms = preg_split("/\s+/", $search, 0, PREG_SPLIT_NO_EMPTY);

        // Merge all search terms together.
        $tokens =  array_merge($quoted_terms, $paren_terms, $norm_terms);


        $clauses = array();

        foreach($tokens as $token){

            if(preg_match('!\((.+?)\)!', $token, $match)){

                $sub_token = explode(",", $match[1]);

            } else {

                $sub_token = array($token);
            }

            $tok_clauses = array();

            foreach($sub_token as $sub){

                $sub = trim($sub);

                if($sub[0]=="-"){
                    $sub = substr($sub, 1);
                    $cond = "NOT LIKE";
                } else {
                    $cond = "LIKE";
                }

                if(preg_match('!"(.+?)"!', $sub, $match)){
                    $sub = $match[1];
                }

                foreach($fields as $field){

                    $tok_clauses[] = "$field $cond ?";
                    $return_tokens[] = "%$sub%";
                }

            }

            $clauses[] = "(".implode(" OR ", $tok_clauses).")";
        }

        return array(
            implode(" AND\n", $clauses),
            $return_tokens
        );
    }


}

class WordcraftPage extends WordcraftData {

    private $wc;

    protected $data_types = array(
        "page_id" => FILTER_SANITIZE_NUMBER_INT,
        "title" => FILTER_SANITIZE_STRING,
        "body" => FILTER_UNSAFE_RAW,
        "nav_label" => FILTER_SANITIZE_STRING,
        "uri" => FILTER_SANITIZE_STRING
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}

class WordcraftComment extends WordcraftData {

    private $wc;

    protected $data_types = array(
        "comment_id" => FILTER_SANITIZE_NUMBER_INT,
        "post_id" => FILTER_SANITIZE_NUMBER_INT,
        "name" => FILTER_SANITIZE_STRING,
        "email" => FILTER_VALIDATE_EMAIL,
        "url" => FILTER_VALIDATE_URL,
        "comment_date" => array(
            'filter'  => FILTER_CALLBACK,
            'options' => array('callback' => array("WordCraft", "validate_time"))
        ),
        "comment" => FILTER_UNSAFE_RAW,
        "ip_address" => FILTER_VALIDATE_IP,
        "status" => FILTER_SANITIZE_STRING,
        "linkback" => FILTER_VALIDATE_BOOLEAN
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}

?>