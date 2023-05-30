<?php

include_once("../1.php");
include_once("../metadata.lib.php");

$query = "SELECT title, metadata FROM organizations WHERE mod_ref = '{$_GET['ModID']}'";
$res = sql($query);
if (sqlrows($res)) {
	$row = sqlget($res);
	$row['metadata'] = view_metadata_as_text(read_metadata(stripslashes($row['metadata']), 'item'));
	$row['metadata'] = eregi_replace("width=([0-9]+)%", "", $row['metadata']);
}

$controller->setView('DocumentBlank');
?>
<html>
<head>
<title>eLearning Server 3000</title>

    <SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
    <SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
    <SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>

    <SCRIPT src="<?=$sitepath?>admin/adm.js" language="JScript" type="text/javascript"></script>
    <SCRIPT src="<?=$sitepath?>js/dynamic.js" language="JScript" type="text/javascript"></script>

    <script type="text/javascript" src="<?=$sitepath?>js/jquery.js"></script>
    <script type="text/javascript" src="<?=$GLOBALS['controller']->view_root->skin_url?>/script.js"></script>

    <style type="text/css">
      @import "<?=$GLOBALS['controller']->view_root->skin_url?>/oldstyle.css";
      @import "<?=$sitepath?>skin.css.php";
    </style>
</head>
<body>
<?
$controller->setMessage("<h3>{$row['title']}</h3> {$row['metadata']}<br> <p><nobr>" . sprintf(_("Выберите один из модулей в программе курса"), "<br>") . "</nobr></p><br>");
?>
</body>
</html>
<?
$controller->terminate();
?>