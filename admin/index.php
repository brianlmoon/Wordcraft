<?php

/**
 * Lists posts made by users of the blog
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

require_once "./admin_common.php";
require_once "../include/url.php";

$start = (empty($_GET["start"])) ? 0 : (int)$_GET["start"];

$filter = (empty($_GET["filter"])) ? "" : $_GET["filter"];

$limit = 50;

list($posts, $total) = wc_db_get_post_list($start, $limit, false, $filter, "", false, false);

$WHEREAMI = "Manage Posts";

require_once "./header.php";

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
    <form action="index.php" method="get">
        <input type="text" class="inputgri" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
        <input type="submit" class="button" value="Filter">
        <a href="index.php">Reset</a>
    </form>
</div>

<?php if(!empty($posts)) { ?>
    <div id="total">
        Total Post: <?php echo $total; ?>
    </div>
    <table cellspacing="0" cellpadding="0" border="0" class="table">
        <tr class="table_header">
            <th>ID</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Tags</th>
            <th>&nbsp;</th>
        </tr>
        <?php foreach($posts as $post) { ?>
            <?php $x = ($x==1) ? 2 : 1; ?>
            <tr class="row<?php echo $x; ?>">
                <td><?php echo $post["post_id"]; ?></td>
                <td><?php echo htmlspecialchars($post["subject"]); ?></td>
                <td><?php echo strftime($WC["date_format_long"], $post["post_date"]); ?></td>
                <td><?php echo htmlspecialchars(implode(", ", $post["tags"])); ?></td>
                <td><a href="<?php echo wc_get_url("post", $post["post_id"]); ?>" target="_blank">View</a>&nbsp;&nbsp;<a href="post.php?mode=edit&post_id=<?php echo $post["post_id"]; ?>">Edit</a>&nbsp;&nbsp;<a href="delete.php?post_id=<?php echo $post["post_id"]; ?>">Delete</a></td>
            </tr>
        <?php } ?>
    </table>

    <p class="paging">
        <a href="index.php">&lt;&lt; First</a>&nbsp;
        <a href="index.php?start=<?php echo $prev; ?>">&lt; Previous</a>&nbsp;

        <strong>Page <?php echo $page; ?>/<?php echo $pages; ?></strong>&nbsp;

        <a href="index.php?start=<?php echo $next; ?>">Next &gt;</a>&nbsp;
        <a href="index.php?start=<?php echo $last; ?>">Last &gt;&gt;</a>
    </p>
<?php } elseif(!empty($_GET["filter"])) { ?>
    No posts match your filter.
<?php } else { ?>
    You have not made any posts yet.
<?php } ?>

<?php require_once "./footer.php"; ?>

