<?php
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}
if (!evo()->hasPermission('web_access_permissions') || $modx->config['use_udperms'] == 0) {
    alert()->setError(3);
    alert()->dumpError();
}

$tbl_documentgroup_names = evo()->getFullTableName('documentgroup_names');
$tbl_webgroup_names = evo()->getFullTableName('webgroup_names');
// find all document groups, for the select :)
$rs = db()->select('id,name', $tbl_documentgroup_names, '', 'name');
if (db()->count($rs) < 1) {
    $docgroupselector = "[no groups to add]";
} else {
    $docgroupselector = '<select name="docgroup">' . "\n";
    while ($row = db()->getRow($rs)) {
        $docgroupselector .= "\t" . '<option value="' . $row['id'] . '">' . $row['name'] . "</option>\n";
    }
    $docgroupselector .= "</select>\n";
}

$rs = db()->select('id,name', $tbl_webgroup_names, '', 'name');
if (db()->count($rs) < 1) {
    $usrgroupselector = '[no user groups]';
} else {
    $usrgroupselector = '<select name="usergroup">' . "\n";
    while ($row = db()->getRow($rs)) {
        $usrgroupselector .= "\t" . '<option value="' . $row['id'] . '">' . $row['name'] . "</option>\n";
    }
    $usrgroupselector .= "</select>\n";
}

?>
<h1><?= $_lang['web_access_permissions'] ?></h1>

<div id="actions">
    <ul class="actionButtons">
        <li id="Button5" class="mutate"><a href="#"
                                           onclick="documentDirty=false;document.location.href='index.php?a=2';"><img
                    alt="icons_cancel" src="<?= $_style["icons_cancel"] ?>"/> <?= $_lang['cancel'] ?>
            </a></li>
    </ul>
</div>

<div class="sectionBody">
    <p><?= $_lang['access_permissions_introtext'] ?></p><?= $modx->config['use_udperms'] != 1 ? '<p>' . $_lang['access_permissions_off'] . '</p>' : '' ?>

    <div class="tab-pane" id="tabPane1">
        <div class="tab-page" id="tabPage1">
            <h2 class="tab"><?= $_lang['web_access_permissions_user_groups'] ?></h2>
            <?php
            // User Groups

            echo '<p>' . $_lang['access_permissions_users_tab'] . '</p>';
            ?>
            <table width="300" border="0" cellspacing="1" cellpadding="3" bgcolor="#ccc">
                <thead>
                <tr>
                    <td><b><?= $_lang['access_permissions_add_user_group'] ?></b></td>
                </tr>
                </thead>
                <tr class="row1">
                    <td>
                        <form method="post" action="index.php" name="accesspermissions" style="margin: 0px;"
                              enctype="multipart/form-data">
                            <input type="hidden" name="a" value="92"/>
                            <input type="hidden" name="operation" value="add_user_group"/>
                            <input type="text" value="" name="newusergroup"/>&nbsp;
                            <input type="submit" value="<?= $_lang['submit'] ?>"/>
                        </form>
                    </td>
                </tr>
            </table>
            <br/>
            <?php
            $tbl_web_groups = evo()->getFullTableName('web_groups');
            $tbl_web_users = evo()->getFullTableName('web_users');
            $field = 'groupnames.*, users.id AS user_id, `users`.username user_name';
            $from = "{$tbl_webgroup_names} AS groupnames";
            $from .= " LEFT JOIN {$tbl_web_groups} AS `groups` ON `groups`.webgroup = groupnames.id";
            $from .= " LEFT JOIN {$tbl_web_users}  AS `users` ON `users`.id = `groups`.webuser";
            $rs = db()->select($field, $from, '', 'groupnames.name');
            if (db()->count($rs) < 1) {
                echo '<span class="warning">' . $_lang['no_groups_found'] . '</span>';
            } else {
                echo "<ul>\n";
                $pid = '';
                while ($row = db()->getRow($rs)) {
                    if ($row['id'] !== $pid) {
                        if ($pid != '') {
                            echo "</li></ul></li>\n";
                        } // close previous one

                        // display the current user group with a rename/delete form
                        echo '<li><form method="post" action="index.php" name="accesspermissions" style="margin-top: 0.5em;" enctype="multipart/form-data">' . "\n" .
                            '	<input type="hidden" name="a" value="92" />' . "\n" .
                            '	<input type="hidden" name="groupid" value="' . $row['id'] . '" />' . "\n" .
                            '	<input type="hidden" name="operation" value="rename_user_group" />' . "\n" .
                            '	<input type="text" name="newgroupname" value="' . htmlspecialchars($row['name']) . '" />&nbsp;' . "\n" .
                            '	<input type="submit" value="' . $_lang['rename'] . '" />&nbsp;' . "\n" .
                            '	<input type="button" value="' . $_lang['delete'] . '" onclick="document.location.href=\'index.php?a=92&usergroup=' . $row['id'] . '&operation=delete_user_group\';" />' . "\n" .
                            '</form>';

                        echo "<ul>\n";
                        echo "\t<li>" . $_lang['web_access_permissions_users_in_group'] . ' ';
                    }
                    if (!$row['user_id']) {
                        // no users in group
                        echo '<i>' . $_lang['access_permissions_no_users_in_group'] . '</i>';
                        $pid = $row['id'];
                        continue;
                    }
                    if ($pid == $row['id']) {
                        echo ', ';
                    } // comma separation :)
                    echo '<a href="index.php?a=88&amp;id=' . $row['user_id'] . '">' . $row['user_name'] . '</a>';
                    $pid = $row['id'];
                }
                echo "</li></ul></li>\n";
                echo "</ul>\n";
            }
            ?>
        </div>


        <div class="tab-page" id="tabPage2">
            <h2 class="tab"><?= $_lang['access_permissions_resource_groups'] ?></h2>
            <?php
            // Document Groups

            echo '<p>' . $_lang['access_permissions_resources_tab'] . '</p>';

            $sql = 'SELECT ' .
                'dgnames.id, ' .
                'dgnames.name, ' .
                'sc.id AS doc_id, ' .
                'sc.pagetitle AS doc_title ' .
                'FROM ' . $tbl_documentgroup_names . ' AS dgnames ' .
                'LEFT JOIN ' . evo()->getFullTableName('document_groups') . ' AS dg ON dg.document_group = dgnames.id ' .
                'LEFT JOIN ' . evo()->getFullTableName('site_content') . ' AS sc ON sc.id = dg.document ' .
                'ORDER BY dgnames.name, sc.id';
            ?>
            <table width="300" border="0" cellspacing="1" cellpadding="3" bgcolor="#ccc">
                <thead>
                <tr>
                    <td><b><?= $_lang['access_permissions_add_resource_group'] ?></b></td>
                </tr>
                </thead>
                <tr class="row1">
                    <td>
                        <form method="post" action="index.php" name="accesspermissions" style="margin: 0px;"
                              enctype="multipart/form-data">
                            <input type="hidden" name="a" value="92"/>
                            <input type="hidden" name="operation" value="add_document_group"/>
                            <input type="text" value="" name="newdocgroup"/>&nbsp;
                            <input type="submit" value="<?= $_lang['submit'] ?>"/>
                        </form>
                    </td>
                </tr>
            </table>
            <br/>
            <?php
            $rs = db()->query($sql);
            if (db()->count($rs) < 1) {
                echo '<span class="warning">' . $_lang['no_groups_found'] . '</span>';
            } else {
                echo '<table width="600" border="0" cellspacing="1" cellpadding="3" bgcolor="#ccc">' . "\n" .
                    '	<thead>' . "\n" .
                    '	<tr><td><b>' . $_lang['access_permissions_resource_groups'] . '</b></td></tr>' . "\n" .
                    '	</thead>' . "\n";
                $pid = '';
                while ($row = db()->getRow($rs)) {
                    if ($row['id'] !== $pid) {
                        if ($pid != '') {
                            echo "</td></tr>\n";
                        } // close previous one

                        echo '<tr><td class="row3"><form method="post" action="index.php" name="accesspermissions" style="margin: 0px;" enctype="multipart/form-data">' . "\n" .
                            '	<input type="hidden" name="a" value="92" />' . "\n" .
                            '	<input type="hidden" name="groupid" value="' . $row['id'] . '" />' . "\n" .
                            '	<input type="hidden" name="operation" value="rename_document_group" />' . "\n" .
                            '	<input type="text" name="newgroupname" value="' . htmlspecialchars($row['name']) . '">&nbsp;' . "\n" .
                            '	<input type="submit" value="' . $_lang['rename'] . '">' . "\n" .
                            '	<input type="button" value="' . $_lang['delete'] . '" onclick="document.location.href=\'index.php?a=92&documentgroup=' . $row['id'] . '&operation=delete_document_group\';" />' . "\n" .
                            '</form>';

                        echo '</td></tr><tr><td class="row2">' . $_lang['access_permissions_resources_in_group'] . ' ';
                    }
                    if (!$row['doc_id']) {
                        // no documents in group
                        echo $_lang['access_permissions_no_resources_in_group'];
                        $pid = $row['id'];
                        continue;
                    }
                    if ($pid == $row['id']) {
                        echo ", \n";
                    }
                    echo '<a href="index.php?a=3&amp;id=' . $row['doc_id'] . '" title="' . htmlspecialchars($row['doc_title']) . '">' . $row['doc_id'] . '</a>';
                    $pid = $row['id'];
                }
                echo "</table>";
            }
            ?>
        </div>

        <div class="tab-page" id="tabPage3">
            <h2 class="tab"><?= $_lang['access_permissions_links'] ?></h2>
            <?php
            // User/Document Group Links

            echo '<p>' . $_lang['access_permissions_links_tab'] . '</p>';

            $sql = "SELECT " .
                "groupnames.*, " .
                "groupacc.id AS link_id, " .
                "dgnames.id AS dg_id, " .
                "dgnames.name AS dg_name " .
                "FROM " . $tbl_webgroup_names . " AS groupnames " .
                "LEFT JOIN " . evo()->getFullTableName('webgroup_access') . " AS groupacc ON groupacc.webgroup = groupnames.id " .
                "LEFT JOIN " . $tbl_documentgroup_names . " AS dgnames ON dgnames.id = groupacc.documentgroup " .
                "ORDER BY name";
            $rs = db()->query($sql);
            if (db()->count($rs) < 1) {
                echo '<span class="warning">' . $_lang['no_groups_found'] . '</span><br />';
            } else {
                ?>
                <table border="0" cellspacing="1" cellpadding="3" bgcolor="#ccc">
                    <thead>
                    <tr>
                        <td><b><?= $_lang["access_permissions_group_link"] ?></b></td>
                    </tr>
                    </thead>
                    <tr class="row1">
                        <td>
                            <form method="post" action="index.php" name="accesspermissions" style="margin: 0px;">
                                <input type="hidden" name="a" value="92"/>
                                <input type="hidden" name="operation" value="add_document_group_to_user_group"/>
                                <?= $_lang["access_permissions_link_user_group"] ?>
                                <?= $usrgroupselector ?>
                                <?= $_lang["access_permissions_link_to_group"] ?>
                                <?= $docgroupselector ?>
                                <input type="submit" value="<?= $_lang['submit'] ?>">
                            </form>
                        </td>
                    </tr>
                </table>
                <br/>
                <?php
                echo "<ul>\n";
                $pid = '';
                while ($row = db()->getRow($rs)) {
                    if ($row['id'] != $pid) {
                        if ($pid != '') {
                            echo "</ul></li>\n";
                        } // close previous one
                        echo '<li><b>' . $row['name'] . '</b>';

                        if (!$row['dg_id']) {
                            echo ' &raquo; <i>' . $_lang['no_groups_found'] . "</i></li>\n";
                            $pid = '';
                            continue;
                        } else {
                            echo "<ul>\n";
                        }
                    }
                    if (!$row['dg_id']) {
                        continue;
                    }
                    echo "\t<li>" . $row['dg_name'];
                    echo ' <small><i>(<a href="index.php?a=92&amp;coupling=' . $row['link_id'] . '&amp;operation=remove_document_group_from_user_group">';
                    echo $_lang['remove'] . '</a>)</i></small>';
                    echo "</li>\n";

                    $pid = $row['id'];
                }
                echo "</ul>";
            }
            ?>
        </div>

    </div>
</div>
<script type="text/javascript">tp1 = new WebFXTabPane(document.getElementById("tabPane1"), true);</script>
