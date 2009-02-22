<?php

/**
 * Logs the user out
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

require_once "../include/common.php";

session_destroy();

header("Location: index.php");
exit();

?>
