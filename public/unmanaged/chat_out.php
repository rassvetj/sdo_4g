<?
require("1.php");

$cid  = $_SESSION['chat']['cid'];
$rid  = $_SESSION['chat']['rid'];
$uid  = $_SESSION['chat']['uid'];
$user = $_SESSION['chat']['user'];

$time = time();
$user_ = $GLOBALS['adodb']->Quote($user);
$query = "DELETE FROM chat_users WHERE uid = '{$uid}'";
sql($query);
$query = "INSERT INTO chat_users (joined, user, rid, cid, uid) VALUES ('{$time}', {$user_}, '{$rid}', '{$cid}', '{$uid}')";
sql($query);

$time = ($_SESSION['s']['begin_time']!=0) ? $_SESSION['s']['begin_time'] : time();
$query = "SELECT * FROM chat_messages WHERE rid = '{$rid}' AND cid = '{$cid}' AND posted >= '{$_SESSION['s']['begin_time']}' ORDER BY posted DESC";
$res = sql($query);

$messages = array();
$i = 0;
while ($row = sqlget($res)) {
	$row['posted'] = isset($row['POSTED']) ? $row['POSTED'] : $row['posted'];
	$row['message'] = isset($row['MESSAGE']) ? $row['MESSAGE'] : $row['message'];
	$messages[$i]['user'] = (($row['uid'] == $row['rid']) || is_teacher($row['uid'])) ? "<b>".$row['user']."</b>" : $row['user'];
	$messages[$i]['user'] = "<span style='background-color:#dddddd'>".$messages[$i]['user'];
	$messages[$i]['posted'] = " (". date("H:i:s", $row['posted']) .")</span>: <font color=000000>";
	$messages[$i]['message'] = $row['message'] . "</font>";
	$i++;
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>">
<meta HTTP-EQUIV="Refresh" CONTENT="10;URL=chat_out.php">
<link rel='stylesheet' href='<?=$GLOBALS['controller']->view_root->skin_url?>/stylesheets/screen.css' type='text/css'>
</head>
<body style="font-family: Verdana;">
<div style="padding:10px;">
<?
	foreach ($messages as $value) {
		echo $value['user'] . $value['posted'] . $value['message'] . "<br>";
	}
?>
<a name="#"></a>
</div>
</body>
</html>