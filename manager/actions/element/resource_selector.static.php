<?php
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}

if (!evo()->hasPermission('edit_module')) {
    alert()->setError(3);
    alert()->dumpError();
}

$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html <?= ($modx_textdir === 'rtl' ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"' ?>>

<head>
    <title><?= $content["name"] . " " . $_lang['element_selector_title'] ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= $modx_manager_charset ?>"/>
    <link rel="stylesheet" type="text/css"
          href="media/style/<?= $manager_theme ?>/style.css<?= "?$theme_refresher" ?>"/>
</head>

<body ondragstart="return false">

<?php
/**
 * Resource Selector
 * Created by Raymond Irving May, 2005
 *
 * Selects a resource and returns the id values to the window.opener["callback"]() function as an array.
 * The name of the callback function is passed via the url as &cb
 */

// get name of callback function
$cb = $_REQUEST['cb'];

// get resource type
$rt = strtolower($_REQUEST['rt']);

// get selection method: s - single (default), m - multiple
$sm = strtolower($_REQUEST['sm']);

// get search string
$query = anyv('search');
$sqlQuery = db()->escape($query);

// select SQL
switch ($rt) {
    case "snip":
        $title = $_lang["snippet"];
        $sql = "SELECT id,name,description FROM " . evo()->getFullTableName("site_snippets") .
            ($sqlQuery ? " WHERE (name LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')" : "") . " ORDER BY name";
        break;

    case "tpl":
        $title = $_lang["template"];
        $sql = "SELECT id,templatename as 'name',description FROM " . evo()->getFullTableName("site_templates") .
            ($sqlQuery ? " WHERE (templatename LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')" : "") . " ORDER BY templatename";
        break;

    case ("tv"):
        $title = $_lang["tv"];
        $sql = "SELECT id,name,description FROM " . evo()->getFullTableName("site_tmplvars") .
            ($sqlQuery ? " WHERE (name LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')" : "") . " ORDER BY name";
        break;

    case ("chunk"):
        $title = $_lang["chunk"];
        $sql = "SELECT id,name,description FROM " . evo()->getFullTableName("site_htmlsnippets") .
            ($sqlQuery ? " WHERE (name LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')" : "") . " ORDER BY name";
        break;

    case ("plug"):
        $title = $_lang["plugin"];
        $sql = "SELECT id,name,description FROM " . evo()->getFullTableName("site_plugins") .
            ($sqlQuery ? " WHERE (name LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')" : "") . " ORDER BY name";
        break;

    case ("doc"):
        $title = $_lang["resource"];
        $sql = "SELECT id,pagetitle as 'name',longtitle as 'description' FROM " . evo()->getFullTableName("site_content") .
            ($sqlQuery ? " WHERE (pagetitle LIKE '%$sqlQuery%') OR (longtitle LIKE '%$sqlQuery%')" : "") . " ORDER BY pagetitle";
        break;
}

?>
<script language="JavaScript" type="text/javascript">
    function saveSelection() {
        var ids = [];
        var ctrl = document.selector['id[]'];
        if (!ctrl.length && ctrl.checked) ids[0] = ctrl.value;
        else
            for (i = 0; i < ctrl.length; i++) {
                if (ctrl[i].checked) ids[ids.length] = ctrl[i].value;
            }
        cb = window.opener["<?= $cb ?>"];
        if (cb) cb("<?= $rt ?>", ids);
        window.close();
    }

    function searchResource() {
        document.selector.op.value = "srch";
        document.selector.submit();
    }

    function resetSearch() {
        document.selector.search.value = "";
        searchResource()
    }

    function changeListMode() {
        var m = parseInt(document.selector.listmode.value) ? 1 : 0;
        if (m) document.selector.listmode.value = 0;
        else document.selector.listmode.value = 1;
        document.selector.submit();
    }

    // restore checkbox function
    function restoreChkBoxes() {
        var i, c, chk;
        var a = window.opener.chkBoxArray;
        var f = document.selector;
        chk = f.elements['id[]'];
        if (!chk.length) chk.checked = (a[chk.value]) ? true : false;
        else {
            for (i = 0; i < chk.length; i++) {
                c = chk[i];
                c.checked = (a[c.value]) ? true : false;
            }
        }
    }

    // set checkbox value
    function setCheckbox(chk) {
        var a = window.opener.chkBoxArray;
        a[chk.value] = chk.checked;
    }

    // restore checkboxes
    setTimeout("restoreChkBoxes();", 100);
</script>
<form name="selector" method="get">
    <input type="hidden" name="id" value="<?= $id ?>"/>
    <input type="hidden" name="a" value="<?= (int)anyv('a') ?>"/>
    <input type="hidden" name="listmode" value="<?= anyv('listmode') ?>"/>
    <input type="hidden" name="op" value=""/>
    <input type="hidden" name="rt" value="<?= $rt ?>"/>
    <input type="hidden" name="rt" value="<?= $rt ?>"/>
    <input type="hidden" name="sm" value="<?= $sm ?>"/>
    <input type="hidden" name="cb" value="<?= $cb ?>"/>
    <div class="section" style="margin:1em;">
        <div class="sectionHeader"
            style="margin:0px"><?= $title . " - " . $_lang['element_selector_title'] ?></div>
        <div class="sectionBody" style="margin-right:0px;margin-left:0px;">
            <p><?= $_lang['element_selector_msg'] ?></p>
            <!-- resources -->
            <table width="100%" border="0" cellspacing="1" cellpadding="2">
                <tr>
                    <td>
                        <div class="searchbar">
                            <table border="0" width="100%">
                                <tr>
                                    <td nowrap="nowrap">
                                        <table border="0">
                                            <tr>
                                                <td><?= $_lang["search"] ?></td>
                                                <td><input class="searchtext" name="search" type="text" size="15"
                                                           value="<?= $query ?>"/></td>
                                                <td class="actionButtons"><a href="#"
                                                                             title="<?= $_lang["search"] ?>"
                                                                             onclick="searchResource();return false;"><?= $_lang["go"] ?></a>
                                                </td>
                                                <td class="actionButtons"><a href="#"
                                                                             title="<?= $_lang["reset"] ?>"
                                                                             onclick="resetSearch();return false;"><img
                                                            src="<?= $_style['icons_refresh'] ?>"
                                                            style="display:inline;"/></a></td>
                                                <td class="actionButtons"><a href="#"
                                                                             title="<?= $_lang["list_mode"] ?>"
                                                                             onclick="changeListMode();return false;"><img
                                                            src="<?= $_style['icons_table'] ?>"
                                                            style="display:inline;"/></a></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="230" class="actionButtons">
                                        <a href="#" style="float:right;margin-left:2px;" onclick="window.close()"><img
                                                src="<?= $_style['icons_cancel'] ?>"/> <?= $_lang['cancel'] ?>
                                        </a>
                                        <a href="#" style="float:right;margin-left:2px;" onclick="saveSelection()"><img
                                                src="<?= $_style['icons_add'] ?>"/> <?= $_lang['insert'] ?>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td valign="top" align="left">
                        <?php
                        $ds = db()->query($sql);
                        if (!$ds) {
                            echo "An error occured while loading records.";
                            exit;
                        } else {
                            include_once(MODX_CORE_PATH . 'controls/datagrid.class.php');
                            $grd = new DataGrid('', $ds, $number_of_results); // set page size to 0 t show all items
                            $grd->noRecordMsg = $_lang["no_records_found"];
                            $grd->cssClass = "grid";
                            $grd->columnHeaderClass = "gridHeader";
                            $grd->itemClass = "gridItem";
                            $grd->altItemClass = "gridAltItem";
                            $grd->columns = $_lang["name"] . " ," . $_lang["description"];
                            $grd->colTypes = "template:<label><input type='" . ($sm == 'm' ? 'checkbox' : 'radio') . "' name='id[]' value='[+id+]' onclick='setCheckbox(this);'> [+value+]</label>";
                            $grd->fields = "name,description";
                            if (anyv('listmode') == 1) {
                                $grd->pageSize = 0;
                            }
                            echo $grd->render();
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>
</body>

</html>
