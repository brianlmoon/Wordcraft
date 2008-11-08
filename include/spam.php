<?php


function wc_akismet_request( $comment, $mode ) {

    global $WC, $WCDB;

    $return = "true";

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

    if($WC["use_akismet"] && !empty($WC["akismet_key"])){

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


function wc_score_user_submission($submission) {

    $points = 0;

    // How many links are in the body
    // More than 2 -1 point per link
    // Less than 2 +2 points
    $link_count = substr_count($submission, "http://");
    $link_count += substr_count($submission, "https://");

    if($link_count > 2){

        $points -= $link_count*2;

        if(strlen($submission)>1024){
            $points -= 10;
        }
    }

    // How long is the body
    // More than 200 characters and there's less than 2 links, + 5 points
    // Less than 20 characters -1 point
    if(strlen($submission)>20){
        if(strlen($submission)>200 && $link_count<2){
            $points += 5;
        }
    } else {
        $points -= 5;
    }

    // attempted BBCode -1 point per
    if(preg_match_all('!\[(url|image|img)!i', $submission, $match)){
        $points -= count($match[0]);
    }

    // Really bad words -20 point per word matched
    $bad_words = array('/\b@\$\$.*/i','/\ba\$\$.*/i','/\bas\$.*/i','/\ba\$s.*/i','/\b@\$s.*/i','/\b@s\$.*/i','/\barse.*/i','/\bass\b/i','/\bassho.*/i','/\bassram.*/i','/\bbi\+ch\b/i','/\bb!\+ch\b/i','/\bb!tch\b/i','/\bb!7ch\b/i','/\bbi7ch\b/i','/\bb17ch\b/i','/\bb1\+ch\b/i','/\bb1tch\b/i','/\bbitch.*/i','/\bbastard\b/i','/\bbreasts\b/i','/\bbutt-pirate\b/i','/\bcock.*/i','/\bc0ck\b/i','/\bcawk\b/i','/\bchink\b/i','/\bclits\b/i','/\bcum\b/i','/\bcunt.*/i','/.*damn\b\b/i','/.*d4mn\b\b/i','/\bdick.*/i','/\bdike.*/i','/\bdildo\b/i','/.*dyke.*/i','/\bejac.*/i','/\bfag.*/i','/\bfatass\b/i','/\bfat@\$\$\b/i','/\bfata\$\$\b/i','/\bfatas\$\b/i','/\bfata\$s\b/i','/\bfat@\$s\b/i','/\bfat@s\$\b/i','/\bfatarse\b/i','/\bfcuk\b/i','/\bfeces\b/i','/\bfeg\b/i','/\bFelcher\b/i','/\bficken\b/i','/\bforeskin\b/i','/\bFu\(.*/i','/.*fuck.*/i','/\bfuk.*/i','/\bfutkretzn\b/i','/\bfux0r\b/i','/\bfrig\b/i','/\bfrigin.*/i','/\bfriggin.*/i','/\bgay\b/i','/\bgaydar\b/i','/\bgook\b/i','/\bh0r\b/i','/\bhoer.*/i','/\bhonkey\b/i','/\bhore\b/i','/\bjackass\b/i','/\bjism\b/i','/\bjizz\b/i','/\bkawk\b/i','/\bkike\b/i','/\bl3i\+ch\b/i','/\bl3itch\b/i','/\bl3i7ch\b/i','/\bl3!tch\b/i','/\bl3!\+ch\b/i','/\blesbian\b/i','/\blesbo\b/i','/\bmasturbat.*/i','/\bmotherfuck.*/i','/\bmofo\b/i','/\bnigga.*/i','/\bnigger.*/i','/\bnutsack\b/i','/\bpenis\b/i','/\bphuck\b/i','/\bpiss.*/i','/\bpoop\b/i','/\bporn\b/i','/\bp0rn\b/i','/\bpr0n\b/i','/\bprick\b/i','/\bpusse\b/i','/\bpussy\b/i','/\bputa\b/i','/\bputo\b/i','/\bqueef.*/i','/\bqueer.*/i','/\bqweef\b/i','/\bscrotum\b/i','/.*shit.*/i','/\bsh!t.*/i','/\bshemale\b/i','/\bslut\b/i','/\bsmut\b/i','/\bsphencter\b/i','/\bspic\b/i','/\bsplooge\b/i','/\bteets\b/i','/\bb00b.*/i','/\bteez\b/i','/\btesticle.*/i','/\btitt.*/i','/\btits\b/i','/\btwat.*/i','/\bvagina\b/i','/\bviag.*/i','/\bv1ag.*/i','/\bv14g.*/i','/\bvi4g.*/i','/\bvittu\b/i','/\bw00se\b/i','/\bwank.*/i','/\bwetback.*/i','/\bwhoar\b/i','/\bwhore\b/');
    foreach($bad_words as $word) {
        if(preg_match($word, $submission)){
            $points -= 20;
        }
    }

    // Milder bad words -5 point per word matched
    $bad_words = array('/\bamcik\b/i','/\bandskota\b/i','/\barschloch\b/i','/\bayir\b/i','/\bboiolas\b/i','/\bbollock.*/i','/\bbuceta\b/i','/\bcabron\b/i','/\bcazzo\b/i','/\bchraa\b/i','/\bchuj\b/i','/\bcipa\b/i','/\bdago\b/i','/\bdaygo\b/i','/\bdego\b/i','/\bdirsa\b/i','/\bdupa\b/i','/\bdziwka\b/i','/\bEkrem.*/i','/\bEkto\b/i','/\benculer\b/i','/\bfaen\b/i','/\bfanculo\b/i','/\bfanny\b/i','/\bfitt.*/i','/\bFlikker\b/i','/\bFotze\b/i','/\bguiena\b/i','/\bhax0r\b/i','/\bh4xor\b/i','/\bh4x0r\b/i','/\bhell\b/i','/\bhelvete\b/i','/\bHuevon\b/i','/\bhui\b/i','/\binjun\b/i','/\bkanker.*/i','/\bklootzak\b/i','/\bknulle\b/i','/\bkuk\b/i','/\bkuksuger\b/i','/\bKurac\b/i','/\bkurwa\b/i','/\bkusi.*/i','/\bkyrpa.*/i','/\bmamhoon\b/i','/\bmerd.*/i','/\bmibun\b/i','/\bmonkleigh\b/i','/\bmouliewop\b/i','/\bmuie\b/i','/\bmulkku\b/i','/\bmuschi\b/i','/\bnazi.*/i','/\bnepesaurio\b/i','/\borospu\b/i','/\bpaska.*/i','/\bperse\b/i','/\bpicka\b/i','/\bpierdol.*/i','/\bpillu.*/i','/\bpimmel\b/i','/\bpimpis\b/i','/\bpizda\b/i','/\bpoontsee\b/i','/\bpreteen\b/i','/\bpula\b/i','/\bpule\b/i','/\bqahbeh\b/i','/\brautenberg\b/i','/\bschaffer\b/i','/\bscheiss.*/i','/\bschlampe\b/i','/\bschmuck\b/i','/\bscrew\b/i','/\bsharmuta\b/i','/\bsharmute\b/i','/\bshipal\b/i','/\bshiz\b/i','/\bskribz\b/i','/\bskurwysyn\b/i','/\bspierdalaj\b/i','/\bsuka\b/i','/\bwichser\b/i','/\bwop.*/i','/\bwtf\b/i','/\byed\b/i','/\bzabourah\b/');
    foreach($bad_words as $word) {
        if(preg_match($word, $submission)){
            $points -= .25;
        }
    }

    // URLs that have certain TLDs -1 point
    $bad_tld = array('AC','AD','AE','AERO','AF','AG','AI','AL','AM','AN','AO','AQ','AR','ARPA','AS','ASIA','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BIZ','BJ','BM','BN','BO','BR','BS','BT','BV','BW','BY','BZ','CA','CAT','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','COOP','CR','CU','CV','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EDU','EE','EG','ER','ES','ET','EU','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GOV','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU','ID','IE','IL','IM','IN','INFO','INT','IO','IQ','IR','IS','IT','JE','JM','JO','JOBS','JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MG','MH','MIL','MK','ML','MM','MN','MO','MOBI','MP','MQ','MR','MS','MT','MU','MUSEUM','MV','MW','MX','MY','MZ','NA','NAME','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM','PN','PR','PRO','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','ST','SU','SV','SY','SZ','TC','TD','TEL','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TP','TR','TRAVEL','TT','TV','TW','TZ','UA','UG','US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','XN','YE','YT','YU','ZA','ZM','ZW');
    foreach($bad_tld as $tld) {
        if(stripos(".$tld", $submission)!==false){
            $points--;
        }
    }

    // URL length More than 30 characters -1 point
    if(preg_match_all('!https*://[^\s]+!i', $submission, $matches)){
        foreach($matches[0] as $match){
            if(strlen($match>30)){
                $points--;
            }
        }
    }

    // Body starts with common spammy words-2 points
    if(preg_match('/^(Hello|Hi|Interesting|Sorry|Nice|Cool|Thanks|Wow)\b/i', $submission)){
        $points -= 2;
    }

    // Random character match 5 consonants -1 point per
    if(preg_match_all('/[bcdfghjklmnpqrstvwxz][bcdfghjklmnpqrstvwxz][bcdfghjklmnpqrstvwxz][bcdfghjklmnpqrstvwxz][bcdfghjklmnpqrstvwxz]/i', $submission, $match)){
        $points -= count($match[0]);
    }


    return $points;
}


?>
