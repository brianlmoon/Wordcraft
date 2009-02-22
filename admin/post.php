<?php

/**
 * Creates/Edits a post
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

include_once "../include/common.php";
include_once "./check_auth.php";
include_once "./admin_functions.php";

// check the mode
if(isset($_POST["mode"])){
    $mode = $_POST["mode"];
} elseif(isset($_GET["mode"])){
    $mode = $_GET["mode"];
} elseif(isset($_GET["post_id"])) {
    $mode = "edit";
} else {
    $mode = "new";
}

if($mode!="new" && $mode!="edit"){
    wc_admin_error("Invalid mode '".htmlspecialchars($mode)."' for post page");
}

if($mode=="edit" && empty($_GET["post_id"]) && empty($_POST["post_id"])){
    wc_admin_error("No post_id provided for edit mode.");
}


// init error to empty
$error = "";


// check for post data
if(count($_POST)){

    if(empty($_POST["subject"]) || empty($_POST["editor"])) {

        $error = "You must fill in a Subject and a Post.";

    }

    if(isset($_POST["custom_date"])){
        $ts = strtotime($_POST["date"]);
        if(empty($ts)){
            $error = "Sorry, I don't recognize the date ".$_POST["date"];
        } else {
            $post_date = date("Y-m-d H:i:s", $ts);
        }
    } elseif($mode=="new"){
        $post_date = date("Y-m-d H:i:s");
    }

    if(empty($_POST["post_id"]) && (empty($_POST["custom_uri"]) || empty($_POST["uri"]))){
        $post_uri = trim(strtolower(preg_replace("![^a-z0-9_]+!i", "-", $_POST["subject"])));
    } else {
        $post_uri = $_POST["uri"];
    }

    $post = wc_db_lookup_uri($post_uri);
    if(!empty($post) && $post["object_id"]!=$_POST["post_id"]){
        $error = "The URI you entered is already in use by another page or post.";
    }

    if($_POST["save_mode"]=="Publish"){
        $published = 1;
        $redir = false;
        if(empty($post_date)){
            $post_date = date("Y-m-d H:i:s");
        }
    } elseif($_POST["save_mode"]=="Save"){
        $published = 0;
        $redir = true;
    } else {
        $published = (int)$_POST["published"];
        $redir = false;
    }

    if(empty($error)){

        if(function_exists("tidy_repair_string")){
            // if we have tidy available, lets use it to conform our
            // HTML to HTML 4 and not XHTML as TinMCE likes to write.
            $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head></head><body>'.$_POST["editor"]."</body></html>";
            $config = array(
                "indent"          => true,
                "indent-spaces"   => 4,
                "show-body-only"  => true,
                "vertical-space"  => true,
                "sort-attributes" => "alpha",
            );
            $_POST["editor"] = trim(tidy_repair_string($html, $config, "utf8"));
        }


        $post_array = array(
            "user_id"        => $WC["user"]["user_id"],
            "post_id"        => $_POST["post_id"],
            "subject"        => $_POST["subject"],
            "body"           => $_POST["editor"],
            "tags"           => $_POST["tags"],
            "allow_comments" => (int)$_POST["allow_comments"],
            "published"      => (int)$published,
            "uri"            => $post_uri,
        );

        if(!empty($post_date)){
            $post_array["post_date"] = $post_date;
        }

        $success = wc_db_save_post($post_array);

        if($success){

            if($WC["send_linkbacks"]){
                wc_admin_handle_linkbacks($post_array["post_id"]);
            }

            if($redir){
                header("Location: post.php??mode=edit&post_id=$post_array[post_id]");
                exit();
            }

            wc_admin_message("Post Saved!", true, "index.php");

        } else{

            $error = "There was an error saving your post.";
        }
    }

    if(!empty($error)){

        // setup the form with the posted data if there is an error
        $post_id = $_POST["post_id"];
        $post_subject = $_POST["subject"];
        $post_body = $_POST["editor"];
        $post_tags = $_POST["tags"];
        $post_custom_date = isset($_POST["custom_date"]);
        $post_date = $_POST["date"];
        $post_allow_comments = isset($_POST["allow_comments"]);
        $post_published = isset($_POST["published"]);

    }

} else {

    // check for initial edit mode
    if(isset($_GET["post_id"])){

        $post = wc_db_get_post($_GET["post_id"]);

        if(!empty($post)){
            $post_id = $post["post_id"];
            $post_uri = $post["uri"];
            $post_subject = $post["subject"];
            $post_body = $post["body"];
            $post_tags = $post["tags_text"];
            $post_date = strftime("%c", strtotime($post["post_date"]));
            $post_allow_comments = $post["allow_comments"];
            $post_published = $post["published"];
        } else {
            wc_admin_error("The post you requested to edit was not found.");
        }

    } else {

        // set up new post form
        $post_id = "";
        $post_uri = "";
        $post_subject = "";
        $post_body = "";
        $post_tags = "";
        $post_date = "";
        $post_allow_comments = $WC["allow_comments"];
        $post_published = true;
    }

}


// set breadcrumb
$WHEREAMI = ($mode=="edit") ? "Edit Post" : "New Post";

$WC_ADMIN_EDITOR = true;

// begin output
include_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="post.php" id="post-form">

    <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

    <div id="post-options">
        <p>
            <strong><input type="checkbox" name="allow_comments" id="allow_comments" value="1" <?php if(!empty($post_allow_comments)) echo "checked"; ?>> <label for="allow_comments">Allow Comments</label></strong><br>
        </p>

        <p>
            <strong><input type="checkbox" name="published" id="published" value="1" <?php if(!empty($post_published)) echo "checked"; ?>> <label for="published">Published</label></strong><br>
        </p>

        <p>
            <strong><input type="checkbox" name="custom_date" id="custom_date" value="1" <?php if(!empty($post_custom_date)) echo "checked "; ?>><label for="custom_date">Custom Date:</label></strong><br>
            <input class="inputgri" type="text" value="<?php echo htmlspecialchars($post_date); ?>" id="date" name="date"><br>
        </p>
    </div>

    <p>
        <strong>Subject:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($post_subject); ?>" id="subject" name="subject" maxlength="100">
    </p>

    <?php if($WC["use_rewrite"]) { ?>
    <p>
        <strong>Post URI:</strong> <input type="checkbox" name="custom_uri" id="custom_uri" value="1" <?php if(!empty($post_custom_uri)) echo "checked "; ?>><label for="custom_uri">Custom URI</label><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($post_uri); ?>" id="uri" name="uri"><br>
    </p>
    <?php } ?>

    <p>
        <strong>Tags:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($post_tags); ?>" id="tags" name="tags"><br>
        <small>Separate with commas. Example: kids, ball game, park</small>
    </p>

    <p class="clear">
        <strong>Post:</strong><br>
        <textarea id="editor" name="editor" rows="20" cols="75"><?php echo htmlspecialchars($post_body); ?></textarea>
    </p>

    <p>
        <?php if(!empty($post_id) && !empty($post_published)) { ?>
            <input class="button" type="submit" name="save_mode" value="Update">
        <?php } else {?>
            <?php if(empty($post_id) || empty($post_published)) { ?>
                <input class="button" type="submit" name="save_mode" value="Publish">
            <?php } ?>
            <input class="button" type="submit" name="save_mode" value="Save">
        <?php } ?>
    </p>

</form>

<?php

include_once "./footer.php";

?>
