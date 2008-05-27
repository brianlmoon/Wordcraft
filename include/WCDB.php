<?php

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

// if init has not loaded, quit the file
if(!defined("WC")) return;

// constants for query_fetch() and fetch() method
define("WC_DB_FETCH_ASSOC", 1);
define("WC_DB_FETCH_NUM", 2);
define("WC_DB_FETCH_BOTH", 3);
define("WC_DB_FETCH_ALL_ASSOC", 4);
define("WC_DB_FETCH_ALL_NUM", 5);
define("WC_DB_FETCH_ALL_BOTH", 6);
define("WC_DB_FETCH_VALUE", 7);
define("WC_DB_FETCH_COUNT", 8);
define("WC_DB_FETCH_INSERT_ID", 9);

/**
 * WCDB Class for PHP ext/mysqli
 *
 * This class creates a WCDB interface for using MySQL via the PHP mysqli extension
 * @author  Brian Moon <brian@phorum.org>
 * @package xaz
 */
class WCDB {

    private $read_connection;
    private $write_connection;
    private $connection;
    private $result;
    private $server;
    private $database;
    private $user;
    private $password;
    private $report_errors = true;
    private $slaves;

    public $last_error = "";

    /**
     * Constructor for WCDB mysqli Class
     * @access  public
     * @param   string  $server     Server name to connect to.
     * @param   string  $database   Database name to select
     * @param   string  $user       User name to use to connect
     * @param   string  $password   Password to use to connect
     * @param   array   $slaves     Optional array of slave servers to connect to for read only data
     * @return  object
     */
    public function __construct($server, $database, $user, $password, $slaves=false) {

        $this->server   = $server;
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;

        if($slaves && is_array($slaves)){
            $this->slaves = $slaves;
        } else {
            $this->slaves = false;
        }

    }

    /**
     * Destructor for WCDB mysqli Class
     * @access  public
     */
    public function __deconstruct() {
        if($this->connection){
            mysqli_close($this->connection);
        }
    }

    /**
     * Universal method to run a query.  Includes connection handling
     * and error checking.  Returns a mysqli result object on success.
     * False on failure.
     * @access  public
     * @param   string  $sql        SQL query to be exectued
     * @param   bool    $read_only  If true, run this query on the read only pool
     * @param   bool    $buffered   Optional value for result type.  If true, results will be buffered.  See docs for mysqli_query for more.
     * @return  mixed
     */
    public function query($sql, $read_only=false, $buffered=true) {

        // if this is a read only query and we have a slave list,
        // pick a read slave for this request.  One request will
        // use just one read slave for all queries.
        if($read_only && $this->slaves){
            $this->connection = false;
            if(!$this->read_connection){
                // loop the servers until we get a connect
                // this could slow you down if you have a lot of downed
                // read servers
                while(!$this->connection && count($this->slaves)){
                    $rand_server = mt_rand(0, count($this->slaves));
                    $this->connect($this->slaves[$rand_server]);
                    if(!$this->connection){
                        // if we could not connect, remove this server
                        // from the array for this request.
                        unset($this->slaves[$rand_server]);
                    }
                }
            }
            $this->connection = $this->read_connection;
        }


        // if we don't have a connection at this point,
        // connect the main database server
        if(!$this->connection){
            // check if we have connected the main write server already
            if(!$this->write_connection){
                $this->connect();
            } else {
                $this->connection = $this->write_connection;
            }
        }


        $buffered = ($buffered) ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT;

        $this->result = mysqli_query($this->connection, $sql, $buffered);
        if(!$this->result){
            $error = "MySQL Error: ".mysqli_error($this->connection)."<br />\n";
            $bt = debug_backtrace();
            foreach($bt as $t){
                $error.= "SQL: $sql<br />\nFile: ".$t["file"]."<br />\nFunction: ".$t["function"]."<br />\nLine:".$t["line"]."<br />\n";
            }
            if($this->report_errors){
                exit($error);
            } else {
                $this->last_error = $error;
            }
        }

        return (bool)$this->result;
    }

    /**
     * Method for running a query and fetching the results into an array.
     * Returns an array on success.  False on failure.
     * @access  public
     * @param   string          $sql        SQL query to be exectued
     * @param   const           $return     What to return.  One of the following:
     *                                      WC_DB_FETCH_ASSOC        Return the first row as an associative array
     *                                      WC_DB_FETCH_NUM          Return the first row as an numerical array
     *                                      WC_DB_FETCH_BOTH         Return the first row with both associative and numerical indices
     *                                      WC_DB_FETCH_ALL_ASSOC    Return all rows as an associative array.  If provided $key will be usde for the array index.
     *                                      WC_DB_FETCH_ALL_NUM      Return all rows as an numerical array.  If provided $key will be usde for the array index.
     *                                      WC_DB_FETCH_ALL_BOTH     Return all rows with both associative and numerical indices.  If provided $key will be usde for the array index.
     *                                      WC_DB_FETCH_VALUE        Return the value of a single field.  If $key is provided, this field will be returned.
     *                                                                    Otherwise, the first field from the first row will be returned.
     *                                      WC_DB_FETCH_COUNT        Return a row count.
     *                                      WC_DB_FETCH_INSERT_ID    Return the insert id of an Insert or Update statement.
     * @param   bool:string     $key        If not false, this field will be used for the FETCH_ALL and FETCH_VALUE options.
     * @return  mixed
     */
    public function query_fetch($sql, $return, $key=false) {

        $ret_val = false;

        switch($return){
            case WC_DB_FETCH_ALL_ASSOC:
            case WC_DB_FETCH_ALL_NUM:
            case WC_DB_FETCH_ALL_BOTH:
                $this->query($sql, false, false);
                $ret_val = $this->fetch_all($return, $key);
                break;

            case WC_DB_FETCH_ASSOC:
            case WC_DB_FETCH_NUM:
            case WC_DB_FETCH_BOTH:
                $this->query($sql, false);
                $ret_val = $this->fetch($return);
                break;

            case WC_DB_FETCH_VALUE:
                $this->query($sql, false);
                $row = $this->fetch(WC_DB_FETCH_ASSOC);
                if($key && isset($row[$key])){
                    // if $key is valid, return its value
                    $ret_val = $row[$key];
                } else {
                    // else return the first item in the array
                    $ret_val = current($row);
                }
                break;

            case WC_DB_FETCH_COUNT:
                $this->query($sql, true);
                if($this->result){
                    $ret_val = mysqli_num_rows($this->result);
                }
                break;

            case WC_DB_FETCH_INSERT_ID:
                $this->query($sql, true);
                if($this->result){
                    $ret_val = mysqli_insert_id($this->connection);
                }
                break;
        }

        if($this->result && is_object($this->result)){
            mysqli_free_result($this->result);
        }

        return $ret_val;
    }


    /**
     * Method for fetching all rows after calling the query() method.
     * Returns an array on success.  False on failure.
     * @access  public
     * @param   const           $return     What to return.  One of the following:
     *                                      WC_DB_FETCH_ALL_ASSOC    Return all rows as an associative array.  If provided $key will be usde for the array index.
     *                                      WC_DB_FETCH_ALL_NUM      Return all rows as an numerical array.  If provided $key will be usde for the array index.
     *                                      WC_DB_FETCH_ALL_BOTH     Return all rows with both associative and numerical indices.  If provided $key will be usde for the array index.
     * @param   string          $key        The field in the result set to be used as the array key
     * @return  mixed
     */
    public function fetch_all($return, $key=false){

        $rows = false;

        if($this->result){
            switch($return){
                case WC_DB_FETCH_ALL_ASSOC:
                    $return = MYSQLI_ASSOC;
                    break;
                case WC_DB_FETCH_ALL_NUM:
                    $return = MYSQLI_NUM;
                    break;
                case WC_DB_FETCH_ALL_BOTH:
                    $return = MYSQLI_BOTH;
                    break;
                default:
                    $return = MYSQLI_ASSOC; // MYSQLI_ASSOC needs to be first in the switch for speed.  Its here as a failsafe.
            }
            while($row = mysqli_fetch_array($this->result, $return)){
                if($key!==false && isset($row[$key])){
                    $rows[$row[$key]] = $row;
                } else {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }


    /**
     * Method for fetching rows one at a time after calling the query() method.
     * Returns an array on success.  False on failure.
     * @access  public
     * @param   const           $return     What to return.  One of the following:
     *                                      WC_DB_FETCH_ASSOC        Return the first row as an associative array
     *                                      WC_DB_FETCH_NUM          Return the first row as an numerical array
     *                                      WC_DB_FETCH_BOTH         Return the first row with both associative and numerical indices
     * @return  mixed
     */
    public function fetch($return=WC_DB_FETCH_ASSOC){

        $row = false;

        if($this->result){
            switch($return){
                case WC_DB_FETCH_ASSOC:
                    $return = MYSQLI_ASSOC;
                    break;
                case WC_DB_FETCH_NUM:
                    $return = MYSQLI_NUM;
                    break;
                case WC_DB_FETCH_BOTH:
                    $return = MYSQLI_BOTH;
                    break;
                default:
                    $return = MYSQLI_ASSOC; // MYSQLI_ASSOC needs to be first in the switch for speed.  Its here as a failsafe.
            }
            $row = @mysqli_fetch_array($this->result, $return);
            if(!is_array($row)) $row = false;
        }

        return $row;
    }


    /**
     * Escapes data for insertion into a query
     * @access  public
     * @param   mixed   $data    Variable to be escaped.  Supports arrays, objects and scalar types.
     * @param   mixed   $type    Optional. If the data is to be escaped in a specific manner, provide the type here.
     *                           The only supported special case is int.  If you want an array of values sanitized
     *                           to ensure they are integers, for example.
     * @return  mixed
     */
    public function escape($data, $type=false){
        if(is_array($data) || is_object($data)){
            foreach($data as &$d){
                $d = $this->escape($d, $type);
            }
        } elseif(is_numeric($data) || is_string($data)) {
            if($type=="int"){
                $data = (int)$data;
            } else {
                $this->connect();
                $data = mysqli_real_escape_string($this->connection, $data);
            }
        } else {
            $bt = debug_backtrace();
            trigger_error("Invalid data type for WCDB->escape() on line ".$bt[0]["line"]." of ".$bt[0]["file"].".", E_USER_WARNING);
        }
        return $data;
    }


    /**
     * Check if the mysql connection is working.  If the connection is
     * working, true will be returned.  If it is not working, the mysql
     * error will be returned.
     * @access  public
     * @return  mixed
     */
    public function check_connection() {

        $error = $this->connect(false, false);

        return $error;

    }


    /**
     * Set error reporting on or off
     * @access  public
     */
    public function report_errors($report_errors) {
        $this->report_errors = (bool)$report_errors;
    }

    /**
     * Method for connecting to mysql
     * @access  private
     */
    private function connect($server=false, $report_error=true) {

        $connect_server = (!$server) ? $this->server : $server;
        $this->connection = mysqli_connect($this->server, $this->user, $this->password, $this->database);
        /* check connection */
        if(!$server){
            $error = mysqli_connect_errno();
            if ($error) {
                if($report_error){
                    exit("Connection to the mysql server failed.  Please check your settings.  The servers said &quot;".$error."&quot;");
                } else {
                    return $error;
                }
            }
            // save this connection to the write_connection var
            $this->write_connection = $this->connection;
        } else {
            // save this connection to the read_connection var
            $this->read_connection = $this->connection;
        }

        return true;
    }


}

?>
