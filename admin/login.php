<?php

/**
 * Login form and login checking
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

include_once "../include/common.php";
include_once "./admin_functions.php";

$error = false;

if(count($_POST)){

    if(empty($_POST["user_name"]) || empty($_POST["password"])){

        $error = "You must fill in both User Name and Password";

    } else {

        include_once "../include/database.php";

        $user_id = wc_db_check_login($_POST["user_name"], $_POST["password"]);

        if($user_id<=0) {

            $error = "The User Name and/or Password you entered was not found";

        } else {

            $_SESSION["wc_user_id"] = $user_id;

            session_write_close();

            if(empty($_SERVER["HTTP_REFERER"])){
                $redir = "index.php";
            } else {
                $redir = $_SERVER["HTTP_REFERER"];
            }

            header("Location: $redir");
            exit();

        }

    }

}

header("Content-Type: text/html; charset=UTF-8");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
    <title>Wordcraft Login</title>

    <link rel="stylesheet" type="text/css" href="./atrandafir582.css" media="screen,print">
    <link rel="stylesheet" type="text/css" href="./admin.css" media="screen,print">

</head>
<body>

<div id="login">

    <form action="login.php" method="post">

    <h1>Wordcraft! <?php echo WC; ?></h1>

    <?php
        if(!empty($error)) {
            wc_admin_message($error, false);
        }
    ?>

    <p>
        <strong>User Name:</strong><br>
        <input class="inputgri" type="text" value="" name="user_name" id="user_name" maxlength="20">
    </p>

    <p>
        <strong>Password:</strong><br>
        <input class="inputgri" type="password" value="" name="password" id="password" maxlength="64">
    </p>

    <p>
        <input type="checkbox" id="remember" name="remember" value="1"><label for="remember">Remember me on this computer</label>
    </p>

    <input class="button" type="submit" value="Login">

    </form>

</div>

</body>
</html>
