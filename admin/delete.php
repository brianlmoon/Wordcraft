<?php

include_once "./check_auth.php";
include_once "./admin_functions.php";

if(!empty($_POST["post_id"]) && is_numeric($_POST["post_id"])){

    if($_POST["delete"]=="Yes"){

        wc_db_delete_post($_POST["post_id"]);

        wc_admin_message("Post deleted.");

    } else {

        header("Location: index.php");
        exit();
    }

} elseif(empty($_GET["post_id"]) || !is_numeric($_GET["post_id"])){
    wc_admin_error("Invalid input for post_id.");
}

$post = wc_db_get_post($_GET["post_id"]);

$WHEREAMI = "Delete";

include_once "./header.php";

$x = 1;

?>

<div id="delete">

    <h3>Are you sure you wish to delete this post?</h3>

    <h1><?php echo $post["subject"]; ?></h1>
    <form action="delete.php" method="post">
        <input type="hidden" name="post_id" value="<?php echo $post["post_id"]; ?>" />
        <input type="submit" name="delete" value="Yes" />&nbsp;&nbsp;<input type="submit" name="delete" value="No" />
    </form>

</div>

<?php include_once "./footer.php"; ?>

