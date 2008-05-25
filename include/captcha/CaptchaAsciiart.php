<?php

require_once dirname(__FILE__) . '/CaptchaBase.php';
require_once dirname(__FILE__) . '/Banner.php';

class CaptchaAsciiart extends CaptchaBase {

    public function generate_captcha_html($question) {

        $PHORUM = $GLOBALS["PHORUM"];

        // We only have upper case chars in our banner class.
        $question = strtoupper($question);

        // Create a bitmap for the generated question.
        $banner = new CaptchaBanner("banner_large.fnt");
        $asciiart = implode("\n", $banner->format($question));
        $asciiart = str_replace("#", "*", $asciiart);

        // Create the HTML code for the captcha.
        $captcha = "<div id=\"spamhurdles_captcha_image\">" .
        "<pre id=\"spamhurdles_captcha_asciiart\">" .
        $asciiart .
        "</pre>" .
        "</div>";

        return array($captcha, "");
    }
}
?>
