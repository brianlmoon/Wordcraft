<?php

/**
 * Lists the users of this system
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */


include_once "./check_auth.php";

$start = (empty($_GET["start"])) ? 0 : (int)$_GET["start"];

$filter = (empty($_GET["filter"])) ? "" : $_GET["filter"];

$limit = 50;

list($users, $total) = wc_db_get_user_list($start, $limit, $filter);

$WHEREAMI = "Users";

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
    <p>
        <a href="user.php">Add a User</a>
    </p>
    <form action="users.php" method="get">
        <input type="text" class="inputgri" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
        <input type="submit" class="button" value="Filter">
        <a href="users.php">Reset</a>
    </form>
</div>

<?php if(!empty($users)) { ?>
    <table cellspacing="0" cellpadding="0" border="0" class="table">
        <tr class="table_header">
            <th>ID</th>
            <th>User Name</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>&nbsp;</th>
        </tr>
        <?php foreach($users as $user) { ?>
            <?php $x = ($x==1) ? 2 : 1; ?>
            <tr class="row<?php echo $x; ?>">
                <td><?php echo $user["user_id"]; ?></td>
                <td><?php echo htmlspecialchars($user["user_name"]); ?></td>
                <td><?php echo htmlspecialchars($user["first_name"]); ?> <?php echo htmlspecialchars($user["last_name"]); ?></td>
                <td><?php echo htmlspecialchars($user["email"]); ?></td>
                <td><a href="user.php?mode=edit&user_id=<?php echo $user["user_id"]; ?>">Edit</a>&nbsp;&nbsp;<a href="user_delete.php?user_id=<?php echo $user["user_id"]; ?>">Delete</a>&nbsp;&nbsp;<a href="user_password.php?user_id=<?php echo $user["user_id"]; ?>">Password</a></td>
            </tr>
        <?php } ?>
    </table>

    <p class="paging">
        <a href="users.php">&lt;&lt; First</a>&nbsp;
        <a href="users.php?start=<?php echo $prev; ?>">&lt; Previous</a>&nbsp;

        <strong>Page <?php echo $page; ?>/<?php echo $pages; ?></strong>&nbsp;

        <a href="users.php?start=<?php echo $next; ?>">Next &gt;</a>&nbsp;
        <a href="users.php?start=<?php echo $last; ?>">Last &gt;&gt;</a>
    </p>
<?php } else { ?>
    No users match your filter.
<?php } ?>

<?php include_once "./footer.php"; ?>

