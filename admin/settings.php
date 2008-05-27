<?php

include_once "./check_auth.php";
include_once "../include/common.php";
include_once "./admin_functions.php";
include_once "../include/akismet.php";

if(count($_POST)){

    $clean_arr = array();

    foreach($_POST as $name=>$data){

        switch($name){
            case "base_url":
            case "session_secret":
            case "session_path":
            case "session_domain":
            case "date_format_long":
            case "date_format_short":
            case "template":
            case "default_title":
            case "default_description":
            case "akismet_key":
            case "session_days":
            case "use_rewrite":
            case "use_captcha":
            case "use_akismet":
            case "moderate_all":
            case "email_comment":
            case "allow_comments":
                $clean_arr[$name] = $data;
                break;

            default:
                wc_admin_error("Invalid post data $name sent.");
        }
    }

    // default check boxes to 0
    if(empty($clean_arr["use_rewrite"])) $clean_arr["use_rewrite"] = 0;
    if(empty($clean_arr["use_captcha"])) $clean_arr["use_captcha"] = 0;
    if(empty($clean_arr["use_akismet"])) $clean_arr["use_akismet"] = 0;
    if(empty($clean_arr["moderate_all"])) $clean_arr["moderate_all"] = 0;
    if(empty($clean_arr["allow_comments"])) $clean_arr["allow_comments"] = 0;

    // check askismet key
    if($clean_arr["use_akismet"]){
        if(empty($clean_arr["akismet_key"])){
            wc_admin_error("To use Akismet, you must provide an Akismet key.");
        } else {
            $ret = wc_akismet_request($clean_arr["akismet_key"], "verify-key");
            if($ret!="valid"){
                wc_admin_error("The Akismet key you entered could not be verified with the Akismet service.");
            }
        }
    }

    $success = wc_db_save_settings($clean_arr);

    if(!$success){
        wc_admin_error("Settings could not be saved.");
    } else {
        wc_admin_message("Settings saved!");
    }

}

// get a fresh array, don't use $WC
$settings = wc_db_get_settings();

$template_options = "";
$dir = dir("../templates");
while(false !== ($d=$dir->read())){
    if(is_dir("../templates/$d") && file_exists("../templates/$d/info.php")){
        include "../templates/$d/info.php";
        $template_options.= "<option value=\"$d\"";
        if($settings["template"]==$d) $template_options.=" selected";
        $template_options.= ">$name</option>\n";
    }
}

$WHEREAMI = "Settings";

include_once "./header.php";

?>

<form method="post" action="settings.php" id="settings-form">

    <h2>General Settings</h2>

    <h2>Template and Content</h2>

    <p>
        <strong>Template:</strong><br />
        <select class="inputgri" name="template" id="template">
            <?php echo $template_options; ?>
        </select>
    </p>

    <p>
        <strong>Default HTML Title:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["default_title"]); ?>" id="default_title" name="default_title" />
    </p>

    <p>
        <strong>Default HTML META Description:</strong><br />
        <textarea class="inputgri" id="default_description" name="default_description"><?php echo htmlspecialchars($settings["default_description"]); ?></textarea>
    </p>

    <p>
        <strong>Search Engine Friendly URLs:</strong><br />
        <input type="checkbox" value="1" <?php if(!empty($settings["use_rewrite"])) echo "checked"; ?> id="use_rewrite" name="use_rewrite" /> <label for="use_rewrite">Yes</label>
    </p>

    <h2>Comment Moderation</h2>

    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["allow_comments"])) echo "checked"; ?> id="allow_comments" name="allow_comments" /> <label for="allow_comments">Allow comments by default on new posts</label></strong><br />
    </p>


    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["moderate_all"])) echo "checked"; ?> id="moderate_all" name="moderate_all" /> <label for="moderate_all">Approve all comments</label></strong><br />
    </p>

    <p>
        <strong>Email the author when:</strong><br />
        <input type="radio" value="all" <?php if($settings["email_comment"]=="all") echo "checked"; ?> id="email_comment_all" name="email_comment" /> <label for="email_comment_all">Any comment is posted.</label><br />
        <input type="radio" value="spam" <?php if($settings["email_comment"]=="spam") echo "checked"; ?> id="email_comment_spam" name="email_comment" /> <label for="email_comment_spam">Comments marked as spam.</label><br />
        <input type="radio" value="none" <?php if($settings["email_comment"]=="none") echo "checked"; ?> id="email_comment_spam" name="email_comment" /> <label for="email_comment_spam">Never email comments.</label>
    </p>

    <h2>Spam Prevention</h2>

    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["use_captcha"])) echo "checked"; ?> id="use_captcha" name="use_captcha" /> <label for="use_captcha">Use Captcha</label></strong><br />
    </p>

    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["use_akismet"])) echo "checked"; ?> id="use_akismet" name="use_akismet" /> <label for="use_akismet">Use Aksimet</label></strong><br />
        <strong>Akismet Key:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["akismet_key"]); ?>" id="akismet_key" name="akismet_key" /><br />
        See <a href="http://akismet.com/">http://akismet.com/</a>
    </p>

    <p>
        <strong>Base URL:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["base_url"]); ?>" id="base_url" name="base_url" />
    </p>

    <h2>Session Settings</h2>

    <p>
        <strong>Session Cookie Expires in Days:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["session_days"]); ?>" id="session_days" name="session_days" />
    </p>

    <p>
        <strong>Session Cookie Path:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["session_path"]); ?>" id="session_path" name="session_path" />
    </p>

    <p>
        <strong>Session Cookie Domain:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["session_domain"]); ?>" id="session_domain" name="session_domain" />
    </p>

    <p>
        <strong>Session Cookie Secret:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["session_secret"]); ?>" id="session_secret" name="session_secret" />
    </p>


    <h2>Date Formats</h2>

    <p>See: <a href="http://www.php.net/strftime">http://www.php.net/strftime</a></p>

    <p>
        <strong>Long Date Format:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["date_format_long"]); ?>" id="date_format_long" name="date_format_long" />
    </p>

    <p>
        <strong>Short Date Format:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["date_format_short"]); ?>" id="date_format_short" name="date_format_short" />
    </p>


    <p>
        <input class="button" type="submit" value="Save" />
    </p>

</form>

<?php include_once "./footer.php"; ?>

