<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $WCDATA["title"]; ?></title>
<meta name="description" content="<?php echo $WCDATA["description"]; ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" type="text/css" href="<?php echo $WCDATA["base_url"]; ?>/templates/bluefreedom2/style.css">
<link rel="alternate" type="application/rss+xml" title="<?php echo $WCDATA["default_title"]; ?>" href="<?php echo $WCDATA["feed_url"]; ?>">

</head>
<body>
<div id="wrap">

<div id="top"></div>

<div id="content">

<div class="header">
<h1><a href="<?php echo $WCDATA["base_url"]; ?>"><?php echo $WCDATA["default_title"]; ?></a></h1>
<h2><?php echo $WCDATA["default_description"]; ?></h2>
</div>

<div class="breadcrumbs">
    <a href="<?php echo $WCDATA["home_url"]; ?>">Home</a>
    <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
        &middot; <a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a>
    <?php } ?>
</div>

<div class="middle">


