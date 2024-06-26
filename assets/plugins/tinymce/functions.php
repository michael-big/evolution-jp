<?php

class TinyMCE
{
    public $params;

    function __construct()
    {
        global $modx;
        $this->params = $modx->event->params;
        $this->params['mce_path'] = MODX_BASE_PATH . 'assets/plugins/tinymce/';
        $this->params['mce_url'] = MODX_BASE_URL . 'assets/plugins/tinymce/';
    }

    function get_lang($lang)
    {
        switch (strtolower($lang)) {
            case 'russian-utf8' :
                $lc = 'ru';
                break;
            case 'japanese-utf8':
            case 'japanese-euc' :
                $lc = 'ja';
                break;
            default             :
                $lc = 'en';
        }
        return $lc;
    }

    function get_skin_names()
    {
        global $modx, $_lang;
        $params = $this->params;
        $mce_path = $params['mce_path'];
        $option = array();

        $skin_dir = $mce_path . "tiny_mce/themes/advanced/skins/";
        switch ($modx->manager->action) {
            case '11':
            case '12':
            case '74':
                $selected = $this->selected(empty($params['mce_editor_skin']));
                $option[] = '<option value=""' . $selected . '>' . $_lang['mce_theme_global_settings'] . "</option>";
                break;
        }
        foreach (glob("{$skin_dir}*", GLOB_ONLYDIR) as $dir) {
            $dir = str_replace('\\', '/', $dir);
            $skin_name = substr($dir, strrpos($dir, '/') + 1);
            $skins[$skin_name][] = 'default';
            $styles = glob("{$dir}/ui_*.css");
            if (is_array($styles) && 0 < count($styles)) {
                foreach ($styles as $css) {
                    $skin_variant = substr($css, strrpos($css, '_') + 1);
                    $skin_variant = substr($skin_variant, 0, strrpos($skin_variant, '.'));
                    $skins[$skin_name][] = $skin_variant;
                }
            }
            foreach ($skins as $k => $o) {
                foreach ($o as $v) {
                    if ($v === 'default') {
                        $value = $k;
                    } else {
                        $value = "{$k}:{$v}";
                    }
                    $selected = $this->selected($value == $params['mce_editor_skin']);
                    $option[] = '<option value="' . $value . '"' . $selected . '>' . $value . "</option>";
                }
            }
        }
        return implode("\n", $option);
    }

    function selected($cond = false)
    {
        if ($cond !== false) return ' selected="selected"';
        else                return '';
    }

    function checked($cond = false)
    {
        if ($cond !== false) return ' checked="checked"';
        else                return '';
    }

    function get_mce_settings()
    {
        global $modx, $_lang, $usersettings, $settings;
        $params = &$this->params;
        $mce_path = $params['mce_path'];
        $ph = array();

        switch ($modx->manager->action) {
            case 11:
                $config = array();
                break;
            case 12:
            case 74:
                $config = $usersettings;
                if ($usersettings['tinymce_editor_theme']) {
                    $usersettings['tinymce_editor_theme'] = $settings['tinymce_editor_theme'];
                }
                break;
            case 17:
            default:
                $config = $settings;
                break;
        }
        $params['theme'] = $config['tinymce_editor_theme'];
        $params['mce_editor_skin'] = $config['mce_editor_skin'];
        $params['mce_entermode'] = $config['mce_entermode'];
        $params['mce_element_format'] = $config['mce_element_format'];
        $params['mce_schema'] = $config['mce_schema'];
        $params['css_selectors'] = $config['tinymce_css_selectors'];
        $params['custom_plugins'] = $config['tinymce_custom_plugins'];
        $params['custom_buttons1'] = $config['tinymce_custom_buttons1'];
        $params['custom_buttons2'] = $config['tinymce_custom_buttons2'];
        $params['custom_buttons3'] = $config['tinymce_custom_buttons3'];
        $params['custom_buttons4'] = $config['tinymce_custom_buttons4'];
        $params['mce_template_docs'] = $config['mce_template_docs'];
        $params['mce_template_chunks'] = $config['mce_template_chunks'];

        // language settings
        if (!@include($mce_path . "lang/" . $modx->config['manager_language'] . '.inc.php')) {
            include_once("{$mce_path}lang/english.inc.php");
        }

        include_once("{$mce_path}settings/default_params.php");
        $ph += $_lang;

        $theme_options = '';
        switch ($modx->manager->action) {
            case '11';
            case '12';
            case '74';
                $selected = empty($params['theme']) ? '"selected"' : '';
                $theme_options .= '<option value="" ' . $selected . '>' . $_lang['mce_theme_global_settings'] . "</option>\n";
        }
        $themes['simple'] = $_lang['mce_theme_simple'];
        $themes['editor'] = $_lang['mce_theme_editor'];
        $themes['creative'] = $_lang['mce_theme_creative'];
        $themes['logic'] = $_lang['mce_theme_logic'];
        $themes['advanced'] = $_lang['mce_theme_advanced'];
        $themes['legacy'] = (!empty($_lang['mce_theme_legacy'])) ? $_lang['mce_theme_legacy'] : 'legacy';
        $themes['custom'] = $_lang['mce_theme_custom'];
        foreach ($themes as $key => $value) {
            $selected = $this->selected($key == $params['theme']);
            $key = '"' . $key . '"';
            $theme_options .= "<option value={$key}{$selected}>{$value}</option>\n";
        }
        $ph['display'] = ($_SESSION['browser'] === 'modern') ? 'table-row' : 'block';
        $ph['display'] = $modx->config['use_editor'] == 1 ? $ph['display'] : 'none';

        $ph['theme_options'] = $theme_options;
        $ph['skin_options'] = $this->get_skin_names();

        $ph['entermode_options'] = '<label><input name="mce_entermode" type="radio" value="p" ' . $this->checked($ph['mce_entermode'] === 'p') . '/>' . $_lang['mce_entermode_opt1'] . '</label><br />';
        $ph['entermode_options'] .= '<label><input name="mce_entermode" type="radio" value="br" ' . $this->checked($ph['mce_entermode'] === 'br') . '/>' . $_lang['mce_entermode_opt2'] . '</label>';
        switch ($modx->manager->action) {
            case '11':
            case '12':
            case '74':
                $ph['entermode_options'] .= '<br />';
                $ph['entermode_options'] .= '<label><input name="mce_entermode" type="radio" value="" ' . $this->checked(empty($params['mce_entermode'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
                break;
        }

        $ph['element_format_options'] = '<label><input name="mce_element_format" type="radio" value="xhtml" ' . $this->checked($ph['mce_element_format'] === 'xhtml') . '/>XHTML</label><br />';
        $ph['element_format_options'] .= '<label><input name="mce_element_format" type="radio" value="html" ' . $this->checked($ph['mce_element_format'] === 'html') . '/>HTML</label>';
        switch ($modx->manager->action) {
            case '11':
            case '12':
            case '74':
                $ph['element_format_options'] .= '<br />';
                $ph['element_format_options'] .= '<label><input name="mce_element_format" type="radio" value="" ' . $this->checked(empty($params['mce_element_format'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
                break;
        }

        $ph['schema_options'] = '<label><input name="mce_schema" type="radio" value="html4" ' . $this->checked($ph['mce_schema'] === 'html4') . '/>HTML4(XHTML)</label><br />';
        $ph['schema_options'] .= '<label><input name="mce_schema" type="radio" value="html5" ' . $this->checked($ph['mce_schema'] === 'html5') . '/>HTML5</label>';
        switch ($modx->manager->action) {
            case '11':
            case '12':
            case '74':
                $ph['schema_options'] .= '<br />';
                $ph['schema_options'] .= '<label><input name="mce_schema" type="radio" value="" ' . $this->checked(empty($params['mce_schema'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
                break;
        }

        $gsettings = file_get_contents("{$mce_path}inc/gsettings.inc.html");

        foreach ($ph as $name => $value) {
            $name = '[+' . $name . '+]';
            $gsettings = str_replace($name, $value, $gsettings);
        }
        return $gsettings;
    }

    function get_mce_script()
    {
        global $modx;
        $params = &$this->params;
        $mce_path = $params['mce_path'];
        $mce_url = $params['mce_url'];

        $params['css_selectors'] = $modx->config['tinymce_css_selectors'];
        $params['use_browser'] = $modx->config['use_browser'];
        $params['editor_css_path'] = $modx->config['editor_css_path'];

        if ($modx->isBackend() || ((int)$_GET['quickmanagertv'] == 1 && isset($_SESSION['mgrValidated']))) {
            $params['theme'] = $modx->config['tinymce_editor_theme'];
            $params['mce_editor_skin'] = $modx->config['mce_editor_skin'];
            $params['mce_entermode'] = $modx->config['mce_entermode'];
            $params['language'] = $this->get_lang($modx->config['manager_language']);
            $params['frontend'] = false;
            $params['custom_plugins'] = $modx->config['tinymce_custom_plugins'];
            $params['custom_buttons1'] = $modx->config['tinymce_custom_buttons1'];
            $params['custom_buttons2'] = $modx->config['tinymce_custom_buttons2'];
            $params['custom_buttons3'] = $modx->config['tinymce_custom_buttons3'];
            $params['custom_buttons4'] = $modx->config['tinymce_custom_buttons4'];
            $params['toolbar_align'] = $modx->config['manager_direction'] === 'rtl' ? 'rtl' : 'ltr';
            $params['webuser'] = null;
        } else {
            $frontend_language = isset($modx->config['fe_editor_lang']) ? $modx->config['fe_editor_lang'] : '';
            $webuser = (isset($modx->config['rb_webuser']) ? $modx->config['rb_webuser'] : null);

            $params['theme'] = $params['webtheme'];
            $params['webuser'] = $webuser;
            $params['language'] = $this->get_lang($frontend_language);
            $params['frontend'] = true;
            $params['custom_plugins'] = $params['webPlugins'];
            $params['custom_buttons1'] = $params['webButtons1'];
            $params['custom_buttons2'] = $params['webButtons2'];
            $params['custom_buttons3'] = $params['webButtons3'];
            $params['custom_buttons4'] = $params['webButtons4'];
            $params['toolbar_align'] = $params['webAlign'];
        }

        $str = '';

        $theme = $params['theme'];
        switch ($theme) {
            case 'custom':
                $plugins = $params['custom_plugins'];
                $buttons1 = $params['custom_buttons1'];
                $buttons2 = $params['custom_buttons2'];
                $buttons3 = $params['custom_buttons3'];
                $buttons4 = $params['custom_buttons4'];
                break;
            case 'simple':
            case 'creative':
            case 'logic':
            case 'legacy':
            case 'advanced':
            case 'full':
            case 'default':
            case 'editor':
            default:
                $set = include($mce_path . 'settings/toolbar.settings.inc.php');
                if (empty($theme) || $theme === 'editor') {
                    $theme = 'default';
                }
                $plugins = $set[$theme]['p'];
                $buttons1 = $set[$theme]['b1'];
                $buttons2 = $set[$theme]['b2'];
                $buttons3 = $set[$theme]['b3'];
                $buttons4 = $set[$theme]['b4'];
                if (is_dir("{$mce_path}tiny_mce/plugins/quickupload")) {
                    $plugins = 'quickupload,' . $plugins;
                    $buttons2 = 'quickupload,' . $buttons2;
                }
                if ($modx->manager->action == '4' || $modx->manager->action == '27' || $modx->manager->action == '78') {
                    global $content;
                    if (isset($content['template']) && $content['template'] === '0') {
                        $plugins = str_replace('autosave', '', $plugins);
                        if (strpos($plugins, 'fullpage') === false) {
                            $plugins .= ',fullpage';
                        }
                        if (strpos($buttons1 . $buttons2 . $buttons3 . $buttons4, 'fullpage') === false) {
                            if (!empty($buttons2)) $buttons2 = 'fullpage,' . $buttons2;
                            else                  $buttons1 .= ',fullpage';
                        }
                    }
                    if (empty($modx->config['mce_template_docs']) && empty($modx->config['mce_template_chunks'])) {
                        $plugins = str_replace(
                            array('template', ',,'),
                            array('', ','),
                            $plugins
                        );
                        $buttons1 = str_replace(',template', '', $buttons1);
                        $buttons2 = str_replace(',template', '', $buttons2);
                        $buttons3 = str_replace(',template', '', $buttons3);
                        $buttons4 = str_replace(',template', '', $buttons4);
                    }
                }
        }

        $str .= $this->build_mce_init($plugins, $buttons1, $buttons2, $buttons3, $buttons4) . "\n";
        $str .= $this->build_tiny_callback();
        if ($params['link_list'] === 'enabled') {
            $str .= '<script src="' . $mce_url . 'js/tinymce.linklist.php"></script>' . "\n";
        }
        return $str;
    }

    function build_mce_init($plugins, $buttons1, $buttons2, $buttons3, $buttons4)
    {
        global $modx;
        $params = $this->params;
        $mce_path = $params['mce_path'];
        $mce_url = $params['mce_url'];

        $ph['refresh_seed'] = filesize("{$mce_path}tiny_mce/tiny_mce.js");
        $ph['mce_url'] = $mce_url;
        $ph['elmList'] = implode(',', $params['elements']);
        $ph['width'] = (!empty($params['width'])) ? $params['width'] : '100%';
        $ph['height'] = (!empty($params['height'])) ? $params['height'] : '300';
        $ph['language'] = (empty($params['language'])) ? 'en' : $params['language'];
        if (strpos($modx->config['mce_editor_skin'], ':') !== false) {
            list($skin, $skin_variant) = explode(':', $modx->config['mce_editor_skin']);
        } else $skin = $modx->config['mce_editor_skin'];
        $ph['skin'] = $skin;
        if ($skin_variant) $ph['skin_variant'] = $skin_variant;
        else              $ph['skin_variant'] = '';

        $ph['document_base_url'] = MODX_SITE_URL;
        switch ($params['mce_path_options']) {
            case 'Site config':
            case 'siteconfig':
                if ($modx->config['strip_image_paths'] == 1) {
                    $ph['relative_urls'] = 'true';
                    $ph['remove_script_host'] = 'true';
                    $ph['convert_urls'] = 'true';
                } else {
                    $ph['relative_urls'] = 'false';
                    $ph['remove_script_host'] = 'false';
                    $ph['convert_urls'] = 'true';
                }
                break;
            case 'Root relative':
            case 'docrelative':
                $ph['relative_urls'] = 'true';
                $ph['remove_script_host'] = 'true';
                $ph['convert_urls'] = 'true';
                break;
            case 'Absolute path':
            case 'rootrelative':
                $ph['relative_urls'] = 'false';
                $ph['remove_script_host'] = 'true';
                $ph['convert_urls'] = 'true';
                break;
            case 'URL':
            case 'fullpathurl':
                $ph['relative_urls'] = 'false';
                $ph['remove_script_host'] = 'false';
                $ph['convert_urls'] = 'true';
                break;
            case 'No convert':
            default:
                $ph['relative_urls'] = 'true';
                $ph['remove_script_host'] = 'true';
                $ph['convert_urls'] = 'false';
        }

        if ($modx->config['mce_entermode'] !== 'br' && $modx->manager->action !== '78') {
            $ph['forced_root_block'] = 'p';
            $ph['force_p_newlines'] = 'true';
            $ph['force_br_newlines'] = 'false';
        } else {
            $ph['forced_root_block'] = '';
            $ph['force_p_newlines'] = 'false';
            $ph['force_br_newlines'] = 'true';
        }
        $ph['element_format'] = $modx->config['mce_element_format'];
        $ph['schema'] = $modx->config['mce_schema'];

        $ph['toolbar_align'] = $params['toolbar_align'];
        $ph['file_browser_callback'] = 'mceOpenServerBrowser';
        $ph['plugins'] = $plugins;
        $ph['buttons1'] = $buttons1;
        $ph['buttons2'] = $buttons2;
        $ph['buttons3'] = $buttons3;
        $ph['buttons4'] = $buttons4;
        $ph['mce_formats'] = empty($params['mce_formats']) ? 'p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre,address' : $params['mce_formats'];
        $ph['css_selectors'] = empty($params['css_selectors']) ? $modx->config['tinymce_css_selectors'] : $params['css_selectors'];
        $ph['disabledButtons'] = isset($params['disabledButtons']) ? $params['disabledButtons'] : '';
        $ph['mce_resizing'] = $params['mce_resizing'];
        $ph['date_format'] = $modx->toDateFormat(null, 'formatOnly');
        $ph['time_format'] = '%H:%M:%S';
        $ph['entity_encoding'] = $params['entity_encoding'];
        $ph['onchange_callback'] = "'myCustomOnChangeHandler'";
        $ph['terminate'] = empty($params['customparams']) ? '' : ',';
        $ph['customparams'] = rtrim(
            evo()->parseText(
                $params['customparams']
                , evo()->documentObject
                , '[*'
                , '*]'
            ),
            ',');
        $content_css[] = "{$mce_url}style/content.css";
        if (preg_match('@^/@', $params['editor_css_path'])) {
            $content_css[] = $params['editor_css_path'];
        } elseif (preg_match('@^https?://@', $params['editor_css_path'])) {
            $content_css[] = $params['editor_css_path'];
        } elseif ($params['editor_css_path'] !== '') {
            $content_css[] = MODX_BASE_URL . $params['editor_css_path'];
        }
        $ph['content_css'] = implode(',', $content_css);
        $ph['link_list'] = $params['link_list'] === 'enabled' ? "'" . $mce_url . "js/tinymce.linklist.php'" : 'false';

        $ph['tpl_list'] = $mce_url . "js/get_template.php";

        $mce_init = file_get_contents($mce_path . "js/mce_init.inc.js");
        foreach ($ph as $name => $value) {
            $name = '[+' . $name . '+]';
            $mce_init = str_replace($name, $value, $mce_init);
        }
        return $mce_init;
    }

    function build_tiny_callback()
    {
        return str_replace(
            '[+cmsurl+]',
            MODX_BASE_URL . 'manager/media/browser/mcpuk/browser.php?editor=tinymce',
            file_get_contents($this->params['mce_path'] . 'js/modx_fb.js.inc')
        );
    }
}
