<?php
$dbg_templates = [];
$dbg_templates['main'] = <<<TPL
<!DOCTYPE html> 
<html> 
	<head>
	<title>[+title+]</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=[+charset+]" />
	<link rel="stylesheet" type="text/css" href="[+base_url+]media/style/[+theme+]/style.css" /> 
	<link rel="stylesheet" type="text/css" href="[+ditto_base_url+]debug/debug.template.css" />
	</head>
	<body>
	<div class="section">
	<div class="sectionHeader">&nbsp;[+title+]</div>
		<div class="sectionBody"> 
		<div class="tab-pane" id="dittoDebug"> 
	[+content+]
	</div>
	</div>
	</div>
	<script type="text/javascript" src="[+base_url+]media/script/tabpane.js">
		tpDittoDebug = new WebFXTabPane( document.getElementById( "dittoDebug" ) ); 
	</script>
	</body>
</html>
TPL;

$dbg_templates['links'] = <<<TPL
<img src="[+dbg_icon_url+]" /> <a onclick="window.open('[+open_url+]','[+dbg_title+]','width=720, height=500, toolbar=0, menubar=0, status=0, alwaysRaised=1, dependent=1, scrollbars=1, resizable=yes');" style="cursor:pointer;cursor:hand;">[+open_dbg_console+]</a><br />
<img src="[+dbg_icon_url+]" /> <a href="[+save_url+]">[+save_dbg_console+]</a><br /><br />
TPL;

$dbg_templates['item'] = '[+pagetitle+] ([+id+])';

$dbg_templates['tab'] = <<<TPL
	<div class="tab-page" id="tab_[+title+]">
		<h2 class="tab">[+title+]</h2>
		[+tab_content+]
	</div>
TPL;
