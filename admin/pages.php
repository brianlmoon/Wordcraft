<?php

/**
 * Lists pages made by users of the blog
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

list($pages, $total) = wc_db_get_page_list($start, $limit, false, $filter);

$WHEREAMI = "Manage Pages";

require_once "./header.php";

$x = 1;

$pgs = ceil($total/$limit);

$pg = $start/$limit + 1;

$prev = $start - $limit;
$next = $start + $limit;
$last = $pgs*$limit;

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
    <form action="pages.php" method="get">
        <input type="text" class="inputgri" name="filter" value="<?php echo htmlspecialchars($filter, ENT_COMPAT, "UTF-8"); ?>">
        <input type="submit" class="button" value="Filter">
        <a href="pages.php">Reset</a>
    </form>
</div>

<?php if(!empty($pages)) { ?>
    <table cellspacing="0" cellpadding="0" border="0" class="table">
        <tr class="table_header">
            <th>ID</th>
            <th>Title</th>
            <th>&nbsp;</th>
        </tr>
        <?php foreach($pages as $page) { ?>
            <?php $x = ($x==1) ? 2 : 1; ?>
            <tr class="row<?php echo $x; ?>">
                <td><?php echo $page["page_id"]; ?></td>
                <td><?php echo htmlspecialchars($page["title"], ENT_COMPAT, "UTF-8"); ?></td>
                <td><a href="<?php echo wc_get_url("page", array($page["page_id"], $page["uri"])); ?>" target="_blank">View</a>&nbsp;&nbsp;<a href="page.php?mode=edit&page_id=<?php echo $page["page_id"]; ?>">Edit</a>&nbsp;&nbsp;<a href="delete.php?page_id=<?php echo $page["page_id"]; ?>">Delete</a></td>
            </tr>
        <?php } ?>
    </table>

    <p class="paging">
        <a href="pages.php">&lt;&lt; First</a>&nbsp;
        <a href="pages.php?start=<?php echo $prev; ?>">&lt; Previous</a>&nbsp;

        <strong>Page <?php echo $pg; ?>/<?php echo $pgs; ?></strong>&nbsp;

        <a href="pages.php?start=<?php echo $next; ?>">Next &gt;</a>&nbsp;
        <a href="pages.php?start=<?php echo $last; ?>">Last &gt;&gt;</a>
    </p>
<?php } else { ?>
    No pages match your filter.
<?php } ?>

<?php require_once "./footer.php"; ?>

