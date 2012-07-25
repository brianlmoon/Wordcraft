<?php

namespace Wordcraft;

class Wordcraft_Application extends Wordcraft_Core {

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

    public static function instance() {
        static $singleton;
        if(is_null($singleton)){
            $singleton = new Wordcraft_Application();
        }
        return $singleton;
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


?>
