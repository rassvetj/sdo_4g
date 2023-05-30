<?php
require_once('1.php');
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" />
    <style type="text/css">
      html,body
      {
        margin: 0;
        padding: 0;
      }
      #imgtop td { background: url(<?=$GLOBALS['controller']->view_root->skin_url?>/images/img_rep.jpg) repeat-x; position: relative; }
    </style>
  </head>
  <body>
  <table>
    <table width="100%" height="100%" cellspacing="0" cellpadding="0">
      <tr id="imgtop" height="65">
        <td>
          <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td width="780"><img alt="" src="<?=$GLOBALS['controller']->view_root->skin_url?>/images/img_tl.jpg" /></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>