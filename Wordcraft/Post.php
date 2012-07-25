<?php

namespace Wordcraft;

class Wordcraft_Post extends Wordcraft_Core {

    const TABLE = "posts";

    private $wc;

    protected $data_types = array(
        "post_id" => FILTER_SANITIZE_NUMBER_INT,
        "subject" => FILTER_SANITIZE_STRING,
        "body" => FILTER_UNSAFE_RAW,
        "post_date" => array(
            "filter"  => FILTER_CALLBACK,
            "options" => array("WordCraft_Data", "validate_time"),
            "hint" => FILTER_SANITIZE_NUMBER_INT
        ),
        "user_id" => FILTER_SANITIZE_NUMBER_INT,
        "uri" => FILTER_SANITIZE_STRING,
        "allow_comments" => FILTER_VALIDATE_BOOLEAN,
        "published" => FILTER_VALIDATE_BOOLEAN,
        "tags" => array(
            "filter" => FILTER_SANITIZE_STRING,
            "flags" => FILTER_REQUIRE_ARRAY
        )
    );

    protected $primary_key = "post_id";

    protected $sub_data = array(
        "tags" => array(
            "table" => "tags",
            "data_types" => array(
                "post_id" => FILTER_SANITIZE_NUMBER_INT,
                "subject" => FILTER_SANITIZE_STRING,
            ),
            "foreign_key" => "post_id"
        )
    );

    public function __construct($init_data=null) {

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
        if($this->hooks[$key]){
            $this->plugin($key, $this);
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

                list($where, $tokens) = Wordcraft_Data::create_like_string(array("subject", "body"), $filters["search"]);

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

}

