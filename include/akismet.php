<?php


function wc_akismet_request( $comment, $mode ) {

    global $WC, $WCDB;

    $return = "";

    $valid_modes = array(
        "verify-key",
        "submit-spam",
        "submit-ham",
        "comment-check"
    );

    if(!in_array($mode, $valid_modes)){
        trigger_error("Invalid mode for function ".__FUNCTION__.".", E_USER_WARNING);
        return;
    }

    if(!empty($WC["akismet_key"])) {

        if($mode == "verify-key"){
            $host = "rest.akismet.com";

            $data = "key=".urlencode($comment)."&blog=".urlencode($WC["base_url"]);

        } else {

            $host = $WC["akismet_key"].".rest.akismet.com";

            $data = "blog=".urlencode($WC["base_url"]);
            $data.= "&user_ip=".urlencode($_SERVER["REMOTE_ADDR"]);
            $data.= "&user_agent=".urlencode($_SERVER["HTTP_USER_AGENT"]);
            $data.= "&referrer=".urlencode($_SERVER["HTTP_REFERER"]);
            $data.= "&permalink=".urlencode(wc_get_url("post", $comment["post_id"]));
            $data.= "&comment_type=".urlencode("forum");
            $data.= "&comment_author=".urlencode($comment["name"]);
            $data.= "&comment_author_email=".urlencode($comment["email"]);
            $data.= "&comment_author_url=".urlencode($comment["url"]);
            $data.= "&comment_content=".urlencode($comment["comment"]);
        }

        $fp = @fsockopen( $host, 80, $errno, $errstr, 8 );

        if($fp){
            stream_set_timeout ( $fp, 10 );

            fputs( $fp, "POST /1.1/$mode HTTP/1.0\n" );
            fputs( $fp, "Host: $host\n" );
            fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
            fputs( $fp, "Content-length: " . strlen( $data ) . "\n" );
            fputs( $fp, "User-Agent: WC/".WC."\n" );
            fputs( $fp, "Connection: close\n\n" );
            fputs( $fp, $data );

            $x=1;
            while ( !feof( $fp ) ) {
                $buf .= fgets( $fp, 1024 );

                // if the fgets returns nothing on the first return,
                // the remote server is timing out.
                if($x==1 && $buf==""){
                    $errstr="timeout waiting for data";
                    break;
                }
                $x++;
            }

            fclose( $fp );

        }

        if(!$fp || empty($buf)){
            trigger_error("Could not open $host: $errstr", E_USER_WARNING);
        }

        $buf = str_replace( "\r", "", $buf ); //strip out carriage returns
        list( $page_data["headers"], $page_data["content"] ) = explode( "\n\n", $buf, 2 );

        $return = $page_data["content"];
    }

    return $return;
}


?>
