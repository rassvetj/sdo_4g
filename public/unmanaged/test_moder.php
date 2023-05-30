<?

// define all varibles and settings
   require_once("1.php");
   
   
   extract($_GET);
   extract($_POST);
   
   
   $GLOBALS['controller']->setView('DocumentContent');
   include("test.inc.php");
   $sysm=loadwords("sysmsg.html");
// $courses - course array of current user
// pr($courses)
   //$self_view = ($controller->checkPermission(TESTS_VIEW_RESULTS) && test_view_results_self());
   //if (!$teach && !$self_view) exitmsg($sysm[1].$sysm[2].$sysm[3].$sysm[4],"/?$sess");
// redirect to the index page if user is not registred
   //if (empty($courses)) exitmsg($sysm[5],"/?$sess");
// redirect to the index page if user have no Courses
// get value CID - current course id of select first of user courses


   if (!isset($s[test_modvar])) $s[test_modvar]=0;


   if ($GLOBALS['controller']->enabled) $s[$ss][cid] = $_GET['cid'];
   $CID=(isset($_GET['CID'])) ? intval($_GET['CID']) : $s[$ss][cid];
   //if ($GLOBALS['controller']->enabled && isset($_POST['CID'])) $s[$ss][cid] = (int) $_POST['CID'];
   //$CID=$s[$ss][cid];

//   pr($s);

	function test_view_results_self(){
		$query = "
			SELECT
			  seance.`mid`
			FROM
			  seance
			WHERE
			  (seance.stid = '{$_GET['stid']}') AND
			  (seance.`mid` = '{$_SESSION['s']['mid']}')
		";
		$res = sql($query);
		return sqlrows($res);
	}

   function getmmnum ($tid) {
      global $CID;
      $cnt=sqlvalue("SELECT COUNT(*) FROM seance WHERE cid='".$CID."' AND tid='".$tid."' AND bal IS NULL","err1");
      return $cnt;
   }

   function getNavnum ($tid,$stid,$type=0) {
      global $CID;
      if (!$type) $cnt=sqlvalue("SELECT COUNT(*) FROM seance WHERE cid='".$CID."' AND tid='".$tid."' AND stid>'".$stid."'","err1");
         else $cnt=sqlvalue("SELECT COUNT(*) FROM seance WHERE cid='".$CID."' AND tid='".$tid."' AND stid<'".$stid."'","err1");
      return $cnt;
   }

   function getLT ($tid,$stid) {
      global $CID;
      $cnt=sqlvalue("SELECT stid FROM seance WHERE cid='".$CID."' AND tid='".$tid."' AND stid<'".$stid."'","err1");
      return $cnt;
   }

   function getGT ($tid,$stid) {
      global $CID;
      $cnt=sqlvalue("SELECT stid FROM seance WHERE cid='".$CID."' AND tid='".$tid."' AND stid>'".$stid."'","err1");
      return $cnt;
   }

if (in_array($_REQUEST['action'], array('complete', 'clearsence'))) $c = $_REQUEST['action'];

switch ($c) {

// Список заданий и кол-ва вопросов на проверку
case "":

   $html=show_tb(1);
   $page=$PHP_SELF;
   $allcontent=loadtmpl("mtasks-main.html");
   $cheader=loadtmpl("all-cHeader.html");
   $allwords=loadwords("mtasks-words.html");
   $words['PAGENAME']=$allwords[0];
   $words['PAGESTATIC']="";
   $words['coursename']=cid2title($CID);
   $words['task']=$allwords[1];
   $words['count']=$allwords[2];
   $mtasks="";
   $mtaskstmp=loadtmpl("mtasks-tr.html");

   $res=sql("SELECT t1.tid, t1.title FROM test t1
            INNER JOIN seance t2 ON (t2.tid = t1.tid)
            WHERE t2.cid='".$CID."' GROUP BY t1.tid, t1.title","err1");
   while ($row=sqlget($res)) {
     $mt['ID']=$row['tid'];
     $mt['CID']=$CID;
     $mt['task']=$row['title'];
     $mt['count']=getmmnum($row['tid']);
     $mtasks.=words_parse($mtaskstmp,$mt,"MT-");
   }

   if ($GLOBALS['controller']->enabled) $cheader='';
   $allcontent=str_replace("[ACTIONS]",'',$allcontent);
   $allcontent=str_replace("[BUTTONS]",'',$allcontent);
   $allcontent=str_replace("[BEFORE]",'',$allcontent);
   $allcontent=str_replace("[BEFORE1]",'',$allcontent);
   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   $html=str_replace("[MTASKS-HEADER]",$cheader,$html);
   $html=str_replace("[TEST-LIST]",$mtasks,$html);
   $html=str_replace("[INSIDE-TID]","",$html);
   $html=str_replace("[PAGE]",$page,$html);
   $html=str_replace("[PATH-CORNER]",$GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . '/' : $sitepath,$html);
   
   if ($GLOBALS['controller']->enabled) {
       $html=words_parse($html,$words);
       $html=path_sess_parse($html);

       if($CID) {
            $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
       }
       $kurses = selCourses($s['tkurs'], $CID , $GLOBALS['controller']->enabled);
       //$GLOBALS['controller']->addFilter(_("Курс"), 'CID', $kurses, $CID, true);
       $GLOBALS['controller']->setHeader(_("Проверка Заданий"));
   }
   
   //echo $html;
   //$GLOBALS['controller']->setView('DocumentBlank');
   //ob_start();
   $GLOBALS['controller']->terminate();
   //ob_end_flush();
   //printtmpl($html);
    

   break;

// Вывод списка вопросов на проверку
case "showtask":

   if (isset($_POST['action']) && ($_POST['action'] == 'delete_selected')
       && is_array($_POST['checked']) && count($_POST['checked'])) {
           
       $sql = "DELETE FROM seance WHERE stid IN ('".join("','",array_keys($_POST['checked']))."') AND kod IN ('".join("','",$_POST['checked'])."')";
       if (sql($sql)) {
           $sql = "UPDATE loguser
                   SET moderby = '".(int) $_SESSION['s']['mid']."',
                       modertime = '".time()."',
                       needmoder = '0'
                   WHERE stid IN ('".join("','",array_keys($_POST['checked']))."') AND kod IN ('".join("','",$_POST['checked'])."')";
           sql($sql);
       }
       //$GLOBALS['controller']->setMessage(_("Выделенные ответы успешно удалены"),JS_GO_URL,'test_moder.php?c=showtask&tid='.$tid.'&cid='.$cid);
       //$GLOBALS['controller']->terminate();
       //exit();
   }

   $html=show_tb(1);
   $page=$PHP_SELF;
   $allcontent=loadtmpl("mtasks-main.html");
   $allwords=loadwords("mtasks-words.html");
   $allheader=ph("<a href='[PAGE]?[SESSID]'>".$allwords[0]."</a>");
   $words['coursename']=tid2title($tid);
   $words['task']=$allwords[3];
   $words['count']=$allwords[4];
   $words['fio']=$allwords[5];
   $words['later']=$allwords[6];

   $mtasks="";
   $mtaskstmp=loadtmpl("mtasks-tr1.html");

//   $res=sql("SELECT * FROM seance WHERE tid='".$tid."'","err1");

   $res=sql("SELECT seance.kod, UNIX_TIMESTAMP(seance.time) as time, seance.cid,
                    seance.stid, seance.kod, seance.tid, seance.bal as ball,
                    People.FirstName, People.LastName, People.Patronymic, list.qdata as qdata,
                    test.title as tdata,subjects.name as ctitle
             FROM People, list, subjects, seance LEFT JOIN test on seance.tid = test.tid
             WHERE seance.tid='".$tid."' AND seance.mid = People.mid
                   AND seance.kod = list.kod AND subjects.subid = seance.cid
             ORDER BY People.LastName ASC, seance.stid DESC, time ASC","err249");

/*pr("SELECT seance.kod, UNIX_TIMESTAMP(seance.time) as time, seance.cid,
                    seance.stid, seance.kod, seance.tid, seance.bal as ball,
                    People.FirstName, People.LastName, People.Patronymic, list.qdata as qdata,
                    test.title as tdata,Courses.title as ctitle
             FROM People, list, Courses, seance LEFT JOIN test on seance.tid = test.tid
             WHERE seance.tid='".$tid."' AND seance.mid = People.mid
                   AND seance.kod = list.kod AND Courses.CID = seance.cid
             ORDER BY People.LastName ASC, seance.stid DESC, time ASC");
exit;*/
   if (sqlrows($res)<1) $mtasks="<tr><td colspan='6' align='center'>"._("нет ни одного вопроса на проверку")."</td></tr>";
   else  while ($row=sqlget($res))
         {
           $mt['ID']=$row['tid'];
           $mt['sTID']=$row['stid'];
           $mt['CID']=$CID;
           $mt['task']=qdata2text($row['qdata']);
           $mt['ball']=$row['ball'];
           $mt['kod']=ue($row[kod]);
           $mt['fname']=html($row['FirstName']);
           $mt['lname']=html($row['LastName']);
           $mt['patronymic']=html($row['Patronymic']);
           $mt['later']=date("H:i d:m:Y",$row['time']);
           $mtasks.=words_parse($mtaskstmp,$mt,"MT-");
         }

   $allcontent=str_replace("[ACTIONS]","
       <table border=0 cellpadding=12 cellspacing=0>
         <tr>
           <td>
           "._("Выполнить действие:").": &nbsp;
           <input type='hidden' name='tid' value='{$tid}'>
           <select id=\"sel_action\" name=\"action\" onChange=\"javascript:disableCheckboxes((this.value == 'clearsence') || (this.value == 'complete'))\">
             <option value=\"\"> "._('---')."</option>
             <option value=\"complete\"> "._('сохранить результаты')."</option>
             <option value=\"clearsence\"> "._('удалить обработанные вопросы')."</option>
             <option value=\"delete_selected\"> "._('удалить выделенные вопросы')."</option>
           </select>
           </td><td>[OKBUTTON]
           </td>
         </tr>
       </table>",$allcontent);

$str_confirm_complete = "'" . _("Вы действительно желаете сохранить результаты? При этом произойдет перерасчет общего балла за задание и оценки за занятие (в случае, если занятие оценивается по формуле). Если занятие предусматривает штраф за несвоевремменную сдачу, величина штрафа будет также расчитана заново. Будут обработаны только полностью проверенные сеансы выполнения данного задания. Продолжить?") . "'";
$str_confirm_clear = "'" . _("Вы действительно удалить обработанные вопросы? После этой операции тексты ответов и прикрепленные к ним файлы будут удалены. Продолжить?") . "'";

   $allcontent=str_replace("[BEFORE]",'<th><input type="checkbox" onClick="selectAllAnswers(this.checked)" id="checkall"></th>',$allcontent);
   $allcontent=str_replace("[BEFORE1]",'<th align="center">'._("сеанс").'</th>',$allcontent);
   $allcontent=str_replace("[CONFIRM_COMPLETE]",$str_confirm_complete,$allcontent);
   $allcontent=str_replace("[CONFIRM_CLEAR]",$str_confirm_clear,$allcontent);
   $allcontent=str_replace("[OKBUTTON]",okbutton('', 'onClick="javascript: return confirmSelect();"'),$allcontent);
   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   $html=str_replace("[MTASKS-HEADER]",$allheader,$html);
   $html=str_replace("[TEST-LIST]",$mtasks,$html);
   $html=str_replace("[INSIDE-TID]","<th>[W-fio]</th><th>[W-later]</th>",$html);
   $html=str_replace("[PAGE]",$page,$html);
   $html=str_replace("[PATH-CORNER]",$GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . '/' : $sitepath,$html);

   if ($GLOBALS['controller']->enabled) {
       $html=words_parse($html,$words);
       $html=path_sess_parse($html);
       $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
       $GLOBALS['controller']->setHeader(_("Проверка вопросов со свободным ответом"));
   }
    $GLOBALS['controller']->terminate();


   break;


case "show_questions":

   $s[test_modvar]=1;
   $res=sql("
      SELECT seance.kod, UNIX_TIMESTAMP(seance.time) time, seance.cid, seance.stid, seance.kod,
             seance.tid, seance.bal, People.FirstName, People.LastName,
          list.qdata as qdata,
          test.title as tdata,
          Courses.title as ctitle
      FROM seance, People, list, Courses
     LEFT JOIN test on seance.tid=test.tid
      WHERE seance.cid IN (".implode(",",$s[tkurs]).")
            AND seance.mid=People.mid
         AND seance.kod=list.kod
         AND Courses.CID=seance.cid
      ORDER BY seance.kod","err249");
   if (sqlrows($res)==0) exitmsg(_("Больше вопросов для проверки нет."),"$PHP_SELF?$sess");

   $html=show_tb(1);
   $page=$PHP_SELF;
   $allcontent=loadtmpl("mtasks-main2.html");
   $cheader=loadtmpl("all-cHeader.html");
   $allwords=loadwords("mtasks-words.html");
   $mtasks="";
   $words['PAGENAME']=$allwords[9];
   $words['PAGESTATIC']=loadtmpl("mtasks-static3.html");
   $words['begin']=$allwords[10];
   $words['question']=$allwords[11];
   $words['ball']=$allwords[12];
   $words['fio']=$allwords[13];
   $words['testname']=$allwords[14];
   $words['coursename']=$allwords[15];
   $words['later']=$allwords[16];

   $mt=array();
   $mtasks="";
   $mtt=loadtmpl("mtasks-tr1.html");

   while ($r=sqlget($res)) {

      $mt['kod']=ue($r[kod]);
      $mt['sTID']=$r['stid'];
      $mt['question']=substr(html($r['qdata']),0,25)." ... ";
      $mt['qfull']=html($r['qdata']);
      $mt['ball']=$r['bal'];
      $mt['fname']=html($r['FirstName']);
      $mt['lname']=html($r['LastName']);
      $mt['TID']=$r['tid'];
      $mt['tname']=($r['tdata']) ? $r['tdata'] : $allwords[17];
      $mt['CID']=$r['cid'];
      $mt['coursename']=$r['ctitle'];
      $mt['later']=date("H:i d:m:Y",$r['time']);

      $mtasks.=words_parse($mtt,$mt,"MT-");
   }

   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   $html=str_replace("[MTASKS-HEADER]",$cheader,$html);
   $html=str_replace("[MTASKS-TESTS]",$mtasks,$html);
   $html=str_replace("[PAGE]",$page,$html);
   if ($GLOBALS['controller']->enabled) {
       $html=words_parse($html,$words);
       $html=path_sess_parse($html);
       $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
       $GLOBALS['controller']->setHeader(_("Проверка вопросов со свободным ответом"));
   }

   printtmpl($html);

   exit;

case "find_first":

   $s[test_modvar]=0;
   $res=sql("SELECT * FROM seance WHERE cid IN (".implode(",",$s[tkurs]).") AND bal IS NULL","err2");
   if (sqlrows($res)==0) exitmsg(_("Больше вопросов для проверки нет."),"$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);
   location("$PHP_SELF?c=moder&stid=$r[stid]&cid=$r[cid]&kod=".ue($r[kod])."$sess");
   exit;


case "moder":

   intvals("cid stid");
   if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: "._("Вы не можете обращаться к временным вопросам чужих курсов"));

   $rq="SELECT stid, mid, kod, cid, UNIX_TIMESTAMP(time) as time, tid, text, attach, filename, bal
        FROM seance
        WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
   $res=sql($rq,"err2");
   if (sqlrows($res)==0) exitmsg(_("Больше вопросов для проверки нет."),"$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM People WHERE mid=$r[mid]","err3");
   $rppl=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM list WHERE kod='".addslashes($r[kod])."'","err4");
   $rvop=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM test WHERE tid=$r[tid]","err5");
   $rtest=sqlget($res);
   sqlfree($res);
   //pr($rtest);

//   echo show_tb();

//   echo "<h3>Модерирование отложенного вопроса</h3>
   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];

   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo ph(_("Оценка отложенных на проверку ответов"));
   echo "
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td>
   <a href=$PHP_SELF?$sess>&lt;&lt; "._("к началу проверки")."</a><br>
   "._("Курс:")." ".cid2title($r[cid])." <!-- ($r[cid]) --><br>
   "._("Задание:")." $rtest[title] <!--($r[tid]) --><br>
   <!-- "._("Код вопроса:")." $r[kod]<br-->
  "._(" Дан ответ:")." ".date("H:i d/m/Y",$r[time])."<br>
   "._("Студент:")." $rppl[FirstName] $rppl[LastName] ($rppl[EMail])<br>
   <!--ID "._("сеанса:")." $r[stid]-->
         </td>
      </tr>
   </table><br>

   <!--hr size=1 noshade></p-->";

   include_once("template_test/$rvop[qtype]-v.php");
   $func="v_vopros_$rvop[qtype]";
   $null=array();
//   echo "<input type=hidden id='ischecked_0' value=0>";
//   echo $func($rtest,"template_test/$rvop[qtype]",0,$null);
   echo ph(_("Вопрос"));
   echo "<!--h3>Вопрос:</h3><br-->
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td>
   ".qdata2text($rvop[qdata]);
   echo "
         </td>
      </tr>
   </table><br>
   <!--p><hr size=1 noshade></p-->";
//   echo "<h3>Полученный ответ студента</h3>
   echo ph(_("Полученный ответ"));
   echo "
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td>
   "._("Текстовый ответ:")."<br>
   <textarea rows=10 cols=60>".html(substr($r[text],0,65535))."</textarea><br>
   <a href=$PHP_SELF?c=download&what=1&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess>"._("Скачать ответ целиком")."</a> |
   <a href=$PHP_SELF?c=download&what=2&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess target=_blank>"._("Открыть ответ в новом окне")."</a>
   <br><br>";

   if (strlen($r[attach]))
   echo _("Присоединенный файл")." (".strlen($r[attach])." "._("байт), показано первые 8Кбайт:")."<br>
   <textarea rows=10 cols=60>".html(substr($r[attach],0,8190))."</textarea><br>
   <a href=$PHP_SELF?c=download&what=3&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess>"._("Скачать прикрепленный файл целиком")."</a> |
   <a href=$PHP_SELF?c=download&what=4&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess target=_blank>"._("Открыть прикрепленный файл в новом окне")."</a>
         </td>
      </tr>
   </table><br>";

   echo "<!--P><hr size=1 noshade><P>
   <h3>"._("Ваша оценка ответа учащегося")."</h3-->";
   echo ph(_("Ваша оценка ответа учащегося"));
   echo "
   <form action=$PHP_SELF name=moder>
   <input type=hidden name=c value=\"moder_submit\">
   <input type=hidden name=stid value=\"$stid\">
   <input type=hidden name=cid value=\"$cid\">
   <input type=hidden name=kod value=\"".html($kod)."\">

   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td>
   "._("Возможный диапазон баллов:от")."
   <b>$rvop[balmin]</b> "._("до")." <b>$rvop[balmax]</b>";

   echo " <br> <label for=vmoder2><input type=radio name=vmoder value=2 id=vmoder2>
   "._("Произвольный балл (в пределах диапазона):")."
   <input size=6 type=text name=moder2 value=\"\"
   onPropertyChange='document.moder.vmoder2.checked=1'
   onClick='document.moder.vmoder2.checked=1'></label><br>";

   if ($rvop[balmax]-$rvop[balmin]<15) {
      echo "<label for=vmoder1><input type=radio name=vmoder value=1 checked id=vmoder1>
      Быстрая оценка:<small> ";
      for ($i=$rvop[balmin]; $i<=$rvop[balmax]; $i++) {
         echo "<input ".($i==$rvop[balmin]?"checked":"")."
            onPropertyChange='document.moder.vmoder1.checked=1'
            type=radio name=moder1 value='$i' id=x_".md5($i)."><label for=x_".md5($i).">$i</label> ";
      }
      echo "</label><br></small>";
   }

   if ($r[bal]!=NULL) echo "<P><font color=red><b>"._("Внимание!")."</b></font>
      "._("Вопрос уже имеет оценку в баллах:")." <b>$r[bal]</b>";

   echo "<P>"._("Далее перейти:")."<br>
   <label for=tmv1><input id=tmv1 type=radio name=test_modvar value=0
      ".($s[test_modvar]==0?"checked":"")."
      onPropertyChange='if (this.checked) xsave.checked=1'
      >"._("к проверке очередного вопроса")."</a></label><br>
   <label for=tmv2><input id=tmv2 type=radio name=test_modvar value=1
      ".($s[test_modvar]==1?"checked":"").">"._("к списку вопросов для проверки")."</a></label>
   <P>
   <label for=xsave><input checked id=xsave type=checkbox
      name=issave value=1 onPropertyChange='if (tmv1.checked && !xsave.checked) xsave.checked=1;'>
      "._("Записать вашу оценку в базу данных")."
      <sup><a href=# onclick='alert(\""._("Снять эту галочку можно только при выборе &lt;Далее перейти к списку вопросов")."&gt;\"); return false'>[?]</a></sup></label>
   <!--input type=submit value='&nbsp; "._("ДАЛЕЕ")." &gt;&gt; &nbsp;'-->
            </td>
      </tr>
   </table><br>
               <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
                  <tr>
                     <td align=\"right\" valign=\"top\"><input type=\"image\" name=\"ok\" onmouseover=\"this.src='".$sitepath."images/send_.gif';\" onmouseout=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\"></td>
                  </tr>
         </table>
   </form>";
     echo $html[1];

     $GLOBALS['controller']->captureStop(CONTENT);
//   echo show_tb();
/*
   echo "<table width=100% border=0 cellspacing=0 cellpadding=6 bgcolor=white><tr><td>
   <br><br>
   <center><h3>Внешний вид оригинального вопроса в окне максимальной ширины:</h3></center>
   <hr size=1 noshade>";
   include_once("test.inc.php");
   include_once("template_test/$rvop[qtype]-v.php");
   $func="v_vopros_$rvop[qtype]";
   $null=array();
   echo "<input type=hidden id='ischecked_0' value=0>";
   echo $func($rtest,"template_test/$rvop[qtype]",0,$null);


   echo "<hr size=1 noshade><P>&nbsp;</td></tr></table>";
*/
   break;



case "moder_submit":

   intvals("cid stid");
   if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: "._("Вы не можете обращаться к временным вопросам чужих курсов"));

   if ($vmoder==1) $bal=$moder1;
   else $bal=$moder2;
   $bal=doubleval($bal);
   if ($vmoder==0) $issave=1;

   if ($issave) {
      $res=sql("SELECT * FROM list WHERE kod='".addslashes($kod)."'","errTM275");
      if (!sqlrows($res)) {
         alert(_("Данный вопрос был стерт из базы данных. Получить данные о нем невозможно. Зачисляю 0 баллов."));
      }
      else {
         $r=sqlget($res);
         if ($bal>$r[balmax]) {
            $bal=$r[balmax];
            alert(_("Вы не можете начислить за этот вопрос более, чем")." $r[balmax] "._("баллов. Зачисляю баллов:")." $r[balmax]");
         }
         if ($bal<$r[balmin]) {
            $bal=$r[balmin];
            alert(_("Вы не можете начислить за этот вопрос менее, чем")." $r[balmin] "._("баллов. Зачисляю баллов:")." $r[balmin]");
         }
      }

      $rq="SELECT stid FROM seance WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
      $res=sql($rq,"err2");
      if (sqlrows($res)==0) exitmsg(_("Данный вопрос, нуждающийся в проверке, более не найден."),"$PHP_SELF?$sess");
      $r=sqlget($res);
      sqlfree($res);

      $rq="UPDATE seance SET bal=$bal
           WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
      sql($rq,"errTM268");
      sqlfree($res);
   }

   $s[test_modvar]=intval($test_modvar)%2;
   if ($s[test_modvar]) exit(refresh("$PHP_SELF?c=show_questions$sess"));
   exit(refresh("$PHP_SELF?c=find_first$sess"));


case "minimoder":

   $cid = (int) $_GET['cid'];
   $stid = (int) $_GET['stid'];
   //intvals("cid stid");
   if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: "._("Вы не можете обращаться к временным вопросам чужих курсов"));

   $CID=$cid;
   //, UNIX_TIMESTAMP(time) time
   $rq="SELECT stid, mid, kod, cid, UNIX_TIMESTAMP(time) as time, tid, text, attach, filename, bal, comments, review
        FROM seance
        WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
   $res=sql($rq,"err2");
   if (sqlrows($res)==0) exitmsg(_("Больше вопросов для проверки нет."),"$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM People WHERE mid=$r[mid]","err3");
   $rppl=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM list WHERE kod='".addslashes($r[kod])."'","err4");
   $rvop=sqlget($res);
   sqlfree($res);

   $res=sql("SELECT * FROM test WHERE tid=$r[tid]","err5");
   $rtest=sqlget($res);
   sqlfree($res);

   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];

   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setView('DocumentContent');
   $GLOBALS['controller']->setHeader(_("Проверка вопросов со свободным ответом"));
   $rgroup=MIDgroupArray($rppl['MID'],$cid);

   echo "
   <table width=100% class=main cellspacing=0>
      <tr>
         <td width=50%>"._("Курс").": ".cid2title($r[cid])."</td>
         <td>"._("Студент").": <a href=\"javascript:wopen('{$GLOBALS['sitepath']}userinfo.php?mid={$rppl['MID']}','userinfo',400,300);\">$rppl[FirstName] $rppl[LastName]</a></td>
      </tr>
      <tr>
         <td>"._("Задание").": $rtest[title]</td>
         <td>".((is_array($rgroup) && count($rgroup)) ? _("Группа").": ".implode(", ",$rgroup) : '')."</td>
      </tr>
      <tr>
         <td>"._("Дата ответа").": ".date("H:i d.m.Y",$r[time])."<br />".$rere."</td>
         <td></td>
      </tr>
   </table>";

   include_once("template_test/$rvop[qtype]-v.php");
   $func="v_vopros_$rvop[qtype]";
   $null=array();
   $temp_url = ($_SERVER['HTTP_X_REWRITE_URL'] == "") ? str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) :  str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['HTTP_X_REWRITE_URL']);
   echo "
   <form action='" . $temp_url . "' name='moder' method=\"POST\" enctype='multipart/form-data'>
   <table width=100% class=main cellspacing=0>
       <tr><th colspan=2>"._("Вопрос:")." ".qdata2text($rvop[qdata])."</th></tr>
       <tr>
           <td valign=top width=50%><b>"._("Полученный ответ:")."</b><br> ".nl2br(html(substr($r[text],0,65535)));
   $r['attach'] = trim($r['attach']);
   if (!empty($r['attach']) && strlen($r[attach])>2 && $r['attach']!='0x')
   echo "<br><a href=$PHP_SELF?c=download&what=3&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess>
         "._("открыть прикрепленный файл")." (".strlen($r[attach])." "._("байт").")</a>";

   echo "</td>
           <td valign=top>
               <b>"._("Комментарий:")."</b><br>
               <textarea style=\"width:100%\" name=\"comments\" rows=10 wrap=\"soft\">{$r[comments]}</textarea><br>
               <b>"._("Рецензия:")."</b><br>
               <input type='file' name='reviewFile' /><br>
               <a href=$PHP_SELF?c=download&what=5&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess>
                "._("открыть файл с рецензией")." (".strlen($r[review])." "._("байт").")</a>
           </td>
       </tr>
   </table>
   ";
   /*
   echo "<h3>Вопрос</h3>
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td><i>".qdata2text($rvop[qdata])."</i></td>
      </tr>
   </table><br>";
   echo "<h3>"._("Полученный ответ")."</h3>
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td><i>".html(substr($r[text],0,65535))."</i><br><br></td>
      </tr>
      <tr>
      <td align='right'>
   <!--a href=$PHP_SELF?c=download&what=2&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess target=_blank>"._("открыть ответ")."</a-->
   <br><br>";

   if (strlen($r[attach]) && !empty($r[attach]))
   echo "<a href=$PHP_SELF?c=download&what=3&stid=$r[stid]&cid=$r[cid]&kod=$kod$sess>
          "._("открыть прикрепленный файл")." (".strlen($r[attach])." байт)</a>";

   echo "</td>
      </tr>
   </table><hr/>";
   */
   echo "
   <input type=hidden name='c' value=\"minimoder_submit\">
   <input type=hidden name='stid' value=\"$stid\">
   <input type=hidden name='cid' value=\"$cid\">
   <input type=hidden name='kod' value=\"".html($kod)."\">
   <table width=100% class=main cellspacing=0>
      <tr>
         <td align='left'> ";

   echo " <b>"._("Балл")."</b> [ $rvop[balmin] .. $rvop[balmax] ]
   </td><td align='right'>";
;

   if ($rvop[balmax]-$rvop[balmin]<15) {
      for ($i=$rvop[balmin]; $i<=$rvop[balmax]; $i++) {
         echo "<a href='#' onclick='document.moder.moder2.value=$i;return false;'><b>$i</b></a> | ";
      }
   }

   echo "<input type=hidden name=issave value=1>
      - &gt; <label for=vmoder2><input type=hidden name=vmoder value=2>
   <input size=6 type=text name=moder2 value=\"$r[bal]\"></label>
            </td>
      </tr>
      </table>
      <br>";

/*   $lt=getNavnum($r[tid],$stid,1);
   if ($lt) $pstid=getLT($r[tid],$stid);
   $gt=getNavnum($r[tid],$stid);
   if ($gt) $nstid=getGT($r[tid],$stid);
   echo "<table width=100% class=main cellspacing=0>
      <tr>
      <td align='left' width='50%'>".(($lt) ? "<a href='$PHP_SELF?c=minimoder&stid=$pstid&cid=$cid&kod=$kod$sess'>&lt;&lt; Назад (".$lt.")</a>" : "")."</td>
      <td align='right' width='50%'>".(($gt) ? "<a href='$PHP_SELF?c=minimoder&stid=$nstid&cid=$cid&kod=$kod$sess'>Далее (".$gt.") &gt;&gt;</a>" : "")."
            </td>
      </tr>
   </table><br>";
*/
   echo okbutton();
   echo "</form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   echo $html[1];

   break;



case "minimoder_submit":

   intvals("cid stid");

   $comments = trim(strip_tags($comments));

   //if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: "._("Вы не можете обращаться к временным вопросам чужих курсов"));

   if ($vmoder==1) $bal=$moder1;
   else $bal=$moder2;
   $bal=doubleval($bal);

   if ($vmoder==0) $issave=1;

   if ($issave) {

      $res=sql("SELECT * FROM list WHERE kod='".addslashes($kod)."'","errTM275");
      if (!sqlrows($res)) {
         alert(_("Данный вопрос был стерт из базы данных. Получить данные о нем невозможно. Зачисляю 0 баллов."));
      }
      else {
         $r=sqlget($res);
         if ($bal>$r[balmax]) {
            $bal=$r[balmax];
            alert(_("Вы не можете начислить за этот вопрос более, чем")." $r[balmax] "._("баллов. Зачисляю баллов:")." $r[balmax]");
         }
         if ($bal<$r[balmin]) {
            $bal=$r[balmin];
            alert(_("Вы не можете начислить за этот вопрос менее, чем")." $r[balmin] "._("баллов. Зачисляю баллов:")." $r[balmin]");
         }
      }

      $rq="SELECT stid, bal, time FROM seance WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
      $res=sql($rq,"err2");
      if (sqlrows($res)==0) exitmsg(_("Данный вопрос, нуждающийся в проверке, более не найден."),"$PHP_SELF?$sess");
      $r=sqlget($res);
      sqlfree($res);

      
      if($_FILES['reviewFile']['name'] != ''){
          $rq="UPDATE seance SET 
                   bal={$bal}, 
                   time='{$r['time']}', 
                   comments=".$GLOBALS['adodb']->Quote($comments).",
                   review_filename = '".$_FILES['reviewFile']['name']."'
               WHERE stid=$stid AND cid=$cid AND kod=".$GLOBALS['adodb']->Quote($kod)."";
      }else{
          $rq="UPDATE seance SET 
                   bal={$bal}, 
                   time='{$r['time']}', 
                   comments=".$GLOBALS['adodb']->Quote($comments)."
               WHERE stid=$stid AND cid=$cid AND kod=".$GLOBALS['adodb']->Quote($kod)."";
      }
      
      
//      $rq="UPDATE seance SET bal=$bal, lastbal=".doubleval($r[bal])."
//           WHERE stid=$stid AND cid=$cid AND kod='".addslashes($kod)."'";
      sql($rq,"errTM268");
      //добавим рецензию препода
      move_uploaded_file($_FILES['reviewFile']['tmp_name'], $newFileName = 'temp/'.md5(time()));
      $GLOBALS['adodb']->updateBlobFile('seance', 'review', $newFileName, "stid=$stid AND cid=$cid AND kod=".$GLOBALS['adodb']->Quote($kod));
      sqlfree($res);
   }

//   $s[test_modvar]=intval($test_modvar)%2;
//   if ($s[test_modvar]) exit(refresh("$PHP_SELF?c=show_questions$sess"));
   $GLOBALS['controller']->setView('DocumentBlank');
   return 'update_ok';
   
   $GLOBALS['controller']->setMessage(_("Балл и комментарий успешно сохранены. Для перерасчета балла за задание выберите соответствующую опцию в списке \"Выполнить действие\" в нижней части страницы."),JS_CLOSE_SELF_REFRESH_OPENER);
   $GLOBALS['controller']->terminate();
   exit();

   exit(refresh("$PHP_SELF?c=minimoder&stid=$stid&cid=$cid&kod=$kod$sess"));


case "download":

   intvals("cid stid");
  // if (!isset($s[tkurs][$_GET['cid']]) && !$self_view) exitmsg("HackDetect: "._("Вы не можете обращаться к временным вопросам чужих курсов"));
   if($_GET['type'] == 'review'){
       echoAttachedFile( $_GET['cid'],  $_GET['stid'], 5, $_GET['kod']);
   }else{
       echoAttachedFile( $_GET['cid'],  $_GET['stid'], $what, $_GET['kod']);
   }
   exit;



case "complete":
   //$res=sql("LOCK TABLES loguser WRITE, seance WRITE, logseance WRITE");
   sqlfree($res);

//   for ($i=0; $i<100; $i++) echo "<!-- -->";
//   echo "<h3>Обновление базы данных</h3>";
//   flush();
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Обновление базы данных"));

   $res=sql("
      SELECT stop,stid,tid,mid,cid FROM loguser
      WHERE (needmoder=1 OR moder=1) AND status>0 AND tid = '{$_REQUEST['tid']}' ORDER BY stid","errTM315");

   $saved = array();

   while ($r=sqlget($res)) {

//      echo "<li><b>Сеанс N$r[stid]...</b> ";
      $res2=sql("SELECT COUNT(*) FROM seance WHERE stid=$r[stid] AND bal IS NULL","errTM325");
      $cnt=sqlres($res2,0,0);
      sqlfree($res2);

      if ($cnt) {
      		$skipped[] = $r['stid'];
//         echo "не на все вопросы преподаватель поставил оценку (осталось $cnt шт) - ПРОПУСК";
//         flush();
         continue;
      }

      $res4=sql("SELECT * FROM loguser WHERE stid=$r[stid]","errTM342");
      $loguser=sqlget($res4);
      sqlfree($res4);
      $log=unserialize($loguser[log]);

      /*
      echo "<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>";
      unset($loguser[log]);
      pr($loguser);
      pr($log);
      */

      $log_sql = "SELECT bal,kod FROM logseance WHERE stid='".(int) $r['stid']."'";
      $log_res = sql($log_sql);
      while($log_row=sqlget($log_res)) {
          $logseance[$log_row['kod']] = $log_row;
      }

      $res3=sql("SELECT * FROM seance WHERE stid=$r[stid]","errTM345");
      $mkod=array();
      while ($rr=sqlget($res3)) {
         if (($rr['bal']!==NULL)) {
            if (($rr['bal']!=$logseance[$rr['kod']]['bal'])) {
                $updated[$rr['mid']][$rr['tid']]['stid'] = $rr['stid'];
                if (!empty($rr['comments'])) {
                    $updated[$rr['mid']][$rr['tid']]['comments'][$rr['kod']] = $rr['comments'];
                }
            }
         } else {
             unset($updated[$rr['mid']][$rr['tid']]);
         }
         //echo "$rr[stid] | $rr[kod] | $rr[bal]<br>";
         $mkod[$rr[kod]]=$rr[bal];
         $mattach[$rr[kod]]=$rr[attach];
         $mtext[$rr[kod]]=$rr[text];
         $mfilename[$rr[kod]]=$rr[filename];
         $mlastbal[$rr[kod]] = $rr[lastbal];
         $mcomments[$rr[kod]] = $rr[comments];
         
         $mreview[$rr[kod]] = $rr[review];
         $mreview_filename[$rr[kod]] = $rr[review_filename];
      }
      sqlfree($res3);

      //pr($mattach);
      //pr($mkod);
      //pr($
      if(is_array($log[akod])){
      foreach ($log[akod] as $k=>$v) {
         if (!isset($mkod[$v])) continue;
         $log[abal][$k]=doubleval($mkod[$v]);

         $loguser[bal] = $loguser[bal] - doubleval($mlastbal[$v]) + doubleval($mkod[$v]);
         //         $loguser[bal]+=doubleval($mkod[$v]);
         //echo "----- {$log[abalmin2][$k]} {$log[abalmax2][$k]} ---------";
         if (doubleval($log[abalmax2][$k])==0)
            $good=0;
         else
            $good=doubleval($log[abal][$k]*100)/doubleval($log[abalmax2][$k]);
         if ($good>100) $good=100;
         if ($good<0) $good=0;
         $log[agood][$k]=$good;
         $otvet=array();
         $tmp=array();
         if (strlen($mattach[$v])) $tmp[]=_("приложен")." {$mfilename[$v]} "._("длиной")." ".strlen($mattach[$v])." "._("байт");
         if (strlen($mtext[$v])) $tmp[]=_("приложен текст")." ".strlen($mtext[$v])." "._("байт");
         if (count($tmp)) {
            $otvet[main][]=implode("; ",$tmp);
            $otvet[good][]=($good==100?1:0);
            $otvet[info][]=_("См. общую оценку");
         }

         $sql = "UPDATE seance SET lastbal='{$mkod[$v]}' WHERE stid='{$lastuser['stid']}' AND cid='{$lastuser['cid']}' AND kod='{$v}'";
         sql($sql);

         $rq="
            UPDATE logseance SET
               bal={$log[abal][$k]},
               good='{$good}',
               otvet='".serialize($otvet)."',
               comments=".$GLOBALS['adodb']->Quote($mcomments[$v]).",
               review_filename = " .$GLOBALS['adodb']->Quote($mreview_filename[$v]). "
            WHERE stid=$r[stid] AND kod='$v'";
//         exit(pr($rq));
         $res10=sql($rq,"errTM405");
         sqlfree($res10);

        $GLOBALS['adodb']->UpdateBlob('logseance', 'review',$mreview[$v],"stid=$r[stid] AND kod='$v'");
        $tmpl = "UPDATE seance SET lastbal={$log[abal][$k]} WHERE stid=$r[stid] AND kod='$v'";
        $res8 = sql($tmpl);
        sqlfree($res8);

      }
      }

      if (count($mkod)>0) {

         if (!in_array($r['stid'], $saved)) $saved[] = $r['stid'];

          $rq="
          UPDATE loguser SET
          bal='{$loguser[bal]}',
          moderby='{$s[mid]}',
          modertime=".time().",
          needmoder='0',
          log=".$GLOBALS['adodb']->Quote(serialize($log))."
          WHERE stid=$r[stid]";
          //echo "<pre>$rq</pre>";
          $res7=sql($rq,"errTM390");
          sqlfree($res7);


      //}

      /**
      * Апдейт оценки по формуле для занятия
      */
      $sql = "SELECT schedule.end, schedule.SHEID, schedule.CID, scheduleID.toolParams, vedomost
            FROM schedule INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
            WHERE scheduleID.mid='".(int)$loguser[mid]."'
            AND scheduleID.toolParams LIKE '%tests_testID=".(int) $loguser['tid'].";%'
            OR scheduleID.toolParams LIKE '%tests_testID = ".(int) $loguser['tid'].";%'";
      $result = sql($sql);
      while ($rrow = sqlget($result)) {

          $par = $rrow['toolParams'];
          $test_id=getIntVal($par,"tests_testID=");
          $formula_id=getIntVal($par,"formula_id=");
          $penaltyFormula_id = getIntVal($par,'penaltyFormula_id=');

          if ($formula_id > 0 && ($test_id==$loguser['tid'])) {
            $result2=sql("SELECT * FROM formula WHERE (CID='".(int) $rrow['CID']."' OR CID='0') AND type=1 AND id=$formula_id","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
            while ($rrow2 = sqlget($result2)){
                $formula=$rrow2[formula];
            }
//            sqlfree($res);
            // ставит оценку за последние результаты теста для студента MID и тестк TID по заданной формуле

            $mark=viewFormula( $formula,$text,$loguser['balmin'],$loguser['balmax2'],$loguser['bal'] );

            // Применение штрафной формулы!!!

            if ($penaltyFormula_id) {
//                $days = (int) ((strtotime($rrow['end'])-$r['stop'])/60/60/24);
				$days = getPenaltyDays($r['stop'], strtotime($rrow['end']));
                $penaltyFormula = getPenaltyFormula($penaltyFormula_id);
                $penalty = viewPenaltyFormula($penaltyFormula,$days);
                if ($penalty) $mark = round($mark*$penalty,2);
            }

            $sql="UPDATE `scheduleID` SET `V_STATUS`='".$mark."'
                  WHERE MID='".$loguser[mid]."' AND SHEID='".(int) $rrow['SHEID']."'";
            $result3=sql($sql,"errSAVE_MARK");
          }

      }

      /////////////////////
      }


      // Удаление модерируемых вопросов
//      $res8=sql("DELETE FROM seance WHERE stid=$r[stid]","errTM395");
//      sqlfree($res8);


//      echo " готово"; flush();

      /*
      echo "</td><td>";
      pr($loguser);
      pr($log);
      echo "</td></tr></table>";
      */

   }
   if (is_array($updated) && count($updated)) {
       foreach ($updated as $mid=>$stids) {
           $stids = array_unique($stids);
           foreach ($stids as $tid=>$v) {
                $sql = "SELECT * FROM loguser WHERE mid='".(int) $mid."' AND stid='".(int) $v['stid']."'";
                $res = sql($sql);
                if (sqlrows($res) && ($row = sqlget($res))) {
                    /**
                    * Посылаем сообщение что чувак твоё задание проверено и тебе пипец
                    */
                    $comments = '';
                    if (is_array($v['comments']) && count($v['comments'])) {
                        $sql = "SELECT kod,qdata FROM list WHERE kod IN ('".join("','",array_keys($v['comments']))."')";
                        $res2 = sql($sql);
                        while($row2 = sqlget($res2)) {
                            $qdata = explode($GLOBALS['brtag'],$row2['qdata']);
                            $updated_kods[$row2['kod']] = $qdata[0];
                        }
                        foreach ($v['comments'] as $kod => $comment) {
                            $comments .= _("Вопрос:")." {$updated_kods[$kod]}<br>"._("Комментарий:")." {$comment}<br><br>";
                        }
                    }
                    mailTostud('free_questions_checked',$mid,$row['cid'],array('tid'=>$tid,'teacher'=>$s['mid'],'mark'=>$row[bal],'comments'=>$comments));
                }
           }
       }
   }
//echo"5";
   //$GLOBALS['controller']->captureStop(CONTENT);
   
   //$GLOBALS['controller']->terminate();
   //$str_saved = (!empty($saved)) ? _("Сохранены результаты по следующим сеансам выполнения задания:") . "<li>" . implode('<li>', $saved) : _('Результаты по всем сеансам были расчитаны ранее. Нет новых результатов.');
   //exitmsg($str_saved,"$PHP_SELF?c=showtask&tid={$tid}&cid={$cid}");

   break;

case 'clearsence':

   $res=sql("
      SELECT * FROM loguser
      INNER JOIN seance ON (loguser.stid = seance.stid)
      WHERE loguser.moder=1 AND loguser.needmoder=0 AND loguser.status>0 AND loguser.tid='{$tid}'");
    

   while ($r=sqlget($res)) {

        $res0=sql("SELECT * FROM seance WHERE stid='$r[stid]' AND bal IS NULL","errTM395");
        if (!sqlrows($res0)){
        	if (!in_array($r[stid], $deleted)) $deleted[] = $r[stid];
	        $res1=sql("DELETE FROM seance WHERE stid='$r[stid]'","errTM395");
    	    sqlfree($res1);
        }

   }
   $str_saved = (!empty($deleted)) ? _("Удалены вопросы по следующим сеансам:") . "<li>" . implode('<li>', $deleted) : _('Нет целиком обработанных сеансов.');

   //exitmsg($str_saved,"$PHP_SELF?c=showtask&tid={$tid}&cid={$cid}");

   break;

}


?>