<?php

include_once "./check_auth.php";
include_once "./admin_functions.php";


// check the mode
if(isset($_POST["mode"])){
    $mode = $_POST["mode"];
} elseif(isset($_GET["mode"])){
    $mode = $_GET["mode"];
} else {
    $mode = "new";
}

if($mode!="new" && $mode!="edit"){
    wc_admin_error("Invalid mode '".htmlspecialchars($mode)."' for pages page");
}

if($mode=="edit" && empty($_GET["page_id"]) && empty($_POST["page_id"])){
    wc_admin_error("No page_id provided for edit mode.");
}


// init error to empty
$error = "";


// check for post data
if(count($_POST)){

    if(empty($_POST["nav_label"]) || empty($_POST["title"]) || empty($_POST["editor"])) {

        $error = "You must fill in all fields.";

    }

    if(empty($error)){

        $page_array = array(
            "page_id"   => $_POST["page_id"],
            "nav_label" => $_POST["nav_label"],
            "title"     => $_POST["title"],
            "body"      => $_POST["editor"]
        );

        if(empty($_POST["page_id"])){
            $page_array["uri"].= strtolower(preg_replace("![^a-z0-9_]+!i", "-", trim($_POST["title"])));
        }

        $success = wc_db_save_page($page_array);

        if($success){
            wc_admin_message("Page Saved!");
        } else{
            $error = "There was an error saving your page.";
        }
    }

    if(!empty($error)){

        // setup the form with the posted data if there is an error
        $page_id = $_POST["page_id"];
        $page_nav_label = $_POST["nav_label"];
        $page_title = $_POST["title"];
        $page_body = $_POST["editor"];
    }

} else {

    // check for initial edit mode
    if(isset($_GET["page_id"])){

        $page = wc_db_get_page($_GET["page_id"]);

        if(!empty($page)){
            $page_id = $page["page_id"];
            $page_title = $page["title"];
            $page_nav_label = $page["nav_label"];
            $page_body = $page["body"];
        } else {
            wc_admin_error("The page you requested to edit was not found.");
        }

    } else {

        // set up new post form
        $page_id = "";
        $page_title = "";
        $page_nav_label = "";
        $page_body = "";
    }

}


// set breadcrumb
$WHEREAMI = ($mode=="edit") ? "Edit Page" : "New Page";


// begin output
include_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="page.php" id="post-form">

    <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($page_id); ?>" />
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>" />

    <p>
        <strong>Navigation Label:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($page_nav_label); ?>" id="page_nav_label" name="nav_label" maxlength="30" />
    </p>

    <p>
        <strong>Title:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($page_title); ?>" id="page_title" name="title" maxlength="100" />
    </p>

    <p>
        <strong>Page Body:</strong><br />
        <textarea id="editor" name="editor" rows="20" cols="75"><?php echo htmlspecialchars($page_body); ?></textarea>
    </p>

    <p>
        <input class="button" type="submit" value="Save" />
    </p>

</form>

<script>

(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;

    var myConfig = {
        height: '300px',
        width: '930px',
        dompath: true,
        focusAtStart: false,
        handleSubmit: true,
        autoHeight: true,
        css: YAHOO.widget.SimpleEditor.prototype._defaultCSS + 'body{ font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 100%; } ',
        toolbar: {
            collapse: false,
            titlebar: '',
            draggable: false,
            buttonType: 'advanced',
            buttons: [
                { group: 'textstyle', label: 'Font Style',
                    buttons: [
                        { type: 'push', label: 'Bold CTRL + SHIFT + B', value: 'bold' },
                        { type: 'push', label: 'Italic CTRL + SHIFT + I', value: 'italic' },
                        { type: 'push', label: 'Underline CTRL + SHIFT + U', value: 'underline' },
                        { type: 'separator' },
                        { type: 'push', label: 'Subscript', value: 'subscript', disabled: true },
                        { type: 'push', label: 'Superscript', value: 'superscript', disabled: true },
                        { type: 'separator' },
                        { type: 'color', label: 'Font Color', value: 'forecolor', disabled: true },
                        { type: 'color', label: 'Background Color', value: 'backcolor', disabled: true },
                        { type: 'separator' },
                        { type: 'push', label: 'Remove Formatting', value: 'removeformat', disabled: true },
                        { type: 'push', label: 'Show/Hide Hidden Elements', value: 'hiddenelements' }
                    ]
                },
                { type: 'separator' },
                { group: 'alignment', label: 'Alignment',
                    buttons: [
                        { type: 'push', label: 'Align Left CTRL + SHIFT + [', value: 'justifyleft' },
                        { type: 'push', label: 'Align Center CTRL + SHIFT + |', value: 'justifycenter' },
                        { type: 'push', label: 'Align Right CTRL + SHIFT + ]', value: 'justifyright' },
                        { type: 'push', label: 'Justify', value: 'justifyfull' }
                    ]
                },
                { type: 'separator' },
                { group: 'parastyle', label: 'Paragraph Style',
                    buttons: [
                    { type: 'select', label: 'Normal', value: 'heading', disabled: true,
                        menu: [
                            { text: 'Normal', value: 'none', checked: true },
                            { text: 'Header 1', value: 'h1' },
                            { text: 'Header 2', value: 'h2' },
                            { text: 'Header 3', value: 'h3' },
                            { text: 'Header 4', value: 'h4' },
                            { text: 'Header 5', value: 'h5' },
                            { text: 'Header 6', value: 'h6' }
                        ]
                    }
                    ]
                },
                { type: 'separator' },
                { group: 'indentlist', label: 'Indenting and Lists',
                    buttons: [
                        { type: 'push', label: 'Indent', value: 'indent', disabled: true },
                        { type: 'push', label: 'Outdent', value: 'outdent', disabled: true },
                        { type: 'push', label: 'Create an Unordered List', value: 'insertunorderedlist' },
                        { type: 'push', label: 'Create an Ordered List', value: 'insertorderedlist' }
                    ]
                },
                { type: 'separator' },
                { group: 'insertitem', label: 'Insert Item',
                    buttons: [
                        { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true },
                        { type: 'push', label: 'Insert Image', value: 'insertimage' },
                        { type: 'push', label: 'Edit HTML Code', value: 'editcode' }
                    ]
                }
            ]
        }
    };

    var myEditor = new YAHOO.widget.Editor('editor', myConfig);
    myEditor._defaultToolbar.buttonType = 'advanced';

    var state = 'off';

    myEditor.on('toolbarLoaded', function() {

        this.toolbar.on('editcodeClick', function() {

            var ta = this.get('element'),
                iframe = this.get('iframe').get('element');

            if (state == 'on') {
                state = 'off';
                this.toolbar.set('disabled', false);

                this.setEditorHTML(ta.value);
                if (!this.browser.ie) {
                    this._setDesignMode('on');
                }

                Dom.removeClass(iframe, 'editor-hidden');
                Dom.addClass(ta, 'editor-hidden');
                this.show();
                this._focusWindow();
            } else {
                state = 'on';

                this.cleanHTML();

                Dom.addClass(iframe, 'editor-hidden');
                Dom.removeClass(ta, 'editor-hidden');
                this.toolbar.set('disabled', true);
                this.toolbar.getButtonByValue('editcode').set('disabled', false);
                this.toolbar.selectButton('editcode');
                this.dompath.innerHTML = 'Editing HTML Code';
                this.hide();
            }
            return false;
        }, this, true);

        this.on('cleanHTML', function(ev) {
            this.get('element').value = ev.html;
        }, this, true);

        this.on('afterRender', function() {
            var wrapper = this.get('editor_wrapper');
            wrapper.appendChild(this.get('element'));
            this.setStyle('width', '100%');
            this.setStyle('height', '100%');
            this.setStyle('visibility', '');
            this.setStyle('top', '');
            this.setStyle('left', '');
            this.setStyle('position', '');

            this.addClass('editor-hidden');
        }, this, true);
    }, myEditor, true);


    myEditor.render();

})();
</script>

<?php

include_once "./footer.php";

?>
