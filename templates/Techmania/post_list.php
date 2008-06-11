<?php  foreach($WCDATA["posts"] as $post) { ?>
    <h1><?php echo $post["subject"]; ?> <small>by <?php echo $post["user_name"]; ?></small></h1>

    <div class="body">
        <?php echo $post["body"]; ?>
    </div>

    <p>
    Tags:
        <?php foreach($post["tags"] as $tag) { ?>
            <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
        <?php } ?>
    </p>

    <p class="comments align-right">
        <a href="<?php echo $post["url"]; ?>">Read more</a> :
        <a href="<?php echo $post["url"]; ?>#comments">Comments(<?php echo $post["comment_count"]; ?>)</a> :
        <?php echo $post["post_date"]; ?>
    </p>

<?php } ?>
