<?php

include_once "./check_auth.php";
include_once "./admin_functions.php";
include_once "../include/config.php";

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
                $clean_arr[$name] = $data;
                break;

            default:
                wc_admin_error("Invalid post data $name sent.");
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
        <input type="checkbox" value="1" <?php if(!empty($settings["use_rewrite"])) echo "checked"; ?> id="use_rewrite" name="use_rewrite" /> Yes
    </p>

    <h2>Spam Prevention</h2>

    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["use_captcha"])) echo "checked"; ?> id="use_captcha" name="use_captcha" /> Use Captcha</strong><br />
    </p>

    <p>
        <strong><input type="checkbox" value="1" <?php if(!empty($settings["use_akismet"])) echo "checked"; ?> id="use_akismet" name="use_akismet" /> Use Aksimet</strong><br />
        <strong>Akismet Key:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($settings["akismet_key"]); ?>" id="akismet_key" name="akismet_key" /><br />
        See <a href="http://akismet.com/">http://akismet.com/</a>
    </p>

    <p>
        <input class="button" type="submit" value="Save" />
    </p>

</form>

<?php include_once "./footer.php"; ?>

