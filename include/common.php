<?php

// Check that this file is not loaded directly.
if ( basename( __FILE__ ) == basename( $_SERVER["PHP_SELF"] ) ) exit();

define("WC", "0.1");

include_once dirname(__FILE__)."/config.php";
include_once dirname(__FILE__)."/database.php";

$settings = wc_db_get_settings();

$WC = array_merge($WC, $settings);

unset($settings);



// utility function

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
