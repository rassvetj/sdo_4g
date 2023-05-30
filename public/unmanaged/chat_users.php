<? 

require_once "1.php";

$cid  = $_SESSION['chat']['cid'];
$rid  = $_SESSION['chat']['rid'];
$uid  = $_SESSION['chat']['uid'];
$user = $_SESSION['chat']['user'];

$time = time() - 20;
$query = "SELECT DISTINCT 
              chat_users.*, 
              People.LastName AS lname, 
              People.FirstName AS fname, 
              People.Patronymic AS mname
          FROM chat_users 
          INNER JOIN People ON (People.MID=chat_users.uid)
          WHERE rid = '{$rid}' AND cid = '{$cid}' AND joined >= '{$time}'";
$res = sql($query);

$array = array();
$i = 0;
while($row = sqlget($res)) {
	$array[$i]['user']  = $row['user'];
	$array[$i]['lname'] = $row['lname'];
	$array[$i]['fname'] = $row['fname'];
	$array[$i]['mname'] = $row['mname'];
	$array[$i]['rid']   = $row['rid'];
	$array[$i]['uid']   = $row['uid'];
	$i++;
}
    
?>

<html>
<head>
  <link rel='stylesheet' href='<?=$GLOBALS['controller']->view_root->skin_url?>/stylesheets/screen.css' type='text/css'>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>">
  <meta HTTP-EQUIV="Refresh" CONTENT="10;URL=chat_users.php">
  <title>Users</title>
<SCRIPT LANGUAGE="JavaScript">
<!-- 
function setFocus() {
  if (document.all) {
    parent.wIn.document.all['form1'].txt.focus(); 
  } else {
    parent.wIn.document.form1.txt.focus();
  }
}

function sendMessage(name) {
  if (document.all) {
    parent.wIn.document.all['form1'].txt.value=name+', '; 
  } else {
    parent.wIn.document.form1.txt.value=name+', ';
  }
  setFocus();
} 
// -->
</SCRIPT> 
</head>
<body class="chat_right_body" marginwidth="10" marginheight="10" leftmargin="10" topmargin="10">
<div class="oldstyle">
<small>
<b><?=_("сейчас в чате:")?></b><br><br>
<nobr>
<?
	foreach ($array as $value) {
		$user = (($value['rid'] == $value['uid']) || is_teacher($value['uid'])) ? "<b><u>".$value['user']."</u></b>" : $value['user'];
		echo "<table border='0'>
		          <tr>
		              <td>
            		      <a href=\"javascript:void(0);\" onClick=\"window.open('{$GLOBALS['sitepath']}userinfo.php?mid=".(int) $value['uid']."','user_".(int) $value['uid']."','toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400 , height=300');\">
            		          <img border=0 src=\"{$GLOBALS['sitepath']}reg.php4?getimg=".(int) $value['uid']."\" width='32' />
            		      </a>
            		  </td>
            		  <td>            		  
            		      <a href=\"javascript:sendMessage('{$value['user']}');\" title=\"{$value['lname']} {$value['fname']} {$value['mname']}\">
            		          <small>{$user}</small>
            		      </a>
            		  </td>
                  </tr>
              </table>";		
	}
?> 
</nobr></small>
</font>
</div>
</body>
</html>