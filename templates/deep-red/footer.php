                </div>

                <div id="sidebar">

                    <h3>about</h3>

                    <div id="about">
                        Brian Moon, of dealnews.com, shares what he knows (and learns) about PHP, MySQL and other stuff
                    </div>

                    <div id="ad">
                        <script type="text/javascript"><!--
                        google_ad_client = "pub-7076699294893330";
                        /* 300x250 for moonspot */
                        google_ad_slot = "1860110704";
                        google_ad_width = 300;
                        google_ad_height = 250;
                        //-->
                        </script>
                        <script type="text/javascript"
                        src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                        </script>
                    </div>

                    <?php if(isset($WCDATA["admin"])) { ?>
                        <h3>admin</h3>

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

                    <h3>tags</h3>

                    <ul>
                        <?php foreach($WCDATA["tags"] as $tag) { ?>
                            <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                        <?php } ?>
                    </ul>

                </div>

                <div id="footer">
                    Powered by <a href="http://code.google.com/p/wordcraft/">Wordcraft</a>.
                    <small>Template based on <a href="http://templates.arcsin.se/deep-red-website-template/">Deep Red</a></small>
                </div>

            </div>

        </div>

        <script>
            document.getElementById('sidebar').style.minHeight = document.getElementById('content').offsetHeight + "px";
        </script>

        <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
        var pageTracker = _gat._getTracker("UA-89211-7");
        pageTracker._trackPageview();
        </script>

    </body>
</html>

