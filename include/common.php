<?php

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

define("WC", "0.1");

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
