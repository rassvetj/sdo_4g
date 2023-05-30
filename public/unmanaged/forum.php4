<?
require_once('1.php');
require_once('positions.lib.php');
require_once('lib/classes/MessageFilter.class.php');

$inboxes = array(
0=>_("Все"),
1=>_("Начальник"),
2=>_("Куратор"),
3=>_("Уч. администрация"),
4=>_("Элементы уч. структуры"),
5=>_("Одногруппники"),
6=>_("Однокурсники"),);

$dependences = array(
4=>array('departments'),
5=>array('courses'),
6=>array('courses'),
);

//$dean_name="Вопросы администрации";

function showCourseMessages( $course ){
      $sql="SELECT forummessages.name as name, forummessages.email as email, forumthreads.course as $course
            FROM `forummessages`,
                 `forumthreads`
            WHERE
            forumthreads.thread=forummessages.thread
            AND forummessages.thread='".$thread."'
            GROUP by forumthreads.course";

}

function check_sendForumMain($mid, $cid) {
    if (is_teacher($mid) || is_dean($mid) || is_admin($mid)) {
        return true;
    }
    $q = "SELECT * FROM Students WHERE MID = '{$mid}' AND CID = '{$cid}'";
    $r = sql($q);
    if (sqlrows($r)) {
        return true;
    }
    return false;
}

function sendForumMail($thread,$userName,$message) {
   global $dean_name;
   global $optionstable;
   global $CID;
   
   $sql = "
        SELECT 
          forummessages.email,
          forummessages.name,
          forummessages.mid,
          forumthreads.course
        FROM
          forummessages
          INNER JOIN forumthreads ON (forummessages.thread = forumthreads.thread)
        WHERE
          (forummessages.sendmail = '1') AND 
          (forummessages.is_topic = '1')   
   ";
            if ($result=sql($sql)) {
                while ($res=sqlget($result)) {
                     if (!empty($res['email'])) {
                        $add['course']=$res['course'];
                        $add['lf']=$res['name'];
                        $add['lf2']=$userName;
                        $add['MESSAGE']=$message;
                        $add['email']=$res['email'];
                        if (check_sendForumMain($res['mid'], $CID)) {
                            mailToother("guestmes",$CID,$add);
                        }
                     }
                }
            }

   }

   //if (!$stud) login_error(); 

   //if ($s['perm']==2) $courses=$s['tkurs'];
   //else $courses=$s['skurs'];
   
   $sql = "SELECT DISTINCT Courses.CID, Courses.Title
           FROM Students INNER JOIN Courses ON (Courses.CID=Students.CID)
           WHERE Students.MID='".(int) $s['mid']."'
           ";
   $res = sql($sql);
   $courses = array();
   while($row = sqlget($res)) $courses[$row['CID']] = $row['Title'];

   //if (empty($courses)) login_error();


// 2002 10 02

   //$CID=(isset($_REQUEST['CID'])) ? $_REQUEST['CID'] : /*$s['user']['scourse']*/-2;
   //if ($CID[0]=='d') $department = (int) substr($CID,1);
   
   //if ($s['perm'] == 3) $CID=-1;

   //defines
   //if (!isset($CID)) $CID=reset($courses);



// if (!$CID) {header("location:".$sitepath."start.php4"); exit();}
   //if (!$CID) login_error();

   $SID=get_sid($s['mid'],$CID);

   $PID=get_pid($s['mid'],$CID);

// if (!$SID && !$PID) {header("location:".$sitepath."start.php4"); exit();}

//   if (!$SID && !$PID) login_error();

// end

   //$showfull=1;
   if (isset($_GET['showfull'])) $showfull=$_GET['showfull'];
   elseif (isset($_POST['showfull'])) $showfull=$_POST['showfull'];
   //run

   $userCourses=explode(":",$userCoursesImploded);

// 2002 10 02
   
   if (isset($CID)) $s['user']['scourse'] = $CID;
   $selectedcourse=getfield($coursestable,"Title","CID",$CID);
   if( $selectedcourse =="" ) $selectedcourse=$dean_name;
   if ($CID[0]=='d') $selectedcourse='';
//end
   //echo "PID=$PID";
   if (isset($_GET['delete']) && $PID){
        $rq = "SELECT name,mid FROM $forummessages WHERE id=$delete";
        $rq = sql($rq);
        if (sqlrows($rq))  {        
            $rq = sqlget($rq);
            if ((($rq['mid']==$GLOBALS['s']['mid'])
                && $GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OWN)) 
                || ($GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OTHERS))) {
                    $rq="DELETE FROM $forummessages WHERE id=$delete";
                    @sql( $rq );
                    @sql("UPDATE $forumthreads SET answers=answers-1 WHERE thread='$thread'");
            }
        }
   }

// Добавление сообщения, новой ветки
// =================================
if (isset($addmessage) && !empty($message)){
   $message=htmlspecialchars($message);
   $posted=time();
   $userName=mid2name($s['mid']);
   $userEmail=getField("People","Email","MID",$s['mid']);
   $emailanswers = (isset($_POST['answbymail'])) ? 1 : 0;
   $thread=(isset($_POST['thread'])) ? $_POST['thread'] : "";
   //новая  тема
   //ответ
   if($thread){
      @sql("UPDATE $forumthreads SET lastpost=$posted, answers=answers+1 WHERE thread='$thread'");
      sendForumMail($thread,$userName,$message);
      $is_topic = 0;
   } else {
      //$rq="INSERT INTO $forumthreads (lastpost) VALUES('{$CID}','$posted','{$private}')";
      $rq="INSERT INTO $forumthreads (lastpost) VALUES('$posted')";
      @sql( $rq );
      $thread=sqllast ();
      $is_topic = 1;
   }

   global $adodb;
   
   if ($_POST['courses']) $oid = $_POST['courses'];
   if ($_POST['departments']) $oid = $_POST['departments'];
   if (!$oid && in_array($_POST['type'],array(4,5,6))) $_POST['type'] = 0;
   if (!in_array($_POST['type'],array(4,5,6))) $oid=0;
   
   $SQL = "INSERT INTO $forummessages 
         (thread, posted, icon, message, name, email, sendmail, is_topic, mid, type, oid) 
         VALUES (
         '$thread',
         '$posted',
         $icon,
         ".$adodb->Quote($message).",
         ".$adodb->Quote($userName).",
         ".$adodb->Quote($userEmail).",
         '$emailanswers', 
         '{$is_topic}',
         '{$s['mid']}',
         '".(int) $_POST['type']."',
         '".(int) $oid."')";
   
   sql($SQL);

}
// =================================

//тема
function showtopic($id, $text, $epochtime, $name, $email, $icon, $answers, $teach)
{ ?>
<tr align="center">
<td  align="left"><TABLE border=0 cellspacing=0 cellpadding=0  class=forum>

<tr><td valign=top><img src="images/forum/<?echo $icon;?>.gif" ></td><td><a href="<?echo "?thread=$id";?>"><?echo $text;?> </a></td></tr></table></td>
<td nowrap><a href="mailto:<?echo $name."<".$email.">";?>"><?echo $name;?> </a></td>
<td> <?echo $answers;?> </td>
<td> <span class="text"><?echo date("G:i", $epochtime);?>&nbsp;</span><span class="textdata"><?echo date("d.m.y", $epochtime);?></span></td>
</tr>
<? }


function showmessage($thread, $id, $text, $epochtime, $name, $icon, $teach,$mid)
{
 global $dean_name; ?>
<td valign=top><img src="images/forum/<?echo $icon;?>.gif"></td>
<td width=100% valign="top"><?echo $text;?></td>
<td nowrap><?echo $name;?></td>
<td><?echo date("G:i", $epochtime);?>&nbsp;<?echo date("d.m.y", $epochtime);?></td>

<?
//if ($teach){
   echo "<td width=10 CLASS='cHilight' style='padding:0;'>";
    if ((($mid==$GLOBALS['s']['mid']) 
        && $GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OWN)) 
        || ($GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OTHERS)))
   echo "<a href='?thread=$thread&delete=$id&showfull=".(int) $_REQUEST['showfull']."' title='"._("Удалить сообщение")."'>".getIcon("delete", _("Удалить сообщение"))."</a>";
   echo "</td>";
//}
?>
</tr>
<? }?>
<?
   if (!$showfull) echo show_tb();
      else
         {
          $GLOBALS['controller']->setView('DocumentPopup');   
          if (!isset($top1))
                  ?>
                  <HTML>
                  <head>
                  <META content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" http-equiv="Content-Type">
                  <TITLE>eLearning Server 3000</TITLE>
                  <SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
                  <SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
                  <SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
                  <title></title>
                  <link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
                  </head>
                  <BODY  class=cPageBG leftmargin=0 rightmargin=0 marginwidth=0 topmargin=0 marginheight=0>
                  <?
         }
   $GLOBALS['controller']->captureFromOb(CONTENT);
?>
      <table border=0 cellpadding=0 cellspacing=0 align=center width="100%" class=skip>
<? //запрос
if (isset($thread)){
      if (!$showfull)
      {
          $GLOBALS['controller']->setLink('m100101');
          $GLOBALS['controller']->captureFromOb(TRASH);
      ?>
          <tr><td><a href='forum.php4'><?=_("Список вопросов")?></a><br><br></td></tr>
       <?
          $GLOBALS['controller']->captureStop(TRASH);
      }
       ?>
<? $query =  "SELECT $forumthreads.thread,id,icon,message,name,email,posted,mid
                  FROM $forummessages, $forumthreads
                  WHERE $forummessages.thread=$thread
                    AND $forummessages.thread=$forumthreads.thread
                  ORDER by posted DESC";

   $result=sql($query);
   if ($result){
      $messageFilter = new CMessageFilter();
      $messageFilter->init(); 
      if (sqlrows($result)>0){
?>
<!-- /заголовок таблицы -->
<!-- список тем -->
         <tr><td><table width=100% class=main cellspacing=0 id=forums>
        <tr><th colspan="5"><?=_("ответы")?></th></tr>
         <?
          while ($row = sqlget($result)){
            if (!$messageFilter->is_filtered($row['id'])) continue;  
            showmessage($thread, $row['id'], $row['message'], $row['posted'], $row['name'], $row['icon'], $teach, $row['mid']) ;
         }
         echo "</table></td></tr>";
      }
      else {
//         echo "<tr><td>-<td></tr>";
      }
   }
   else {
      echo "<tr><td>"._("Ошибка в запросе к базе данных")."<td></tr>";
  }
}
else {
if (count($courses)>=1){
  //<!-- заголовок таблицы -->
         $tit=_("Вопросы:")."&nbsp;
          <select name=ChCourse onChange=\"navigate('forum.php4?CID='+ChCourse.value);\">";
         $tit.="<option value='-1'>$dean_name</option>";
         $filter_kurses['-1']=$dean_name;
         if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
             $groups = get_needed_departments($s['mid']);
             if (is_array($groups) && count($groups)) {
                 while(list($k,$v) = each($groups)) $filter_kurses['d'.(int) $k] = $v;
             }
         }
         $filter_kurses['---'] = '---';
         if($s['perm'] != 3) {
            $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']); 
            foreach ($courses as $coursename){
                if (!$courseFilter->is_filtered($coursename)) continue;
                $Title=getfield($coursestable,"Title","CID",$coursename);
                $tit.="<option value='".$coursename."'";
                $tit.=($s['user']['scourse']==$coursename)? " selected":"";
                $tit.=">"._("по курсу")." '".$Title."'</option>";
                $filter_kurses[$coursename] = $Title;
            }
         }
         
         //$GLOBALS['controller']->addFilter('Вопросы','CID',$filter_kurses,$CID,true,'-2',true);
         $tit.="</select><br><br></td></tr>";
         if (!$GLOBALS['controller']->enabled)
         echo "<span class=shedtitle>$tit</span>";
}

$strExtraWhere = ($teach) ? "" : "AND ".$forumthreads.".thread NOT IN (".forum_id_string().")";

    $q="
        SELECT 
          forummessages.id,  
          forumthreads.thread,
          forummessages.message,
          forumthreads.lastpost,
          forummessages.name,
          forummessages.email,
          forummessages.icon,
          forumthreads.answers
        FROM
          forumthreads
          INNER JOIN forummessages ON (forumthreads.thread = forummessages.thread)
        WHERE
          forummessages.is_topic = '1'
          {$strExtraWhere}
        ORDER BY posted DESC";

/*
if ($CID == -1) {
    $q = "
        SELECT 
          forumthreads.thread,
          forummessages.message,
          forumthreads.lastpost,
          forummessages.name,
          forummessages.email,
          forummessages.icon,
          forumthreads.answers
        FROM
          forumthreads
          INNER JOIN forummessages ON (forumthreads.thread = forummessages.thread)
        WHERE
          forummessages.is_topic = '1' AND
          forumthreads.course = '{$CID}'";
          if (!is_kurator($GLOBALS['s']['mid']))
          $q.=" AND (forumthreads.private = '0' OR (forummessages.mid={$GLOBALS['s']['mid']} AND forummessages.is_topic='1'))";          
          $q.="
          {$strExtraWhere}
          ORDER BY posted DESC";

} else {

   $q = "
        SELECT 
          forumthreads.thread,
          forummessages.message,
          forumthreads.lastpost,
          forummessages.name,
          forummessages.email,
          forummessages.icon,
          forumthreads.answers
        FROM
          forumthreads
          INNER JOIN forummessages ON (forumthreads.thread = forummessages.thread)
        WHERE
          forummessages.is_topic = '1' AND
          forumthreads.course = '{$CID}' ";
          if (!is_kurator($GLOBALS['s']['mid']))
          $q.=" AND (forumthreads.private = '0' OR (forummessages.mid={$GLOBALS['s']['mid']} AND forummessages.is_topic='1'))";          
          $q .= "{$strExtraWhere}
          ORDER BY posted DESC
    ";  
}
*/
   $result=sql($q);   
   if ($result){
      $messageFilter = new CMessageFilter();
      $messageFilter->init(); 
      if (sqlrows($result)>0){
?>
<!-- /заголовок таблицы -->
<!-- список тем -->
         <tr  CLASS=QUESTT><td class=BRDR><table width=100% class=main cellspacing=0 id=forums>
<tr>
         <th width="100%"><span ><?=_("вопрос")?></span></th>
         <th><span ><?=_("автор")?></span></th>
         <th><span ><?=_("ответов")?></span></th>
         <th><span ><?=_("дата")?></span></th>
</tr>         
<?      
        while ($row = sqlget($result)){
             if (!$messageFilter->is_filtered($row['id'])) continue;             
             showtopic($row['thread'], substr($row['message'],0,100), $row['lastpost'], $row['name'], $row['email'], $row['icon'], $row['answers'], $teach );
         }
         echo "</table></td></tr>";
      }
      else
         echo "<tr><td>"._("Нет ни одного вопроса")."<td></tr>";
   }
   else {
      echo "<tr><td>"._("Ошибка в запросе к базе данных")."<td></tr>";
  }
}
$strEmail = ($thread) ? "" : '<input type="checkbox" name="answbymail">'._("высылать ответы по email");
?>
                  <tr><td><img src="images/spacer.gif" alt="" width=1 height=20 border=0></td></tr>
<!-- /список тем -->
<!-- /заголовок сообщения -->
                  <tr><td><TABLE border=0 cellspacing=0 cellpadding=0><tr>
<!-- /заголовок сообщения -->
<!-- сообщение -->
                  <tr id=newforum class=questt><td colspan=2  class=brdr>
<?php
   if (($GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OWN)) 
   || ($GLOBALS['controller']->checkPermission(FORUM_PERM_EDIT_OTHERS))) {
?>
<table class=main cellspacing=0>
                   <form  action="forum.php4" method="post" name="NewMessage">
                   <input type="hidden" name="showfull" value="<?=$showfull?>">
                    <? if (isset($thread)){ echo "<input type=hidden name=thread value=$thread>";}?>
                    <input type=hidden name=course value='<? echo $selectedcourse; ?>'>
                    <input type=hidden name=CID value='<? echo $CID; ?>'>
                    <input type=hidden name=addmessage value=1>                   
                        <tr><th colspan="2"><?php if (isset($thread)) echo _("добавить ответ"); else echo _("добавить вопрос"); ?></th></tr>
                        <tr> <td><img src="images/spacer.gif" width=1 height=1 alt=""><?=_("иконка")?></td>
                          <td height=3><input type="radio" name="icon" value="1" checked>
                            <img src="images/forum/1.gif">
                            <input type="radio" name="icon" value="2">
                            <img src="images/forum/2.gif">
                            <input type="radio" name="icon" value="3">
                            <img src="images/forum/3.gif">
                            <input type="radio" name="icon" value="4">
                          <img src="images/forum/4.gif">
                          <input type="radio" name="icon" value="5">
                          <img src="images/forum/5.gif">
                          <input type="radio" name="icon" value="6">
                          <img src="images/forum/6.gif">
                          <input type="radio" name="icon" value="7">
                          <img src="images/forum/7.gif">
                          <input type="radio" name="icon" value="8">
                          <img src="images/forum/8.gif"></td>
                        </tr>
                        <tr>
                          <td nowrap><?=_("текст сообщения")?> </td>
                          <td><textarea name="message" cols="60" rows="5" ></textarea></td>
                        </tr>
<tr>
    <td nowrap>видимость </td>
    <td>
    
    <select name="type" onChange="<?php
    echo "document.getElementById('courses').style.display = 'none';";
    echo "document.getElementById('departments').style.display = 'none';";
    foreach($dependences as $k=>$v) {
        if (is_array($v) && count($v))
            foreach($v as $vv) 
                echo "if (this.value==$k) document.getElementById('$vv').style.display = 'inline';";
    }
    ?>">
<?php
    foreach($inboxes as $k=>$v) {
        echo "<option value=\"$k\"> $v</option>";
    }
?>    
    </select>

    <select id="courses" name="courses" onChange="" style="display: none;">
<?php
    $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']); 
    foreach($courses as $k=>$v) {
        if (!$courseFilter->is_filtered($k)) continue;
        //$Title=getfield($coursestable,"Title","CID",$v);
        echo "<option value=\"$k\"> $v</option>";
    }
?>    
    </select>

    <select id="departments" name="department" onChange="" style="display: none;">
<?php
    $departments = get_needed_departments($s['mid']);
    if (is_array($departments) && count($departments)) {
        foreach($departments as $k=>$v) echo "<option value=\"$k\">$v</option>";
    }
?>    
    </select>
    
    </td>
</tr>
<? 
    if (!isset($thread)){ 
?>
<tr>
    <td colspan="2"><?=$strEmail?></td>
</tr>
<?        
    }
?>
                        <tr>
                          <td colspan="2"><?=okbutton()?></td>
                        </tr>
                  </form>
                      </TABLE>
<?php
   }
?>                      
                      </td></tr>
<!-- /сообщение -->
                      </TABLE></td></tr>
              </table>

<!-- Временно, можно удалить <a href = ../istudium/upload.htm>-</a> -->


<?
$GLOBALS['controller']->captureStop(CONTENT);
if (!$showfull) echo show_tb();//require_once("bottom.php4");
   else {
       $GLOBALS['controller']->terminate();
         
?>
</body>
</html>
<?
   }   
   
?>