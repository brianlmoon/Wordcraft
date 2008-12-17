<?php

include_once "../include/url.php";

function wc_admin_error($error_message, $exit=true) {

    global $WC;

    if($exit) include_once "./header.php";

    ?>
        <div class="notice_error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php

    if($exit) include_once "./footer.php";

    if($exit){
        exit();
    }
}

function wc_admin_message($message, $exit=true, $redir=null) {

    global $WC;

    if($exit) include_once "./header.php";

    ?>
        <div class="notice">
            <?php echo htmlspecialchars($message); ?>
        </div>

        <?php if(!empty($redir)){ ?>

            <meta http-equiv="refresh" content="3;url=<?=$redir?>">

        <?php } ?>

    <?php



    if($exit) include_once "./footer.php";

    if($exit){
        exit();
    }
}


function wc_admin_handle_linkbacks($post_id) {

    global $WCDATA, $WC;

    $post = wc_db_get_post($post_id);
    $post_url = wc_get_url("post", $post_id, $post["uri"]);


        // handle pingbacks/trackbacks
    if(preg_match_all('!href="(.+?)"|href=\'(.+?)\'|href=([^ >]+)!', $post["body"], $match)){

        $urls = array_unique(array_merge($match[1], $match[2], $match[3]));

        foreach($urls as $url){

            if(empty($url)) continue;

            $data = wc_admin_get_url($url, "HEAD");

            if(strpos($data, "X-Pingback")){
                preg_match('!X-Pingback: (.+?)\s!', $data, $match);
                $pingback_url = $match[1];
            }

            if(empty($pingback_url)){
                $data = file_get_contents($url);
                if(preg_match('!<link.+?rel="pingback".*>!si', $data, $match)){
                    if(preg_match('!href="(.+?)"|href=\'(.+?)\'|href=([^ >]+)!', $match[0], $match)){
                        $pingback_url = max($match[1], $match[2], $match[3]);
                    }
                } elseif(preg_match('!<rdf:Description.+?trackback:ping=[\'"](.+?)[\'"]!si', $data, $match)){
                    $trackback_url = $match[1];
                }
            }

            if(!empty($pingback_url)){

                // do pingback
                $data ='<?xml version="1.0"?>';
                $data.='<methodCall>';
                $data.='<methodName>pingback.ping</methodName>';
                $data.='<params>';
                $data.='<param><value><string>'.str_replace("&", "&amp;", $post_url).'</string></value></param>';
                $data.='<param><value><string>'.str_replace("&", "&amp;", $url).'</string></value></param>';
                $data.='</params></methodCall>';

                wc_admin_get_url($pingback_url, "POST", $data);

            } elseif(!empty($trackback_url)) {

                // do trackback
                $data = "url=".urlencode($post_url);
                $data.= "&title=".urlencode($post["title"]);
                $data.= "&blog_name=".urlencode($WC["default_title"]);

                wc_admin_get_url($trackback_url, "POST", $data);

            }

        }
    }

}


function wc_admin_get_url($url, $method="GET", $request_data="") {

    $data = "";

    $url_parts = parse_url($url);
    $host = ($url_parts["scheme"]=="http") ? $url_parts["host"] : "ssl://".$url_parts["host"];
    $port = (isset($url_parts["port"])) ? $url_parts["port"] : ( ($url_parts["scheme"]=="http") ? 80 : 443 );
    $uri = (isset($url_parts["path"])) ? $url_parts["path"] : "/";
    $url.= (isset($url_parts["query"])) ? $url_parts["query"] : "";
    $url.= (isset($url_parts["fragment"])) ? $url_parts["fragment"] : "";

    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if($fp) {
        $packet = "$method $uri HTTP/1.0\r\nHost: $url_parts[host]\r\nContent-Length: ".strlen($request_data)."\r\n\r\n$request_data";
        fputs($fp, $packet);
        while(!feof($fp)){
            $data.= fread($fp, 256);
        }
    }

    return $data;
}

?>
