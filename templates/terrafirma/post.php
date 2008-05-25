    <div class="post">
        <div class="header">
            <h1><?php echo $WCDATA["post"]["subject"]; ?></h1>
            <div class="date"><?php echo $WCDATA["post"]["post_date"]; ?></div>
        </div>
        <div class="content">
            <?php echo $WCDATA["post"]["body"]; ?>
        </div>
        <div class="footer">
            <ul>
                <li class="comments"><a href="<?php echo $WCDATA["post"]["url"]; ?>#comments">Comments (<?php echo $WCDATA["post"]["comment_count"]; ?>)</a></li>
                <li class="readmore"><a href="<?php echo $WCDATA["post"]["url"]; ?>">Permalink</a></li>
                <li class="tags">
                    <?php foreach($WCDATA["post"]["tags"] as $tag) { ?>
                        <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>

    <a name="comments"></a>
    <?php if(!empty($WCDATA["comments"])) { ?>

        <?php foreach($WCDATA["comments"] as $comment) { ?>

            <div class="comment">
                <?php if(!empty($comment["url"])){ ?>
                    <h4><a rel="nofollow" href="<?php echo $comment["url"]; ?>"><?php echo $comment["name"]; ?></a> Says:</h4>
                <?php } else { ?>
                    <h4><?php echo $comment["name"]; ?> Says:</h4>
                <?php } ?>
                <p><?php echo $comment["comment"]; ?><p>
            </div>

        <?php } ?>

    <?php } ?>



    <div id="add-comment">
        <a name="add_comment"></a>
        <h3>Add A Comment</h3>
        <form action="<?php echo $WCDATA["comment_url"]; ?>" method="post">
            <input type="hidden" class="text-input" name="post_id" value="<?php echo $WCDATA["post"]["post_id"]; ?>" />

            <?php if(!empty($WCDATA["user"])) { ?>

                You are logged in as <?php echo $WCDATA["user"]["user_name"]; ?>
                <br /><br />

            <?php } else { ?>

                Your Name:<br />
                <input type="text" class="text-input" name="your_name" value="" maxlength="50" size="30" />
                <br /><br />

                Your Email:<br />
                <input type="text" class="text-input" name="your_email" value="" maxlength="50" size="30" />
                <br /><br />

                Your URL:<br />
                <input type="text" class="text-input" name="your_url" value="" maxlength="50" size="30" />
                <br /><br />

                <?php if($WCDATA["captcha"]) { ?>
                    Spam Prevention:<br />
                    <?php echo $WCDATA["captcha"]; ?>
                    <br /><br />
                <?php } ?>


            <?php } ?>

            Your Comment:<br />
            <textarea name="your_comment" id="your-comment" /></textarea>
            <br /><br />

            <input type="submit" value="Submit" />
        </form>
    </div>
