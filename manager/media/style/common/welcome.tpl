<!-- welcome -->
<div style="margin: 20px 12px;">
    <div class="tab-pane" id="welcomePane" style="border:0">
        <!-- home tab -->
        <div class="tab-page" id="tabhome" style="padding-left:0; padding-right:0;">
            [+OnManagerWelcomePrerender+]
            <h2 class="tab">[+welcome_title+]</h2>
            <div class="sectionHeader">[+site_name+]</div>
            <div class="sectionBody">
                <table>
                    <tr>
                        <td width="120" align="right" valign="top">
                            <img src='media/style/common/images/misc/logo.png' alt='[+logo_slogan+]'/>
                        </td>
                        <td valign="top">
                            [+OnManagerWelcomeHome+]
                            <div style="overflow:hidden;zoom:1;margin-bottom:20px;">
                                [+NewDocIcon+]
                                [+SearchIcon+]
                                [+MessageIcon+]
                                [+HelpIcon+]
                            </div>
                            <div style="overflow:hidden;zoom:1;margin-bottom:20px;">
                                [+ElementsIcon+]
                                [+FileManagerIcon+]
                                [+UserManagerIcon+]
                                [+WebUserManagerIcon+]
                            </div>
                            <div>
                                [+SettingsIcon+]
                                [+BackupIcon+]
                                [+EventLogIcon+]
                                [+SysInfoIcon+]
                            </div>
                            <br style="clear:both"/><!--+Modules+--><br style="clear:both"/>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- system check -->
        <div class="tab-page" id="tabcheck" style="display:[+config_display+]; padding-left:0; padding-right:0;">
            <h2 class="tab" style="display:[+config_display+]"><strong class="alert">[+settings_config+]</strong></h2>
            <div class="sectionHeader">[+configcheck_title+]</div>
            <div class="sectionBody">
                <img src="media/style/common/images/icons/error.png"/>
                [+config_check_results+]
            </div>
        </div>
        [+OnManagerWelcomeRender+]
    </div>
    <script type="text/javascript">
        tpPane = new WebFXTabPane(document.getElementById("welcomePane"), false);
    </script>
</div>
