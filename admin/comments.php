<?php

include_once "./check_auth.php";
include_once "../include/format.php";

$start = (empty($_GET["start"])) ? 0 : (int)$_GET["start"];

$filter = (empty($_GET["filter"])) ? false : $_GET["filter"];
$status = (empty($_GET["status"])) ? false : $_GET["status"];

$limit = 50;

if($status == "non-spam" || empty($status)){
    $fetch_status = array(
        "approved",
        "unapproved"
    );
} elseif($status=="all"){
    $fetch_status = "";
} else {
    $fetch_status = $status;
}

list($comments, $total) = wc_db_get_comments(false, $fetch_status, $start, $limit, $filter);

foreach($comments as $comment){
  wc_format_comment($comment);
  $post_ids[$comment["post_id"]] = $comment["post_id"];
}

list($posts, $post_count) = wc_db_get_post_list(false, false, false, false, false, $post_ids, false);

// get spam count
$spam_count = 0;
if($status!="spam"){
    list($spam, $spam_count) = wc_db_get_comments(false, "spam", 0, 101, "");
    unset($spam);
    if($spam_count > 0){

        if($spam_count > 100){
            $spam_count = "more than $spam_count";
        }

    }
}

$WHEREAMI = "Comments";

include_once "./header.php";

$x = 1;

$pages = ceil($total/$limit);

$page = $start/$limit + 1;

$prev = $start - $limit;
$next = $start + $limit;
$last = $pages*$limit;

if($prev < 0){
    $prev = "";
}

if($next > $total) {
    $next = "";
}

if($last > $total) {
    $last = "";
}

?>

<div id="filter">
    <form action="comments.php" method="get">
        <input type="radio" id="status-all" name="status" value="all" <?php if($_GET["status"]=="all") echo "checked"; ?>><label for="status-all"> All</label>
        <input type="radio" id="status-non-spam" name="status" value="non-spam" <?php if(empty($_GET["status"])) echo "checked"; ?>><label for="status-all"> Non-Spam</label>
        <input type="radio" id="status-approved" name="status" value="approved" <?php if($_GET["status"]=="approved") echo "checked"; ?>><label for="status-approved"> Approved</label>
        <input type="radio" id="status-hidden" name="status" value="unapproved" <?php if($_GET["status"]=="unapproved") echo "checked"; ?>><label for="status-hidden"> Hidden</label>
        <input type="radio" id="status-spam" name="status" value="spam" <?php if($_GET["status"]=="spam") echo "checked"; ?>><label for="status-spam"> Spam</label><br>
        <input type="text" class="inputgri" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
        <input type="submit" class="button" value="Filter">
        <a href="comments.php">Reset</a>
    </form>
</div>

<?php if($spam_count) { ?>
    <div class="notice_error">
        There are <?=$spam_count?> comments flagged as spam.  <a href="comments.php?status=spam">View</a> | <a href="comment_moderate.php?mode=delete_spam">Delete now</a>.
    </div>
<?php } ?>

<?php if(!empty($comments)) { ?>
    <?php foreach($comments as $comment) { ?>
        <div class="comment <?php echo $comment["status"]; ?>">
            <div class="status">Status: <?php echo $comment["status"]; ?></div>
            <strong><?php echo $comment["name"]; ?></strong>&nbsp;
            IP: <?php echo $comment["ip_address"]; ?>&nbsp;
            <?php if(!empty($comment["email"])) { ?>
                Email: <?php echo $comment["email"]; ?>
            <?php } ?>
            <?php if(!empty($comment["url"])) { ?>
                URL: <?php echo $comment["url"]; ?>
            <?php } ?>
            <p>
                <?php echo $comment["comment"]; ?>
            </p>
            <div class="moderation">
                    <a href="comment_moderate.php?comment_id=<?php echo $comment["comment_id"];?>&mode=delete">Delete</a>&nbsp;
                    <?php if($comment["status"] != "SPAM") { ?>
                        <a href="comment_moderate.php?comment_id=<?php echo $comment["comment_id"];?>&mode=spam">Spam</a>&nbsp;
                    <?php } ?>
                    <?php if($comment["status"] == "APPROVED") { ?>
                        <a href="comment_moderate.php?comment_id=<?php echo $comment["comment_id"];?>&mode=hide">Hide</a>&nbsp;
                    <?php } else { ?>
                        <a href="comment_moderate.php?comment_id=<?php echo $comment["comment_id"];?>&mode=approve">Approve</a>&nbsp;
                    <?php } ?>
                </form>
            </div>
            <?php echo strftime($WC["date_format_short"], strtotime($comment["comment_date"])); ?>&nbsp;
            Response To: <a href="post.php?mode=edit&post_id=<?php echo $comment["post_id"];?>"><?php echo $posts[$comment["post_id"]]["subject"]; ?></a>
        </div>
    <?php } ?>

    <p class="paging">
        <a href="index.php">&lt;&lt; First</a>&nbsp;
        <a href="index.php?start=<?php echo $prev; ?>">&lt; Previous</a>&nbsp;

        <strong>Page <?php echo $page; ?>/<?php echo $pages; ?></strong>&nbsp;

        <a href="index.php?start=<?php echo $next; ?>">Next &gt;</a>&nbsp;
        <a href="index.php?start=<?php echo $last; ?>">Last &gt;&gt;</a>
    </p>
<?php } else { ?>
    No comments match your filter.
<?php } ?>

<?php include_once "./footer.php"; ?>

