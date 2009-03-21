                </div>
                <!-- end content -->
                <!-- start sidebars -->
                <div id="sidebar2" class="sidebar">
                    <ul>
                        <li>
                            <form id="search" action="<?php echo $WCDATA["search_url"]; ?>" method="get">
                                <div>
                                    <h2>Site Search</h2>
                                    &nbsp;<input type="text" name="q" id="q" size="15" value="">
                                    <input type="submit" value="Search">
                                </div>
                            </form>
                        </li>
                        <li>
                            <h2>Tags</h2>
                            <ul>
                                <?php foreach($WCDATA["tags"] as $tag) { ?>
                                    <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                                <?php } ?>
                            </ul>
                        <li>
                            <h2>Admin</h2>
                            <ul>
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
                        </li>
                    </ul>
                </div>
                <!-- end sidebars -->
                <div style="clear: both;">&nbsp;</div>
            </div>
            <!-- end page -->
        </div>
        <div id="footer">
            <p>&copy;2009 All Rights Reserved. &nbsp;&bull;&nbsp; Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>.</p>
        </div>
    </body>
</html>

