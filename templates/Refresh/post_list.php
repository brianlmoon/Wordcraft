<?php  foreach($WCDATA["posts"] as $post) { ?>

    <h1><a href="<?php echo $post["url"] ?>"><?php echo $post["subject"]; ?></a> <small>by <?php echo $post["user_name"]; ?></small></h1>

    <div class="body">
        <?php echo $post["body"]; ?>
    </div>

    <p class="post-footer align-right">
        <a href="<?php echo $post["url"]; ?>" class="readmore">Read more</a>
        <a href="<?php echo $post["url"]; ?>#comments" class="comments">Comments (<?php echo $post["comment_count"]; ?>)</a>
        <span class="date"><?php echo $post["post_date"]; ?></span>
        <?php if(!empty($post["tags"])) { ?>
            <br>
            Tags:
            <?php foreach($post["tags"] as $tag) { ?>
                <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
            <?php } ?>
        <?php } ?>

    </p>

<?php } ?>

<?php if(isset($WCDATA["older_url"]) || isset($WCDATA["newer_url"])){ ?>

    <div id="pagenav">

        <?php if(isset($WCDATA["newer_url"])){ ?>
            <a class="newer" href="<?php echo $WCDATA["newer_url"]; ?>">Newer Posts</a>
        <?php } ?>

        <?php if(isset($WCDATA["older_url"])){ ?>
            <a class="older" href="<?php echo $WCDATA["older_url"]; ?>">Older Posts</a>
        <?php } ?>

    </div>

<?php } ?>

