<?php

require_once "../include/common.php";

session_destroy();

header("Location: index.php");
exit();

?>
