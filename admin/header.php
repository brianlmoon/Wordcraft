<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
    <title>Wordcraft Admin</title>

    <!-- YUI Stylesheets -->
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/fonts/fonts-min.css&2.7.0/build/container/assets/skins/sam/container.css&2.7.0/build/menu/assets/skins/sam/menu.css&2.7.0/build/button/assets/skins/sam/button.css&2.7.0/build/editor/assets/skins/sam/editor.css">

    <!-- App Stylesheets -->
    <link rel="stylesheet" type="text/css" href="./atrandafir582.css" media="screen,print">
    <link rel="stylesheet" type="text/css" href="./admin.css" media="screen,print">

    <!-- YUI JavaScript -->
    <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.7.0/build/yahoo-dom-event/yahoo-dom-event.js&2.7.0/build/element/element-min.js&2.7.0/build/container/container_core-min.js&2.7.0/build/menu/menu-min.js&2.7.0/build/button/button-min.js&2.7.0/build/editor/editor-min.js&2.7.0/build/selector/selector-min.js"></script>

    <!-- App Javascript -->
    <script type="text/javascript" src="./admin.js"></script>

    <!-- Editor Config -->
    <?php if(isset($WC_ADMIN_EDITOR)){ ?>
        <script type="text/javascript" src="./yui_editor.js"></script>
    <?php } ?>

</head>
<body class="yui-skin-sam">
    <div id="wrap">
        <div id="top_content">

            <div id="header">
                <!-- rightheader -->
                <div id="rightheader">
                <?php if(!empty($WC["user"]["user_name"])) { ?>
                    <p>
                        Hey <strong><?php echo $WC["user"]["user_name"]; ?></strong>, <a href="logout.php">Logout</a>
                   </p>
                <?php } ?>
                </div>

                <!-- topheader -->
                <div id="topheader">
                    <h1 id="title">
                        <a href="index.php">Wordcraft!</a>
                        <span><?php echo WC; ?></span>
                    </h1>
                </div>

                <!-- navigation -->
                <?php if(!empty($WC["user"]["user_name"])) { ?>
                <div id="navigation">
                    <ul>
                        <li><a href="index.php">Manage Posts</a></li>
                        <li><a href="post.php">New Post</a></li>
                        <li><a href="pages.php">Manage Pages</a></li>
                        <li><a href="page.php">New Page</a></li>
                        <li><a href="users.php">Users</a></li>
                        <li><a href="comments.php">Comments</a></li>
                        <li><a href="settings.php">Settings</a></li>
                        <!-- Keep Last -->
                        <li><a href="../index.php">View Blog</a></li>
                    </ul>
                </div>
                <?php } ?>

            </div>
            <!-- header ends here -->

            <div id="content">
                <p id="whereami">
                    <a href="index.php">Admin</a> &gt;
                    <?php echo $WHEREAMI; ?>
                </p>



