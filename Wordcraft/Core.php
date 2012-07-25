<?php

namespace Wordcraft;

error_reporting(-1);

if(!defined("__DIR__")){
    define("__DIR__", dirname(__FILE__));
}

class Wordcraft_Core {

    protected $db;

    protected $tables;

    protected $hooks;

    protected $properties;

    protected $original;

    public function __construct($config_file) {

        $config = parse_ini_file ($config_file , true);

        if(isset($config['database']["user"]) && isset($config['database']["password"])){
            $this->db = new PDO($config['database']['dsn'], $config['database']["user"], $config['database']["password"]);
        } else {
            $this->db = new PDO($config['database']['dsn']);
        }

        $this->build_table_names($config["database"]["prefix"]);

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

    public function save() {

        $key = "before:".get_class($this).":".__FUNCTION__;
        if($this->wc->hooks[$key]){
            $this->wc->plugin($key, $this);
        }

        $fields = array();
        $sub_data = array();

        foreach($this->properties as $key => $value){

            if(isset($this->sub_data[$key])){

                $sub_data[$key] = $value;

            } else {

                $fields[$key] = $value;
            }
        }

        $this->db->beginTransaction();

        /**
         * Save the primary table first
         */
        $success = $this->save_data($self::TABLE, $this->data_types, $fields, $this->primary_key, $this->original);


        if(!empty($sub_tables) && $success){

            /**
             * Now loop sub tables and save their data
             */

            foreach($sub_tables as $field => $data){

                $table_name = $this->data_types[$field]["table"];
                $fk = $this->data_types[$field]["foreign_key"];

                /**
                 * There is no new data, skip out
                 */
                if($data == $this->original[$field]) continue;

                if(empty($data)){
                    // delete all the data
                    $sql = "delete from $table_name where $fk = ?";
                    $sth->bindParam(1, $this->properties[$fk]);
                    if(!$sth->execute()){
                        $success = false;
                        break;
                    }
                }

                /**
                 * This next little bit looks like a lot of work and it is.
                 * In a normal web environment, database writes are very
                 * expensive. Likewise, on a blog, database writes are not
                 * very common. So, we do all we can to avoid a write in the
                 * application code. I would not recommend this method in a
                 * different application where database writes are common.
                 */

                $new_rows = array();

                $orig_rows = $this->original[$key];

                foreach($orig_rows as $key => $value){
                    ksort($value);
                    $hashed_orig[md5(json_encode($value))] = $key;
                }

                foreach($data as $row){
                    ksort($row);
                    $hash = md5(json_encode($row));
                    if(!isset($hashed_orig[$hash])){
                        $new_rows[] = $row;
                    } else {
                        unset(unset($orig_rows[$hashed_orig[$hash]]);
                    }
                }

                foreach($new_rows as $row){
                    $success = $this->save_data($table_name, $this->data_types[$field]["data_types"], $row);
                    if(!$success) break;
                }

                if($success){
                    foreach($orig_rows as $row){
                        // delete all the data
                        $sql = "delete from $table_name where";
                        foreach(array_keys($row) as $field){
                            $sql.= " $field = ?,";
                        }
                        $sql.= substr($sql, 0, -2);
                        foreach(array_values($row) as $k => $value){
                            $sth->bindParam($k+1, $value);
                        }
                        if(!$sth->execute()){
                            $success = false;
                            break;
                        }
                    }
                }
            }

        }

        if(!$success){

            $this->db->rollback();

        } else {

            $success = $this->db->commit();

            if($success){

                /**
                 * Allow child classses to do some more work after save
                 */
                if(method_exists($this, "after_save")){
                    $this->after_save();
                }

                $key = "after:".get_class($this).":".__FUNCTION__;
                if($this->wc->hooks[$key]){
                    $this->wc->plugin($key, $this);
                }
            }
        }

        return $success;
    }

    private save_data($table, $data_types, $data, $primary_key = null, $original = null) {

        $success = true;

        $fields = array();

        foreach($data as $key => $value){
            if(!isset($data_types[$k])){
                throw new Wordcraft_Exception("No data type definition found for $k");
            }

            if(is_array($data_types[$k])){
                if(isset($data_types[$k]["hint"])){
                    $filter = $data_types[$k]["hint"]
                } else {
                    $filter = $data_types[$k]["filter"];
                }
            } else {
                $filter = $data_types[$k];
            }

            switch ($filter){
                case FILTER_VALIDATE_INT:
                case FILTER_SANITIZE_NUMBER_INT:
                    $fields[$k] = (int)$value;
                    break;

                case FILTER_VALIDATE_FLOAT:
                case FILTER_SANITIZE_NUMBER_FLOAT:
                    $fields[$k] = (float)$value;
                    break;

                case FILTER_VALIDATE_BOOLEAN:
                    $fields[$k] = (int)$value;
                    break;

                case FILTER_UNSAFE_RAW:
                default:
                    $fields[$k] = (string)$value;

                case FILTER_VALIDATE_EMAIL:
                case FILTER_VALIDATE_IP:
                case FILTER_VALIDATE_URL:
                case FILTER_SANITIZE_EMAIL:
                case FILTER_SANITIZE_ENCODED:
                case FILTER_SANITIZE_MAGIC_QUOTES:
                case FILTER_SANITIZE_SPECIAL_CHARS:
                case FILTER_SANITIZE_FULL_SPECIAL_CHARS:
                case FILTER_SANITIZE_STRING:
                case FILTER_SANITIZE_STRIPPED:
                case FILTER_SANITIZE_URL:
                case FILTER_UNSAFE_RAW:
                default:
                    $fields[$k] = (string)$value;
                    break;

            }

            /**
             * If the data is unchanged, do not update it
             */
            if($original && $fields[$k] == $original[$k]){
                unset($fields[$k]);
            }

        }

        if($primary_key || empty($data[$primary_key])){

            /**
             * This is an insert
             */

            $sql = "insert into $table (".
                    implode(",", array_keys($data)).
                    ") values (".
                    implode(",", array_fill(0, count($data), "?")).")";

        } else {

            /**
             * This is an update
             */

            $sql = "update $table set ";

            /**
             * Push the primary key to the end so it will bind in
             * the correct order
             */
            if(isset($fields[$primary_key])){
                $val = $fields[$primary_key];
                unset($fields[$primary_key]);
                $fields[$primary_key] = $val;
            }

            foreach(array_keys($fields) as $f){
                if($f != $primary_key){
                    $sql.= "$f = ?, ";
                }
            }

            // Trim the last , and space
            $sql.= substr($sql, 0, -2);

            $sql.= " where $primary_key = ?";
        }

        $sth = $this->db->prepare($sql);

        foreach(array_values($fields) as $key=>$param){
            $sth->bindParam($key+1, $param);
        }

        if(!$sth->execute()){
            $success = false;
        }

        return $success;
    }

    /**
     * PSR0 Compatible Autoloader
     * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
     *
     * @param   string  $class_name The name of the class to load
     * @return  void
     *
     */
    public static function autoload($class_name) {
        $class_name = ltrim($class_name, '\\');
        $file_name  = '';
        $namespace = '';
        if ($last_ns_pos = strripos($class_name, '\\')) {
            $namespace = substr($class_name, 0, $last_ns_pos);
            $class_name = substr($class_name, $last_ns_pos + 1);
            $file_name  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

        require $file_name;
    }
}

spl_autoload_register(array("Wordcraft_Core", "autoload"));

?>