<?
 
require("1.php"); 

unset($_SESSION['chat']['cid']);
unset($_SESSION['chat']['rid']);
unset($_SESSION['chat']['uid']);
unset($_SESSION['chat']['user']);
 
if (!isset($_GET['uid']) || !isset($_GET['rid'])){
    $controller->setView('DocumentBlank');	
    echo '<html>
    <head>
    <link rel=\'stylesheet\' href=\'styles/style.css\' type=\'text/css\'>
    </head>
    <body class="chat_messages_body"><b>Выберите преподавателя</b>';
    $controller->setMessage(_('Пожалуйста, выберите преподавателя в списке слева'));
    $controller->terminate();
    exit();
}

$query = "SELECT * FROM People WHERE MID='{$_GET['uid']}'";
$res = sql($query);
$row = sqlget($res);

//$user = $row['LastName']." ".$row['FirstName'];
$user = $row['Login'];
$rid = $_GET['rid'];
$uid = $_GET['uid'];
$cid = $_GET['cid'];

//setCookie("user",$user);
//setCookie("rid",$rid);
//setCookie("uid",$uid);
//setCookie("cid",$cid);
$_SESSION['chat']['cid'] = $cid;
$_SESSION['chat']['rid'] = $rid;
$_SESSION['chat']['uid'] = $uid;
$_SESSION['chat']['user'] =$user;

?>

<html>
<head>
<link rel='stylesheet' href='styles/style.css' type='text/css'>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>">
<title>Обсуждения</title>
</head>

<frameset cols="*,200" border="0">
	<frameset rows="*,50" border="0"> 
		<frame name="wOut" src="chat_out.php#" frameborder=0>
		<frame name="wIn" src="chat_in.php" frameborder=0 scrolling="no">
	</frameset>
	<frame name="wUsers" src="chat_users.php" frameborder=0 scrolling="no">	
</frameset>
</html>
