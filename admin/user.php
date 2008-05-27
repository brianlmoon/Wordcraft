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
    wc_admin_error("Invalid mode '".htmlspecialchars($mode)."' for user page.");
}

if($mode=="edit" && empty($_GET["user_id"]) && empty($_POST["user_id"])){
    wc_admin_error("No user_id provided for edit mode.");
}


// init error to empty
$error = "";


// check for POST data
if(count($_POST)){

    if(empty($_POST["user_name"]) || empty($_POST["email"])) {

        $error = "You must fill in a User Name and an Email address.";

    }

    if(empty($error)){

        $user_array = array(
            "user_id"    => $_POST["user_id"],
            "user_name"  => $_POST["user_name"],
            "email"      => $_POST["email"],
            "first_name" => $_POST["first_name"],
            "last_name"  => $_POST["last_name"],
            "about"      => $_POST["editor"],
        );

        $success = wc_db_save_user($user_array);

        if($success){
            wc_admin_message("User Saved!");
        } else{
            $error = "There was an error saving the user.";
        }
    }

    if(!empty($error)){

        // setup the form with the posted data if there is an error
        $user_id = $_POST["user_id"];
        $user_name = $_POST["user_name"];
        $user_first_name = $_POST["first_name"];
        $user_last_name = $_POST["last_name"];
        $user_email = $_POST["email"];
        $user_about = $_POST["about"];
    }

} else {

    // check for initial edit mode
    if(isset($_GET["user_id"])){

        $user = wc_db_get_user($_GET["user_id"]);

        if(!empty($user)){
            $user_id = $user["user_id"];
            $user_name = $user["user_name"];
            $user_first_name = $user["first_name"];
            $user_last_name = $user["last_name"];
            $user_email = $user["email"];
            $user_about = $user["about"];
        } else {
            wc_admin_error("The user you requested to edit was not found.");
        }

    } else {

        // set up new user form
        $user_id = "";
        $user_name = "";
        $user_fist_name = "";
        $user_last_name = "";
        $user_email = "";
        $user_about = "";
    }

}


// set breadcrumb
$WHEREAMI = ($mode=="edit") ? "Edit User" : "New User";


// begin output
include_once "./header.php";

if(!empty($error)){
    wc_admin_error($error, false);
}

?>

<form method="post" action="user.php" id="user-form">

    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>" />
    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>" />

    <p>
        <strong>User Name:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_name); ?>" id="user_name" name="user_name" maxlength="20" />
    </p>

    <p>
        <strong>Email:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_email); ?>" id="email" name="email" maxlength="50" />
    </p>

    <p>
        <strong>First Name:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_first_name); ?>" id="first_name" name="first_name" maxlength="25" />
    </p>

    <p>
        <strong>Last Name:</strong><br />
        <input class="inputgri" type="text" value="<?php echo htmlspecialchars($user_last_name); ?>" id="last_name" name="last_name" maxlength="25" />
    </p>

    <p>
        <strong>About This User:</strong><br />
        <textarea id="editor" name="editor" rows="20" cols="75"><?php echo htmlspecialchars($user_about); ?></textarea>
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
