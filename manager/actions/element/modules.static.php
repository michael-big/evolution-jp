<?php

/**
 * @var array $_lang
 * @var array $_style
 */
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}

global $_PAGE;
if (!(evo()->hasPermission('new_module') || evo()->hasPermission('edit_module') || evo()->hasPermission('exec_module'))) {
    alert()->setError(3);
    alert()->dumpError();
}

manager()->initPageViewState();

// get and save search string
if (anyv('op') === 'reset') {
    $query = '';
    $_PAGE['vs']['search'] = '';
} else {
    $query = anyv('search') ?: array_get($_PAGE, 'vs.search');
    $sqlQuery = db()->escape($query);
    $_PAGE['vs']['search'] = $query;
}

// get & save listmode
$listmode = anyv('listmode', array_get($_PAGE, 'vs.lm'));
$_PAGE['vs']['lm'] = $listmode;


// context menu
include_once(MODX_CORE_PATH . 'controls/contextmenu.php');
$cm = new ContextMenu("cntxm", 150);
$cm->addItem(
    $_lang["run_module"],
    "js:menuAction(1)",
    $_style['icons_save'],
    (!evo()->hasPermission('exec_module') ? 1 : 0)
);
$cm->addSeparator();
$cm->addItem(
    $_lang["edit"],
    "js:menuAction(2)",
    $_style['icons_edit_document'],
    (!evo()->hasPermission('edit_module') ? 1 : 0)
);
$cm->addItem(
    $_lang["duplicate"],
    "js:menuAction(3)",
    $_style['icons_resource_duplicate'],
    (!evo()->hasPermission('new_module') ? 1 : 0)
);
$cm->addItem(
    $_lang["delete"],
    "js:menuAction(4)",
    $_style['icons_delete'],
    (!evo()->hasPermission('delete_module') ? 1 : 0)
);
echo $cm->render();

?>
<script type="text/javascript">
    var selectedItem;
    var contextm = <?= $cm->getClientScriptObject() ?>;

    function showContentMenu(id, e) {
        selectedItem = id;
        contextm.style.left = (e.pageX || (e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)))
        <?= $modx_textdir === 'rtl' ? '-190' : ''; ?> + "px"; //offset menu if RTL is selected
        contextm.style.top = (e.pageY || (e.clientY + (document.documentElement.scrollTop || document.body.scrollTop))) + "px";
        contextm.style.visibility = "visible";
        e.cancelBubble = true;
        return false;
    }

    function menuAction(a) {
        var id = selectedItem;
        switch (a) {
            case 1: // run module
                dontShowWorker = true; // prevent worker from being displayed
                window.location.href = 'index.php?a=112&id=' + id;
                break;
            case 2: // edit
                window.location.href = 'index.php?a=108&id=' + id;
                break;
            case 3: // duplicate
                if (confirm("<?= $_lang['confirm_duplicate_record'] ?>")) {
                    window.location.href = 'index.php?a=111&id=' + id;
                }
                break;
            case 4: // delete
                if (confirm("<?= $_lang['confirm_delete_module'] ?>")) {
                    window.location.href = 'index.php?a=110&id=' + id;
                }
                break;
        }
    }

    document.addEvent('click', function() {
        contextm.style.visibility = "hidden";
    });
</script>

<h1><?= $_lang['module_management'] ?></h1>

<div class="sectionBody">
    <!-- load modules -->
    <p><?= $_lang['module_management_msg'] ?></p>

    <div id="actions">
        <ul class="actionButtons">
            <?php if ((evo()->hasPermission('new_module'))) {
                echo '<li id="newModule" class="mutate"><a href="index.php?a=107"><img src="' . $_style["icons_save"] . '" />' . $_lang["new_module"] . '</a></li>';
            } ?>
            <li id="Button5" class="mutate"><a href="#"
                    onclick="documentDirty=false;document.location.href='index.php?a=2';"><img
                        alt="icons_cancel"
                        src="<?= $_style["icons_cancel"] ?>" /> <?= $_lang['cancel'] ?></a></li>
        </ul>
    </div>

    <div>
        <?php
        $yes = $_lang['yes'];
        $icons_modules = $_style['icons_modules'];
        $field = "id,name,description,IF(locked,'{$yes}','-') as 'locked',IF(disabled,'{$yes}','-') as 'disabled',IF(icon<>'',icon,'{$icons_modules}') as'icon'";
        if (!empty($sqlQuery)) {
            $where = "(name LIKE '%{$sqlQuery}%') OR (description LIKE '%{$sqlQuery}%')";
        } else {
            $where = '';
        }
        $orderby = 'editedon DESC, name ASC';
        $ds = db()->select($field, '[+prefix+]site_modules', $where, $orderby);
        include_once(MODX_CORE_PATH . 'controls/datagrid.class.php');
        $grd = new DataGrid('', $ds, $number_of_results); // set page size to 0 t show all items
        $grd->noRecordMsg = $_lang["no_records_found"];
        $grd->cssClass = "grid";
        $grd->columnHeaderClass = "gridHeader";
        $grd->itemClass = "gridItem";
        $grd->altItemClass = "gridAltItem";
        $grd->fields = "icon,name,description,locked,disabled";
        $grd->columns = $_lang["icon"] . " ," . $_lang["name"] . " ," . $_lang["description"] . " ," . $_lang["locked"] . " ," . $_lang["disabled"];
        $grd->colWidths = "34,,,60,60";
        $grd->colAligns = "center,,,center,center";
        $grd->colTypes = "template:<a class='gridRowIcon' href='#' onclick='return showContentMenu([+id+],event);' title='" . $_lang["click_to_context"] . "'><img src='[+value+]' width='32' height='32' /></a>||template:<a href='index.php?a=108&id=[+id+]' title='" . $_lang["module_edit_click_title"] . "'>[+value+]</a>";
        if ($listmode == '1') {
            $grd->pageSize = 0;
        }
        if (anyv('op') === 'reset') {
            $grd->pageNumber = 1;
        }
        // render grid
        echo $grd->render();
        ?>
    </div>
</div>
