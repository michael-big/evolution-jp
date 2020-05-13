<?php
if(!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') exit();

if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

if (isset($_SESSION['mgrValidated']) && $_SESSION['usertype']!=='manager') {
    @session_destroy();
}

$instcheck_path = MODX_BASE_PATH . 'assets/cache/installProc.inc.php';
if (is_file($instcheck_path)) {
    include_once($instcheck_path);
    if (isset($installStartTime)) {
        if (($_SERVER['REQUEST_TIME'] - $installStartTime) > 5 * 60) {
            unlink($instcheck_path);
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (isset($_COOKIE[session_name()])) {
                session_unset();
                @session_destroy();
            }
            $installGoingOn = 1;
        }
    }
}
if (isset($_GET['installGoingOn'])) {
    $installGoingOn = $_GET['installGoingOn'];
}

// andrazk 20070416 - if session started before install and was not destroyed yet
if (isset($lastInstallTime) && isset($_SESSION['modx.session.created.time']) && isset($_SESSION['mgrValidated'])) {
    if (
        ($_SESSION['modx.session.created.time'] < $lastInstallTime)
        && $_SERVER['REQUEST_METHOD'] !== 'POST'
    ) {
        if (isset($_COOKIE[session_name()])) {
            session_unset();
            @session_destroy();
        }
        header('HTTP/1.0 307 Redirect');
        header('Location: '. MODX_MANAGER_URL . 'index.php?installGoingOn=2');
    }
}

$style_path = MODX_MANAGER_PATH . 'media/style/';
$theme_path = $style_path . $modx->conf_var('manager_theme') . "/";
$touch_path = MODX_BASE_PATH . 'assets/cache/touch.siteCache.idx.php';
if(!isset($_SESSION['mgrValidated']))
{
    if(isset($_GET['frame']) && !empty($_GET['frame'])) $_SESSION['save_uri'] = $_SERVER['REQUEST_URI'];

    // include localized overrides
    include_once(
    sprintf(
        '%slang/%s.inc.php'
        , MODX_CORE_PATH
        , $modx->conf_var('manager_language', 'english')
    )
    );

    if(is_file($theme_path)) {
        include_once($theme_path);
    }

    $modx->setPlaceholder('modx_charset',$modx_manager_charset);
    $modx->setPlaceholder('theme', $modx->conf_var('manager_theme'));
    $modx->setPlaceholder('manager_theme',$modx->conf_var('manager_theme'));
    $modx->setPlaceholder('manager_theme_url'
        , sprintf(
            '%smedia/style/%s/'
            , MODX_MANAGER_URL
            , $modx->conf_var('manager_theme')
        )
    );

    global $tpl, $_lang;

    if(is_file($touch_path) && $_SERVER['REQUEST_TIME'] < filemtime($touch_path)+300) {
        $modx->safeMode = 1;
        $modx->addLog($_lang['logtitle_login_disp_warning'],$_lang['logmsg_login_disp_warning'],2);
        $tpl = file_get_contents("{$style_path}common/login.tpl");
    }
    else touch($touch_path);

    // invoke OnManagerLoginFormPrerender event
    $modx->event->vars = array();
    $modx->event->vars['tpl'] = & $tpl;
    $evtOut = $modx->invokeEvent('OnManagerLoginFormPrerender');
    $modx->event->vars = array();
    $html = is_array($evtOut) ? implode('',$evtOut) : '';
    $modx->setPlaceholder('OnManagerLoginFormPrerender',$html);

    $modx->setPlaceholder('site_name', $modx->conf_var('site_name'));
    $modx->setPlaceholder('logo_slogan',$_lang["logo_slogan"]);
    $modx->setPlaceholder('login_message',$_lang["login_message"]);
    $modx->setPlaceholder('year',date('Y'));

    // andrazk 20070416 - notify user of install/update
    if (isset($installGoingOn)) {
        switch ($installGoingOn) {
            case 1 : $login_message = $_lang["login_cancelled_install_in_progress"]; break;
            case 2 : $login_message = $_lang["login_cancelled_site_was_updated"]   ; break;
        }
        $modx->setPlaceholder(
            'login_message'
            ,'<p><span class="fail">'.$login_message.'</p><p>'.$_lang["login_message"].'</p>'
        );
    }

    if($modx->conf_var('use_captcha')==1) {
        $modx->setPlaceholder(
            'login_captcha_message'
            , sprintf(
                '<p style="margin-top:10px;">%s</p>'
                , $_lang["login_captcha_message"]
            )
        );
        $captcha_image = sprintf(
            '<img id="captcha_image" src="../index.php?get=captcha" alt="%s" />'
            , $_lang["login_captcha_message"]
        );
        $captcha_image = sprintf(
            '<a href="%s" class="loginCaptcha">%s</a>'
            , MODX_MANAGER_URL
            , $captcha_image
        );
        $modx->setPlaceholder('captcha_image',"<div>{$captcha_image}</div>");
        $modx->setPlaceholder(
            'captcha_input'
            , sprintf(
                '<label>%s<input type="text" class="text" name="captcha_code" tabindex="3" value="" autocomplete="off" style="margin-bottom:8px;" /></label>'
                , $_lang["captcha_code"]
            )
        );
    }

    // login info
    $modx->setPlaceholder(
        'uid'
        , preg_replace(
            '/[^a-zA-Z0-9\-_@.]*/'
            , ''
            , $modx->input_cookie('modx_remember_manager','')
        )
    );
    $modx->setPlaceholder('username',$_lang["username"]);
    $modx->setPlaceholder('password',$_lang["password"]);

    // remember me
    $html =  isset($_COOKIE['modx_remember_manager']) ? 'checked="checked"' :'';
    $modx->setPlaceholder('remember_me',$html);
    $modx->setPlaceholder('remember_username',$_lang["remember_username"]);
    $modx->setPlaceholder('login_button',$_lang["login_button"]);

    // load template
    if(!isset($modx->config['manager_login_tpl']) || empty($modx->config['manager_login_tpl'])) {
        $modx->config['manager_login_tpl'] = "{$style_path}common/login.tpl";
    }

    $target = $modx->config['manager_login_tpl'];
    if(isset($tpl) && !empty($tpl)) $login_tpl = $tpl;
    elseif(substr($target,0,1)==='@') {
        if(substr($target,0,6)==='@CHUNK') {
            $target = trim(substr($target,7));
            $login_tpl = $modx->getChunk($target);
        }
        elseif(substr($target,0,5)==='@FILE') {
            $target = trim(substr($target,6));
            $login_tpl = file_get_contents($target);
        }
    } else {
        $chunk = $modx->getChunk($target);
        if($chunk!==false && !empty($chunk)) {
            $login_tpl = $chunk;
        }
        elseif(is_file(MODX_BASE_PATH . $target)) {
            $target = MODX_BASE_PATH . $target;
            $login_tpl = file_get_contents($target);
        }
        elseif(is_file("{$theme_path}login.tpl")) {
            $target = "{$theme_path}login.tpl";
            $login_tpl = file_get_contents($target);
        }
        elseif(is_file("{$theme_path}html/login.html")) { // ClipperCMS compatible
            $target = "{$theme_path}html/login.html";
            $login_tpl = file_get_contents($target);
        }
        else {
            $target = "{$style_path}common/login.tpl";
            $login_tpl = file_get_contents($target);
        }
    }
    $modx->output = $login_tpl;

    // invoke OnManagerLoginFormRender event
    $evtOut = $modx->invokeEvent('OnManagerLoginFormRender');
    $html = is_array($evtOut) ? '<div id="onManagerLoginFormRender">'.implode('',$evtOut).'</div>' : '';
    $modx->setPlaceholder('OnManagerLoginFormRender',$html);

    // merge placeholders
    $modx->output = $modx->parseDocumentSource($modx->output);

    if(is_file($touch_path) && $modx->output)
        unlink($touch_path);

    // little tweak for newer parsers
    $regx = strpos($modx->output,'[[+')!==false ? '~\[\[\+(.*?)\]\]~' : '~\[\+(.*?)\+\]~';
    $modx->output = preg_replace($regx, '', $modx->output); //cleanup

    echo $modx->output;

    exit;

}

// log the user action
$_SESSION['ip'] = $modx->real_ip();

$fields['internalKey'] = $modx->getLoginUserID();
$fields['username']    = $_SESSION['mgrShortname'];
$fields['lasthit']     = $_SERVER['REQUEST_TIME'];
$fields['action']      = $modx->manager->action;
$fields['id']          = (isset($_REQUEST['id']) && preg_match('@^[0-9]+$@',$_REQUEST['id'])) ? $_REQUEST['id'] : 0;
$fields['ip']          = $ip;
if( $modx->manager->action !== 1)
{
    foreach($fields as $k=>$v)
    {
        $keys[]   = $k;
        $values[] = $v;
    }

    $sql = sprintf(
        "REPLACE INTO %s (%s) VALUES ('%s')"
        , $modx->getFullTableName('active_users')
        , implode(',', $keys)
        , implode("','", $values)
    );
    $rs = $modx->db->query($sql);
    if(!$rs) {
        echo "error replacing into active users! SQL: {$sql}\n" . $modx->db->getLastError();
        exit;
    }
    $_SESSION['mgrDocgroups'] = $modx->manager->getMgrDocgroups($modx->getLoginUserID());
}
if(is_file($touch_path)) {
    unlink($touch_path);
}
