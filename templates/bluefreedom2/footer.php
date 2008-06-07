</div>

<div class="right">

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

<div id="clear"></div>

</div>

<div id="bottom"></div>

</div>

<div id="footer">
    Powered by Wordcraft.  Design by <a href="http://www.minimalistic-design.net">Minimalistic Design</a>
</div>

</body>
</html>
