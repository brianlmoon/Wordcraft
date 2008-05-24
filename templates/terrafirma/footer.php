            <!-- primary content end -->

        </div>

        <div id="secondarycontent">

            <!-- secondary content start -->

            <h3>Tags</h3>
            <div class="content">
                <ul class="linklist">
                    <?php foreach($WCDATA["tags"] as $tag) { ?>
                        <li><a href="<?php echo $tag["url"]; ?>"><?php echo $tag["tag"]; ?> (<?php echo $tag["post_count"]; ?>)</a></li>
                    <?php } ?>
                </ul>
            </div>

            <!-- secondary content end -->

        </div>

        <p>&nbsp;</p>


        <div id="footer">

            Powered by Wordcraft. Original Template Design by <a href="http://www.nodethirtythree.com/">NodeThirtyThree</a>.

        </div>

    </div>

</div>

</body>
</html>
