    </div>

    <div id="right">

        <h2>Pages</h2>
        <ul>
            <li><a href="<?php echo $WCDATA["home_url"]; ?>" title="Home">Home</a></li>
            <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                <li><a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a></li>
            <?php } ?>
        </ul>

        <h2>Search</h2>
        <form id="search" action="<?php echo $WCDATA["search_url"]; ?>" method="get">
            <input type="text" name="q" id="q"><input type="submit" id="submit" value="Go">
        </form>

        <h2>Tags</h2>
        <ul>
            <?php foreach($WCDATA["tags"] as $tag) { ?>
                <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
            <?php } ?>
        </ul>

        <?php if(isset($WCDATA["admin"])) { ?>
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
        <?php  } ?>

    </div>

    <div id="footer">

        <p>Powered by Wordcraft |
Template design by <a href="http://webgazette.co.uk/">Ainslie Johnson</a></p>

    </div>

    </div> <!-- Close id="container" -->
</body>

</html>

