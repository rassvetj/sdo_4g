<?php
require_once('1.php');

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->disableNavigation();
$GLOBALS['controller']->setHeader(_('Конструктор курса'));
$GLOBALS['controller']->setHelpSection(-1); //принудительно отключим помощь
$GLOBALS['controller']->terminate();
exit();
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" />
    <style type="text/css">
      body, html {
        margin: 0;
        padding: 0;
				overflow: hidden;
				background: url(<?=$GLOBALS['controller']->view_root->skin_url?>/images/cs-top-bg.gif) bottom left repeat-x;
      }
			body, html, table {
				height: 82px;
      }      
    </style>
  </head>
  <body>
		<table width="100%" cellspacing="0" cellpadding="0">
            <tr>
				<td valign="top" style="padding-left: 20px;">
					<img src="<?=$GLOBALS['controller']->view_root->skin_url.'/images/logo.gif'?>" />
              </td>
				<td align="right" valign="top">
					<a target="_top" href="<?=$sitepath ?>courses.php4" class="tri-state"><img
						title="<?=_('Выход')?>" border="0" src="<?=$GLOBALS['controller']->view_root->skin_url.'/images/buttons/exit.gif'?>"><img
						title="<?=_('Выход')?>" border="0" src="<?=$GLOBALS['controller']->view_root->skin_url.'/images/buttons/exit-mouseover.gif'?>" style="display: none;"><img
						title="<?=_('Выход')?>" border="0" src="<?=$GLOBALS['controller']->view_root->skin_url.'/images/buttons/exit-mousedown.gif'?>" style="display: none;"></a>
        </td>
      </tr>
    </table>
  </body>
</html>