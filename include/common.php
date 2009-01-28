<?php

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

define("WC", "0.7");

include_once dirname(__FILE__)."/config.php";
include_once dirname(__FILE__)."/database.php";

// disable db errors if installing
if(defined("WC_INSTALLING")){
    $WCDB->report_errors(false);
}

// load settings
$settings = wc_db_get_settings();

$WC = array_merge($WC, $settings);

unset($settings);

// check for template preview
if($_GET["preview"]){
    $preview = basename($_GET["preview"]);
    if(file_exists("./templates/$preview")){
        $WC["template"] = $_GET["preview"];
    }
}

// check for stupid magic quotes
if ( get_magic_quotes_gpc() && count( $_REQUEST ) ) {

    // removes slashes from all array-entries
    function wc_recursive_stripslashes( &$array ){
        if ( !is_array( $array ) ) {
            return $array;
        } else {
            foreach( $array as $key => $value ) {
                if ( !is_array( $value ) )
                    $array[$key] = stripslashes( $value );
                else
                    wc_recursive_stripslashes( $value );
            }
        }
        return $array;
    }

    wc_recursive_stripslashes( $_POST );
    wc_recursive_stripslashes( $_GET );
    wc_recursive_stripslashes( $_COOKIE );
    wc_recursive_stripslashes( $_REQUEST );
}

// initialize output settings
$WCDATA = array();



session_name("WCSESSID");

session_start();

// if the session is set to remember or remember
// was checked on the login form, set the cookie with a ttl
if(!empty($_SESSION["remember"]) || isset($_POST["remember"])){

    setcookie(
        ini_get("session.name"),
        session_id(),
        time() + $WC["session_days"] * 86400,
        ini_get("session.cookie_path"),
        ini_get("session.cookie_domain"),
        ini_get("session.cookie_secure"),
        ini_get("session.cookie_httponly")
    );

    $_SESSION["remember"] = true;
}


if(isset($_SESSION["wc_user_id"])){

    $WC["user"] = wc_db_get_user($_SESSION["wc_user_id"]);

}


function wc_hook($hook) {

    global $WC;

    // get arguments passed to the function
    $args = func_get_args();

    // shift off hook name
    array_shift($args);

    if ( isset( $WC["hooks"][$hook] ) &&
         is_array($WC["hooks"][$hook])) {

        // load mods for this hook
        foreach( $WC["hooks"][$hook]["mods"] as $mod )
        {
            $mod = basename($mod);

            // Check if the module file is not yet loaded.
            if (isset($load_cache[$mod])) continue;
            $load_cache[$mod] = 1;

            // Load the module file.
            if ( file_exists("./mods/$mod/$mod.php") ) {
                require_once "./mods/$mod/$mod.php";
            } elseif ( file_exists("./mods/$mod.php") ) {
                require_once "./mods/$mod.php";
            }

            // Load the module database layer file.
            if (!empty($WC['moddblayers'][$mod])) {
                $file = "./mods/$mod/db/{$WC['DBCONFIG']['type']}.php";
                if (file_exists($file)) {
                    require_once($file);
                }
            }
        }

        $called = array();

        foreach( $WC["hooks"][$hook]["funcs"] as $func ) {

            // don't call a function twice in case it gets
            // put into the hook twice somehow
            if(isset($called[$func])) continue;
            $called[$func] = true;

            // call functions for this hook
            if ( function_exists( $func ) ) {
                if(count($args)){
                    $args[0] = call_user_func_array( $func, $args );
                } else {
                    call_user_func( $func );
                }
            }
        }
    }

    if(isset($args[0])){
        return $args[0];
    }
}

// utility function for debugging

function print_var($var) {

    echo "<pre style='text-align: left'>";
    echo "\n";
    echo "type:  ".gettype($var)."\n";
    echo "value: ";
    $val = print_r($var, true);
    echo trim(str_replace("\n", "\n       ", $val));
    echo "\n</pre>";
    echo "\n";
}


?>
