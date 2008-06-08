<?php  foreach($WCDATA["posts"] as $post) { ?>
<h3><a href="<?php echo $post["url"]; ?>"><?php echo $post["subject"]; ?></a><small>by <?php echo $post["user_name"]; ?> on <?php echo $post["post_date"]; ?></small></h3>
<div class="body">
    <?php echo $post["body"]; ?>
</div>
<div class="footer">
    <ul>
        <li class="comments"><a href="<?php echo $post["url"]; ?>#comments">Comments (<?php echo $post["comment_count"]; ?>)</a></li>
        <li class="readmore"><a href="<?php echo $post["url"]; ?>">Permalink</a></li>
        <li class="tags">Tags:
            <?php foreach($post["tags"] as $tag) { ?>
                <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
            <?php } ?>
        </li>
    </ul>
</div><?php } ?>
