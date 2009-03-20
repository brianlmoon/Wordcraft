<?php

/**
 * Admin page for moderating comments
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
require_once "../include/format.php";
require_once "../include/spam.php";

if(count($_POST) && ($_POST["mode"]=="delete_spam" || (!empty($_POST["comment_id"]) && is_numeric($_POST["comment_id"])))){

    if($_POST["confirm"] != "Yes"){
        header("Location: comments.php");
        exit();
    }

    if($_POST["mode"]!="delete_spam"){
        $comment = wc_db_get_comment($_POST["comment_id"]);

        if(empty($comment)){
            wc_admin_error("Comment not found.");
        }
    }

    switch($_POST["mode"]){

        case "delete":
            wc_db_delete_comment($_POST["comment_id"]);
            wc_admin_message("Comment deleted.", true, "comments.php");
            break;
        case "spam":
            wc_akismet_request( $comment, "submit-spam" );
            wc_db_delete_comment($_POST["comment_id"]);
            wc_admin_message("Comment flagged as spam and deleted.", true, "comments.php");
            break;
        case "approve":
            wc_akismet_request( $comment, "submit-ham" );
            wc_db_save_comment(array("comment_id"=>$_POST["comment_id"], "status"=>"APPROVED"));
            wc_admin_message("Comment approved.", true, "comments.php");
            break;
        case "hide":
            wc_db_save_comment(array("comment_id"=>$_POST["comment_id"], "status"=>"UNAPPROVED"));
            wc_admin_message("Comment hidden.", true, "comments.php");
            break;
        case "delete_spam":
            wc_db_delete_spam();
            wc_admin_message("Spam deleted.", true, "comments.php");
            break;
        default:
            wc_admin_error("Invalid mode ".htmlspecialchars($_POST["mode"])." for comment moderation.", ENT_COMPAT, "UTF-8");

    }

} elseif(empty($_GET["mode"])){
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
    case "delete_spam":
        $question = "delete all comments marked as spam";
        break;
    default:
        wc_admin_error("Invalid mode ".htmlspecialchars($_POST["mode"])." for comment moderation.", ENT_COMPAT, "UTF-8");

}

if($_GET["mode"]!="delete_spam"){
    $comment = wc_db_get_comment($_GET["comment_id"]);

    if(empty($comment)){
        wc_admin_error("Comment not found.");
    }

    wc_format_comment($comment);

    $post = wc_db_get_post($comment["post_id"]);
    wc_format_post($post);
}

$WHEREAMI = "Comment Moderation";

require_once "./header.php";

?>

<div id="delete">

    <h3>Are you sure you wish to <?php echo $question; ?>?</h3>

    <?php if(!empty($comment)) { ?>
        <h1>Comment by <?php echo $comment["name"]; ?>. In response to <?php echo $post["subject"]; ?></h1>
    <?php } ?>
    <form action="comment_moderate.php" method="post">
        <input type="hidden" name="comment_id" value="<?php echo (int)$_GET["comment_id"]; ?>">
        <input type="hidden" name="mode" value="<?php echo $_GET["mode"]; ?>">
        <input type="submit" name="confirm" value="Yes">&nbsp;&nbsp;<input type="submit" name="confirm" value="No">
    </form>

</div>

<?php require_once "./footer.php"; ?>

