            <!-- primary content end -->

        </div>

        <div id="secondarycontent">

            <!-- secondary content start -->

            <h3>Tags</h3>
            <div class="content">
                <ul class="linklist">
                    <?php foreach($WCDATA["tags"] as $tag) { ?>
                        <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                    <?php } ?>
                </ul>
            </div>

            <?php if(isset($WCDATA["admin"])) { ?>
            <h3>Admin</h3>
            <div class="content">
                <ul class="linklist">
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
            <?php  } ?>

            <!-- secondary content end -->

        </div>

        <p>&nbsp;</p>


        <div id="footer">

            Powered by Wordcraft. Original Template Design by <a href="http://www.nodethirtythree.com/">NodeThirtyThree</a>.

        </div>

    </div>

</div>

</body>
</html>
