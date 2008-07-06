<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $WCDATA["title"]; ?></title>
<meta name="description" content="<?php echo $WCDATA["description"]; ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/Refresh/style.css">
<link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">

</head>
<body>
<!-- wrap starts here -->
<div id="wrap">

        <!--header -->
        <div id="header">

            <h1 id="logo-text"><a href="<?php echo $WCDATA["base_url"]; ?>"><?php echo $WCDATA["default_title"]; ?></a></h1>
            <h2 id="slogan"><?php echo $WCDATA["default_description"]; ?></h2>

        </div>

        <!-- menu -->
        <div  id="menu">
            <ul>
                <li><a href="<?php echo $WCDATA["home_url"]; ?>">Home</a></li>
                <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                    <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo htmlspecialchars($nav_page["nav_label"]); ?></a></li>
                <?php } ?>
            </ul>
        </div>

        <!-- content-wrap starts here -->
        <div id="content-wrap">

            <div id="sidebar">

                <h1>Tags</h1>
                <div class="left-box">
                    <ul class="sidemenu">
                    <?php foreach($WCDATA["tags"] as $tag) { ?>
                        <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                    <?php } ?>
                    </ul>
                </div>


                <?php if(isset($WCDATA["admin"])) { ?>
                <h1>Admin</h1>
                <div class="left-box">
                    <ul class="sidemenu">
                        <li><a href="<?php echo $WCDATA["admin"]["base_url"]; ?>">Dashboard</a></li>
                        <li><a href="<?php echo $WCDATA["admin"]["logout_url"]; ?>">Log Out</a></li>
                        <li><a href="<?php echo $WCDATA["admin"]["new_post_url"]; ?>">New Post</a></li>
                        <li><a href="<?php echo $WCDATA["admin"]["new_page_url"]; ?>">New Page</a></li>
                        <?php if(isset($WCDATA["admin"]["edit_post_url"])) { ?>
                            <li><a href="<?php echo $WCDATA["admin"]["edit_post_url"]; ?>">Edit Post</a></li>
                        <?php } ?>
                        <?php if(isset($WCDATA["admin"]["edit_page_url"])) { ?>
                            <li><a href="<?php echo $WCDATA["admin"]["edit_page_url"]; ?>">Edit Page</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>


            </div>

            <div id="main">

