<?php  foreach($WCDATA["posts"] as $post) { ?>

    <div class="post">

        <h1><a href="<?php echo $post["url"]; ?>"><?php echo $post["subject"]; ?></a></h1>

        <div class="date"><?php echo $post["post_date"]; ?></div>

        <div class="body">
            <?php echo $post["body"]; ?>
        </div>

        <ul class="stats">

            <li class="comments"><a href="<?php echo $post["url"]; ?>#comments">Comments (<?php echo $post["comment_count"]; ?>)</a></li>
            <li class="plink"><a href="<?php echo $post["url"]; ?>">Permalink</a></li>
            <li class="tags">Tags:&nbsp;
                <?php foreach($post["tags"] as $tag) { ?>
                    <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
                <?php } ?>
            </li>

        </ul>

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
