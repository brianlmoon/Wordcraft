<div class="post">

    <h1 class="title"><a href="<?php echo $WCDATA["post"]["url"]; ?>"><?php echo $WCDATA["post"]["subject"]; ?></a></h1>

    <p class="byline"><small><?php echo $WCDATA["post"]["post_date"]; ?></small></p>

    <div class="entry">

        <?php echo $WCDATA["post"]["body"]; ?>

    </div>

    <p class="links">
        <a href="<?php echo $WCDATA["post"]["url"]; ?>#comments">Comments (<?php echo $WCDATA["post"]["comment_count"]; ?>)</a>&nbsp;&nbsp;&nbsp;&nbsp;
        Tags:&nbsp;
        <?php foreach($WCDATA["post"]["tags"] as $tag) { ?>
            <a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?></a>&nbsp;&nbsp;
        <?php } ?>
    </p>

</div>

<a name="comments"></a>


<?php if(!empty($WCDATA["comments"])) { ?>

    <h5><?php echo $WCDATA["post"]["comment_count"]; ?> comments</h5>

    <?php foreach($WCDATA["comments"] as $comment) { ?>

        <div class="comment">
            <?php if(!empty($comment["gravatar"])){ ?>
                <img src="<?=$comment["gravatar"]?>" alt="Gravatar for <?php echo $comment["name"]; ?>" border="0">
            <?php } ?>
            <?php if(!empty($comment["url"])){ ?>
                <h4><a rel="nofollow" href="<?php echo $comment["url"]; ?>"><?php echo $comment["name"]; ?></a> Says:</h4>
            <?php } else { ?>
                <h4><?php echo $comment["name"]; ?> Says:</h4>
            <?php } ?>
            <p><?php echo $comment["comment"]; ?><p>
        </div>

    <?php } ?>

<?php } ?>


<?php if($WCDATA["post"]["allow_comments"]) { ?>
    <div id="add-comment">
        <a name="add_comment"></a>
        <h3>Add A Comment</h3>
        <form action="<?php echo $WCDATA["comment_url"]; ?>" method="post">
            <input type="hidden" class="text-input" name="post_id" value="<?php echo $WCDATA["post"]["post_id"]; ?>">

            <?php if(!empty($WCDATA["user"])) { ?>

                You are logged in as <?php echo $WCDATA["user"]["user_name"]; ?>
                <br><br>

            <?php } else { ?>

                Your Name:<br>
                <input type="text" class="text-input" name="your_name" value="" maxlength="50" size="30">
                <br><br>

                Your Email:<br>
                <input type="text" class="text-input" name="your_email" value="" maxlength="50" size="30">
                <br><br>

                Your URL:<br>
                <input type="text" class="text-input" name="your_url" value="" maxlength="50" size="30">
                <br><br>

                <?php if($WCDATA["captcha"]) { ?>
                    Spam Prevention:<br>
                    <?php echo $WCDATA["captcha"]; ?>
                    <br><br>
                <?php } ?>


            <?php } ?>

            Your Comment:<br>
            <textarea cols="30" rows="10" name="your_comment" id="your-comment"></textarea>
            <br><br>

            <input type="submit" value="Submit">
        </form>
    </div>

<?php } else {?>

    <p>Comments are disabled for this post.</p>

<?php } ?>



