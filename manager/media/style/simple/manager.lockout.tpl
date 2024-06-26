<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>MODX Content Manager</title>
    <meta http-equiv="content-type" content="text/html; charset=[+modx_charset+]"/>
    <meta name="robots" content="noindex, nofollow"/>
    <link rel="stylesheet" type="text/css" href="media/style/[+theme+]/style.css"/>
    <script type="text/javascript">
        function doLogout() {
            top.location = '[+logouturl+]';
        }

        function gotoHome() {
            top.location = '[+homeurl+]';
        }
    </script>

    <script type="text/javascript">
        if (top.frames.length != 0) {
            top.location = self.document.location;
        }
    </script>

</head>
<body id="login">

<!-- start the login box -->
<div id="mx_loginbox">

    <form method="post" name="loginfrm" id="loginfrm" action="processors/login.processor.php">


        <div id="mx_loginArea">
            <h1 class="siteName">[+site_name+]</h1>
            <div class="loginMessage">[+manager_lockout_message+]</div>
            <br/>
            <input type="button" id="submitButton" value="[+home+]" onclick="return gotoHome();"/>&nbsp;
            <input type="button" id="submitButton" value="[+logout+]" onclick="return doLogout();"/>
        </div>
        <br style="clear:both;height: 1px"/>

    </form>
</div>

<p class="loginLicense">
    <strong>MODX</strong>&trade; is licensed under the GPL license. &copy; 2005-2013 <a href="http://modx.com/"
                                                                                        target="_blank">MODX</a>.
</p>

</body>
</html>
