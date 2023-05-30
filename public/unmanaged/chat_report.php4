<html>
<head>
<link rel='stylesheet' href='<?=$GLOBALS['controller']->view_root->skin_url?>/oldstyle.css' type='text/css'>
</head>
<body class="chat_report_body"><table><tr><td>

<?

require_once("1.php");

$cid  = $_SESSION['chat']['cid'];
$rid  = $_SESSION['chat']['rid'];
$uid  = $_SESSION['chat']['uid'];
$user = $_SESSION['chat']['user'];


if (!$teach) {
	die();
}

echo "<h2 class='chat_report_h2'>{$_SESSION['s']['chat_name']}</h2>";

$controller->setView('DocumentPrint');

$controller->captureFromOb(CONTENT);

$query = "SELECT * FROM chat_messages WHERE rid = '{$rid}' AND posted >= {$_SESSION['s']['begin_time']} AND cid = '{$cid}' ORDER by posted";
$res = sql($query, "err_chat_2");
while ($item = sqlget($res)) {
	$item['posted'] = isset($item['POSTED']) ? $item['POSTED'] : $item['posted'];
	$item['message'] = isset($item['MESSAGE']) ? $item['MESSAGE'] : $item['message'];
	$name = ($item['uid'] == $item['rid']) ? "<b>".$item['user']."</b>" : $item['user'];
	$time = date("d.m.Y H:i:s", $item['posted']);
	$message = $item['message'];
	
	echo  $name . "<font color=000000> (" . $time . "): " . $message . "</font><br>";
}

$time = time() - 60*60*30;
$query = "DELETE FROM chat_messages WHERE posted < '{$time}'";
sql($query);

$controller->captureStop(CONTENT);

?>

</table></body></html>
<?
$controller->terminate();
?>