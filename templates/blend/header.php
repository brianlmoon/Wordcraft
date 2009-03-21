<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Blend
Description: A three-column, fixed-width blog design.
Version    : 1.0
Released   : 20090303

-->
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title><?php echo $WCDATA["title"]; ?></title>
        <meta name="keywords" content="">
        <meta name="Blend" content="">
        <link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/blend/default.css">
        <link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">
    </head>
    <body>
        <!-- start header -->
        <div id="header">
            <div id="logo">
                <a href="<?php echo $WCDATA["home_url"]; ?>"><span><?php echo $WCDATA["default_title"]; ?></span></a>
            </div>
            <div id="menu">
                <ul id="main">
                    <li><a href="<?php echo $WCDATA["home_url"]; ?>">Home</a></li>
                    <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                        <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a></li>
                    <?php } ?>
                </ul>
            </div>

        </div>
        <!-- end header -->
        <div id="wrapper">
            <!-- start page -->
            <div id="page">
                <!-- start content -->
                <div id="content">

