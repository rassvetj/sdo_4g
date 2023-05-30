<?

//   include("1.php");
   include_once("test.inc.php");

   $mid = (isset($_GET['mid']))? $_GET['mid']:"-1";

   $ss="test_e2";

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

   $c=(isset($_GET['c'])) ? $_GET['c'] : "alltest";
   if (!$s[usermode]) {
      $cur_cid=(isset($s[$ss][cid])) ? $s[$ss][cid] : reset($s['tkurs']);
      $cid=(isset($_GET['cid'])) ? $_GET['cid'] : $cur_cid;
   }

//   if ($s[perm]<2) exitmsg(" К этой странице могут обратится только:
//      преподаватель,  представитель учебной администрации, администратор","/?$sess");
//   if (count($s[tkurs])==0) exitmsg("Вы зарегистрированы в статусе преподавателя, но на данный момент вы не преподаете ни на одном из курсов.","/?$sess");

switch ($c) {

case "":

   echo show_tb();

   if (!isset($s[$ss][login])) $s[$ss][login]=$s[login];
   echo "<h3>"._("Статистика и отчеты")."</h3>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->captureStop(_("Статистика и отчеты"));
   echo "<ol>";
   if ($s[usermode]) {
   echo "
   <li>"._("Информация по логину учащегося:")."<br>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <form action=$GLOBALS[PHP_SELF] method=get>$sessf
   <input type=hidden name=c value='login'>
   <tr><td>
   "._("Логин:")." <input type=text name=login value=\"".html($s[$ss][login])."\" size=10>
   <input type=submit value='Ok &gt;'>
   </td></tr>
   </form>
   </table><P>";
   }

   echo "
   <li>"._("Результаты всех тестов")."
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <form action=$PHP_SELF method=get>$sessf
   <input type=hidden name=c value=\"test\">
   <tr><td>
   "._("Курс:")." <select name=cid size=1>";
   $res=sql("SELECT * FROM Courses WHERE CID IN (".implode(",",$s['tkurs']).") ORDER BY Title","errTL135");
   while ($r=sqlget($res)) {
      echo "<option value=$r[CID]".($s[$ss][cid]==$r[CID]?" selected":"").
      ">".substr($r[Title],0,60)."</option>";
   }
   echo "</select> <input type=submit value='Ok &gt;'>
   </td></tr>
   </form>
   </table>";

   if ($s[usermode]) {
      echo "<br>
      <li>"._("Детальный отчет о сеансах тестирования:")."
      <table width=100% border=0 cellspacing=0 cellpadding=0>
      <form action=$PHP_SELF method=get>$sessf
      <input type=hidden name=c value=\"seance\">
      <tr><td>ID "._("сеанса")." <input type=text name=stid value=\"{$s[$ss][stid]}\" size=10> <input type=submit value='Ok &gt;'><br>
      "._("Отчет доступен только преподавателю.")."<br>
      "._("Найти номер сеанса проще в первых 2-х отчетах выше.")."
      </td></tr>
      </form>
      </table>";
   }

   echo "</ol>";
   $GLOBALS['controller']->captureStop(CONTENT);

   echo show_tb();

   exit;

case "clear_study_logs":
    if ($s['perm']>1) {
        $where = array();
        if ($_GET['cid']>0) {
            $where[] = "cid = '".(int) $_GET['cid']."'";
        }
        if ($_GET['tid']>0) {
            $where[] = "tid = '".(int) $_GET['tid']."'";
        }
        if ($_GET['mid']>0) {
            $where[] = "mid = '".(int) $_GET['mid']."'";
        }
        if ($_GET['from']!="") {
            $tmp = explode(".", $_GET['from']);
            $time[0] = @mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
        }
        else {
            $time[0] = 0;
        }
        if ($_GET['to']!="") {
            $tmp = explode(".", $_GET['to']);
            $time[1] = @mktime(23, 59, 59, $tmp[1], $tmp[0], $tmp[2]);
        }
        else {
            $time[1] = time();
        }
        if (count($where)) {
            $where = join(" AND ",$where);
        } else {
            $where = '1=1';
        }
        $sql = "DELETE FROM logseance WHERE time >= {$time[0]} AND time <= {$time[1]} AND $where";
        //pr($sql);
        sql($sql);
        $sql = "DELETE FROM loguser WHERE start >= {$time[0]} AND stop <= {$time[1]} AND $where";
        //pr($sql);
        sql($sql);
        $sql = "DELETE FROM seance WHERE time >= " . $adodb->DBTimeStamp($time[0]) . " AND time <= " . $adodb->DBTimeStamp($time[1]) . " AND $where";
        //pr($sql);
        sql($sql);
        $sql = "DELETE FROM testcount WHERE last >= {$time[0]} AND last <= {$time[1]} AND $where";
		//pr($sql);
        sql($sql);
    }
    $GLOBALS['controller']->setMessage(_("Статистика тестирования и ответов на вопросы очищена"),JS_GO_URL,"{$PHP_SELF}?$sess");
    $GLOBALS['controller']->terminate();
    exit();
break;

case "login":

   $mid=login2mid($login);
   $s[$ss][login]=$login;
   if (!$mid) exitmsg(_("Извините, такого пользователя не зарегистрировано. Введите логин существующего пользователя."),"$PHP_SELF?$sess");

   echo show_tb();
   echo "<h3>"._("Информация о пользователе:")." <u>".html($login)."</u></h3>
   &lt;&lt; <a href=$PHP_SELF?$sess>"._("к началу статистики")."</a><P>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Информация о пользователе:")." ".$login);

   //Информация: $r[Information]<br>

   $r=sqlval("SELECT * FROM People WHERE mid=$mid","errTL271");
   echo "ФИО: $r[LastName] $r[FirstName] $r[Patronymic]<br>
   Email: $r[EMail]<br>
   Адрес: $r[Address]<P>";

   $res=sql("SELECT loguser.stid, loguser.cid, loguser.tid, loguser.balmax, loguser.balmin,
                    loguser.balmax2, loguser.balmin2, loguser.bal, loguser.start,
                    loguser.fulltime, loguser.moder, loguser.needmoder,
                    loguser.moderby, loguser.modertime, loguser.status,
                    loguser.questall, loguser.questdone,
                    subjects.name ctitle, test.title ttitle
             FROM loguser
             LEFT JOIN subjects ON subjects.subid='loguser.cid'
             LEFT JOIN test ON test.tid=loguser.tid
             WHERE mid=$mid
             ORDER BY stid DESC","ettTL72");
   if (sqlrows($res)) {

      echo "
      <table width=100% border=1 cellspacing=0 cellpadding=2 style='text-align:center'>
      <tr>
      <td>"._("Номер сеанса")."</td>
      <td>"._("Курс")."</td>
      <td>"._("Тест")."</td>
      <td>"._("Баллов")."</td>
      <td>"._("Мин/Макс балл")."</td>
      <td>"._("Мин/Макс балл с учетом проверяемых вопросов")."</td>
      <td>"._("Вопросов Ответил/Всего")."</td>
      <td>"._("Начало")."</td>
      <td>"._("Продолж.")."</td>
      <td>"._("Проверка")."</td>
      <td>"._("Статус")."</td>
      </tr>";

      while ($r=sqlget($res)) {
         echo "
         <tr>
         <td>";
         if (isset($s[tkurs][$r[cid]])) echo "<a href=$PHP_SELF?c=seance&stid=$r[stid]$sess>"._("Смотреть отчет:")."$r[stid]</a>";
         else echo $r[stid];
         echo "</td>
         <td>$r[ctitle]</td>
         <td>$r[ttitle]</td>
         <td>$r[bal]</td>
         <td>$r[balmin]/$r[balmax]</td>
         <td>$r[balmin2]/$r[balmax2]</td>
         <td>".($r[questdone]==$r[questall]?
                   "$r[questdone]/$r[questall]":
                   "<font color=blue>$r[questdone]/$r[questall]</font>")."</td>
         <td>".date("d/m/Y H:i",$r[start])."</td>
         <td>".($r[fulltime]?duration($r[fulltime]):"?")."</td>";
         if ($r[needmoder]==0 && $r[moder]==0) echo "<td>&nbsp;</td>";
         else {
            if ($r[needmoder]==1) echo "<td colspan=2><font color=blue>"._("Сеанс еще не проверен преподавателем")."</font></td>";
            else echo "<td>"._("Проверен:")." ".mid2login($r[moderby])." ".
               date("d/m/Y H:i",$r[modertime])."</td>";
         }
         echo "</td>";

         if ($r[needmoder]==0 || $r[moder]==0) {
            echo "<td>";
            switch ($r[status]) {
               case 0: echo "<font color=green>"._("идет тестирование")."</font>"; break;
               case 1: echo _("закончен"); break;
               case 2: echo "<font color=red>"._("брошен")." (timeout)</font>"; break;
               case 3: echo "<font color=blue>"._("прерван по команде")."</font>"; break;
               case 4: echo _("досрочно завершен"); break;
               case 5: echo "<font color=blue>"._("прерван лимитом времени")."</font>"; break;
            }
            echo "</td>";
         }
         echo "</tr>";

      }

      echo "</table>";


   }

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;



case "test":

   intvals("cid");

   $s[$ss][cid]=$cid;
   $title=cid2title($cid);
   if ($title=="") exitmsg(_("Ошибочный номер курса"),"$PHP_SELF?$sess");

   if (isset($s[tkurs][$cid])) $logs=1; else $logs=0;

   echo show_tb();

//   echo "<h3>Отчет о всех тестированиях на курсе: <u>$title</u></h3>
//   <!-- &lt;&lt; <a href=$PHP_SELF?$sess>к началу статистики</a><P-->";
   echo ph(_("Выставление оценок за задания"),"");
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Выставление оценок за задание"));

   if ($s[usermode]) echo "<a href=$PHP_SELF?$sess>&lt;&lt; "._("К началу статистики и отчетов")."</a><P>";

   echo "
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <form action=$PHP_SELF method=get name=\"change\">$sessf
   <input type=hidden name=c value=\"test\">
   <tr><td>
   <span class=shedtitle>"._("Курс:")."</span>&nbsp; &nbsp;<select name=cid size=1 onchange=\"form.submit();\">";
   $res=sql("SELECT * FROM Courses WHERE CID IN (".implode(",",$s['tkurs']).") ORDER BY Title","errTL135");
   while ($r=sqlget($res)) {
      echo "<option value=$r[CID]".($s[$ss][cid]==$r[CID]?" selected":"").
      ">".substr($r[Title],0,60)."</option>";
   }
   echo "</select><!--input type=submit value='Ok &gt;'-->
   </td></tr>
   </form>
   </table> <br>";


   $res=sql("SELECT loguser.stid, loguser.tid, loguser.balmax, loguser.balmin,
                    loguser.balmax2, loguser.balmin2, loguser.bal, loguser.start,
                    loguser.fulltime, loguser.moder, loguser.needmoder,
                    loguser.moderby, loguser.modertime, loguser.status,
                    loguser.questall, loguser.questdone,
                    People.login, People.LastName, People.FirstName, People.Login,
                    test.title ttitle
             FROM loguser
             LEFT JOIN People ON People.mid=loguser.mid
             LEFT JOIN test ON test.tid=loguser.tid
             WHERE loguser.cid='$cid'
             ORDER BY stid DESC","ettTL72");
   $toolTip = new ToolTip();
   if (sqlrows($res)) {

      echo "
      <table width=100% border=0 cellspacing=1 cellpadding=1 style='text-align:center' bgcolor=#D1CBBD>
      <tr class=\"questt\">
      <th class=thsmall>Login, "._("ФИО, отчет")."</th>
      <th class=thsmall>".TEST_TYPE."</th>
      <th class=thsmall>"._("Балл")."</th>
      <th class=thsmall>min/max<br>".$toolTip->display('test_min_max')."</th>
      <th class=thsmall>"._("Вопросов Ответил")."/<br>"._("Всего")."</th>
      <th class=thsmall>"._("Начало").",<br>"._("затрачено")."</th>
      <th class=thsmall>"._("Проверка препода")."-<br>"._("вателем")."</th>
      <th class=thsmall>"._("Статус")."</th>
      </tr>";

      while ($r=sqlget($res)) {
         echo "
         <tr class=\"questt\">
         ".($logs?"<td class=testsmall><a href=$PHP_SELF?c=seance&stid=$r[stid]>$r[Login], $r[FirstName] $r[LastName]</a></td>":"<td class=testsmall>$r[FirstName] $r[LastName]</td>")."
         <td class=testsmall>$r[ttitle]</td>
         <td class=tests>$r[bal]</td>
         <td class=tests>$r[balmin]/$r[balmax]<br>$r[balmin2]/$r[balmax2]</td>
         <td>".($r[questdone]==$r[questall]?
                   "$r[questdone]/$r[questall]":
                   "<font color=blue>$r[questdone]/$r[questall]</font>")."</td>
         <td class=testsmall>".date("d.m.Y H:i",$r[start])."<br>".
         ($r[fulltime]?duration($r[fulltime]):"?")."</td>";
         if ($r[needmoder]==0 && $r[moder]==0) echo "<td class=tests>&nbsp;</td>";
         else {
            if ($r[needmoder]==1) echo "<td colspan=2 class=tests><font color=blue>"._("Сеанс еще не проверен преподавателем")."</font></td>";
            else echo "<td class=tests>".mid2name($r[moderby])." ".
               date("d.m.Y H:i",$r[modertime])."</td>";
         }
         echo "</td>";

         if ($r[needmoder]==0 || $r[moder]==0) {
            echo "<td class=tests>";
            echo "<font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][0]}</font>";
            echo "</td>";
         }
         echo "</tr>";

      }

      echo "</table>";


   }

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;



case "alltest":
   intvals("cid");
   if (!isset($s[$ss][cid])) $s[$ss][cid]=-1;
   if (!isset($s[$ss][tid])) $s[$ss][tid]=-1;
   if (!isset($s[$ss][mid])) $s[$ss][mid]=-1;
   if (!empty($cid))         $s[$ss][cid]=intval($cid);
   if (!empty($tid))         $s[$ss][tid]=intval($tid);
   if (!empty($mid)) {
        $s[$ss][mid]=intval($mid);
   }
   $s[$ss]['from'] = (isset($_GET['from']) ? $_GET['from'] : "");//date("d.m.Y", time());
   $s[$ss]['to'] = (isset($_GET['to']) ? $_GET['to'] : "");//date("d.m.Y", time());
   $people_count = get_people_count();
   $people_count = 100;
   if ($people_count>=ITEMS_TO_ALTERNATE_SELECT) {
        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
        $js =
            "
            function show_user_select(html) {
                var elm = document.getElementById('users');
                if (elm) elm.innerHTML = '<select name=mid id=mid>'+html+'</select>';
            }

            function get_user_select(str) {
                var current = 0;

                var select = document.getElementById('mid');
                if (select) current = select.value;

                cid = -1;
                var select = document.getElementById('cid');
                if (select) cid = select.value;

                tid = -1;
                var select = document.getElementById('tid');
                if (select) tid = select.value;

                var elm = document.getElementById('users');
                if (elm) elm.innerHTML = '<select><option>"."Загружаю данные..."."</option></select>';

                x_search_user_options_sql(str, cid, tid, current, show_user_select);
            }

            function show_test_select(html) {
                var elm = document.getElementById('test');
                if (elm) elm.innerHTML = html;
            }

            function get_test_select(cid) {
                var elm = document.getElementById('test');
                if (elm) elm.innerHTML = '<select><option>"._("Загружаю данные...")."</option></select>';

                x_get_test_select(cid, show_test_select);
            }

            ";

        $sajax_javascript = CSajaxWrapper::init(array('search_user_options_sql', 'get_test_select')).$js;
   }



   //линк отображается у всех кроме ~student без вызова setLink()
   $confirm = sprintf(  _("Очистить статистику тестирования %s %s %s %s?"),
                        ($s[$ss][cid] != -1) ? _("по курсу ") . "'" . get_course_title($s[$ss][cid]) . "'" : _("по всем курсам"),
                        ($s[$ss][tid] != -1) ? _("по заданию ") . "'" . get_test_title($s[$ss][tid]) . "'" : _("по всем заданиям"),
                        ($s[$ss][mid] != -1) ? _("по пользователю ") . "'" . get_people_title($s[$ss][mid]) . "'" : _("по всем пользователям"),
                        ($s[$ss]['from'] != "" || $s[$ss]['to'] != "") ?
                        _("по дате прохождения заданий в период") . (($s[$ss]['from'] != "") ? _(" с ") . $s[$ss]['from'] : "") . (($s[$ss]['to'] != "") ? _(" по ") . $s[$ss]['to'] : "") : _("по всем датам прохождения заданий"));

   $GLOBALS['controller']->setLink('m150201',array((int) $s[$ss][cid],(int) $s[$ss][tid],(int) $_GET['mid'], $s[$ss]['from'], $s[$ss]['to']), str_replace("'", "\'", $confirm));

   echo show_tb();
   echo ph(_("Статистика о пройденных заданиях"),"");
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Статистика тестирований"));
   $GLOBALS['controller']->captureFromOb(TRASH);
   echo "
   <table width=100% border=0 cellspacing=0 cellpadding=0 nowrap>
   <form action=$PHP_SELF method=get name=main1>$sessf
   <input type=hidden name=c value=\"alltest\">
   <tr>
   <td><span class=shedtitle>"._("Курс:")."</span></td>
   <td><select name=cid size=1 onchange='document.main1.submit();' class=s9>
   <option value=-1>----------- "._("Любой курс")." -----------------</option>";
   $kurses = $s['tkurs'];
   //if (is_array($s['tkurs']) && count($s['tkurs'])) $kurses = $s['tkurs'];
   //elseif (is_array($s['skurs']) && count($s['skurs'])) $kurses = $s['skurs'];
   $res=sql("SELECT cid,title FROM Courses WHERE cid IN (".implode(",",$kurses).") ORDER BY title","errTL135");
   while ($r=sqlget($res)) {
      echo "<option value=$r[cid]".($s[$ss][cid]==$r[cid]?" selected":"").
      ">".substr($r[title],0,90)."</option>";
      $filter_kurses[$r[cid]]=$r[title];
   }
   echo "</select>
   </td></tr>";
   $GLOBALS['controller']->addFilter(_("Курс"), 'cid',$filter_kurses,$s[$ss][cid],false,'-1', true, "onChange=\"get_test_select(this.value); get_user_select(jQuery('#search').get(0).value);\" id=\"cid\"");
   /*if ($s[$ss][cid]!=-1) {
      echo "
      <tr><td><span class=shedtitle>"._("Задание:")."</span></td>
      <td><select name=tid size=1 onchange='document.main1.submit();' class=s9>
      <option value=-1>----------- "._("Любое задание")." -----------</option>";
      $sql = "SELECT DISTINCT vol1 FROM organizations WHERE vol1 > 0 AND cid = '".(int) $s[$ss][cid]."'";
      $res = sql($sql);
      $tids = array();
      while($row = sqlget($res)) {
          $tids[$row['vol1']] = $row['vol1'];
      }

      $where = '';
      if (count($tids)) {
          $tids = array_chunk($tids, 50);
          $where .= ' OR (';
          for($i=0; $i<count($tids); $i++) {
              if ($i>0) $where .= ' OR ';
              $where .= "tid IN ('".join("','",$tids[$i])."')";
          }
          $where .= ')';
      }

      $sql = "SELECT tid,title FROM test WHERE cid='{$s[$ss][cid]}' $where ORDER BY title";
      $res=sql($sql,"errTL700");
      while ($r=sqlget($res)) {
         echo "<option value=$r[tid]".($s[$ss][tid]==$r[tid]?" selected":"").
         ">".substr($r[title],0,90)."</option>";
         $filter_tests[$r[tid]] = $r[title];
      }
      echo "</select>
      </td></tr>";
      //$GLOBALS['controller']->addFilter(_("Задание"), 'tid',$filter_tests,$s[$ss][tid],false,'-1');
   }
   else
      $s[$ss][tid]=-1;
   */
   $GLOBALS['controller']->addFilter(TEST_TYPE, 'test', 'div', get_test_select($s[$ss][cid], $s[$ss][tid]));

   echo "
   <tr><td><span class=shedtitle>"._("Обучаемый:")."</span></td>
   <td><select name=mid size=1 onchange='document.main1.submit();' class=s9>
   <option value=-1>----------- Любой человек -----------</option>";

   $res=sql("SELECT People.mid, People.firstname, People.lastname, People.login
             FROM People
             INNER JOIN Students ON (Students.MID = People.mid)
             WHERE People.last>".(time()-60*60*24*365*2)." AND Students.CID IN (".implode(",",$kurses).")
             ORDER BY People.lastname, People.firstname","errTL717");
   while ($r=sqlget($res)) {
      if((isset($_GET['mid']))&&($_GET['mid'] == $r['mid'])) {
          $selected = "selected";
      }
      else {
          $selected = "";
      }
      echo "<option value=$r[mid] $selected>".substr("$r[lastname] $r[firstname] ($r[login])",0,90)."</option>";
      $filter_people[$r[mid]] = substr("$r[lastname] $r[firstname] ($r[login])",0,90);
   }
   echo "</select>
   </td></tr>";
   //$GLOBALS['controller']->addFilter(_("Пользователь"), 'mid',$filter_people,$_GET['mid'],false,'-1');


   $GLOBALS['controller']->addFilter(_('Фильтр пользователей'),'search',false,$search,false,0,true,"onKeyUp=\"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);\"","<input type=\"button\" value=\""._("Все")."\" onClick=\"if (elm = document.getElementById('search')) elm.value='*'; get_user_select('*');\"> ");
   $GLOBALS['controller']->addFilter(_('Пользователь'), 'users', 'div', '<select name=mid id=mid>'.search_user_options_sql(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$search),$s[$ss][cid], $s[$ss][tid], $_GET['mid']).'</select>', true);


   $js_input =  <<<EOD
<script type="text/javascript" src="{$sitepath}js/datepicker.js"></script>
<script type="text/javascript">
<!--
    $.datePicker.setDateFormat('dmy','.');
    $.datePicker.setLanguageStrings(
        ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
        ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        {p:'Пред', n:'След', c:'X', b:'Выбрать дату'}
    );
//-->
</script>
<style type="text/css">
    @import url("{$sitepath}js/datepicker.css");
</style>
EOD;

   $from_input = $js_input . <<<EOD
<input type="text" id="from_" name="from" value="{$s[$ss]['from']}">
<script type="text/javascript">
<!--
    $('#from_').datePicker({startDate:'01/01/2000', firstDayOfWeek:1});
//-->
</script>
EOD;

    $to_input = <<<EOD
<input type="text" id="to_" name="to" value="{$s[$ss]['to']}">
<script type="text/javascript">
<!--
    $('#to_').datePicker({startDate:'01/01/2000', firstDayOfWeek:1});
//-->
</script>
EOD;
   $GLOBALS['controller']->addFilter(_("Дата прохождения с"), 'from', 'div', $from_input, true);
   $GLOBALS['controller']->addFilter(_("Дата прохождения по"), 'to', 'div', $to_input, true);
   $GLOBALS['controller']->addFilterJavaScript($sajax_javascript);

   echo "
   </form>
   </table><br>";
   $GLOBALS['controller']->captureStop(TRASH);
   $sql1="
      loguser.stid as stid, loguser.tid, loguser.cid, loguser.balmax, loguser.balmin,
      loguser.balmax2, loguser.balmin2, loguser.bal, loguser.start,
      loguser.fulltime, loguser.moder, loguser.needmoder,
      loguser.moderby, loguser.modertime, loguser.status,
      loguser.questall, loguser.questdone";
   $sql2="loguser";
   $sql3=array();
   if ($s[$ss][cid]==-1) {
      $sql1.=", subjects.name as ctitle";
      $sql2.=" LEFT JOIN subjects ON subject.subid=Courses.CID ";
      $sql3[] = "Courses.is_poll = 0";
       if (count($kurses)) {
           $kurses = implode(",", $kurses);
           $sql3[] = "Courses.CID IN ({$kurses})";
       } else {
           $sql3[] = "0";
       }
   }
   else {
      $sql3[]=" loguser.cid='{$s[$ss][cid]}' ";
   }
   if ($s[$ss][tid]==-1) {
      $sql1.=", test.title as ttitle";
      $sql2.=" LEFT JOIN test ON loguser.tid=test.tid ";
   }
   else {
      $sql3[]=" loguser.tid={$s[$ss][tid]} ";
   }
   if ($s[$ss][mid]==-1) {
      $sql1.=", People.login, People.LastName, People.FirstName, People.Patronymic";
      $sql2.=" INNER JOIN People ON loguser.mid=People.mid ";
   }
   else {
      $sql3[]=" loguser.mid={$s[$ss][mid]} ";
   }

   if($s[$ss]['from']!="") {
       $tmp = explode(".", $s[$ss]['from']);
       $time = @mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
       $sql3[]=" loguser.start >= {$time} ";
   }

   if($s[$ss]['to']!="") {
       $tmp = explode(".", $s[$ss]['to']);
       $time = @mktime(23, 59, 59, $tmp[1], $tmp[0], $tmp[2]);
       $sql3[]=" loguser.stop <= {$time} ";
   }

   if (count($sql3)) $sql3=" WHERE ".implode(" AND ",$sql3);
   else $sql3="";

   $toolTip = new ToolTip();

   $rq="SELECT $sql1
        FROM $sql2
        $sql3
        ORDER BY stid DESC";

   $res=sql($rq,"errTL745");
   if (sqlrows($res)) {
       echo "
      <table width=100% class=main cellspacing=0>
      <tr class=trlog>";
      if ($s[$ss][cid]==-1) {
             echo "<th class=thsmall style='text-align:center'>"._("Курс")."</th>";
      }
      if ($s[$ss][tid]==-1) {
             echo "<th class=thsmall style='text-align:center'>".TEST_TYPE."</th>";
      }
      if ($s[$ss][mid]==-1) {
             echo "<th class=thsmall style='text-align:center'>"._("ФИО").", login</th>";
      }
      echo "
      <th class=thsmall style='text-align:center'>"._("Балл")."</th>
      <th class=thsmall style='text-align:center'>min/max<br>".$toolTip->display('test_min_max')."</th>
      <th class=thsmall style='text-align:center'>"._("Вопросов Ответил")."/<br>"._("Всего")."</th>
      <th class=thsmall style='text-align:center'>"._("Начало").",<br>"._("затрачено")."</th>
      <th class=thsmall style='text-align:center'>"._("Проверка препода-<br>вателем")."</th>
      <th class=thsmall style='text-align:center'>"._("Статус")."</th>
      <th class=thsmall style='text-align:center'>"._("Отчеты")."</th>

      </tr>";

      while ($r=sqlget($res)) {
         echo "<tr class=trlog>";
         if ($s[$ss][cid]==-1) {
                 echo "<td>$r[ctitle]</td>";
         }
         if ($s[$ss][tid]==-1) {
                 echo "<td>$r[ttitle]</td>";
         }
         if ($s[$ss][mid]==-1) {
                 echo "<td>$r[LastName] $r[FirstName] $r[Patronymic], $r[login]</td>";
         }
         echo "
         <td style='text-align:center'><b>$r[bal]</b></td>
         <td style='text-align:center'>$r[balmin]/$r[balmax]<br>$r[balmin2]/$r[balmax2]</td>
         <td style='text-align:center'>".($r[questdone]==$r[questall]?
                   "$r[questdone]/$r[questall]":
                   "<font color=blue>$r[questdone]/$r[questall]</font>")."</td>
         <td>".date("d.m.Y H:i",$r[start])."<br>".
         ($r[fulltime]?duration($r[fulltime]):"?")."</td>";
         if ($r[needmoder]==0 && $r[moder]==0) echo "<td>&nbsp;</td>";
         else {
            if ($r[needmoder]==1) echo "<td colspan=2><font color=blue>"._("Сеанс еще не проверен преподавателем")."</font></td>";
            else echo "<td>".mid2name($r[moderby])." ".
               date("d.m.Y H:i",$r[modertime])."</td>";
         }
         echo "</td>";

         if ($r[needmoder]==0 || $r[moder]==0) {
            echo "<td>";
            echo "<font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][0]}</font>";
            echo "</td>";
         }
         echo "<td align='center'><a href='test_log.php?c=mini&stid=$r[stid]' target='_blank'><img src='images/icons/print.gif' border='0' alt='Сжатый отчет' title='"._("Открыть краткий отчет")."'/></a>&nbsp;<a href='$PHP_SELF?c=seance&fulllog=1&stid=$r[stid]$sess'><img src='images/icons/print_a.gif' border='0' alt='Подробный отчет' title = '"._('Открыть подробный отчет')."'/></a></td>";

         echo "</tr>";
      }
      echo "</table>";
   } else {
      echo "
      <table width=100% class=main cellspacing=0>
      <tr class=trlog>";
      if ($s[$ss][cid]==-1) {
             echo "<th class=thsmall style='text-align:center'>"._("Курс")."</th>";
      }
      if ($s[$ss][tid]==-1) {
             echo "<th class=thsmall style='text-align:center'>".TEST_TYPE."</th>";
      }
      if ($s[$ss][mid]==-1) {
             echo "<th class=thsmall style='text-align:center'>"._("ФИО").", login</th>";
      }
      echo "
      <th class=thsmall style='text-align:center'>"._("Балл")."</th>
      <th class=thsmall style='text-align:center'>min/max<br>".helpalert(_("Минимальный и максимальный возможный балл, без учета модерируемых вопросов. На второй стоке: минимальный и максимальный возможный балл, c учетом модерируемых преподавателями вопросов. Из-за того, что модерируемые вопросы проверяются не сразу, максимальный бал после проверки может быть больше."),"(?)",1)."</th>
      <th class=thsmall style='text-align:center'>"._("Вопросов Ответил")."/<br>"._("Всего")."</th>
      <th class=thsmall style='text-align:center'>"._("Начало").",<br>"._("затрачено")."</th>
      <th class=thsmall style='text-align:center'>"._("Проверка препода-<br>вателем")."</th>
      <th class=thsmall style='text-align:center'>"._("Статус")."</th>
      <th class=thsmall style='text-align:center'>"._("Отчеты")."</th>

      </tr>
      <tr>
      <td colspan=99 align=center>"._('нет данных для отображения')."
      </td>
      </tr>";

      echo "</table>";
   }

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;





case "seance":

   intvals("stid");
   $s[$ss][stid];
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Отчет о сеансе тестирования"));
   $GLOBALS['controller']->setHelpSection('otchet');
   if (!$fulllog) echo show_tb();
      else {
         $html=path_sess_parse(create_new_html(0,0));
         $html=explode("[ALL-CONTENT]",$html);
         echo $html[0];
      }
//   echo "<h3>Отчет о сеансе тестирования N$stid</h3>
   $GLOBALS['controller']->captureFromOb(TRASH);
   echo ph(_("Отчет о сеансе тестирования"),"");
   $GLOBALS['controller']->captureStop(TRASH);
   echo "
   <br>
   <table width=100% border=0 cellspacing=0 cellpadding=0 class=hello>
      <tr>
         <td valign=top>";
   $GLOBALS['controller']->captureFromOb(TRASH);
   echo "
         <a href=$PHP_SELF?$sess>&lt;&lt; "._("к началу статистики")."</a>";
   $GLOBALS['controller']->captureStop(TRASH);
   echo "
         </td>
         <td align=right>";
   		 /*
         if ($fulllog)
            echo "<a href=$PHP_SELF?c=seance&fulllog=0&stid=$stid$sess>&lt;&lt; "._("Скрыть подробный отчет")."</a>";
         else {
            echo "<a href=$PHP_SELF?c=seance&fulllog=1&stid=$stid$sess>показать подробный отчет &gt;&gt;</a><br>";
            echo "<a href=$PHP_SELF?c=mini&stid=$stid&back=1$sess target=_blank>показать сжатый отчет &gt;&gt;</a>";
         }*/

   echo "</td>
       </tr>
       <tr>
         <td colspan=2>";

   if ($s[perm]<2) {
      echo _("Доступ к этой странице имеют только:")."
      <li>"._("Преподаватели (в пределах своего курса)")."
      <li>"._("Деканы")."
      <li>"._("Администраторы")."<br><br><br>
      "._("Здесь размещается закрытая информация - правильные ответы и
      отчеты по каждому вопросу.")."</td></tr></table>";
      echo show_tb();
      exit;
   }

   $res=sql("SELECT * FROM loguser WHERE stid=$stid","errTL252");
   if (!sqlrows($res))
      exitmsg(_("Такого сеанса не существует:")." $stid","$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   //<b>информация:</b> $rr[Information]<br>
   $rr=sqlval("SELECT * FROM People WHERE mid=$r[mid]","errTL271");
   echo "
   <b>"._("курс:")."</b> ".cid2title($r[cid])."<br>
   <b>".strtolower(TEST_TYPE).":</b> ".tid2title($r[tid])."<br>
   <br>
   <b>"._("ФИО:")."</b> $rr[LastName] $rr[FirstName] $rr[Patronymic]<br>
   <b>e-mail:</b> $rr[EMail]<br>
   <b>"._("адрес:")."</b> $rr[Address]<br><br>";
   echo "</td>
       </tr>
       <tr>
         <td colspan=2>";

   switch ($r[status]) {
      case 0:
      case 2:
      case 3:
         echo "
         "._("начало сеанса:")." ".date("d-m-Y H:i:s",$r[start])."<br>
         <font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][1]}</font>
         <br>";
         break;

      case 1:
      case 4:
      case 5:
         echo "
         <b>"._("набрано баллов:")." $r[bal]</b><br>
         "._("минимально возможное:")."   $r[balmin2]<br>
         "._("максимально возможное:")."  $r[balmax2]<br>
         "._("ответил на вопросы:")."     $r[questdone]<br>
         "._("всего вопросов:")."         $r[questall]<br>
         "._("начало сеанса:")."          ".date("d-m-Y H:i:s",$r[start])."<br>
         "._("завершение сеанса:")."      ".date("d-m-Y H:i:s",$r[stop])."<br>
         "._("длительность:")."           ".duration($r[fulltime])."<br>";
         if ($r[needmoder]==0 && $r[moder]==0);
         else {
            echo _("Это задание, требующее проверки преподавателем.")."<br>";
            if ($r[needmoder]==1) echo "<font color=blue>"._("Сеанс еще не проверен преподавателем.")."</font>";
            else echo _("Проверен преподавателем:")." ".mid2name($r[moderby])." ".
               date("d-m-Y H:i",$r[modertime]);
         }
         echo "<br>
         <font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][1]}</font>";
         break;

      default:
         err("errTL649: "._("Неизвестный статус завершенности тестирования! Необходимо добавить описание."),__FILE__,__LINE__);
   }

   echo "</td>
       </tr>
   </table>";

   if ($fulllog) {
      $res=sql("SELECT stid, kod, number, time, bal, balmax, balmin, good, vopros,
                       otvet, LENGTH(attach) as size, filename, text
                FROM logseance
                WHERE stid=$stid
                ORDER BY number","errTL337");

      $breakline="<table width=100% border=0 cellspacing=0 cellpadding=0 bgcolor=black><tr><td>
      <img src=about:blank width=1 height=3 style=></td></tr></table><P>";
      $breakline="<br><br><hr size=1 noshade>";


      while ($r=sqlget($res)) {
/*      echo "<xmp>";
         pr($r);
      echo "</xmp>";
*/
         $str = $r[vopros];
         $str = str_replace("\\r\\n", "", $str);
         $str = str_replace("'", "", $str);
         $str = str_replace("\\'", "'", $str);
         $str = str_replace('\\"', '"', $str);

         //var_dump(htmlspecialchars($str));
         //die();

         echo "$breakline $str<P>";
         echo "<hr size=1 noshade>";
         $ok=unserialize($r[otvet]);
         echo "<b>"._("Правильность ответа:")." $r[good]%</b><br>";
         echo _("Набрано баллов:")." $r[bal] ("._("диапазон: от")." $r[balmin] "._("до")." $r[balmax])<br>";
         echo _("Ответ был получен:")." ".date("d/m/Y H:i:s",$r[time])."<br>";
         if ($ok[moder])
           echo _("ВОПРОС С ПРОВЕРКОЙ ПРЕПОДАВАТЕЛЕМ")." !<br>";
         if (is_array($ok[error]) && count($ok[error]))
         foreach ($ok[error] as $v) echo _("Ошибка в ответе:")." $v<br>";
         if (is_array($ok[main]) && count($ok[main])) {
            echo "
            <table width=100% class=main cellspacing=0>
            <tr><th colspan=20 align=center>"._("Анализ ответов")."</td></tr>
            <tr>
            <th>"._("Что выбрал тестируемый при ответе")."</td>
            <th>"._("Правильно ли это")."</td>
            </tr>";
            foreach ($ok[main] as $k=>$v) {
                echo "<tr bgcolor=white><td>$v</td>";
                echo "<td>";
                if (is_array($ok['weights']) && count($ok['weights'])) {
                    if (in_array($ok['qtype'],array(11))) {
                        echo $ok['otv'][$k];
                    } else {
                        echo $ok[weights][$k];
                    }
                }
                else
                echo $ok[info][$k];
                echo "</td></tr>";
            }
            echo "</table>";
         }
         echo "<P>";
         //pr($r[otvet]);
      }
      echo "$breakline<P>";

   }

   if (!$fulllog) {
       if (!$GLOBALS['controller']->enabled)
       echo show_tb();
   }
   else echo $html[1];
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   break;




case "mini" :

   intvals("stid");
   $s[$ss][stid];
   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];
   $GLOBALS['controller']->setView('DocumentContent');
   $GLOBALS['controller']->captureFromOb(CONTENT);
   //$GLOBALS['controller']->setView('DocumentPrint');
   echo "
    <table width=100% class=main cellspacing=0>
     <tr>
      <td colspan='2'>";
   //if ($s[perm]<1) {
      // Проверим, свою ли попытку смотрит пользователь и может ли пользователь смотреть свои попытки.
   //   echo _("Доступ к этой странице имеют только:")."
   //   <li>"._("Преподаватели (в пределах своего курса)")."
   //   <li>"._("Деканы")."
   //   <li>"._("Администраторы")."<br><br><br>
   //   "._("Здесь размещается закрытая информация - правильные ответы и
   //   отчеты по каждому вопросу.")."</td></tr></table>";
   //   echo $html[1];
   //   exit;
   //}

   $res=sql("SELECT loguser.*, subjects.name as ctitle 
   				FROM loguser 
   				LEFT JOIN subjects ON subjects.subid = loguser.CID
   				WHERE stid=$stid","errTL252");
   if (!sqlrows($res))
      exitmsg(_("Такого сеанса не существует:")." $stid","$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   $isPoll = false;
   if ($r['sheid']) {
       if (in_array(getField('schedule', 'typeID', 'SHEID', $r['sheid']), array(2055, 2056, 2057, 2053))) {
           $isPoll = true;
       }
   }

   $rr=sqlval("SELECT * FROM People WHERE mid=$r[mid]","errTL271");
   echo "<h2>$rr[LastName] $rr[FirstName] $rr[Patronymic]</h2>
   </td></tr>";

   $lCID=$r[cid];

   switch ($r[status]) {

      case 1:
      case 4:
      case 5:
         echo "<tr>
         <td>".TEST_TYPE.": <b>".tid2title($r[tid])."</b></td>
         <td>"._("Протокол выполнения:")."</td>
         </tr><tr>
         <td>"._("Курс:")." <b>".$r[ctitle]."</b></td>
         <td>"._("Начал:")." ".date("d.m.y в H:i:s",$r[start])."</td>
         </tr><tr>
         <td>E-mail: <a href='mailto:'".$rr[EMail]."'>".$rr[EMail]."</a></td>
         <td>"._("Закончил:")." ".date("d.m.y в H:i:s",$r[stop])."</td>
         </tr><tr>
         <td>"._("Набрано баллов:")." <b>".$r[bal]."</b> (".$r[balmin2]." - ".$r[balmax2].") </td>
         <td>"._("Длительность:")." ".duration($r[fulltime])."</td>
         </tr><tr>
         <td>"._("Задано")." ".$r[questall]." "._("вопросов, отвечено на")." ".$r[questdone]."</td>
         <td></td>
         </tr>";
/*         if ($r[needmoder]==0 && $r[moder]==0);
         else {
             echo "<tr>
                   <td colspan=2>"._("Это задание, требующее проверки преподавателем.")."</td></tr><tr>
                   <td colspan=2>";
            if ($r[needmoder]==1) echo "<font color=blue>"._("Сеанс еще не проверен преподавателем.")."</font>";
            else echo "Проверено: ".mid2name($r[moderby])." ".
               date("d.m.y H:i",$r[modertime]);
           echo "</td></tr>";
         }*/
         echo "<tr><td colspan=2><font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][1]}</font></td></tr>";
         break;

      default:
         echo"<tr><td colspan='2'>"._("начало сеанса:")." ".date("d-m-Y H:i:s",$r[start])."</td></tr><tr>
         <td colspan=2>
         <font color={$teststatus[$r[status]][2]}>{$teststatus[$r[status]][1]}</font>
         </td></tr>";
         break;
   }

   echo "</table>";



    //[#16872
    $res = sql("select log from loguser where stid='{$stid}'");
    $row = sqlget($res);
    /*
     * #17136
     * Если используется БД MSSQL, то в поле log данные
     * сохраняются в кодировке cp1251
     */
    if(dbdriver == 'mssql' || dbdriver == 'mssqlnative'){
        $config = Zend_Registry::get('config');
        if($config->charset != 'cp1251') {
            $charset = $config->charset;
            $row['log'] = iconv('cp1251',$charset, $row['log']);
        }
    }
    $akod = unserialize($row['log']);

    $res = sql("select qtema, sum(balmax) sumbalmax, sum(balmin) sumbalmin from list where kod in ('".implode("','", $akod['akod'])."') group by qtema");
    $themes_balls = array();
    while($row = sqlget($res))
    {
        $themes_balls[$row['qtema']] = $row;
    }
//#16872]


        $themesSeparate = sql("SELECT  logseance.qtema, SUM(logseance.balmax) as sumBalmax, SUM(logseance.balmin) as sumBalmin, SUM(logseance.bal) as sumBal
                FROM logseance, list
                WHERE stid=$stid
                AND logseance.kod = list.kod
                GROUP BY logseance.qtema","errTL337");

    if(count($themesSeparate) > 0){

        echo "<br><h1>" . _('Распределение правильных ответов по темам') . '</h1>';
        echo "<table class='main' width='100%' cellspacing=0>";
        echo "<thead><th>Название</th><th>Проценты</th></thead>";
        echo "<tbody>";
        while($oneTheme = sqlget($themesSeparate)){
            $themeName="<td>";
            if (empty($oneTheme['qtema']))
                $themeName .= "Без темы</td><td>";
            else
                $themeName .= $oneTheme['qtema'] . "</td><td>";
            echo "<tr>";
            echo $themeName. round( $oneTheme['sumBal'] * 100 / ($themes_balls[$oneTheme['qtema']]['sumbalmax'] - $themes_balls[$oneTheme['qtema']]['sumbalmin']) , 1). "%</td>";
//#16872            echo $themeName. round( $oneTheme['sumBal'] * 100 / ($oneTheme['sumBalmax'] - $oneTheme['sumBalmin']) , 1). "%</td>";
            echo "</tr>";
        }
        echo "</tbody>
        </table>";
    }


    $res=sql("SELECT logseance.stid as stid, logseance.cid as cid, logseance.kod as kod, number, time, bal,
                logseance.balmax as balmax, logseance.comments as comments,
                logseance.balmin as balmin, good, vopros, list.weight,
                       otvet, LENGTH(attach) as size, filename, text,
                       list.qdata as qdata, logseance.review_filename, logseance.review
                FROM logseance, list
                WHERE stid=$stid
                AND logseance.kod = list.kod
                ORDER BY number","errTL337");

      $breakline="<table width=100% border=0 cellspacing=0 cellpadding=0 bgcolor=black><tr><td>
      <img src=about:blank width=1 height=3 style=></td></tr></table><P>";
      $breakline="<P><hr size=1 noshade><P>";


      while ($r=sqlget($res)) {

         $ok=unserialize($r[otvet]);
         $good = 0;
         if ($val = ($r['balmax']-$r['balmin'])) {
             $good = intval((($r['bal']-$r['balmin'])*100)/$val);
         }
         echo $breakline."
         <table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
           <td colspan=3 align='left' width='80%'>
           <table width=100% cellspacing=0><tr>
           <td><b>"._("Вопрос:")."</b></td>
           <td align=right>";
         	if($ok[moder] && !$isPoll){
         		echo _('не оценивается');
         	}
         	elseif (!$isPoll) echo "<b>$r[bal]</b> "._("балл.")." (".$r[balmin]."...".$r[balmax].")<b>{$good}%</b>";
         	echo "</td></tr></table>
           </td>
         </tr>
         <tr>
           <td width=85% align=left colspan=2>";
         echo qdata2text($r[qdata]);
         echo "</td>
           <td width=15% nowrap><span class='small'>".date("d.m.y H:i:s",$r[time])."</span></td>
         </tr>";
         if (count($ok) && is_array($ok)) {
            echo "<tr><td colspan=99><b>"._("Ответ:")."</b></td></tr>";
            //<tr><td colspan=99>";
            //if ($ok[moder])
              //echo "<tr><td colspan=99>"._("ВОПРОС С ПРОВЕРКОЙ ПРЕПОДАВАТЕЛЕМ")."</td></tr>";
            //echo "</td></tr>";
            if (count($ok[error]))
                foreach ($ok[error] as $v)
                  echo "<tr><td colspan=99>"._("Ошибка:")." ".$v."</td></tr>";
            if ( count($ok[main]) && is_array($ok[main]) ) {
               foreach( $ok[main] as $k=>$v ) {
                  echo "<tr><td class=s9 colspan=2>";
                  if ($ok[good][$k]) {
                     echo "$v</td><td align=right>";
                     if (empty($r['weight']) && !$isPoll)
                     echo "<b>"._('Верно')."</b>";
                  }
                  else{
                     if (empty($r['weight']) && !$isPoll) {
                        echo "<font color=#cc0000>";
                     }
                     echo $v;
                     if (empty($r['weight']) && !$isPoll) {
                         echo "</font>";
                         echo "<BR>";
                     }

                     //echo echoAttachedFile( $r['cid'],  $stid, 4, $r['kod']);
//                     if (!$ok[moder]) echo showHistogram( explode(",", $v ));
                     echo "</td> <td align=right>";
                     if (empty($r['weight']) && !$ok[moder] && !$isPoll) {
                     echo '<font color=#cc0000>'._("Неверно").'</font>';
                     }

                  }

                    echo "</td></tr>";

					$qqq = "SELECT logseance.attach, logseance.text, logseance.filename FROM logseance INNER JOIN seance ON (logseance.stid=seance.stid) WHERE logseance.stid='{$stid}' AND logseance.cid='{$r['cid']}' AND logseance.kod='{$r['kod']}'";
					$ress=sql($qqq,"err10 in test");
					$rrr = sqlget($ress);
					sqlfree( $ress );
					if (!empty($rrr['text'])) {
                        if (($rrr['text'][0] == "'") && ($rrr['text'][strlen($rrr['text'])-1])) {
                             $rrr['text'] = substr($rrr['text'], 1, -1);
                        }

                        $rrr['text'] = str_replace("\\r\\n", "", $rrr['text']);
                        $rrr['text'] = str_replace("'", "", $rrr['text']);
                        $rrr['text'] = str_replace("\\'", "'", $rrr['text']);
                        $rrr['text'] = str_replace('\\"', '"', $rrr['text']);

					    echo "<tr><td colspan=99><b>"._("Текст ответа:")."</b></td></tr>";
                        echo "<tr><td colspan=99>".$rrr['text']."</td></tr>";
					}

					if(!empty($rrr['attach']) && !empty($rrr['filename'])) {
					  echo "<tr><td colspan='2'><a href=\"{$GLOBALS['sitepath']}test_moder.php?c=download&what=3&stid={$stid}&cid={$r['cid']}&kod={$r['kod']}\">"._("скачать файл")."</a></td></tr>";
					}
               }
            }
            if (!empty($r[comments])) {
                echo "<tr><td colspan=99><b>"._("Комментарий преподавателя:")."</b></td></tr>";
                echo "<tr><td colspan=99>".$r[comments]."</td></tr>";
            }
             if (!empty($r['review']) && !empty($r['review_filename'])) {
                echo "<tr><td colspan=99><b>"._("Скачать рецензию преподавателя:")."</b></td></tr>";
                echo "<tr><td colspan=99><a href=\"{$GLOBALS['sitepath']}test_moder.php?c=download&what=3&stid={$stid}&cid={$r['cid']}&kod={$r['kod']}&type=review\">"._("скачать файл")."</a></td></tr>";
            }

         }
         echo "</table>";
      }

      echo "$breakline";
   echo $html[1];
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
}

?>