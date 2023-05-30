<?
require_once('1.php');
require_once("lib/FCKeditor/fckeditor.php");
require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

define('INDEX_GB_DAYS',3);

if (isset($_GET['cid'])) {
	$_SESSION['s']['user']['scourse'] = (integer)$_GET['cid'];
}

if (!$stud) login_error();

$courses = $teach?$s[tkurs]:$s[skurs];
//if (empty($courses)) login_error();

echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);

function select_people_used($cid) {
    if ($cid) {
    $sql = "SELECT DISTINCT
               People.MID,
               People.Login,
               People.LastName,
               People.FirstName,
               People.Patronymic,
               People.EMail
            FROM People, Students
            WHERE
                (People.MID = Students.MID AND Students.CID = '$cid')
           ";
    $res =sql($sql);
    $i = 0;
        $htmlHead = "<ul style='list-style-type:none;'>
                  <li>
                    <input type='checkbox' onclick=\"var i=0; while(elm=document.getElementById('person_['+i+']')) {elm.checked = this.checked; i++;}\" checked=''/>&nbsp;"._("Отметить все")."
                  </li>
                  <li>&nbsp;</li>
             ";
        $html = '';
    while ($row = sqlget($res)) {
        //if ($row['MID'] == $GLOBALS['s']['mid']) {
        //    $style = "style='display:none;'";
        //    $id = "";
        //}else {
            $style = "";
            $id = "id='person_[$i]'";
            $i++;
        //}

        $html .= "<li $style>
                    <input $id type='checkbox' checked='' value='{$row['MID']}' name='mid[]'/>&nbsp;
                    {$row['LastName']}&nbsp;{$row['FirstName']}&nbsp;{$row['Patronymic']}&nbsp;({$row['Login']})&nbsp;&nbsp;{$row['EMail']}
                  </li>";
    }
    
        $html = $html ? ($htmlHead.$html."</ul>") : '';
    }

    return $html;
}


$js = "function show_people_list(html) {
        var elm = document.getElementById('newmessage2');
        var f = document.getElementById('guestBookAddForm');
           if (elm) {
               elm.innerHTML = html;
           }
           if (f) {
               f.onSubmit = 'return false';
           }
       }
       function selectPeople(cid) {
           if (cid==0) {
               $(\"input[name=sendto][value=all]\").attr(\"checked\", true);
               $(\"#newmessage2\").hide();
               $(\"input[name=sendto]\").attr(\"disabled\", true);
               return true;             
           }else {
               $(\"input[name=sendto]\").attr(\"disabled\", false);
           }
           var elm = document.getElementById('newmessage2');
           var f = document.getElementById('guestBookAddForm');
           if (elm) {
               elm.innerHTML = \"<img src='images/indicator.gif' hspace='15' vspace='5'>\";
           }
           if (f) {
               f.onSubmit = '';
           }
           x_select_people_used(cid, show_people_list);
       }";

$sajax_javascript = CSajaxWrapper::init(array('select_people_used')).$js;
echo "<script type='text/javascript'>$sajax_javascript</script>";

if (isset($_GET['delete'])){
   $rq = "SELECT name as name, mid as mid FROM $guestbook WHERE PostID=$delete";
   $rq = sql($rq);
   if (sqlrows($rq))  {
       $rq = sqlget($rq);
        if ((($rq['mid']==$GLOBALS['s']['mid'])
            && $GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OWN))
            || ($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OTHERS))) {
                sql("DELETE FROM $guestbook WHERE PostID='{$_GET['delete']}'");
                sql("DELETE FROM posts3_mids WHERE postid='".(int) $_GET['delete']."'");
                refresh('guestbook.php4');
        }
   }
}

$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
if (is_array($courses) && count($courses)) {
foreach ($courses as $coursename){
    if (!$courseFilter->is_filtered($coursename)) {
        continue;
    }
    $Title=getfield($coursestable,"Title","CID",$coursename);
    $kurses[$coursename] = $Title;
}
$GLOBALS['controller']->addFilter(_("Курс"),'kurs',$kurses,$kurs);
}

if (!empty($_POST['msgbody'])){

    if (($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OWN))
    || ($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OTHERS))) {

        $postvars['posted']=time();
        $postvars['name']=$s['user']['lname']." ".$s['user']['fname']." ".$s['user']['patronymic'];
        $postvars['course']=intval($_POST['course']);// echo "<H1>".$_POST['course']."</H>";
        $postvars['email']=$s['user']['email'];
        $postvars['text']=$_POST['msgbody'];
        $postvars['mid'] = $s['mid'];

        //   if(!isset($_POST['sendmail'])) { //why??
        global $adodb;
        if($_GET['new'])
        {
			//var_dump("INSERT INTO $guestbook (posted, name, CID, email, text, mid)
            //             VALUES (".$adodb->DBTimeStamp(time()).",".$adodb->Quote($postvars['name']).",".$adodb->Quote($postvars['course']).",".$adodb->Quote($postvars['email']).","); exit;
        	$query = "INSERT INTO $guestbook (posted, name, CID, email, text, mid)
                         VALUES (".$adodb->DBTimeStamp(time()).",".$adodb->Quote($postvars['name']).",".$adodb->Quote($postvars['course']).",".$adodb->Quote($postvars['email']).",";
        	$query .= $adodb->Quote($postvars['text']);
        	$query .=",'".(int) $postvars['mid']."')";
        }
        elseif($_GET['edit'])
        {
        	$query = "UPDATE $guestbook 
        			SET CID = ".$adodb->Quote($postvars['course']).", text = ".$adodb->Quote($postvars['text'])." 
        			WHERE PostID = ".$adodb->Quote($_GET['edit']);
        }
        sql($query, "errInsertPosts3");
        //   }
        $cid=$postvars['course'];

        if ($postid = sqllast()) {
            $all = true;
            if (isset($_POST['sendto']) && ($_POST['sendto'] != "all") && is_array($_POST['mid']) && count($_POST['mid'])) {
                foreach($_POST['mid'] as $mid) {
                    if ($mid>0) {
                        sql("INSERT INTO posts3_mids (postid, mid) VALUES ('".(int) $postid."','".(int) $mid."')");
                        $all = false;
                    }
                }
            }
            if ($all) {
                sql("INSERT INTO posts3_mids (postid, mid) VALUES ('".(int) $postid."','0')");
            }
        }

        if (isset($_POST['sendmail']) && isset($teach)) {

        /*                  $course=getField($coursestable,"Title","CID",$cid);
                      $from=getField($optionstable,"value","name","dekanEMail");
                      $fromname=getField($optionstable,"value","name","dekanName");
                      $headers = "From: $fromname<$from>\n";
                      $headers .="Content-type: text/html; Charset={$GLOBALS['controller']->lang_controller->lang_current->encoding}\n";
                      $headers .="X-Sender: <$from>\n";
                      $subj="New Message in Announcements has been added.";
        */
        // Send mail for students
        	  $q = "SELECT $peopletable.MID as mid FROM $studentstable, $peopletable WHERE $studentstable.CID=$cid AND $studentstable.MID=$peopletable.MID";
        	  $r = @sql($q);
        	  while($row = sqlget($r)) {
        	  	$mids_all[] = $row['mid'];
        	  }

              $mids = ($_POST['sendto'] == "all") ? $mids_all : $_POST['mid'];
              $mids_send = array();

                      $query="SELECT $peopletable.MID as mid, $peopletable.EMail as email, $peopletable.FirstName as fName, $peopletable.LastName as lName FROM $studentstable, $peopletable WHERE $studentstable.CID=$cid AND $studentstable.MID=$peopletable.MID";
                      $result2=@sql($query);
                      while ($res=sqlget($result2))
                         {
                         	if(!is_array($mids) || !in_array($res['mid'], $mids))
                               continue;

                            if(in_array($res['mid'], $mids_send))
                               continue;

                            $mids_send[] = $res['mid'];
        //                     $body="Привет ".$res['fName']." ".$res['lName'].", <br>\n<br>\n ".$s['user']['fname']." ".$s['user']['lname']." только что добавл новое обьявление по курсу '".$course."'.<br>\n<hr><br>\n".$fromname;
        //                     @mail($res['email'], $subj, $body, $headers);
                            $add['MESSAGE']=$postvars['text'];
                            $add['lf']=$res['fName']." ".$res['lName'];
                            $add['lf2']=$s['user']['fname']." ".$s['user']['lname'];
                            mailTostud("guestmes",$res['mid'],$cid,$add);
                         }

        // Send mail for teachers

                      $query="SELECT $peopletable.MID as mid, $peopletable.EMail as email, $peopletable.FirstName as fName, $peopletable.LastName as lName FROM $teacherstable, $peopletable WHERE $teacherstable.CID=$cid AND $teacherstable.MID=$peopletable.MID";
                      $result2=@sql($query);
                      while ($res=sqlget($result2))
                         {  if(!is_array($mids) || !in_array($res['mid'], $mids))
                                continue;

                            if(in_array($res['mid'], $mids_send))
                               continue;
                            $mids_send[] = $res['mid'];
        //                      $body="Привет ".$res['fName']." ".$res['lName'].", <br>\n<br>\n ".$s['user']['fname']." ".$s['user']['lname']." только что добавл новое обьявление по курсу '".$course."'.<br>\n<hr><br>\n".$fromname;
        //                      @mail($res['email'], $subj, $body, $headers);
                            $add['MESSAGE']=$postvars['text'];
                            $add['lf']=$res['fName']." ".$res['lName'];
                            $add['lf2']=$s['user']['fname']." ".$s['user']['lname'];
                            mailToteach("guestmes",$res['mid'],$cid,$add);
                         }
          }  // send
          refresh($GLOBALS['sitepath']."guestbook.php4");
          exit();

    } // perms
    //else $GLOBALS['controller']->setMessage('У вас нет полномочий');

}
if ($teach) $courses=$s[tkurs];
  else  $courses=$s[skurs];
?>
<form id='guestBookAddForm' method=post name='post' action=''>
<table border=0 cellpadding=0 cellspacing=0 align=center width="100%">
                <!-- заголовок -->
<tr><td  height=10 width=100%>
<?php
if (!$_GET['new'] && !$_GET['edit'] && $GLOBALS['s']['perm']>1) {
    $smarty = new Smarty_els();
    $smarty->assign('url', $GLOBALS['sitepath'].'guestbook.php4?new=1');
    $smarty->assign('caption', _("создать объявление"));
    $smarty->display('common/add_link.tpl');
}
?>
</td></tr>

<tr>
<td class=skip>
<?
if (!$GLOBALS['controller']->enabled){
?>
<table border=0 cellpadding=0 cellspacing=0 align=center width="100%" class=cHilight><tr>
                        <td valign=top class=shedtitle >
                        <!--? echo ph("Объявления") ?-->
                        <a href='' onclick="location.reload();" title="Обновить"><?=_("Объявления")?></a></td>
                        <td width="100%" background="images/schedule/back.gif"><img src="images/spacer.gif"></td>
                        <td height=19 valign=bottom nowrap><div><b><a title="" class=wingna style="font-size: 14px">&#224;</a></b></div></td>
                </tr></table>
<?
}
?>
                </td></tr>
<?
//сообщение
function showmessage($pid, $msg, $course, $epochtime, $name, $email, $bgcolor, $b_Teacher,$mid)
{
global $teach;
if ($epochtime >= (time()- INDEX_GB_DAYS*24*60*60)) {
    $background = "style='background: {$bgcolor};'";
}
?>
                <tr><td><table width=100% class="main <?php echo substr($bgcolor, 1); ?>" cellspacing=0 id="<?php echo $pid ?>post">
                      <tr><th><?echo date("G:i", $epochtime);?> <?echo date("d.m.y", $epochtime);?>,
                        <a href="javascript:void(0);" onClick="wopen('userinfo.php?mid=<?php echo $mid;?>','',600,425)"><?echo $name;?> </a>
                      </th>
<?
$bActions = ((($mid==$GLOBALS['s']['mid']) && $GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OWN)) || ($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OTHERS)));
if ($bActions) {
    echo "<th>"._("Действия")."</th>";
}
?>
                      </tr>
                      <tr><td <?php echo $background?>><?php echo nl2br($msg); ?><br><br></td>
<?
    if ($bActions) {
        echo "<td width='10%' valign='top' align='right' {$background}>";
        echo "<a href='?edit=$pid' title='"._("Редактировать объявление")."'>".getIcon("edit", _("Редактировать объявление"))."</a>";
        echo "<a href='?delete=$pid' onclick = \"if (! confirm('"._("Вы действительно желаете удалить объявление?")."')) return false;\" title='"._("Удалить объявление")."'>".getIcon("delete", _("Удалить объявление"))."</a>";
        echo "</td>";
    }
?>
</tr>
<?
if (!empty($course)) {
    echo "<tr><td " . ($bActions ? "colspan=2" : "") . " {$background}>".getIcon("note", $course)." {$course}</td></tr>";
}
?>
            </table></td></tr>
            <tr><td height=20><img src="images/spacer.gif" width="1" height="1"></td></tr>
<? }

//запрос
/*
$SQL = "SELECT DISTINCT posts3.PostID as postid, posts3.posted
        FROM $guestbook
        LEFT JOIN posts3_mids ON (posts3_mids.postid=posts3.PostID)
        WHERE (";
if ($GLOBALS['kurs']>0) {
    $SQL .= "CID='{$GLOBALS['kurs']}'   ";
} else {
    foreach ($courses as $CID) {
        $SQL .= "CID='$CID' OR ";
    }
}
$SQL = substr($SQL, 0, -3);
$SQL .= ") AND (posts3_mids.mid='0' OR posts3_mids.mid='".(int) $_SESSION['s']['mid']."' OR posts3.mid='".(int) $_SESSION['s']['mid']."' OR posts3_mids.postid IS NULL) ";
$SQL .= "ORDER BY posted DESC LIMIT $GuestBookShownRows";
echo "<!--SQL=$SQL-->";
$result = sql($SQL);
while($row = sqlget($result)) {
	$ids[] = $row['postid'];
}
if (is_array($ids) && count($ids)) {
    $SQL = "SELECT posts3.PostID as postid, name as name, cid as cid, email as email, text as text, UNIX_TIMESTAMP(posted) as posted, posts3.mid as mid
            FROM $guestbook
            WHERE posts3.PostID IN ('".join("','",$ids)."')";
    $SQL .= "ORDER BY posted DESC LIMIT $GuestBookShownRows";
    $result = sql($SQL);
}
*/
$row = sqlget(sql("SELECT `CID` FROM `Courses` WHERE `is_poll` = 1"));
$cidOpros = $row['CID'];

$crntCurses = array();
if (!$_REQUEST['kurs']){
    switch ($_SESSION['s']['perm']){
        case 1:
            $crntCurses = $_SESSION['s']['skurs'];
            break;

        case 2:
            $crntCurses = $_SESSION['s']['tkurs'];
            break;

        default:
            $crntCurses = array_merge($_SESSION['s']['skurs'],$_SESSION['s']['tkurs']);
            break;
    }
}else {
    $crntCurses[] = $_REQUEST['kurs'];
}

if ($s['perm']>1) {
    $sql = "SELECT posts3.PostID as postid,
                   UNIX_TIMESTAMP(posts3.posted) as posted,
                   posts3.name as name,
                   posts3.CID as cid,
                   posts3.email as email,
                   posts3.text as text,
                   posts3.mid as mid,
                   posts3.startday as startday,
                   posts3.stopday as stopday
            FROM posts3 
            LEFT JOIN posts3_mids
              ON  (posts3_mids.postid=posts3.PostID)
            LEFT JOIN Courses
              ON  (posts3.CID=Courses.CID)
            WHERE Courses.is_poll = 0
              AND (posts3.CID IN ('".join("','",$crntCurses)."') )
              OR posts3.CID=0
			GROUP BY posts3.postid  
            ORDER BY posted DESC LIMIT $GuestBookShownRows";
}else {
    $sql = "SELECT posts3.PostID as postid,
                   UNIX_TIMESTAMP(posts3.posted) as posted,
                   posts3.name as name,
                   posts3.CID as cid,
                   posts3.email as email,
                   posts3.text as text,
                   posts3.mid as mid,
                   posts3.startday as startday,
                   posts3.stopday as stopday
            FROM `posts3`
            LEFT JOIN `posts3_mids`
              ON  (posts3_mids.postid=posts3.PostID)
            LEFT JOIN `Courses`
              ON  (posts3.CID=Courses.CID)              
            WHERE 
				Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$crntCurses)."')) AND posts3_mids.mid='0'
				OR Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$crntCurses)."')) AND posts3_mids.mid='".(int) $_SESSION['s']['mid']."'
				OR Courses.is_poll = 0 AND (posts3.CID IN ('".join("','",$crntCurses)."')) AND posts3.mid = posts3_mids.mid AND posts3.mid = '".(int) $_SESSION['s']['mid']."'
				OR posts3.CID=0
			GROUP BY posts3.postid	
            ORDER BY posted DESC  LIMIT $GuestBookShownRows";
}
$result = sql($sql);

// показ сообщений
$colors[0]="#C7D2C2";
$colors[1]="#FEFDF5";
$colors[2]="#EDEDED";
$i=0;
if (sqlrows($result) && !$_GET['new'] && !$_GET['edit']) {    
    while ($row=sqlget($result)) {
        $Title=getfield($coursestable,"Title", "CID", $row['cid']);
        $row['postid'] = isset($row['postid']) ? $row['postid'] : $row['PostID'];
        showmessage($row['postid'],
                    $row['text'],
                    $Title/*$row->course*/,
                    $row['posted'],
                    $row['name'],
                    $row['email'],
                    $colors[2],
                    $teach,
                    $row['mid']
                    );
        $i++;
    }
}elseif(!$_GET['new'] && !$_GET['edit']) {
    echo "
        <tr>
            <td>
                <table width=100% class=main cellspacing=0>
                      <tr>
                        <th>&nbsp;</th>
                      </tr>
                      <tr>
                        <td align='center'>
                            "._("нет объявлений")."
                            <br /><br />
                        </td>
                </table>
            </td>
        </tr>   
    ";
}
?>
                <tr><td><img src="images/spacer.gif" alt="" width=1 height=20 border=0></td></tr>
                <!-- /заголовок -->
<?php
   if (($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OWN)
   || $GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OTHERS)) && $_GET['new']) {

       ?>
<!-- Написать объявление -->
                <tr><td><table width=100% border=0 cellspacing="0" cellpadding="0">
                      <tr><td colspan=2 ><a name='form_add' /><table id=newmessage width=100% border=0 cellspacing=0 cellpadding=5>

                            <tr class=questt><td>
                            <table width=100% class=main cellspacing=0>
                     <tr><th colspan="2"><?=_("создать объявление")?><a name="addForm" /></th></tr>
                     <tr><td><?=_("Курс")?></td><td>
                     <select name="course" id="sel_courses" onchange="selectPeople(this.value);">
                        <?= $GLOBALS['s']['perm']>2 ? '<option value="0">Всем</option>' : '' ?>
      <? /*"javascript:document.location.href='<?=$PHP_SELF?>?cid='+this.value+'#addForm';"*/
         //$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
         foreach ($courses as $coursename){
             if (!$courseFilter->is_filtered($coursename)) continue;
             $Title=getfield($coursestable,"Title","CID",$coursename);
             echo "<option value='".$coursename."'";
             echo ($s['user']['scourse']==$coursename)? " selected":"";
             echo ">".$Title."</option>";
         }
     ?>
                            </select></td></tr>
                            <tr>
                            <td nowrap><?=_("Текст объявления")?></td>
                            <td>
<?php
$oFCKeditor = new FCKeditor('msgbody') ;
$oFCKeditor->BasePath   = "{$GLOBALS['sitepath']}lib/FCKeditor/";
$oFCKeditor->Value      = '';
$oFCKeditor->Width      = 500;
$oFCKeditor->Height     = 300;
$oFCKeditor->ToolbarSet = 'ForumToolbar';
$fck_code = $oFCKeditor->Create();
?>
                            </td>
                            </tr>
                     <?
                     if ($teach)
                           {
                     ?>
                     	<tr><td colspan="2"><input type="radio" name="sendto" value="all" checked onClick="removeElem('newmessage2')" />&nbsp;<?=_("всем слушателям")?>
                     	<input type="radio" name="sendto" value="personal" onClick="putElem('newmessage2')" />&nbsp;<?=_("персонально")?></td></tr>
                     <tr><td colspan="2">
                     <span class=hidden2 id='newmessage2'></span>
                     </td></tr>
                     	<tr><td colspan="2"><input type="checkbox" name="sendmail" value="ok" />&nbsp;<?=_("Разослать по e-mail")?></td></tr>
                     <?
                           }
                     ?>
                            <tr><td align="right" colspan="2">
                            	<div style='float: right; margin-left:4px' class='button'><a href='<?=$sitepath?>guestbook.php4'><?=_("Отмена") ?></a></div>
 								<?=okbutton()?>
                            </td></tr>
                     </table></td></tr>
                            <tr><td height=2><img src="images/spacer.gif" width=1 height=1></td></tr>


                      </table></td></tr>
                </table></td></tr>
<?php
   } // check perms
   
// **************************************************************************** EDIT

   if (($GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OWN)
   || $GLOBALS['controller']->checkPermission(GUESTBOOK_PERM_EDIT_OTHERS)) && $_GET['edit']) {

   	$sql = "SELECT CID, text FROM posts3 WHERE PostID = ".$_GET['edit'];
	$result = sql($sql);
	if (!$post = sqlget($result))
		refresh('guestbook.php4');
       ?>
<!-- Написать объявление -->
                <tr><td><table width=100% border=0 cellspacing="0" cellpadding="0">
                      <tr><td colspan=2 ><a name='form_edit' /><table id=newmessage width=100% border=0 cellspacing=0 cellpadding=5>

                            <tr class=questt><td>
                            <table width=100% class=main cellspacing=0>
                     <tr><th colspan="2"><?=_("редактировать объявление")?><a name="addForm" /></th></tr>
                     <tr><td><?=_("Курс")?></td><td>
                     <select name="course" id="sel_courses" onchange="selectPeople(this.value);">
                        <?= $GLOBALS['s']['perm']>2 ? '<option value="0">Всем</option>' : ''; 
         foreach ($courses as $coursename){
             if (!$courseFilter->is_filtered($coursename)) continue;
             $Title=getfield($coursestable,"Title","CID",$coursename);
             echo "<option value='".$coursename."'";
             echo ($post['CID']==$coursename)? " selected":"";
             echo ">".$Title."</option>";
         }
     ?>
                            </select></td></tr>
                            <tr>
                            <td nowrap><?=_("Текст объявления")?></td>
                            <td>
<?php
$oFCKeditor = new FCKeditor('msgbody') ;
$oFCKeditor->BasePath   = "{$GLOBALS['sitepath']}lib/FCKeditor/";
$oFCKeditor->Value      = $post['text'];
$oFCKeditor->Width      = 500;
$oFCKeditor->Height     = 300;
$oFCKeditor->ToolbarSet = 'ForumToolbar';
$fck_code = $oFCKeditor->Create();
?>
                            </td>
                            </tr>
                     <?
                     if ($teach)
                           {
                     ?>
                     	<tr><td colspan="2"><input type="radio" name="sendto" value="all" checked onClick="removeElem('newmessage2')" />&nbsp;<?=_("всем слушателям")?>
                     	<input type="radio" name="sendto" value="personal" onClick="putElem('newmessage2')" />&nbsp;<?=_("персонально")?></td></tr>
                     <tr><td colspan="2">
                     <span class=hidden2 id='newmessage2'></span>
                     </td></tr>
                     	<tr><td colspan="2"><input type="checkbox" name="sendmail" value="ok" />&nbsp;<?=_("Разослать по e-mail")?></td></tr>
                     <?
                           }
                     ?>
                            <tr><td align="right" colspan="2">
                            	<div style='float: right; margin-left:4px' class='button'><a href='<?=$sitepath?>guestbook.php4'><?=_("Отмена") ?></a></div>
 								<?=okbutton()?>
                            </td></tr>
                     </table></td></tr>
                            <tr><td height=2><img src="images/spacer.gif" width=1 height=1></td></tr>


                      </table></td></tr>
                </table></td></tr>
<?php
   }
// ****************************************************** END OF EDIT
?>
                <tr><td height=20><img src="images/spacer.gif" width="1" height="1"></td></tr>

             </table>
             </form>
             <script type="text/javascript">
                document.observe('dom:loaded', function() {
                    selectPeople($F('sel_courses'));
                });
                //document.observe('dom:loaded', selectPeople($F('sel_courses')));
             </script>

<?


//  include('bottom.php4');

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();


?>