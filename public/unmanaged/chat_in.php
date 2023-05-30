<?

require("1.php");

$cid  = $_SESSION['chat']['cid'];
$rid  = $_SESSION['chat']['rid'];
$uid  = $_SESSION['chat']['uid'];
$user = $_SESSION['chat']['user'];

if(!empty($_POST['txt'])) {
	global $adodb;
	$time = time();
	$message = $GLOBALS['adodb']->Quote(htmlspecialchars($_POST['txt']));
	$user_ = $GLOBALS['adodb']->Quote($user);
	$query = "	INSERT INTO chat_messages (rid, uid, cid, user, message, posted)
			VALUES ('{$rid}', '{$uid}', '{$cid}', {$user_}, {$message}, '{$time}') ";
	sql($query);
}

$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromOb(CONTENT);
?>

<script>

function setFocus() {
  if (document.all) {
    document.all['form1'].txt.focus(); 
  } else {
    document.form1.txt.focus();
  }
}

</script>

<form action="chat_in.php" name="form1" method=post>
<table cellspacing="0" cellpadding="0" border="0" style="padding-left:10px;">
    <tr>
        <td>
            <input class="chat_input" type="text" size="55" name="txt" >&nbsp;&nbsp;
        </td>
        <td>
         <?php
         echo okbutton();
         ?>
        </td>
    </tr>
</table>
</form>

<?php
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>