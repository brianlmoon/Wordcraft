<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
    Template Author Info:
    terrafirma1.0 by nodethirtythree design
    http://www.nodethirtythree.com
-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $WCDATA["title"]; ?></title>
<meta name="description" content="<?php echo $WCDATA["description"]; ?>">
<link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/terrafirma/default.css">
<link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">
</head>
<body>

<div id="outer">

    <div id="upbg"></div>

    <div id="inner">

        <div id="header">
            <div class="title"><a href="<?php echo $WCDATA["home_url"]; ?>"><?php echo $WCDATA["default_title"]; ?></a></div>
            <div class="subtitle"><?php echo $WCDATA["default_description"]; ?></div>
        </div>

        <div id="splash"></div>

        <div id="menu">
            <ul>
                <li class="first"><a href="<?php echo $WCDATA["home_url"]; ?>">Home</a></li>
                <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                    <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a></li>
                <?php } ?>
            </ul>

        </div>


        <div id="primarycontent">

            <!-- primary content start -->

