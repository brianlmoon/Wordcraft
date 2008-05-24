<?php  foreach($WCDATA["posts"] as $post) { ?>

    <div class="post">
        <div class="header">
            <h1><?php echo $post["subject"]; ?></h1>
            <div class="date"><?php echo $post["post_date"]; ?></div>
        </div>
        <div class="content">
            <?php echo $post["body"]; ?>
        </div>
        <div class="footer">
            <ul>
                <li class="comments"><a href="post.php?post_id=<?php echo $post["post_id"]; ?>#comments">Comments (<?php echo $post["comment_count"]; ?>)</a></li>
                <li class="readmore"><a href="post.php?post_id=<?php echo $post["post_id"]; ?>">Permalink</a></li>
                <li class="tags">
                    <?php foreach($post["tags"] as $tag) { ?>
                        <a href="tag.php?tag=<?php echo $tag; ?>"><?php echo $tag; ?></a>&nbsp;&nbsp;
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>

<?php } ?>
