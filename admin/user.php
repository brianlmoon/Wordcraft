<?php

/**
 * Create/Edit a user entry
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

require_once "./admin_common.php";
require_once "./admin_functions.php";


// check the mode
if(isset($_POST["mode"])){
    $mode = $_POST["mode"];
} elseif(isset($_GET["mode"])){
    $mode = $_GET["mode"];
} else {
    $mode = "new";
}

if($mode!="new" && $mode!="edit"){
    wc_admin_error("Invalid mode '".htmlspecialchars($mode)."' for user page.");
}

if($mode=="edit" && empty($_GET["user_id"]) && empty($_POST["user_id"])){
    wc_admin_error("No user_id provided for edit mode.");
}


// init error to empty
$error = "";


// check for POST data
if(count($_POST)){

    if(empty($_POST["user_name"]) || empty($_POST["email"])) {

        $error = "You must fill in a User Name and an Email address.";

    }

    if(empty($error)){

        $user_array = array(
            "user_id"    => $_POST["user_id"],
            "user_name"  => $_POST["user_name"],
            "email"      => $_POST["email"],
            "first_name" => $_POST["first_name"],
            "last_name"  => $_POST["last_name"],
            "about"      => $_POST["editor"],
        );

        $success = wc_db_save_user($user_array);

        if($success){
            wc_admin_message("User Saved!", true, "users.php");
        } else{
            $error = "There was an error saving the user.";
        }
    }

    if(!empty($error)){

        // setup the form with the posted data if there is an error
        $user_id = $_POST["user_id"];
        $user_name = $_POST["user_name"];
        $user_first_name = $_POST["first_name"];
        $user_last_name = $_POST["last_name"];
        $user_email = $_POST["email"];
        $user_about = $_POST["about"];
    }

} else {

    // check for initial edit mode
    if(isset($_GET["user_id"])){

        $user = wc_db_get_user($_GET["user_id"]);

        if(!empty($user)){
            $user_id = $user["user_id"];
            $user_name = $user["user_name"];
            $user_first_name = $user["first_name"];
            $user_last_name = $user["last_name"];
            $user_email = $user["email"];
            $user_about = $user["about"];
        } else {
            wc_admin_error("The user you requested to edit was not found.");
        }

    } else {

        // set up new user form
        $user_id = "";
        $user_name = "";
        $user_fist_name = "";
        $user_last_name = "";
        $user_email = "";
        $user_about = "";
    }

}


// set breadcrumb
$WHEREAMI = ($mode=="edit") ? "Edit User" : "New User";

$WC_ADMIN_EDITOR = true;

// begin output
require_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="user.php" id="user-form">

    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

    <p>
        <strong>User Name:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_name); ?>" id="user_name" name="user_name" maxlength="20">
    </p>

    <p>
        <strong>Email:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_email); ?>" id="email" name="email" maxlength="50">
    </p>

    <p>
        <strong>First Name:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_first_name); ?>" id="first_name" name="first_name" maxlength="25">
    </p>

    <p>
        <strong>Last Name:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_last_name); ?>" id="last_name" name="last_name" maxlength="25">
    </p>

    <p>
        <strong>About This User:</strong><br>
        <textarea id="editor" name="editor" rows="20" cols="75"><?php echo htmlspecialchars($user_about); ?></textarea>
    </p>

    <p>
        <input class="button" type="submit" value="Save">
    </p>

</form>

<?php

require_once "./footer.php";

?>
