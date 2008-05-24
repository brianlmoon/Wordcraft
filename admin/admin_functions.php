<?php

function wc_admin_error($error_message, $exit=true) {

    global $WC, $USER;

    if($exit) include_once "./header.php";

    ?>
        <div class="notice_error">
            <?php echo $error_message; ?>
        </div>
    <?php

    if($exit) include_once "./footer.php";

    if($exit){
        exit();
    }
}

function wc_admin_message($message, $exit=true) {

    global $WC, $USER;

    if($exit) include_once "./header.php";

    ?>
        <div class="notice">
            <?php echo $message; ?>
        </div>
    <?php

    if($exit) include_once "./footer.php";

    if($exit){
        exit();
    }
}


?>
