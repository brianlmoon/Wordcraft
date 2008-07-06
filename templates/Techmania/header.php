<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $WCDATA["title"]; ?></title>
<meta name="description" content="<?php echo $WCDATA["description"]; ?>">
<link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/Techmania/style.css">
<link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">
</head>
<body>

<!-- wrap starts here -->
<div id="wrap">

        <div id="header">

            <h1 id="logo-text"><a href="<?php echo $WCDATA["home_url"]; ?>"><?php echo $WCDATA["default_title"]; ?></a></h1>
            <h2 id="slogan"><?php echo $WCDATA["default_description"]; ?></h2>

            <div id="header-tabs">
                <ul>
                    <li><a href="<?php echo $WCDATA["home_url"]; ?>"><span>Home</span></a></li>
                    <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                        <li><a href="<?php echo $nav_page["url"]; ?>"><span><?php echo htmlspecialchars($nav_page["nav_label"]); ?></span></a></li>
                    <?php } ?>
                </ul>
            </div>

        </div>

        <!-- content-wrap starts here -->
        <div id="content-wrap">

            <div id="main">

