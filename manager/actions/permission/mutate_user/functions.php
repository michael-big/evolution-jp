<?php

// converts date format dd-mm-yyyy to php date
function ConvertDate($date)
{
    global $modx;
    if (!$date) {
        return "0";
    }
    return $modx->toTimeStamp($date);
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

function user($key, $default = null)
{
    global $user;
    return evo()->array_get($user, $key, $default);
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

function getUser($userid)
{
    $field = 'mu.*, ua.*';
    $from = [
        '[+prefix+]manager_users mu',
        'LEFT JOIN [+prefix+]user_attributes ua ON ua.internalKey=mu.id'
    ];
    $rs = db()->select(
        $field,
        $from,
        sprintf("mu.id='%s'", db()->escape($userid))
    );

    if (!db()->count($rs)) {
        return false;
    }

    $user = db()->getRow($rs);

    $rs = db()->select('*', '[+prefix+]user_settings', "user='" . $userid . "'");
    while ($row = db()->getRow($rs)) {
        if (isset($user[$row['setting_name']])) {
            continue;
        }
        $user[$row['setting_name']] = $row['setting_value'];
    }

    if (!isset($user['failedlogins'])) {
        $user['failedlogins'] = 0;
    }

    return $user;
}

function hasUserPermission($action)
{
    if ($action == 12) {
        if (!hasPermission('edit_user')) {
            return false;
        }
        return true;
    }
    if ($action == 11) {
        if (!hasPermission('new_user')) {
            return false;
        }
        return true;
    }
    return false;
}

function activeUserCheck($userid)
{
    $rs = db()->select(
        'internalKey, username',
        '[+prefix+]active_users',
        sprintf("action='12' AND id='%s'", $userid)
    );
    if (db()->count($rs) > 1) {
        while ($lock = db()->getRow($rs)) {
            if ($lock['internalKey'] == evo()->getLoginUserID()) {
                continue;
            }
            alert()->setError(5, sprintf(lang('lock_msg'), $lock['username'], 'user'));
            return false;
        }
    }
    return true;
}

function blockedmode($user)
{
    if (!$user) {
        return '0';
    }

    if ($user['blocked']??null == 1) {
        return '1';
    }
    if ($user['blockeduntil']??null && $user['blockeduntil'] > time()) {
        return '1';
    }
    if ($user['blockedafter']??null && $user['blockedafter'] < time()) {
        return '1';
    }
    if (isset($user['failedlogins']) && 3 < $user['failedlogins']) {
        return '1';
    }
    return '0';
}

function saveOptions()
{
    $option = [];
    $option[] = html_tag(
        'option',
        [
            'value' => 'next',
            'selected' => evo()->input_any('save_action') == 'next' ? null : ''
        ],
        lang('stay_new')
    );
    $option[] = html_tag(
        'option',
        [
            'value' => 'stay',
            'selected' => evo()->input_any('save_action') == 'stay' ? null : ''
        ],
        lang('stay')
    );
    $option[] = html_tag(
        'option',
        [
            'value' => 'close',
            'selected' => evo()->input_any('save_action', 'close') == 'close' ? null : ''
        ],
        lang('close')
    );
    return $option;
}

function aButtonSave()
{
    if (!hasPermission('save_user')) {
        return '';
    }
    return html_tag(
        'li',
        [
            'id' => 'Button1',
            'class' => 'mutate'
        ],
        html_tag(
            'a',
            [
                'href' => '#',
                'onclick' => 'documentDirty=false; document.userform.save.click();'
            ],
            html_tag(
                'img',
                ['src' => style('icons_save')]
            )
                . lang('update')
        )
            . html_tag(
                'span',
                ['class' => 'and'],
                ' + '
            )
            . html_tag(
                'select',
                [
                    'id' => 'stay',
                    'name' => 'save_action'
                ],
                implode("\n", saveOptions())
            )
    );
}

function aButtonDelete($userid)
{
    if (request_intvar('a') != '12' || evo()->getLoginUserID() == $userid || !hasPermission('delete_user')) {
        return '';
    }

    return manager()->ab(
        [
            'onclick' => 'deleteuser();',
            'icon' => style('icons_delete_document'),
            'label' => lang('delete')
        ]
    );
}

function aButtonCancel()
{
    return manager()->ab(
        [
            'onclick' => "document.location.href='index.php?a=75';",
            'icon' => style('icons_cancel'),
            'label' => lang('cancel')
        ]
    );
}
