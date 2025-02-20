<?php

/**
 * @var array $_lang
 * @var array $_style
 */

if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}

switch ((int)anyv('a', 0)) {
    case 78:
        if (!evo()->hasPermission('edit_chunk')) {
            alert()->setError(3);
            alert()->dumpError();
        }
        break;
    case 77:
        if (!evo()->hasPermission('new_chunk')) {
            alert()->setError(3);
            alert()->dumpError();
        }
        break;
    default:
        alert()->setError(3);
        alert()->dumpError();
}

$id = anyv('id', 0);
if (!preg_match('@^[0-9]+$@', $id)) {
    alert()->setError(3);
    alert()->dumpError();
}

// Get table names (alphabetical)

// Check to see the snippet editor isn't locked
$rs = db()->select('internalKey, username', '[+prefix+]active_users', "action=78 AND id='{$id}'");
if (db()->count($rs) > 1) {
    while ($row = db()->getRow($rs)) {
        if ($row['internalKey'] != evo()->getLoginUserID()) {
            $msg = sprintf($_lang['lock_msg'], $row['username'], $_lang['chunk']);
            alert()->setError(5, $msg);
            alert()->dumpError();
        }
    }
}

$content = [];
if (preg_match('@^[1-9][0-9]*$@', $id)) {
    $rs = db()->select('*', '[+prefix+]site_htmlsnippets', "id='{$id}'");
    $total = db()->count($rs);
    if ($total > 1) {
        exit('<p>Error: Multiple Chunk sharing same unique ID.</p>');
    }
    if (!$total) {
        exit("<p>Chunk doesn't exist.</p>");
    }
    $content = db()->getRow($rs);
    $_SESSION['itemname'] = $content['name'];
} else {
    $_SESSION['itemname'] = 'New Chunk';
}

// restore saved form
$formRestored = false;
if (manager()->hasFormValues()) {
    $form_v = manager()->loadFormValues();
    $formRestored = true;
} else {
    $form_v = $_POST;
}

if ($formRestored == true || postv('changeMode')) {
    $content = array_merge($content, $form_v);
    $content['content'] = postv('ta');
}

function entity($key, $default = null)
{
    global $content;

    if (!isset($content['published'])) {
        $content['published'] = 1;
    }
    if ($key === 'pub_date') {
        return !empty($content['pub_date'])
            ? evo()->toDateFormat($content['pub_date'])
            : '';
    }

    if ($key === 'unpub_date') {
        return !empty($content['unpub_date'])
            ? evo()->toDateFormat($content['unpub_date'])
            : '';
    }

    return $content[$key] ?? $default;
}

if (isset($form_v['which_editor'])) {
    $which_editor = postv('which_editor');
} elseif (!entity('editor_type')) {
    $which_editor = 'none';
}

// Print RTE Javascript function
?>
<script>
    // Added for RTE selection
    function changeRTE() {
        var whichEditor = document.getElementById('which_editor');
        if (whichEditor) {
            for (var i = 0; i < whichEditor.length; i++) {
                if (whichEditor[i].selected) {
                    newEditor = whichEditor[i].value;
                    break;
                }
            }
        } else newEditor = '';

        documentDirty = false;
        document.mutate.a.value = <?= manager()->action ?>;
        document.mutate.which_editor.value = newEditor;
        document.mutate.changeMode.value = newEditor;
        document.mutate.submit();
    }

    function duplicaterecord() {
        if (confirm("<?= $_lang['confirm_duplicate_record'] ?>")) {
            documentDirty = false;
            document.location.href = "index.php?id=<?= anyv('id') ?>&a=97";
        }
    }

    function deletedocument() {
        if (confirm("<?= $_lang['confirm_delete_htmlsnippet'] ?>")) {
            documentDirty = false;
            document.location.href = "index.php?id=" + document.mutate.id.value + "&a=80";
        }
    }

    function resetpubdate() {
        if (document.mutate.pub_date.value != '' || document.mutate.unpub_date.value != '') {
            if (confirm("<?= $_lang['mutate_htmlsnippet.dynamic.php1'] ?>")) {
                document.mutate.pub_date.value = '';
                document.mutate.unpub_date.value = '';
            }
        }
        documentDirty = true;
    }
</script>

<form class="htmlsnippet" id="mutate" name="mutate" method="post" action="index.php" enctype="multipart/form-data">
    <?php

    // invoke OnChunkFormPrerender event
    $tmp = ['id' => $id];
    $evtOut = evo()->invokeEvent('OnChunkFormPrerender', $tmp);
    if (is_array($evtOut)) {
        echo implode('', $evtOut);
    }

    ?>
    <input type="hidden" name="a" value="79" />
    <input type="hidden" name="id" value="<?= anyv('id') ?>" />
    <input type="hidden" name="mode" value="<?= (int)anyv('a') ?>" />
    <input type="hidden" name="changeMode" value="" />

    <h1><?= $_lang['htmlsnippet_title'] ?></h1>

    <div id="actions">
        <ul class="actionButtons">
            <?php if (evo()->hasPermission('save_chunk')): ?>
                <li id="save" class="primary mutate">
                    <a href="#" onclick="documentDirty=false;jQuery('#mutate').submit();">
                        <img src="<?= $_style["icons_save"] ?>" /> <?= $_lang['update'] ?>
                    </a>
                    <span class="and"> + </span>
                    <select id="stay" name="stay">
                        <?php if (evo()->hasPermission('new_chunk')) { ?>
                            <option id="stay1"
                                value="1" <?= anyv('stay') == 1 ? ' selected=""' : '' ?>><?= $_lang['stay_new'] ?></option>
                        <?php } ?>
                        <option id="stay2"
                            value="2" <?= anyv('stay') == 2 ? ' selected="selected"' : '' ?>><?= $_lang['stay'] ?></option>
                        <option id="stay3"
                            value="" <?= anyv('stay') == '' ? ' selected=""' : '' ?>><?= $_lang['close'] ?></option>
                    </select>
                </li>
            <?php endif; ?>
            <?php
            if (anyv('a') == '78') {
                if (evo()->hasPermission('new_chunk')) {
                    echo manager()->ab(
                        [
                            'onclick' => 'duplicaterecord();',
                            'icon' => $_style['icons_resource_duplicate'],
                            'label' => $_lang['duplicate']
                        ]
                    );
                }
                if (evo()->hasPermission('delete_chunk')) {
                    echo manager()->ab(
                        [
                            'onclick' => 'deletedocument();',
                            'icon' => $_style['icons_delete_document'],
                            'label' => $_lang['delete']
                        ]
                    );
                }
            }
            echo manager()->ab(
                [
                    'onclick' => "document.location.href='index.php?a=76';",
                    'icon' => $_style['icons_cancel'],
                    'label' => $_lang['cancel']
                ]
            );
            ?>
        </ul>
    </div>

    <div class="sectionBody">
        <div class="tab-pane" id="chunkPane">
            <div class="tab-page" id="tabGeneral">
                <h2 class="tab"><?= $_lang['settings_general'] ?></h2>
                <table>
                    <tr>
                        <th align="left"><?= $_lang['htmlsnippet_name'] ?></th>
                        <td align="left">
                            {{<input name="name" type="text" maxlength="100"
                                    value="<?= hsc(entity('name')) ?>"
                                    class="inputBox" style="width:300px;">}}
                        </td>
                    </tr>
                </table>

                <div>
                    <div
                        style="padding:3px 8px; overflow:hidden;zoom:1; background-color:#eeeeee; border:1px solid #c3c3c3; border-bottom:none;margin-top:5px;">
                        <span style="font-weight:bold;"><?= $_lang['chunk_code'] ?></span>
                    </div>
                    <textarea
                        dir="ltr" class="phptextarea" name="post"
                        style="height:350px;width:100%"><?= hsc(entity('post') ?? entity('snippet')) ?></textarea>
                </div>

                <span class="warning"><?= $_lang['which_editor_title'] ?></span>
                <select id="which_editor" name="which_editor"
                    onchange="gotosave=true;documentDirty=false;changeRTE();">
                    <option value="none" <?= $which_editor === 'none' ? ' selected="selected"' : '' ?>>
                        <?= $_lang['none'] ?>
                    </option>
                    <?php
                    // invoke OnRichTextEditorRegister event
                    $evtOut = evo()->invokeEvent('OnRichTextEditorRegister');
                    if (is_array($evtOut)) {
                        foreach ($evtOut as $i => $editor) {
                            echo "\t" . '<option value="' . $editor . '"' . ($which_editor == $editor ? ' selected="selected"' : '') . '>' . $editor . "</option>\n";
                        }
                    }
                    ?>
                </select>
                <?php

                // invoke OnChunkFormRender event
                $tmp = ['id' => $id];
                $evtOut = evo()->invokeEvent('OnChunkFormRender', $tmp);
                if (is_array($evtOut)) {
                    echo implode('', $evtOut);
                }
                ?>

            </div>

            <div class="tab-page" id="tabInfo">
                <h2 class="tab"><?= $_lang['settings_properties'] ?></h2>
                <table>
                    <tr>
                        <th align="left"><?= $_lang['chunk_opt_published'] ?></th>
                        <td><input
                                name="published" onclick="resetpubdate();"
                                type="checkbox"
                                <?= (entity('published') == 1) ? ' checked="checked"' : '' ?>
                                class="inputBox" value="1" /></td>
                    </tr>
                    <tr>
                        <th align="left"><?= $_lang['page_data_publishdate'] ?></th>
                        <td>
                            <input id="pub_date" name="pub_date" type="text"
                                value="<?= entity('pub_date') ?>" class="DatePicker" />
                            <a onclick="document.mutate.pub_date.value=''; documentDirty=true; return true;"
                                style="cursor:pointer; cursor:hand;">
                                <img src="<?= $_style["icons_cal_nodate"] ?>"
                                    alt="<?= $_lang['remove_date'] ?>" /></a>
                        </td>
                    </tr>
                    <tr>
                        <th align="left"><?= $_lang['page_data_unpublishdate'] ?></th>
                        <td>
                            <input id="unpub_date" name="unpub_date" type="text"
                                value="<?= entity('unpub_date') ?>" class="DatePicker" />
                            <a onclick="document.mutate.unpub_date.value=''; documentDirty=true; return true;"
                                style="cursor:pointer; cursor:hand">
                                <img src="<?= $_style["icons_cal_nodate"] ?>"
                                    alt="<?= $_lang['remove_date'] ?>" /></a>
                        </td>
                    </tr>
                    <tr>
                        <th align="left"><?= $_lang['existing_category'] ?></th>
                        <td align="left"><span style="font-family:'Courier New', Courier, mono"></span>
                            <select name="categoryid" style="width:300px;">
                                <option value="0"><?= $_lang["no_category"] ?></option>
                                <?php
                                $ds = manager()->getCategories();
                                if ($ds) {
                                    foreach ($ds as $n => $v) {
                                        echo "\t\t\t\t" . '<option value="' . $v['id'] . '"' . (entity('category') == $v['id'] || (empty(entity('category')) && postv('categoryid') == $v['id']) ? ' selected="selected"' : '') . '>' . hsc($v['category']) . "</option>\n";
                                    }
                                }
                                ?>
                                <option value="-1">&gt;&gt; <?= $_lang["new_category"] ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr id="newcategry" style="display:none;">
                        <th align="left" valign="middle"><?= $_lang['new_category'] ?></th>
                        <td align="left" valign="top"><input
                                name="newcategory"
                                type="text" maxlength="45"
                                value="<?= entity('newcategory') ?>"
                                class="inputBox" style="width:300px;">
                        </td>
                    </tr>
                    <tr>
                        <th align="left"><?= $_lang['htmlsnippet_desc'] ?></th>
                        <td align="left">
                            <textarea
                                name="description"
                                style="padding:0;height:4em;width:300px;"><?= hsc(entity('description')) ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th align="left" valign="middle"><?= $_lang['resource_opt_richtext'] ?></th>
                        <td align="left" valign="top"><input
                                name="editor_type"
                                type="checkbox"
                                <?= entity('editor_type') == 1 ? ' checked="checked"' : '' ?>
                                class="inputBox" value="1" /></td>
                    </tr>
                    <?php if (evo()->hasPermission('save_chunk') == 1) { ?>
                        <tr>
                            <td align="left" colspan="2">
                                <label><input
                                        name="locked"
                                        type="checkbox"
                                        <?= entity('locked') == 1 || entity('locked') === 'on' ? ' checked="checked"' : '' ?>
                                        class="inputBox" value="on" /> <?= $_lang['lock_htmlsnippet'] ?>
                                    <span class="comment"><?= $_lang['lock_htmlsnippet_msg'] ?></span></label>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="tab-page" id="tabHelp">
                <h2 class="tab">ヘルプ</h2>
                <?= $_lang['htmlsnippet_msg'] ?>
            </div>
        </div>
        <script>
            var stay = <?=
                        ((evo()->config('remember_last_tab') == 2) || (getv('stay') == 2)) ? 'true' : 'false'
                        ?>;
            chunkPane = new WebFXTabPane(document.getElementById('chunkPane'), stay);
        </script>
</form>
</div>
<script>
    var readonly = <?= (entity('locked') == 1 || entity('locked') === 'on') ? 1 : 0 ?>;
    if (readonly == 1) {
        jQuery('textarea,input[type=text]').prop('readonly', true);
        jQuery('select').addClass('readonly');
        jQuery('#save').hide();
        jQuery('input[name="locked"]').click(function() {
            jQuery('#save').toggle();
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
    jQuery('#save a').click(function() {
        documentDirty = false;
        jQuery('#mutate').submit();
    });
</script>
<?php
// invoke OnRichTextEditorInit event
if ($use_editor == 1) {
    $tmp = [
        'editor' => $which_editor,
        'elements' => ['post']
    ];
    $evtOut = evo()->invokeEvent('OnRichTextEditorInit', $tmp);
    if (is_array($evtOut)) {
        echo implode('', $evtOut);
    }
}
