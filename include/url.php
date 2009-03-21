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
 * @param   array   $args       Array of page parameters to be used to build the URL.
 * @param   bool    $escape     If true, the URL is escaped for use in HTML.
 * @return  string
 *
 */
function wc_get_url($page_type, $args=null, $escape=true) {

    global $WC;

    $url = "";

    if(empty($args)) $args = array();

    if(!isset($WC["url_formats"][$page_type])){

        $bt = debug_backtrace();

        trigger_error("Invalid value for parameter 1 to ".__FUNCTION__."() in ".$bt[0]["file"]." on line ".$bt[0]["line"], E_USER_WARNING);

    } else {

        $encode_args = true;

        // special sef/rewrite handling
        if($WC["use_rewrite"]){
            if($page_type=="post" || $page_type=="page"){
                // only create a sef url if there is a custom uri value
                if(!empty($args[1])){
                    $page_type = $page_type."_sef";
                    $args[0] = $args[1];
                    unset($args[1]);
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

        array_unshift($args, $WC["url_formats"][$page_type]["page"]);
        array_unshift($args, $WC["base_url"]);

        $format = "";
        if(count($args) > 2){
            $format = $WC["url_formats"][$page_type]["format"];
        }

        $url = vsprintf("%s/%s".$format, $args);

    }

    if($escape){
        $url = htmlspecialchars($url, ENT_COMPAT, "UTF-8");
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
