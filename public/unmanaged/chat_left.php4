<?

require_once("1.php");
require_once("fun_lib.inc.php4");

if (!defined("LOGO_URL")) {
	define("LOGO_URL", true);
}
$controller->setView('DocumentFrame');
$GLOBALS['controller']->view_root->return_path = "index.php";
$GLOBALS['controller']->disableNavigation();
$GLOBALS['controller']->setLink('m100201');
$chat_report = "";
$tt = time() - 20;
$ch_array = array(); $i=0;

if (!isset($_SESSION['s']['begin_time'])) {
	$_SESSION['s']['begin_time'] = time() ;
}

$q = ($s['perm']==2) ? "SELECT People.*, Teachers.* FROM Teachers INNER JOIN People ON Teachers.MID = People.MID WHERE Teachers.CID={$_GET['cid']} AND Teachers.MID={$_GET['uid']}" : "SELECT People.*, Teachers.* FROM Teachers INNER JOIN People ON Teachers.MID = People.MID WHERE Teachers.CID={$_GET['cid']}";

$r = sql($q);

while ($row = sqlget($r)) {
	$rid = $row['MID'];
	$room_name = isset($row['LASTNAME']) ? $row['LASTNAME'] : $row['LastName'];
	$room_name .= " ";
	$room_name .= isset($row['FIRSTNAME']) ? $row['FIRSTNAME'] : $row['FirstName'];
	$ch_array[$rid] = $room_name;
	sql($q);
}	

if($s['perm']==2) {
	$array[$s['mid']] = $ch_array[$s['mid']];
	unset($ch_array);
	$ch_array = $array;
	
	$_SESSION['s']['chat_name'] = $ch_array[$s['mid']];
	
	//$chat_report = "<a href=chat_report.php4 target=_blank title='"._('распечатать историю чата')."'><img src='images/icons/save.gif' vspace=5 hspace=5 border=0></a><br>";
}

?>


    <HTML><head>
    <META content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" http-equiv="Content-Type">	
    <TITLE>eLearning Server</TITLE>
     <SCRIPT src="js/FormCheck.js" language='JScript' type='text/javascript'></script>
     <SCRIPT src="js/img.js" language='JScript' type='text/javascript'></script>
     <SCRIPT src="js/hide.js" language='JScript' type='text/javascript'></script>
     <title>eLearning Server</title>
     <link rel='stylesheet' href='styles/style.css' type='text/css'>
     </head>
     <BODY  class="chat_left_body" leftmargin=0 rightmargin=0 marginwidth=0 topmargin=0 marginheight=0>
<table width="100%" height="100%">
<tr><td valign="top">
                <script>
                        function swapWidth() {
                        obj_frameset=top.document.getElementById('mainFrameset');
                        str_cols=obj_frameset.cols;
                        state1='200,*';
                        state2='45,*';
                        obj_frameset.cols = (str_cols==state2) ? state1 : state2;
                        return true;
                        }
                        function createAdditionalFrame() {
                        var obj_frameset = top.document.getElementById('mainFrameset');
                         f = top.document.createElement('frame');
                         obj_frameset.appendChild(f);
                         obj_frameset.cols = '200,*';

                        }
                </script>
<a href="/" target="_top" class="coursename1"><img src="images/icons/book.gif" border=0 alt="На главную" vspace=5 hspace=5></a><br>

<span id="menu_void">
                 &nbsp;&nbsp;&nbsp;<a style="cursor: hand" onClick="swapWidth();removeElem('menu_void');putElem('menu_links');" title="свернуть"><span style="font-family:Webdings; color:black;">4</span></a>
                    <H3><U>Преподаватели:</U></H3><table border=0 cellpadding=0 cellspacing=0 align=center width="100%" class=cHilight><tr><td>

<?php 

$controller->captureFromOb(CONTENT_EXPANDED);
echo "<div class='tree-view course-structure'>
        <ul>\n";
foreach ($ch_array as $key=>$value) {
	$boolLink = false;
	if ($_GET['rid'] == $key) {
		//$value = "<font class='chat_font_this'><b>".$value."</b></font>";
		$value = "<strong>$value</strong>";		
		$image = "images/menu/bullet-over.gif";
	}
	elseif (!sqlrows(sql("SELECT * FROM chat_users WHERE rid = '{$key}' AND uid = '{$key}' AND cid = '{$_GET['cid']}' AND joined > '$tt'"))) {
		//$value = "<font class='chat_font_disabled'>".$value."</font>";
		$image = "images/menu/bullet-gr.gif";
	}
	else {
		//$value = "<font class='chat_font_enabled'>".$value."</font>";
		$image = "images/menu/bullet.gif";
		$boolLink = true;
	}
	
	echo "<li class='courseStructureAllowedItem open' preview='true'>
              <a href='chat_web.php4?rid=$key&uid={$s['mid']}&cid={$_GET['cid']}' target='_top'>{$value}</a>
          </li>";
	
	//echo "<a href='chat_web.php4?rid=$key&uid={$s['mid']}&cid={$_GET['cid']}' target='_top'><LI> {$value}</a>";
}
echo "  \n</ul>
      </div>";

echo "<br><br>".$chat_report;

$controller->captureStop(CONTENT_EXPANDED);

?>
             
</td></tr></table>
</span>
                
                 <span id="menu_links" class=hidden>
                &nbsp;&nbsp;&nbsp;<a style="cursor: hand" onClick="swapWidth();removeElem('menu_links');putElem('menu_void');" title="развернуть"><span style="font-family:Webdings; color:black;">6</span></a>
               <H3></H3><table border=0 cellpadding=0 cellspacing=0 align=center width="100%" class=cHilight><tr><td>
               
<?php 
$controller->captureFromOb(CONTENT_COLLAPSED);

foreach ($ch_array as $key=>$value) {
	$boolLink = false;
	if ($_GET['rid'] == $key) {
		$image = "images/menu/bullet-over.gif";
	}
	elseif (!sqlrows(sql("SELECT * FROM chat_users WHERE rid = '{$key}' AND uid = '{$key}' AND cid = '{$_GET['cid']}' AND joined > '$tt'"))) {
		$image = "images/menu/bullet-gr.gif";
	}
	else {
		$image = "images/menu/bullet.gif";
		$boolLink = true;
	}
	echo "<a href='chat_web.php4?rid=$key&uid={$s['mid']}&cid={$_GET['cid']}' title='{$value}' target='_top'><img src='{$image}' border='0' id='dm1' name='dm1' vspace='8' hspace='15'></a><br>";
}

echo $chat_report;

$controller->captureStop(CONTENT_COLLAPSED);

?>               
               
               
               </td></tr></table></span>      
               
               <tr>

         </td>
      </tr>
   </table>

   </td></tr>
   </table>
   </BODY></HTML>
<?
$controller->terminate();
?>