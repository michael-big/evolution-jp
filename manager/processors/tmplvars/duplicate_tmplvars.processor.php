<?php
if (!isset($modx) || !evo()->isLoggedin()) {
    exit;
}
if (!evo()->hasPermission('edit_template')) {
    alert()->setError(3);
    alert()->dumpError();
}
$id = getv('id');
if (!preg_match('/^[0-9]+\z/', $id)) {
    echo 'Value of $id is invalid.';
    exit;
}

// duplicate TV
$tpl = $_lang['duplicate_title_string'];
$tbl_site_tmplvars = evo()->getFullTableName('site_tmplvars');
$sql = "INSERT INTO $tbl_site_tmplvars (`type`, `name`, `caption`, `description`, `default_text`, `elements`, `rank`, `display`, `display_params`, `category`)
    SELECT `type`, REPLACE('{$tpl}','[+title+]',`name`) AS `name`, `caption`, `description`, `default_text`, `elements`, `rank`, `display`, `display_params`, `category`
    FROM $tbl_site_tmplvars WHERE `id`={$id}";
$rs = db()->query($sql);

if (!$rs) {
    echo "A database error occured while trying to duplicate TV: <br /><br />" . db()->getLastError();
    exit;
} // get new id

$newid = $modx->db->getInsertId();

if (!$newid) {
    echo "A database error occured while trying to get the new TV id: <br /><br />" . db()->getLastError();
    exit;
}

// duplicate TV Template Access Permissions
$tbl_site_tmplvar_templates = evo()->getFullTableName('site_tmplvar_templates');
$sql = "INSERT INTO {$tbl_site_tmplvar_templates} (tmplvarid, templateid)
		SELECT $newid, templateid
		FROM {$tbl_site_tmplvar_templates} WHERE tmplvarid={$id}";
$rs = db()->query($sql);

if (!$rs) {
    echo "A database error occured while trying to duplicate TV template access: <br /><br />" . db()->getLastError();
    exit;
}


// duplicate TV Access Permissions
$tbl_site_tmplvar_access = evo()->getFullTableName('site_tmplvar_access');
$sql = "INSERT INTO {$tbl_site_tmplvar_access} (tmplvarid, documentgroup)
		SELECT $newid, documentgroup
		FROM {$tbl_site_tmplvar_access} WHERE tmplvarid={$id}";
$rs = db()->query($sql);

if (!$rs) {
    echo "A database error occured while trying to duplicate TV Acess Permissions: <br /><br />" . db()->getLastError();
    exit;
}

// finish duplicating - redirect to new variable
header("Location: index.php?a=301&id=$newid");
