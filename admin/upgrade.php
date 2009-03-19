<?php

/**
 * Install script that handles sanity checks and sets up the database
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

define("WC_INSTALLING", true);

// do this so session check will not fail
ob_start();

require_once "../include/common.php";
require_once "./admin_functions.php";

require_once "./header.php";

if(!isset($WC["db_version"]) || $WC["db_version"] != WC_DB_VERSION){

    $pending_upgrades = array();

    $d = dir("../include/upgrade");
    while (false !== ($entry = $d->read())) {
        $ver = (int)$entry;
        if($ver > $WC["db_version"]){

            $pending_upgrades[] = $entry;
        }
    }
    $d->close();

    $current = array_shift($pending_upgrades);
    if(count($pending_upgrades)){
        $next = (int)array_shift($pending_upgrades);
    }

    $version = (int)$current;

    $schema = file_get_contents("../include/upgrade/$current");

    $schema = str_replace("{PREFIX}", $WC["db_prefix"], $schema);

    preg_match_all('!^[a-z].+?;!ism', $schema, $matches);

    $WCDB->query("BEGIN");

    $success = true;

    foreach($matches[0] as $sql){
        // trim off the ;
        $sql = substr($sql, 0, -1);

        $success = $WCDB->query($sql);

        if(!$success){
            wc_admin_error("Oops! There was an error upgrarding your database.  The server said: '".$WCDB->last_error."'.", false);
            $continue = false;
            $WCDB->query("ROLLBACK");
            break;
        }

    }

    if($success){
        $WCDB->query("COMMIT");

        wc_db_save_settings(array("db_version"=>$version));

        wc_admin_message("Great!  The database was updated to version $version without error.", false);
        ?>

        <?php if(isset($next)){ ?>
            <p>
            Press "Continue" to upgrade the datbase to version <?php echo $next; ?>.
            </p>
            <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                <input type="submit" value="Continue">
            </form>

        <?php } ?>


        <?php
    }


} else {

    wc_admin_message("Your database is up to date.");

}

require_once "./footer.php";

?>

