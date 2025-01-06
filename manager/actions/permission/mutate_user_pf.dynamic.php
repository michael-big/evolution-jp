<?php
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}

if (anyv('a') != 74 || !evo()->hasPermission('change_password')) {
    alert()->setError(3);
    alert()->dumpError();
}

$userid = evo()->getLoginUserID();

// get user attribute
$rs = db()->select('*', '[+prefix+]user_attributes', "internalKey='{$userid}'");
$total = db()->count($rs);
if ($total > 1) {
    exit('More than one user returned!<p>');
} elseif ($total < 1) {
    exit('No user returned!<p>');
}

$userdata = db()->getRow($rs);

// get user settings
$rs = db()->select('*', '[+prefix+]user_settings', "user='{$userid}'");
$usersettings = array();
while ($row = db()->getRow($rs)) {
    $usersettings[$row['setting_name']] = $row['setting_value'];
}

// manually extract so that user display settings are not overwritten
foreach ($usersettings as $k => $v) {
    switch ($k) {
        case 'manager_language':
        case 'manager_theme':
            break;
        default:
            ${$k} = $v;
    }
}

// get user name
$rs = db()->select('*', '[+prefix+]manager_users', "id='{$userid}'");
$total = db()->count($rs);
if ($total > 1) {
    exit('More than one user returned while getting username!<p>');
} elseif ($total < 1) {
    exit('No user returned while getting username!<p>');
}

$usernamedata = db()->getRow($rs);

$_SESSION['itemname'] = $usernamedata['username'];

// restore saved form
$formRestored = false;
if ($modx->manager->hasFormValues()) {
    $form_v = $modx->manager->loadFormValues();
    // restore post values
    $userdata = array_merge($userdata, $form_v);
    $userdata['dob'] = ConvertDate($userdata['dob']);
    $usernamedata['username'] = postv('newusername');
    $usernamedata['oldusername'] = postv('oldusername');
    $usersettings = array_merge($usersettings, $userdata);
    $usersettings['allowed_days'] = is_array(postv('allowed_days')) ? implode(",", postv('allowed_days')) : "";
    extract($usersettings, EXTR_OVERWRITE);
}

// include the country list language file
$_country_lang = array();
$lcdir_path = MODX_CORE_PATH . 'lang/country/';
include_once($lcdir_path . 'english_country.inc.php');
$lang_path = sprintf($lcdir_path . '%s_country.inc.php', $modx->config['manager_language']);
if ($modx->config['manager_language'] != "english" && is_file($lang_path)) {
    include_once($lang_path);
}

$displayStyle = ($_SESSION['browser'] === 'modern') ? 'table-row' : 'block';
?>
    <script type="text/javascript">

        function changestate(element) {
            documentDirty = true;
            currval = eval(element).value;
            if (currval == 1) {
                eval(element).value = 0;
            } else {
                eval(element).value = 1;
            }
        }

        function changePasswordState(element) {
            currval = eval(element).value;
            if (currval == 1) {
                document.getElementById("passwordBlock").style.display = "block";
            } else {
                document.getElementById("passwordBlock").style.display = "none";
            }
        }

        function deleteuser() {
            <?php if($_GET['id'] == evo()->getLoginUserID()) { ?>
            alert("<?= $_lang['alert_delete_self'] ?>");
            <?php } else { ?>
            if (confirm("<?= $_lang['confirm_delete_user'] ?>") == true) {
                document.location.href = "index.php?id=" + document.userform.userid.value + "&a=33";
            }
            <?php } ?>
        }

        // change name
        function changeName() {
            if (confirm("<?= $_lang['confirm_name_change'] ?>") == true) {
                var e1 = document.getElementById("showname");
                var e2 = document.getElementById("editname");
                e1.style.display = "none";
                e2.style.display = "<?= $displayStyle ?>";
            }
        }

        function OpenServerBrowser(url, width, height) {
            var iLeft = (screen.width - width) / 2;
            var iTop = (screen.height - height) / 2;

            var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes";
            sOptions += ",width=" + width;
            sOptions += ",height=" + height;
            sOptions += ",left=" + iLeft;
            sOptions += ",top=" + iTop;

            var oWindow = window.open(url, "FCKBrowseWindow", sOptions);
        }

        function BrowseServer() {
            var w = screen.width * 0.7;
            var h = screen.height * 0.7;
            OpenServerBrowser("<?= $base_url ?>manager/media/browser/mcpuk/browser.php?Type=images", w, h);
        }

        function SetUrl(url, width, height, alt) {
            document.userform.photo.value = url;
            document.images['iphoto'].src = "<?= $base_url ?>" + url;
        }
    </script>

    <form action="index.php?a=32" method="post" name="userform" enctype="multipart/form-data">
        <?php

        // invoke OnUserFormPrerender event
        $tmp = array("id" => $userid);
        $evtOut = evo()->invokeEvent("OnUserFormPrerender", $tmp);
        if (is_array($evtOut)) {
            echo implode("", $evtOut);
        }
        ?>
        <input type="hidden" name="mode" value="74">
        <input type="hidden" name="userid" value="<?= $userid ?>">
        <input type="hidden" name="newusername" value="<?= $usernamedata['username'] ?>"/>
        <input type="hidden" name="role" value="<?= $userdata['role'] ?>"/>
        <input type="hidden" name="failedlogincount" value="<?= $userdata['failedlogincount'] ?>">
        <input type="hidden" name="blockedmode"
                value="<?= ($userdata['blocked'] == 1 || ($userdata['blockeduntil'] > time() && $userdata['blockeduntil'] != 0) || ($userdata['blockedafter'] < time() && $userdata['blockedafter'] != 0) || $userdata['failedlogins'] > 3) ? "1" : "0" ?>"/>

        <h1><?= $_lang['profile'] ?></h1>
        <div id="actions">
            <ul class="actionButtons">
                <li id="Button1" class="mutate">
                    <a href="#" onclick="documentDirty=false; document.userform.save.click();">
                        <img src="<?= $_style["icons_save"] ?>"/> <?= $_lang['update'] ?>
                    </a>
                    <span class="and"> + </span>
                    <select id="stay" name="stay">
                        <option id="stay1"
                                value="1" <?= selected(anyv('stay') == 1) ?> ><?= $_lang['stay_new'] ?></option>
                        <option id="stay2"
                                value="2" <?= selected(anyv('stay') == 2) ?> ><?= $_lang['stay'] ?></option>
                        <option id="stay3"
                                value="" <?= selected(anyv('stay') == '') ?> ><?= $_lang['close'] ?></option>
                    </select>
                </li>
                <?php
                if (anyv('a') == 74) { ?>
                    <li id="Button3"><a href="#" onclick="deleteuser();"><img
                                src="<?= $_style["icons_delete_document"] ?>"/> <?= $_lang['delete'] ?>
                        </a></li>
                <?php } ?>
                <li id="Button5" class="mutate"><a href="#" onclick="document.location.href='index.php?a=2';"><img
                            src="<?= $_style["icons_cancel"] ?>"/> <?= $_lang['cancel'] ?></a></li>
            </ul>
        </div>
        <!-- Tab Start -->
        <div class="sectionBody">
            <style type="text/css">
                table.settings {
                    border-collapse: collapse;
                    width: 100%;
                }

                table.settings tr {
                    border-bottom: 1px dotted #ccc;
                }

                table.settings th {
                    font-size: inherit;
                    vertical-align: top;
                    text-align: left;
                }

                table.settings th, table.settings td {
                    padding: 5px;
                }
            </style>
            <div class="tab-pane" id="userPane">
                <!-- Profile -->
                <div class="tab-page" id="tabProfile">
                    <h2 class="tab"><?= $_lang["profile"] ?></h2>
                    <table class="settings">
                        <tr>
                            <th valign="top"><?= $_GET['a'] == '11' ? $_lang['password'] . ":" : $_lang['change_password_new'] . ":" ?></th>
                            <td><label><input name="newpasswordcheck" type="checkbox"
                                            onclick="changestate(document.userform.newpassword);changePasswordState(document.userform.newpassword);"<?= anyv('a') == 11 ? " checked disabled" : "" ?>><input
                                        type="hidden" name="newpassword"
                                        value="<?= anyv('a') == 11 ? 1 : 0 ?>"/></label><br/>
                                <span style="display:<?= anyv('a') == 11 ? "block" : "none" ?>"
                                    id="passwordBlock">
    <fieldset style="width:300px">
    <legend><b><?= $_lang['password_gen_method'] ?></b></legend>
    <label><input type=radio name="passwordgenmethod"
                value="g" <?= postv('passwordgenmethod') == "spec" ? "" : 'checked="checked"' ?> /><?= $_lang['password_gen_gen'] ?></label><br/>
    <label><input type=radio name="passwordgenmethod"
                value="spec" <?= postv('passwordgenmethod') == "spec" ? 'checked="checked"' : "" ?>><?= $_lang['password_gen_specify'] ?></label><br/>
    <div style="padding-left:20px">
    <label for="specifiedpassword" style="width:120px"><?= $_lang['change_password_new'] ?>:</label>
    <input type="password" name="specifiedpassword" onkeypress="document.userform.passwordgenmethod[1].checked=true;"
        size="20" autocomplete="off"/><br/>
    <label for="confirmpassword" style="width:120px"><?= $_lang['change_password_confirm'] ?>:</label>
    <input type="password" name="confirmpassword" onkeypress="document.userform.passwordgenmethod[1].checked=true;"
        size="20" autocomplete="off"/><br/>
    <small><span class="warning" style="font-weight:normal"><?= $_lang['password_gen_length'] ?></span></small>
    </div>
    </fieldset>
    <input type="hidden" name="passwordnotifymethod" value="s"/>
    </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_email'] ?>:</th>
                            <td>
                                <input type="text" name="email" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['email']) ?>"/>
                                <input type="hidden" name="oldemail"
                                        value="<?= htmlspecialchars(!empty($userdata['oldemail']) ? $userdata['oldemail'] : $userdata['email']) ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_full_name'] ?>:</th>
                            <td><input type="text" name="fullname" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['fullname']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_phone'] ?>:</th>
                            <td><input type="text" name="phone" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['phone']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_mobile'] ?>:</th>
                            <td><input type="text" name="mobilephone" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['mobilephone']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_fax'] ?>:</th>
                            <td><input type="text" name="fax" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['fax']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_street'] ?>:</th>
                            <td><input type="text" name="street" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['street']) ?>"
                                        onchange="documentDirty=true;"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_city'] ?>:</th>
                            <td><input type="text" name="city" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['city']) ?>"
                                        onchange="documentDirty=true;"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_state'] ?>:</th>
                            <td><input type="text" name="state" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['state']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_zip'] ?>:</th>
                            <td><input type="text" name="zip" class="inputBox"
                                        value="<?= htmlspecialchars($userdata['zip']) ?>"/></td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_country'] ?>:</th>
                            <td>
                                <select size="1" name="country" class="inputBox">
                                    <?php $chosenCountry = isset($_POST['country']) ? $_POST['country'] : $userdata['country']; ?>
                                    <option value="" <?= selected(empty($chosenCountry)) ?> >&nbsp;</option>
                                    <?php
                                    foreach ($_country_lang as $key => $country) {
                                        echo '<option value="' . $key . '"' . selected(isset($chosenCountry) && $chosenCountry == $key) . ">{$country}</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_dob'] ?>:</th>
                            <td>
                                <input type="text" id="dob" name="dob" class="DatePicker"
                                       value="<?php echo($userdata['dob'] ? $modx->toDateFormat($userdata['dob'],
                                           'dateOnly') : ""); ?>" onblur="documentDirty=true;">
                                <a onclick="document.userform.dob.value=''; return true;"
                                   style="cursor:pointer; cursor:hand"><img align="absmiddle"
                                                                            src="media/style/<?= $manager_theme ?>/images/icons/cal_nodate.gif"
                                                                            border="0"
                                                                            alt="<?= $_lang['remove_date'] ?>"></a>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang['user_gender'] ?>:</th>
                            <td><select name="gender" class="inputBox">
                                    <option value=""></option>
                                    <option
                                        value="1" <?= selected($userdata['gender'] == '1') ?>><?= $_lang['user_male'] ?></option>
                                    <option
                                        value="2" <?= selected($userdata['gender'] == '2') ?>><?= $_lang['user_female'] ?></option>
                                    <option
                                        value="3" <?= selected($userdata['gender'] == '3') ?>><?= $_lang['user_other'] ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th valign="top"><?= $_lang['comment'] ?>:</th>
                            <td>
                                <textarea type="text" name="comment" class="inputBox"
                                          rows="5"><?= htmlspecialchars($userdata['comment']) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang["user_photo"] ?></th>
                            <td><input type="text" maxlength="255" style="width: 150px;" name="photo"
                                       value="<?= htmlspecialchars($userdata['photo']) ?>"/>
                                <input type="button" value="<?= $_lang['insert'] ?>" onclick="BrowseServer();"/>
                                <div><?= $_lang["user_photo_message"] ?></div>
                                <div>
                                    <?php
                                    if (isset($_POST['photo'])) {
                                        $photo = $_POST['photo'];
                                    } elseif (!empty($userdata['photo'])) {
                                        $photo = $userdata['photo'];
                                    } else {
                                        $photo = $modx->config['base_url'] . 'manager/' . $_style['tx'];
                                    }

                                    if (substr($photo, 0, 1) !== '/' && !preg_match('@^https?://@', $photo)) {
                                        $photo = $modx->config['base_url'] . $photo;
                                    }
                                    ?>
                                    <img name="iphoto" src="<?= $photo ?>"/>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Interface & editor settings -->
                <div class="tab-page" id="tabPage5">
                    <h2 class="tab"><?= $_lang["settings_ui"] ?></h2>
                    <table class="settings">
                        <tr>
                            <th><?= $_lang["manager_theme"] ?></th>
                            <td><select name="manager_theme" size="1" class="inputBox"
                                        onchange="document.userform.theme_refresher.value = Date.parse(new Date())">
                                    <option value=""><?= $_lang["user_use_config"] ?></option>
                                    <?php
                                    $files = glob(MODX_MANAGER_PATH . 'media/style/*/style.php');
                                    foreach ($files as $file) {
                                        $file = str_replace('\\', '/', $file);
                                        if ($file != "." && $file != ".." && substr($file, 0, 1) != '.') {
                                            $themename = substr(dirname($file), strrpos(dirname($file), '/') + 1);
                                            $selectedtext = $themename == $usersettings['manager_theme'] ? "selected='selected'" : "";
                                            echo "<option value='$themename' $selectedtext>" . ucwords(str_replace("_",
                                                    " ", $themename)) . "</option>";
                                        }
                                    }
                                    ?>
                                </select><input type="hidden" name="theme_refresher" value="">
                                <div><?= $_lang["manager_theme_message"] ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang["a17_manager_inline_style_title"] ?></th>
                            <td>
                                <textarea name="manager_inline_style" id="manager_inline_style"
                                          style="width:95%; height: 9em;"><?= $manager_inline_style ?></textarea><br/>
                                &nbsp;&nbsp; <label><input type="checkbox" id="default_manager_inline_style"
                                                           name="default_manager_inline_style"
                                                           value="1" <?= isset($usersettings['manager_inline_style']) ? '' : 'checked' ?> /> <?= $_lang["user_use_config"] ?>
                                </label>
                                <div><?= $_lang["a17_manager_inline_style_message"] ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang["mgr_login_start"] ?></th>
                            <td><input type='text' maxlength='50' style="width: 100px;" name="manager_login_startup"
                                       value="<?= isset($_POST['manager_login_startup']) ? $_POST['manager_login_startup'] : $usersettings['manager_login_startup'] ?>">
                                <div><?= $_lang["mgr_login_start_message"] ?></div>
                            </td>
                        </tr>
                        <tr>
                            <th><?= $_lang["language_title"] ?></th>
                            <td><select name="manager_language" size="1" class="inputBox">
                                    <option value=""><?= $_lang["user_use_config"] ?></option>
                                    <?php
                                    $activelang = (!empty($usersettings['manager_language'])) ? $usersettings['manager_language'] : '';
                                    $dir = dir(MODX_CORE_PATH . 'lang');
                                    while ($file = $dir->read()) {
                                        if (strpos($file, '.inc.php') !== false) {
                                            $endpos = strpos($file, ".");
                                            $languagename = trim(substr($file, 0, $endpos));
                                            $selectedtext = selected($activelang === $languagename);
                                            ?>
                                            <option
                                                value="<?= $languagename ?>" <?= $selectedtext ?>><?= ucwords(str_replace("_",
                                                    " ", $languagename)); ?></option>
                                            <?php
                                        }
                                    }
                                    $dir->close();
                                    ?>
                                </select>
                                <div><?= $_lang["language_message"] ?></div>
                            </td>
                        </tr>
                        <tr id="editorRow0" style="display: <?= $use_editor == 1 ? $displayStyle : 'none' ?>">
                            <th><?= $_lang["which_editor_title"] ?></th>
                            <td>
                                <select name="which_editor" class="inputBox">
                                    <option value=""><?= $_lang["user_use_config"] ?></option>
                                    <?php
                                    $edt = isset ($usersettings["which_editor"]) ? $usersettings["which_editor"] : '';
                                    // invoke OnRichTextEditorRegister event
                                    $evtOut = evo()->invokeEvent("OnRichTextEditorRegister");
                                    echo '<option value="none"' . selected($edt == 'none') . ">" . $_lang["none"] . "</option>\n";
                                    if (is_array($evtOut)) {
                                        for ($i = 0; $i < count($evtOut); $i++) {
                                            $selected = selected($edt == $evtOut[$i]);
                                            echo sprintf('<option value="%s">%s</option>', $selected, $evtOut[$i]);
                                        }
                                    }
                                    ?>
                                </select>
                                <div><?= $_lang["which_editor_message"] ?></div>
                            </td>
                        </tr>
                        <tr id="editorRow14" class="row3"
                            style="display: <?= $use_editor == 1 ? $displayStyle : 'none' ?>">
                            <th><?= $_lang["editor_css_path_title"] ?></th>
                            <td><input type="text" maxlength="255" style="width: 250px;" name="editor_css_path"
                                       value="<?= isset($usersettings["editor_css_path"]) ? $usersettings["editor_css_path"] : "" ?>"/>
                                <div><?= $_lang["editor_css_path_message"] ?></div>
                            </td>
                        </tr>
                        <tr class='row1'>
                            <td colspan="2" style="padding:0;">
                                <?php
                                // invoke OnInterfaceSettingsRender event
                                $evtOut = evo()->invokeEvent("OnInterfaceSettingsRender");
                                if (is_array($evtOut)) {
                                    echo implode('', $evtOut);
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <input type="submit" name="save" style="display:none">
        <?php

        // invoke OnUserFormRender event
        $tmp = array("id" => $userid);
        $evtOut = evo()->invokeEvent("OnUserFormRender", $tmp);
        if (is_array($evtOut)) {
            echo implode("", $evtOut);
        }
        ?>
    </form>
    <script type="text/javascript">
        var remember = <?= (($modx->config['remember_last_tab'] == 2) || ($_GET['stay'] == 2)) ? 'true' : 'false' ?>;
        tpUser = new WebFXTabPane(document.getElementById("userPane"), remember);
    </script>
<?php

// converts date format dd-mm-yyyy to php date
function ConvertDate($date)
{
    global $modx;
    if ($date == "") {
        return "0";
    } else {
        return $modx->toTimeStamp($date);
    }
}

function checkbox($name, $value, $label, $cond)
{
    global $modx;
    $tpl = '<label><input type="checkbox" name="[+name+]" value="[+value+]" [+checked+] />[+label+]</label>';
    $ph['name'] = $name;
    $ph['value'] = $value;
    $ph['label'] = $label;
    $ph['checked'] = checked($cond);
    return $modx->parseText($tpl, $ph);
}

function checked($cond)
{
    if ($cond) {
        return 'checked';
    }
    return '';
}

function selected($cond)
{
    if ($cond) {
        return 'selected';
    }
    return '';
}
