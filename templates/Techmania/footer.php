            </div>

            <div id="sidebar">

                <h1>Tags</h1>
                <ul class="sidemenu">
                    <?php foreach($WCDATA["tags"] as $tag) { ?>
                        <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                    <?php } ?>
                </ul>

                <?php if(isset($WCDATA["admin"])) { ?>
                <h1>Admin</h1>
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
                <?php } ?>

            </div>

        <!-- content-wrap ends here -->
        </div>

        <div id="footer">

            <span id="footer-left">
                <strong>Powered by Wordcraft</strong> |
                Design by: <strong>styleshout</strong>
            </span>

            <span id="footer-right">
                <a href="<?php echo $WCDATA["home_url"]; ?>">Home</a> |
                    <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                        <a href="<?php echo $nav_page["url"]; ?>"><?php echo htmlspecialchars($nav_page["nav_label"]); ?></a> |
                    <?php } ?>
                <a href="<?php echo $WCDATA["feed_url"]; ?>">RSS Feed</a>
            </span>

        </div>

<!-- wrap ends here -->
</div>
</body>
</html>

