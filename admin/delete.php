<?php

require_once "./admin_common.php";
require_once "./admin_functions.php";

if(!empty($_POST["post_id"]) && is_numeric($_POST["post_id"])){

    if($_POST["delete"]=="Yes"){

        wc_db_delete_post($_POST["post_id"]);

        wc_admin_message("Post deleted.", true, "index.php");

    } else {

        header("Location: index.php");
        exit();
    }

} elseif(!empty($_POST["page_id"]) && is_numeric($_POST["page_id"])){

    if($_POST["delete"]=="Yes"){

        wc_db_delete_page($_POST["page_id"]);

        wc_admin_message("Page deleted.", true, "pages.php");

    } else {

        header("Location: index.php");
        exit();
    }


} else {

    if(isset($_GET["post_id"])){
        $post_id = (int)$_GET["post_id"];
        $object = "post";
    } elseif(isset($_GET["page_id"])) {
        $page_id = (int)$_GET["page_id"];
        $object = "page";
    } else {
        wc_admin_error("Invalid input for deletion.");
    }
}

if($object=="post"){
    $post = wc_db_get_post($_GET["post_id"]);
    $subject = $post["subject"];
} elseif($object=="page"){
    $page = wc_db_get_page($_GET["page_id"]);
    $subject = $page["title"];
}

$WHEREAMI = "Delete";

require_once "./header.php";

$x = 1;

?>

<div id="delete">

    <h3>Are you sure you wish to delete this <?php echo $object; ?>?</h3>

    <h1><?php echo $subject; ?></h1>
    <form action="delete.php" method="post">
        <?php if($object=="post") { ?>
            <input type="hidden" name="post_id" value="<?php echo $post["post_id"]; ?>">
        <?php } elseif($object=="page") { ?>
            <input type="hidden" name="page_id" value="<?php echo $page["page_id"]; ?>">
        <?php } ?>
        <input type="submit" name="delete" value="Yes">&nbsp;&nbsp;<input type="submit" name="delete" value="No">
    </form>

</div>

<?php require_once "./footer.php"; ?>

