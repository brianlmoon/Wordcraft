<?php

require_once(dirname(__FILE__) . '/CaptchaBase.php');

class CaptchaPlaintext extends CaptchaBase {

    function generate_captcha_html($question) {

        $captcha = '<div id="spamhurdles_captcha_image">'.$question.'</div>';
        return array($captcha, "");
    }
}
?>
