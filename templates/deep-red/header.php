<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $WCDATA["title"]; ?></title>

        <meta name="description" content="<?php echo $WCDATA["description"]; ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/deep-red/default.css">
        <link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">

    </head>
    <body>

        <div id="wrapper">

            <div id="main">

                <div id="content">

                    <div id="logo">
                        <a href="<?php echo $WCDATA["home_url"]; ?>"><span><?php echo $WCDATA["default_title"]; ?></span></a>
                    </div>

                    <ul id="menu">
                        <li><a href="<?php echo $WCDATA["home_url"]; ?>">Home</a></li>
                        <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                            <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a></li>
                        <?php } ?>
                    </ul>

