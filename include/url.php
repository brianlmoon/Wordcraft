<?php

/**
 * Functions used to build URLs for Wordcraft
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */


/**
 * Builds a URL to a wordcraft page
 *
 * @param   string  $page_type  The type of page (index,post,page,etc) to build a URL for.
 * @param   string  $1...$n     A dynamic list of page parameters to be used to build the URL.
 * @return  string
 *
 */
function wc_get_url($page_type) {

    global $WC;

    $url = "";

    $args = func_get_args();

    if(!isset($WC["url_formats"][$page_type])){

        $bt = debug_backtrace();

        trigger_error("Invalid value for parameter 1 to ".__FUNCTION__."() in ".$bt[0]["file"]." on line ".$bt[0]["line"], E_USER_WARNING);

    } else {

        $encode_args = true;

        // special sef/rewrite handling
        if($WC["use_rewrite"]){
            if($page_type=="post" || $page_type=="page"){
                // only create a sef url if there is a custom uri value
                if(!empty($args[2])){
                    $page_type = $page_type."_sef";
                    $args[1] = $args[2];
                    unset($args[2]);
                    $encode_args = false;
                }
            }
        }

        if($encode_args){
            foreach($args as $key=>$arg){
                if($key>0){
                    $args[$key] = urlencode($arg);
                }
            }
        }

        switch(count($args)){

            case 1:
                $url = sprintf("%s/%s", $WC["base_url"], $WC["url_formats"][$page_type]["page"]);
                break;
            case 2:
                $url = sprintf("%s/%s".$WC["url_formats"][$page_type]["format"], $WC["base_url"], $WC["url_formats"][$page_type]["page"], $args[1]);
                break;
            case 3:
                $url = sprintf("%s/%s".$WC["url_formats"][$page_type]["format"], $WC["base_url"], $WC["url_formats"][$page_type]["page"], $args[1], $args[2]);
                break;
            case 4:
                $url = sprintf("%s/%s".$WC["url_formats"][$page_type]["format"], $WC["base_url"], $WC["url_formats"][$page_type]["page"], $args[1], $args[2], $args[3]);
                break;
            default:
                $bt = debug_backtrace();
                trigger_error("Wrong parameter count for ".__FUNCTION__."() in ".$bt[0]["file"]." on line ".$bt[0]["line"], E_USER_WARNING);
                break;
        }
    }

    return $url;
}


/**
 * Returns the current pages URL
 *
 * @param   bool    $include_query_string   If true, the query string of the current URL is returned
 * @return  string
 *
 */
function wc_get_current_url($include_query_string=true) {

    $url = "";

    if(isset($_SERVER["SCRIPT_URI"])){

        $url = $_SERVER["SCRIPT_URI"];

    } else {
        // On some systems, the port is also in the HTTP_HOST, so we
        // need to strip the port if it appears to be in there.
        if (preg_match('/^(.+):(.+)$/', $_SERVER['HTTP_HOST'], $m)) {
            $host = $m[1];
            if (!isset($_SERVER['SERVER_PORT'])) {
                $_SERVER['SERVER_PORT'] = $m[2];
            }
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
        $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]!="off") ? "https" : "http";
        $port = ($_SERVER["SERVER_PORT"]!=443 && $_SERVER["SERVER_PORT"]!=80) ? ':'.$_SERVER["SERVER_PORT"] : "";
        $url = $protocol.'://'.$host.$port.$_SERVER['PHP_SELF'];
    }

    if($include_query_string && !empty($_SERVER["QUERY_STRING"])){
        $url += "?".$_SERVER["QUERY_STRING"];
    }

    return $url;
}

?>
