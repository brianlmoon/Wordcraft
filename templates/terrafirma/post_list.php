<?php  foreach($WCDATA["posts"] as $post) { ?>
    <div class="post">
        <div class="header">
            <h1><a href="<?php echo $post["url"] ?>"><?php echo $post["subject"]; ?></a> <small>by <?php echo $post["user_name"]; ?></small></h1>
            <div class="date"><?php echo $post["post_date"]; ?></div>
        </div>
        <div class="content">
            <?php echo $post["body"]; ?>
        </div>
        <div class="footer">
            <ul>
                <li class="comments"><a href="<?php echo $post["url"]; ?>#comments">Comments (<?php echo $post["comment_count"]; ?>)</a></li>
                <li class="readmore"><a href="<?php echo $post["url"]; ?>">Permalink</a></li>
                <?php if(!empty($post["tags"])) { ?>
                    <li class="tags">
                        <?php foreach($post["tags"] as $tag) { ?>
                            <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

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

