<?php

include_once "./check_auth.php";
include_once "./admin_functions.php";

if(empty($_GET["user_id"]) && empty($_POST["user_id"])){
    wc_admin_error("No user_id provided.");
} elseif(isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
} elseif(isset($_GET["user_id"])) {
    $user_id = $_GET["user_id"];
}

// init error to empty
$error = "";


// check for POST data
if(count($_POST)){

    if(empty($_POST["password1"]) || empty($_POST["password2"])) {

        $error = "Please fill in both password fields.";

    } else if($_POST["password1"]!=$_POST["password2"]) {

        $error = "Passwords do not match.";

    }

    if(empty($error)){

        $user_array = array(
            "user_id"    => $_POST["user_id"],
            "password"   => $_POST["password1"],
        );

        $success = wc_db_save_user($user_array);

        if($success){
            wc_admin_message("User Password Saved!", true, "users.php");
        } else{
            $error = "There was an error saving the user.";
        }
    }

}


if(isset($user_id)){
    // check for initial edit mode

    $user = wc_db_get_user($user_id);

    $user_id = $user["user_id"];

    if(empty($user)){
        wc_admin_error("The user you requested to edit was not found.");
    }

}


// set breadcrumb
$WHEREAMI = "Set User Password";


// begin output
include_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="user_password.php" id="user-form">

    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>" />

    <p>
        <strong>User Name: </strong><?php echo $user["user_name"]; ?>
    </p>

    <p>
        <strong>Password:</strong><br />
        <input class="inputgri" type="password" autocomplete="off" value="" id="password1" name="password1" />
    </p>
    <p>
        <strong>Confirm Password:</strong><br />
        <input class="inputgri" type="password" autocomplete="off" value="" id="password2" name="password2" />
    </p>
    <p>
        <input class="button" type="submit" value="Save" />
    </p>

</form>

<?php

include_once "./footer.php";

?>
