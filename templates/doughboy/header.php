<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
    Template Author Info:
    doughboy by Brian Moon
    http://brian.moonspot.net/
-->
<html>
<head>
<title><?php echo $WCDATA["title"]; ?></title>
<meta name="description" content="<?php echo $WCDATA["description"]; ?>">
<link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/doughboy/default.css">
<link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">
<script type="text/javascript">
function init() {
    document.getElementById('primarycontent').style.minHeight = document.getElementById('secondarycontent').clientHeight + 'px';
}
</script>

</head>
<body onload="init();">

<div id="outer">

    <div id="header">
        <div class="title"><a href="<?php echo $WCDATA["home_url"]; ?>"><span><?php echo $WCDATA["default_title"]; ?></span></a></div>
        <div class="subtitle"><?php echo $WCDATA["default_description"]; ?></div>
    </div>

    <ul id="menu">
        <li class="first"><a href="<?php echo $WCDATA["home_url"]; ?>">Home</a></li>
        <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
            <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo htmlspecialchars($nav_page["nav_label"]); ?></a></li>
        <?php } ?>
    </ul>

    <div id="primarycontent">

            <!-- primary content start -->

