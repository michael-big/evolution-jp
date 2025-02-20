<?php
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}

switch ((int)anyv('a')) {
    case 22:
        if (!evo()->hasPermission('edit_snippet')) {
            alert()->setError(3);
            alert()->dumpError();
        }
        break;
    case 23:
        if (!evo()->hasPermission('new_snippet')) {
            alert()->setError(3);
            alert()->dumpError();
        }
        break;
    default:
        alert()->setError(3);
        alert()->dumpError();
}

$id = (int)anyv('id', 0);

// check to see the snippet editor isn't locked
$rs = db()->select('internalKey, username', '[+prefix+]active_users', "action=22 AND id='{$id}'");
$limit = db()->count($rs);
if ($limit > 1) {
    for ($i = 0; $i < $limit; $i++) {
        $lock = db()->getRow($rs);
        if ($lock['internalKey'] != evo()->getLoginUserID()) {
            $msg = sprintf($_lang['lock_msg'], $lock['username'], $_lang['snippet']);
            alert()->setError(5, $msg);
            alert()->dumpError();
        }
    }
}
// end check for lock

$content = [];
if (getv('id') && preg_match('@^[0-9]+$@', getv('id'))) {
    $rs = db()->select('*', '[+prefix+]site_snippets', "id='{$id}'");
    $limit = db()->count($rs);
    if ($limit > 1) {
        echo "Oops, Multiple snippets sharing same unique id. Not good.<p>";
        exit;
    }
    if ($limit < 1) {
        header("Location: /index.php?id=" . $site_start);
    }
    $content = db()->getRow($rs);
    $_SESSION['itemname'] = $content['name'];
} else {
    $_SESSION['itemname'] = "New snippet";
}
// restore saved form
$formRestored = false;
if (manager()->hasFormValues()) {
    $form_v = manager()->loadFormValues();
    $formRestored = true;
}

if ($formRestored) {
    $content = array_merge($content, $form_v);
}

function entity($key, $default = null)
{
    global $content;
    return $content[$key] ?? $default;
}

?>
<script type="text/javascript">
    function duplicaterecord() {
        if (confirm("<?= $_lang['confirm_duplicate_record'] ?>")) {
            documentDirty = false;
            document.location.href = "index.php?id=<?= anyv('id') ?>&a=98";
        }
    }

    function deletedocument() {
        if (confirm("<?= $_lang['confirm_delete_snippet'] ?>")) {
            documentDirty = false;
            document.location.href = "index.php?id=" + document.mutate.id.value + "&a=25";
        }
    }

    function setTextWrap(ctrl, b) {
        if (!ctrl) return;
        ctrl.wrap = (b) ? "soft" : "off";
    }

    // Current Params
    var currentParams = {};

    function showParameters(ctrl) {
        var c, p, df, cp;
        var ar, desc, value, key, dt;

        currentParams = {}; // reset;

        if (ctrl) {
            f = ctrl.form;
        } else {
            f = document.forms['mutate'];
            if (!f) return;
        }

        // setup parameters
        tr = document.getElementById('displayparamrow');
        dp = (f.properties.value) ? f.properties.value.split("&") : "";
        if (!dp) tr.style.display = 'none';
        else {
            t = '<table width="300" style="margin-bottom:3px;">';
            for (p = 0; p < dp.length; p++) {
                dp[p] = (dp[p] + '').replace(/^\s|\s$/, ""); // trim
                ar = dp[p].split("=");
                key = ar[0]; // param
                ar = (ar[1] + '').split(";");
                desc = ar[0]; // description
                dt = ar[1]; // data type
                value = decode((ar[2]) ? ar[2] : '');

                // store values for later retrieval
                if (key && dt === 'list') currentParams[key] = [desc, dt, value, ar[3]];
                else if (key) currentParams[key] = [desc, dt, value];

                if (dt) {
                    switch (dt) {
                        case 'int':
                            c = '<input type="text" name="prop_' + key + '" value="' + value + '" size="30" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)" />';
                            break;
                        case 'menu':
                            value = ar[3];
                            c = '<select name="prop_' + key + '" style="width:168px" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)">';
                            ls = (ar[2] + '').split(",");
                            if (currentParams[key] == ar[2]) currentParams[key] = ls[0]; // use first list item as default
                            for (i = 0; i < ls.length; i++) {
                                c += '<option value="' + ls[i] + '"' + ((ls[i] == value) ? ' selected="selected"' : '') + '>' + ls[i] + '</option>';
                            }
                            c += '</select>';
                            break;
                        case 'list':
                            value = ar[3];
                            ls = (ar[2] + '').split(",");
                            if (currentParams[key] == ar[2]) currentParams[key] = ls[0]; // use first list item as default
                            c = '<select name="prop_' + key + '" size="' + ls.length + '" style="width:168px" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)">';
                            for (i = 0; i < ls.length; i++) {
                                c += '<option value="' + ls[i] + '"' + ((ls[i] == value) ? ' selected="selected"' : '') + '>' + ls[i] + '</option>';
                            }
                            c += '</select>';
                            break;
                        case 'list-multi':
                            value = (ar[3] + '').replace(/^\s|\s$/, "");
                            arrValue = value.split(",")
                            ls = (ar[2] + '').split(",");
                            if (currentParams[key] == ar[2]) currentParams[key] = ls[0]; // use first list item as default
                            c = '<select name="prop_' + key + '" size="' + ls.length + '" multiple="multiple" style="width:168px" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)">';
                            for (i = 0; i < ls.length; i++) {
                                if (arrValue.length) {
                                    for (j = 0; j < arrValue.length; j++) {
                                        if (ls[i] == arrValue[j]) {
                                            c += '<option value="' + ls[i] + '" selected="selected">' + ls[i] + '</option>';
                                        } else {
                                            c += '<option value="' + ls[i] + '">' + ls[i] + '</option>';
                                        }
                                    }
                                } else {
                                    c += '<option value="' + ls[i] + '">' + ls[i] + '</option>';
                                }
                            }
                            c += '</select>';
                            break;
                        case 'textarea':
                            c = '<textarea class="phptextarea" name="prop_' + key + '" cols="50" rows="4" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)">' + value + '</textarea>';
                            break;
                        default: // string
                            c = '<input type="text" name="prop_' + key + '" value="' + value + '" size="30" onchange="setParameter(\'' + key + '\',\'' + dt + '\',this)" />';
                            break;

                    }
                    t += '<tr><td><div>' + desc + '</div><div>' + c + '</div></td></tr>';
                }

            }
            t += '</table>';
            td = document.getElementById('displayparams');
            td.innerHTML = t;
            tr.style.display = '';
        }
        implodeParameters();
    }

    function setParameter(key, dt, ctrl) {
        var v;
        if (!ctrl) return null;
        switch (dt) {
            case 'int':
                ctrl.value = parseInt(ctrl.value);
                if (isNaN(ctrl.value)) ctrl.value = 0;
                v = ctrl.value;
                break;
            case 'menu':
                v = ctrl.options[ctrl.selectedIndex].value;
                currentParams[key][3] = v;
                implodeParameters();
                return;
            case 'list':
                v = ctrl.options[ctrl.selectedIndex].value;
                currentParams[key][3] = v;
                implodeParameters();
                return;
            case 'list-multi':
                var arrValues = [];
                for (var i = 0; i < ctrl.options.length; i++) {
                    if (ctrl.options[i].selected) {
                        arrValues.push(ctrl.options[i].value);
                    }
                }
                currentParams[key][3] = arrValues.toString();
                implodeParameters();
                return;
            default:
                v = ctrl.value + '';
                break;
        }
        currentParams[key][2] = v;
        implodeParameters();
    }

    // implode parameters
    function implodeParameters() {
        var v, p, s = '';
        for (p in currentParams) {
            if (currentParams[p]) {
                v = currentParams[p].join(";");
                if (s && v) s += ' ';
                if (v) s += '&' + p + '=' + encode(v);
            }
        }
        document.forms['mutate'].properties.value = s;
    }

    function encode(s) {
        s = s + '';
        s = s.replace(/=/g, '%3D'); // =
        s = s.replace(/&/g, '%26'); // &
        return s;
    }

    function decode(s) {
        s = s + '';
        s = s.replace(/%3D/g, '='); // =
        s = s.replace(/%26/g, '&'); // &
        return s;
    }
</script>

<form name="mutate" id="mutate" method="post" action="index.php?a=24" enctype="multipart/form-data">
    <?php
    // invoke OnSnipFormPrerender event
    $tmp = array("id" => $id);
    $evtOut = evo()->invokeEvent("OnSnipFormPrerender", $tmp);
    if (is_array($evtOut)) {
        echo implode("", $evtOut);
    }
    ?>
    <input type="hidden" name="id" value="<?= entity('id') ?>">
    <input type="hidden" name="mode" value="<?= getv('a') ?>">

    <div id="actions">
        <ul class="actionButtons">
            <?php if (evo()->hasPermission('save_snippet')): ?>
                <li id="Button1" class="mutate">
                    <a href="#"
                        onclick="documentDirty=false;jQuery('#mutate').submit();jQuery('#Button1').hide();jQuery('input,textarea,select').addClass('readonly');">
                        <img src="<?= $_style["icons_save"] ?>" /> <?= $_lang['update'] ?>
                    </a>
                    <span class="and"> + </span>
                    <select id="stay" name="stay">
                        <option id="stay1"
                            value="1" <?= anyv('stay') == 1 ? ' selected=""' : '' ?>><?= $_lang['stay_new'] ?></option>
                        <option id="stay2"
                            value="2" <?= anyv('stay') == 2 ? ' selected="selected"' : '' ?>><?= $_lang['stay'] ?></option>
                        <option id="stay3"
                            value="" <?= anyv('stay') == '' ? ' selected=""' : '' ?>><?= $_lang['close'] ?></option>
                    </select>
                </li>
            <?php endif; ?>
            <?php
            if (getv('a') == 22) {
                if (evo()->hasPermission('new_snippet')) {
                    echo manager()->ab(array(
                        'onclick' => 'duplicaterecord();',
                        'icon' => $_style['icons_resource_duplicate'],
                        'label' => $_lang['duplicate']
                    ));
                }
                if (evo()->hasPermission('delete_snippet')) {
                    echo manager()->ab(array(
                        'onclick' => 'deletedocument();',
                        'icon' => $_style['icons_delete_document'],
                        'label' => $_lang['delete']
                    ));
                }
            }
            echo manager()->ab(array(
                'onclick' => "document.location.href='index.php?a=76';",
                'icon' => $_style['icons_cancel'],
                'label' => $_lang['cancel']
            ));
            ?>
        </ul>
    </div>

    <h1><?= $_lang['snippet_title'] ?></h1>

    <div class="sectionBody">
        <div class="tab-pane" id="snipetPane">
            <!-- General -->
            <div class="tab-page" id="tabSnippet">
                <h2 class="tab"><?= $_lang['settings_general'] ?></h2>
                <table>
                    <tr>
                        <th align="left"><?= $_lang['snippet_name'] ?></th>
                        <td align="left">[[<input name="name" type="text" maxlength="100"
                                value="<?= hsc(entity('name')) ?>"
                                class="inputBox" style="width:300px;">]]
                        </td>
                    </tr>
                </table>
                <!-- PHP text editor start -->
                <div>
                    <div
                        style="padding:3px 8px; overflow:hidden;zoom:1; background-color:#eeeeee; border:1px solid #c3c3c3; border-bottom:none;margin-top:5px;">
                        <span style="float:left;font-weight:bold;"><?= $_lang['snippet_code'] ?></span>
                        <span style="float:right;color:#707070;"><?= $_lang['wrap_lines'] ?>
                            <input name="wrap" type="checkbox" checked="checked" class="inputBox"
                                onclick="setTextWrap(document.mutate.post,this.checked)" /></span>
                    </div>
                    <?php
                    if (isset($content['snippet'])) {
                        $code = trim(hsc(entity('snippet')));
                    } elseif (isset($content['post'])) {
                        $code = trim(hsc(entity('post')));
                    } else {
                        $code = '';
                    }
                    ?>
                    <textarea class="phptextarea" dir="ltr" name="post" style="width:100%; height:370px;"
                        wrap="soft"><?= $code ?></textarea>
                </div>
                <!-- PHP text editor end -->
            </div>

            <!-- Properties -->
            <div class="tab-page" id="tabProps">
                <h2 class="tab"><?= $_lang['settings_properties'] ?></h2>
                <table>
                    <tr>
                        <th align="left"><?= $_lang['existing_category'] ?>:</th>
                        <td align="left">
                            <select name="categoryid" style="width:300px;">
                                <option value="0"><?= $_lang["no_category"] ?></option>
                                <?php
                                $ds = manager()->getCategories();
                                if ($ds) {
                                    foreach ($ds as $n => $v) {
                                        echo '<option value="' . $v['id'] . '"' . (entity('category') == $v['id'] ? ' selected="selected"' : '') . '>' . hsc($v['category']) . '</option>';
                                    }
                                }
                                ?>
                                <option value="-1">&gt;&gt; <?= $_lang["new_category"] ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr id="newcategry" style="display:none;">
                        <th align="left" valign="top" style="padding-top:10px;">
                            <?= $_lang['new_category'] ?>:
                        </th>
                        <td align="left" valign="top" style="padding-top:10px;">
                            <input name="newcategory" type="text"
                                maxlength="45" value=""
                                class="inputBox"
                                style="width:300px;">
                        </td>
                    </tr>
                    <tr>
                        <th align="left" style="padding-top:10px">
                            <?= $_lang['snippet_desc'] ?>:
                        </th>
                        <td align="left" style="padding-top:10px">
                            <textarea name="description"
                                style="padding:0;height:4em;"><?= entity('description') ?></textarea>
                        </td>
                    </tr>
                    <?php if (evo()->hasPermission('save_snippet') == 1) { ?>
                        <tr>
                            <td style="padding-top:10px" align="left" valign="top" colspan="2">
                                <label>
                                    <input style="padding:0;margin:0;" name="locked"
                                        type="checkbox" <?= entity('locked') == 1 ? "checked='checked'" : '' ?>
                                        class="inputBox">
                                    <b><?= $_lang['lock_snippet'] ?></b>
                                    <span class="comment"><?= $_lang['lock_snippet_msg'] ?></span>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php
                    $from = "[+prefix+]site_modules AS sm " .
                        "INNER JOIN [+prefix+]site_module_depobj AS smd ON smd.module=sm.id AND smd.type=40 " .
                        "INNER JOIN [+prefix+]site_snippets AS ss ON ss.id=smd.resource ";
                    $ds = db()->select(
                        'sm.id,sm.name,sm.guid',
                        $from,
                        "smd.resource='{$id}' AND sm.enable_sharedparams='1'",
                        'sm.name'
                    );
                    $guid_total = db()->count($ds);
                    if ($guid_total > 0) {
                        $options = '';
                        while ($row = db()->getRow($ds)) {
                            $options .= "<option value='" . $row['guid'] . "'" . (entity('moduleguid') == $row['guid'] ? " selected='selected'" : "") . ">" . hsc($row['name']) . "</option>";
                        }
                    }
                    ?>
                    <?php if ($guid_total > 0) {
                    ?>
                        <tr>
                            <th align="left" style="padding-top:10px;"><?= $_lang['import_params'] ?>:</th>
                            <td align="left" valign="top" style="padding-top:10px;">
                                <select name="moduleguid" style="width:300px;">
                                    <?= $options ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td align="left" valign="top"><span
                                    class="comment"><?= $_lang['import_params_msg'] ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th align="left" valign="top"><?= $_lang['snippet_properties'] ?>:</th>
                        <td align="left" valign="top">
                            <textarea name="properties" maxlength="65535"
                                class="inputBox phptextarea"
                                onChange="showParameters(this);"><?= entity('properties') ?></textarea>
                        </td>
                    </tr>
                    <tr id="displayparamrow">
                        <td valign="top" align="left">&nbsp;</td>
                        <td align="left" id="displayparams">&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabHelp">
                <h2 class="tab">ヘルプ</h2>
                <?= $_lang['snippet_msg'] ?>
            </div>
        </div>
    </div>
    <?php
    // invoke OnSnipFormRender event
    $tmp = array("id" => $id);
    $evtOut = evo()->invokeEvent("OnSnipFormRender", $tmp);
    if (is_array($evtOut)) {
        echo implode("", $evtOut);
    }
    ?>
</form>

<script type="text/javascript">
    setTimeout('showParameters();', 10);
    var tpstatus = <?= (($modx->config['remember_last_tab'] == 2) || (getv('stay') == 2)) ? 'true' : 'false' ?>;
    tpSnippet = new WebFXTabPane(document.getElementById("snipetPane"), tpstatus);
    var readonly = <?= (entity('locked') == 1 || entity('locked') == 'on') ? '1' : '0' ?>;
    if (readonly == 1) {
        jQuery('textarea,input[type=text]').prop('readonly', true);
        jQuery('select').addClass('readonly');
        jQuery('#Button1').hide();
        jQuery('input[name="locked"]').click(function() {
            jQuery('#Button1').toggle();
        });
    }
    jQuery('input[name="locked"]').click(function() {
        jQuery('textarea,input[type=text]').prop('readonly', jQuery(this).prop('checked'));
        jQuery('select').toggleClass('readonly');
    });
    jQuery('select[name="categoryid"]').change(function() {
        if (jQuery(this).val() == '-1') {
            jQuery('#newcategry').fadeIn();
        } else {
            jQuery('#newcategry').fadeOut();
            jQuery('input[name="newcategory"]').val('');
        }
    });
</script>