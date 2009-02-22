<?php

/**
 * Creates/Edits a page
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

include_once "./check_auth.php";
include_once "./admin_functions.php";


// check the mode
if(isset($_POST["mode"])){
    $mode = $_POST["mode"];
} elseif(isset($_GET["mode"])){
    $mode = $_GET["mode"];
} else {
    $mode = "new";
}

if($mode!="new" && $mode!="edit"){
    wc_admin_error("Invalid mode '".htmlspecialchars($mode)."' for pages page");
}

if($mode=="edit" && empty($_GET["page_id"]) && empty($_POST["page_id"])){
    wc_admin_error("No page_id provided for edit mode.");
}


// init error to empty
$error = "";


// check for post data
if(count($_POST)){

    if(empty($_POST["nav_label"]) || empty($_POST["title"]) || empty($_POST["editor"])) {

        $error = "You must fill in all fields.";

    }

    if(empty($error)){

        $page_array = array(
            "page_id"   => $_POST["page_id"],
            "nav_label" => $_POST["nav_label"],
            "title"     => $_POST["title"],
            "body"      => $_POST["editor"]
        );

        if(empty($_POST["page_id"])){
            $page_array["uri"].= strtolower(preg_replace("![^a-z0-9_]+!i", "-", trim($_POST["title"])));
        }

        $success = wc_db_save_page($page_array);

        if($success){
            wc_admin_message("Page Saved!", true, "pages.php");
        } else{
            $error = "There was an error saving your page.";
        }
    }

    if(!empty($error)){

        // setup the form with the posted data if there is an error
        $page_id = $_POST["page_id"];
        $page_nav_label = $_POST["nav_label"];
        $page_title = $_POST["title"];
        $page_body = $_POST["editor"];
    }

} else {

    // check for initial edit mode
    if(isset($_GET["page_id"])){

        $page = wc_db_get_page($_GET["page_id"]);

        if(!empty($page)){
            $page_id = $page["page_id"];
            $page_title = $page["title"];
            $page_nav_label = $page["nav_label"];
            $page_body = $page["body"];
        } else {
            wc_admin_error("The page you requested to edit was not found.");
        }

    } else {

        // set up new post form
        $page_id = "";
        $page_title = "";
        $page_nav_label = "";
        $page_body = "";
    }

}


// set breadcrumb
$WHEREAMI = ($mode=="edit") ? "Edit Page" : "New Page";

$WC_ADMIN_EDITOR = true;

// begin output
include_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="page.php" id="post-form">

    <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($page_id); ?>">
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

    <p>
        <strong>Navigation Label:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($page_nav_label); ?>" id="page_nav_label" name="nav_label" maxlength="30">
    </p>

    <p>
        <strong>Title:</strong><br>
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($page_title); ?>" id="page_title" name="title" maxlength="100">
    </p>

    <p>
        <strong>Page Body:</strong><br>
        <textarea id="editor" name="editor" rows="20" cols="75"><?php echo htmlspecialchars($page_body); ?></textarea>
    </p>

    <p>
        <input class="button" type="submit" value="Save">
    </p>

</form>

<?php

include_once "./footer.php";

?>
