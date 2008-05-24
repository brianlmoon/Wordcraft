<?php

include_once "./check_auth.php";
include_once "./admin_functions.php";
include_once "../include/format.php";
include_once "../include/akismet.php";

if(!empty($_POST["comment_id"]) && is_numeric($_POST["comment_id"])){

    $comment = wc_db_get_comment($_POST["comment_id"]);

    if(empty($comment)){
        wc_admin_error("Comment not found.");
    }

    switch($_POST["mode"]){

        case "delete":
            wc_db_delete_comment($_POST["comment_id"]);
            wc_admin_message("Comment deleted.");
            break;
        case "spam":
            wc_akismet_request( $comment, "report-spam" );
            wc_db_delete_comment($_POST["comment_id"]);
            wc_admin_message("Comment flagged as spam and deleted.");
            break;
        case "approve":
            wc_akismet_request( $comment, "report-ham" );
            wc_admin_message("Comment approved.");
            break;
        case "hide":

            wc_admin_message("Comment hidden.");
            break;
        default:
            wc_admin_error("Invalid mode ".htmlspecialchars($_POST["mode"])." for comment moderation.");

    }

} elseif(empty($_GET["mode"]) || empty($_GET["comment_id"]) || !is_numeric($_GET["comment_id"])){

    wc_admin_error("Invalid input for comment moderation.");
}

switch($_GET["mode"]){

    case "delete":
        $question = "delete this comment";
        break;
    case "spam":
        $question = "delete this comment and flag it as spam";
        break;
    case "approve":
        $question = "approve this comment";
        break;
    case "hide":
        $question = "hide this comment";
        break;
    default:
        wc_admin_error("Invalid mode ".htmlspecialchars($_POST["mode"])." for comment moderation.");

}

$comment = wc_db_get_comment($_GET["comment_id"]);

if(empty($comment)){
    wc_admin_error("Comment not found.");
}

wc_format_comment($comment);

$post = wc_db_get_post($comment["post_id"]);
wc_format_post($post);

$WHEREAMI = "Comment Moderation";

include_once "./header.php";

?>

<div id="delete">

    <h3>Are you sure you wish to <?php echo $question; ?>?</h3>

    <h1>Comment by <?php echo $comment["name"]; ?>. In response to <?php echo $post["subject"]; ?></h1>
    <form action="comment_moderate.php" method="post">
        <input type="hidden" name="comment_id" value="<?php echo (int)$_GET["comment_id"]; ?>" />
        <input type="hidden" name="mode" value="<?php echo $_GET["mode"]; ?>" />
        <input type="submit" name="confirm" value="Yes" />&nbsp;&nbsp;<input type="submit" name="confirm" value="No" />
    </form>

</div>

<?php include_once "./footer.php"; ?>

