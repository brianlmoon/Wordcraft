            </div>

        <!-- content-wrap ends here -->
        </div>

        <!--footer starts here-->
        <div id="footer">

            <p>
                <strong>Powered by Wordcraft</strong> |
                Design by: <a href="http://www.styleshout.com/">styleshout</a>

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <a href="<?php echo $WCDATA["home_url"]; ?>">Home</a> |
                <?php if(isset($WCDATA["nav_pages"])) foreach($WCDATA["nav_pages"] as $nav_page) { ?>
                    <a href="<?php echo $nav_page["url"]; ?>"><?php echo $nav_page["nav_label"]; ?></a> |
                <?php } ?>
                <a href="<?php echo $WCDATA["feed_url"]; ?>">RSS Feed</a>

            </p>

        </div>

<!-- wrap ends here -->
</div>

</body>
</html>

