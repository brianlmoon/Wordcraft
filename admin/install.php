<?php

define("WC_INSTALLING", true);

// do this so session check will not fail
ob_start();

include_once "../include/common.php";
include_once "./admin_functions.php";

$steps = array("", "check", "database", "account", "settings", "done");

$step = (empty($_POST["step"])) ? "" : $_POST["step"];

if(!in_array($step, $steps)){
    wc_admin_error("Invalid step $step for install program.");
}

$continue = true;

$WHEREAMI = "Install";

include_once "./header.php";
?>

<h1>Welcome to the Wordcraft installer!</h1>

<?php if(empty($step)){ ?>

    <p>Here is what we are going to do.</p>

    <ol>
        <li>Check for requirements</li>
        <li>Setup the database</li>
        <li>Install default settings</li>
        <li>Create your first account</li>
    </ol>

    <?php $step = "check"; ?>

<?php } elseif($step=="check") { ?>

    <?php

    if(ini_get("safe_mode")!=0 || ini_get("open_basedir")!=""){
        if(!defined("WC")){
            $show_stoppers[] = "Safe mode is enabled on this server.  This is includes the open_basedir option.  This will prevent Wordcraft from working.  Safe mode is no longer recommended by the PHP team.  You should disable it.";
        } else {
            $warning[] = "Safe mode is enabled on this server.  This may cause problems that prevent Wordcraft from working.  Safe mode is no longer recommended by the PHP team.  You should disable it.";
        }
    } else {
        $ok[] = "Safe mode check passed.";
    }

    if(!function_exists("mysqli_connect")){
        $show_stoppers[] = "The MySQL Improved (mysqli) extension is not available on this server.  You will need to have this installed to use Wordcraft.";
    } else {
        $ok[] = "MySQL check passed.";
    }

    if(!function_exists("session_start") || !session_start()){
        $warnings[] = "PHP Sessions are not available on this server.  You will not be able to use the Captcha spam prevention.";
    } else {
        $ok[] = "Sessions check passed.";
    }

    if(get_magic_quotes_runtime()){
        $show_stoppers[] = "Magic quotes runtime has been detected.  This is a horrible idea.  It is such a bad idea that PHP has removed it in PHP6. Wordcraft will not work with this feature on.";
    } else {
        $ok[] = "Magic quotes check passed.";
    }

    $message = "";

    if(isset($ok)){
        $message.= "Good news, these items are just fine.<br /><span style=\"color: green;\">";
        $message.= implode("<br />", $ok);
        $message.= "</span>";
        if(empty($show_stoppers) && empty($warnings)){
            $message.="<br />Hit continue and we can keep going.";
        }
    }

    if(isset($show_stoppers)){
        $message.= "Some major problems are preventing us from continuing.<br /><span style=\"color: red;\">";
        $message.= implode("<br />", $show_stoppers);
        $message.= "</span>";
        $continue = false;
    } else {
        $step = "database";
    }

    if(isset($warnings)){
        $message.= "Hmm, there are a few things that may be a problem for Wordcraft.<br /><span style=\"color: orange;\">";
        $message.= implode("<br />", $warnings);
        $message.= "</span><br />If you think you can live with these problems, go ahead and continue.";
    }


    ?>

<?php } elseif($step=="database") { ?>

    <?php

        $WCDB->report_errors(false);

        $conn_check = $WCDB->check_connection();

        if($conn_check !== true){

            $message = "Oops!  The connection to your database failed.  Please check your database settings in config.php and try again.  The database server said: '$conn_check'.";
            $continue = false;

        } else {

            $schema = file_get_contents("../include/schema.sql");

            $schema = str_replace("{PREFIX}", $WC["db_prefix"], $schema);

            preg_match_all('!CREATE.+?;!ism', $schema, $matches);

            $WCDB->query("BEGIN");

            foreach($matches[0] as $sql){
                // trim off the ;
                $sql = substr($sql, 0, -1);

                $success = $WCDB->query($sql);

                if(!$success){
                    $message = "Oops! There was an error creating some of the tables for your database.  The server said: '".$WCDB->last_error."'.";
                    $continue = false;
                    $WCDB->query("ROLLBACK");
                    break;
                }

            }

            if($continue){
                $WCDB->query("COMMIT");
                $message = "Great!  The database was created without error.  Next we will create your first user account.  Go ahead, click continue.";
            }

        }

        if($continue) $step = "settings";
    ?>

<?php } elseif($step=="settings") { ?>

    <?php
        $default_settings = array(
           "use_akismet" => 0,
           "use_captcha" => 0,
           "use_rewrite" => 0,
           "akismet_key" => "",
           "base_url" => "http://".$_SERVER["HTTP_HOST"].dirname(dirname($_SERVER["REQUEST_URI"])),
           "date_format_long" => "%a, %b %e, %Y %I:%M %p",
           "date_format_short" => "%D %I:%M %p",
           "default_description" => "A simple blogging application.",
           "default_title" => "Wordcraft ".WC,
           "session_days" => 30,
           "session_domain" => "",
           "session_path" => "/",
           "session_secret" => uniqid(),
           "template" => "terrafirma",
           "moderate_all" => 0,
           "email_comment" => "spam",
           "allow_comments" => 1,
           "send_linkbacks" => 1,
        );

        $success = wc_db_save_settings($default_settings);

        if(!$success){
            $message = "Well, something went wrong trying to save the default settings.  Maybe this database error will help you: '".$WCDB->last_error."'.";
            $continue = false;
        } else {
            $message = "Okay, the settings are saved.  Let's keep going.  Hit continue.";
            $step = "account";
        }

    ?>

<?php } elseif($step=="account") { ?>

    <?php

        // set this false as it uses its own form
        $continue = false;

        if(count($_POST) && isset($_POST["user_name"])){

            if(empty($_POST["user_name"])){
                $errors[] = "Wordcraft will need a user name.";
            }

            if(empty($_POST["email"])){
                $errors[] = "Wordcraft will need an email address.";
            }

            if(empty($_POST["password1"]) || empty($_POST["password2"])) {

                $errors[] = "Please fill in both password fields.";

            } else if($_POST["password1"]!=$_POST["password2"]) {

                $errors[] = "Passwords do not match.";

            }

            if(empty($errors)){
                $user_array = array(
                    "user_name"  => $_POST["user_name"],
                    "email"      => $_POST["email"],
                    "password"   => $_POST["password1"],
                );

                $WCDB->report_errors(false);

                $success = wc_db_save_user($user_array);

                if($success){

                    $message = "Woohoo!  Your user was created just fine.  Hit continue and we will finish up.";
                    $step = "done";
                    $continue = true;
                }
            }

            if(!$success || !empty($errors)){

                if(!empty($errors)){
                    $message = "Wordcraft needs a little more information.<br />";
                    $message.= implode("<br />", $errors);
                } else {
                    $message = "Uh Oh!  Your user could not be created.  The database said '".$WCDB->last_error."'.";
                }

                $user_name  = $_POST["user_name"];
                $user_email = $_POST["user_email"];
            }

        } else {
            $user_name = "";
            $user_email = "";
        }

    ?>

    <?php if(!$continue) { ?>

        <h2>Create your first user</h2>

        <?php if(!empty($message)){ ?>
            <p><?php echo htmlspecialchars($message); ?></p>
            <?php unset($message); ?>
        <?php } ?>

        <form action="install.php" method="post">
            <input type="hidden" name="step" value="<?php echo $step; ?>" />

            <p>
                <strong>User Name:</strong><br />
                <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_name); ?>" id="user_name" name="user_name" maxlength="20" />
            </p>

            <p>
                <strong>Email:</strong><br />
                <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_email); ?>" id="email" name="email" maxlength="50" />
            </p>

            <p>
                <strong>Password:</strong><br />
                <input class="inputgri" type="password" autocomplete="off" value="" id="password1" name="password1" />
            </p>

            <p>
                <strong>Confirm Password:</strong><br />
                <input class="inputgri" type="password" autocomplete="off" value="" id="password2" name="password2" />
            </p>

            <input type="submit" value="Continue >" />

        </form>
    <?php } ?>

<?php } elseif($step=="done") { ?>

    <?php $continue = false; ?>

    <h2>Happy days!</h2>

    <p>Congratulations, that is it!  Wordcraft is installed.  You probably want to <a href="index.php">login to the admin</a> now and have a look at the Settings page.</p>

    <h3>A note about Spam Prevention</h3>

    <p>By default, there is no spam prevention enabled.  Wordcraft has two methods for spam prevention: Captcha and Akismet.  Wordcraft believes Akismet is the way to go.  Captcha can be useful, but Akismet is much more user friendly and has a great track record.</p>

<?php } ?>

<?php if(!empty($message)){ ?>
    <p><?php echo $message; ?></p>
<?php } ?>

<?php if($continue) { ?>
<form action="install.php" method="post">
    <input type="hidden" name="step" value="<?php echo $step; ?>" />
    <input type="submit" value="Continue >" />
</form>
<?php } ?>

<?php include_once "./footer.php"; ?>

