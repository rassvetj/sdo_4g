<?php
   # От 20.12.2018 En
   # ACHTUNG WARNING ATTENTION !!!
   # В этом скрипте зачем-то заменили поле qdata на url. Зачем?
   # Логики в этом не вижу. Это разные поля!
   # "модуль тестирования" - RIP...

   require_once("1.php");
   
   // echo '<pre>'; exit(var_dump($GLOBALS['controller']));

    if (isset($_REQUEST['c'])) {   
        $c = $_REQUEST['c'];   
    }

    if (isset($_REQUEST['kod'])) {
        $kod = $_REQUEST['kod'];
    }

    if (isset($_REQUEST['fnum'])) {
        $fnum = $_REQUEST['fnum'];
    }

   require_once("test.inc.php");

   // ТИПЫ ВОПРОСОВ ДЛЯ ОПРОСА QUIZ

   if (isset($_GET['quiz_id'])) {
       $GLOBALS['qtypes'] = array(1=>1, 2=>2, 6=>6);
   }
   if (isset($_GET['task_id'])) {
       $GLOBALS['qtypes'] = array(6=>6);
   }

   //require_once('teachers/ziplib.php');

   $GLOBALS['controller']->setView('DocumentContent');

   if ($controller->enabled) $s[usermode]=0;

   if (isset($_POST['sel_action'])) {

   		$_POST[$_POST['sel_action']] = true;
   		$$_POST['sel_action'] = true;
   }


   /*if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) exitmsg(_("К этой странице могут обратится только: преподаватель, представитель учебной администрации, администратор"),"/?$sess");
   if (count($s[tkurs])==0) exitmsg(_("В данное время Вы не преподаете ни на одном из курсом."),"/?$sess");
*/
   $ss="test_e1";

   $ss_time1=mktime(0,0,0,1,1,1990);
   $ss_time2=mktime(0,0,0,1,1,2034);

   if (!isset($start)) $start=0;
   if (!isset($s[$ss][limit]))  $s[$ss][limit]=50; // размер страницы
   if (!isset($s[$ss][order]))  $s[$ss][order]='kod'; // сортировка, колонка
   if (!isset($s[$ss][desc]))   $s[$ss][desc]=''; // сортировка, порядок
   if (!isset($s[$ss][cid]))    $s[$ss][cid]=0; // текущий курс (изменяется ниже автоматом)
   if (!isset($s[$ss][tid]))    $s[$ss][tid]=0; // текущий тест
   if (!isset($s[$ss][time1]))  $s[$ss][time1]=$ss_time1;
   if (!isset($s[$ss][time2]))  $s[$ss][time2]=$ss_time2;
   if (!isset($s[$ss][kod]))    $s[$ss][kod]=array(); // фильтр кода
   if (!isset($s[$ss]['goto']))   $s[$ss]['goto']=0; // спец флаг для переходов
   if (!isset($s[$ss][gotopost])) $s[$ss][gotopost]=0; // куда переходить при редактировании вопросов (список вопросов / список вопросов + выделение галочками / повторное редактирование)
   if (!isset($s[$ss][type]))   $s[$ss][type]=2; // тип вопроса при добавлении

/*   if (isset($cid)) {
      if (!isset($s[tkurs][$cid])){
          //exit("HackDetect: "._("ошибочный номер курса")." (errTL333): [$cid]");
          $GLOBALS['controller']->setMessage(_('ошибочный номер курса'), JS_GO_URL, 'test_list.php');
          $GLOBALS['controller']->terminate();
          exit();
      }
   }*/
   if ($s[$ss][cid]==0) {
       if (is_array($s['tkurs']) && count($s['tkurs'])) {
           $keys=array_keys($s[tkurs]);
           $s[$ss][cid]=$keys[0];
       }
   }

   $ordermenu=array(
      'kod'=>_("код"),
      'qtype'=>_("тип"),
      'balmax'=>_("макс.балл"),
      'balmin'=>_("мин.балл"),
      'last'=>_("изменен"),
   );

   intvals("start");

if ($GLOBALS['controller']->enabled && empty($c)) $s[$ss][cid] = ($_GET['CID']) ? $_GET['CID'] : 0;
//if ($GLOBALS['controller']->enabled) $cont_type = (empty($s[$ss][cid])) ? TRASH : CONTENT;
$cont_type = CONTENT;

if (isset($_REQUEST['CID'])) {
    $is_locked_course = is_course_locked($_REQUEST['CID']);
} elseif (isset($_REQUEST['cid'])) {
    $is_locked_course = is_course_locked($_REQUEST['cid']);
}

switch ($c) {

case "":
    $GLOBALS['controller']->setHelpSection('test_list');

   require_once('lib/sajax/SajaxWrapper.php');

   $js = "function show_themes_list(arr) {
                //alert(arr);
                eval('var arr = '+arr);

                var elm = document.getElementById('themeFilter');

                elm.options.length = 0;
                for(var key in arr) {
                    opt = new Option(key, arr[key]);
                    if (elm) {
                       try
                          {
                            elm.add(opt,null);
                          }
                        catch(ex)
                          {
                            elm.add(opt); // IE
                          }
                   }
                   if (key == '$theme') {
                       elm.selectedIndex = elm.length-1;
                   }
                }

          }
          function selectThemes(cid) {
              var elm = document.getElementById('themeFilter');
              elm.options.length = 0;
              opt = new Option('"._('Загрузка').'...'."', '999');
              try {
              elm.add(opt, null);
              }
              catch (err) {
                elm.add(opt); //IE
              }

              x_get_all_themes_array(cid, show_themes_list);
           }

           if ('$CID') \$P(document.observe('dom:loaded', function(e){selectThemes('$CID')}));

           function show_test_list(arr) {
                var elm = document.getElementById('test_list');

                elm.options.length = 0;
                for(var key in arr) {
                    opt = new Option(arr[key], key);
                    if (elm) {
                       try
                          {
                            elm.add(opt,null);
                          }
                        catch(ex)
                          {
                            elm.add(opt); // IE
                          }
                   }
                }

          }
          function showTestList(cid) {
              var elm = document.getElementById('test_list');
              elm.options.length = 0;
              opt = new Option('"._('Загрузка').'...'."', '999');
              try {
              elm.add(opt, null);
              }
              catch (err) {
                elm.add(opt); //IE
              }

              x_get_all_test_array(cid, show_test_list);
           }
               ";
   $sajax_javascript = CSajaxWrapper::init(array('get_all_themes_array','get_all_test_array')).$js;

   $GLOBALS['controller']->setHeader(_("Все вопросы курса"));
   $GLOBALS['controller']->setLink('m160102', array(0));
   $courselist=selCourses($s[tkurs],$CID, $GLOBALS['controller']->enabled);
   $GLOBALS['controller']->addFilter(_("Курс"), 'CID', $courselist, $CID, REQUIRED,0, true, "onChange='selectThemes(this.options[this.selectedIndex].value);'");
   if ($CID) {
   $GLOBALS['controller']->setLink('m160104',array($CID));
   }
   if (!isset($theme)) $theme = -1;
   $GLOBALS['controller']->addFilter(_("Тема"), 'theme', array()/*get_all_themes_array($CID)*/, $theme, false, -1, true, "id='themeFilter'");
   $question_types = array(
       1  => _("одиночный выбор"),
       2  => _("множественный выбор"),
       3  => _("на соответствие"),
       12 => _("на упорядочивание"),
       4  => _("с прикрепленным файлом"),
       5  => _("заполнение формы"),
       6  => _("свободный ответ"), // это есть задание
       7  => _("выбор по карте на картинке"),
       8  => _("выбор из набора картинок"),
       9  => _("внешний объект"), // это есть упражнение
       10 => _("внешняя программа"), // это есть упражнение
       11 => _("табличный ввод"), // не используется
       );
   $GLOBALS['controller']->addFilter(_("Тип вопроса"), 'listType', $question_types, $listType);
   $GLOBALS['controller']->setFilterScope('m16', $str, $gr);
   if ($CID>0){
        if (($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN)
            || $GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS))
            && !$is_locked_course) {
       		$GLOBALS['controller']->setLink('m160201');
       		$GLOBALS['controller']->setLink('m160202',array($CID));
        }
   }
   $sql_kod="";
   if (count($s[$ss][kod])) {
      $sql_kod=" AND ( ";
      foreach ($s[$ss][kod] as $v) {
         $sql_kod.=" kod LIKE ".$GLOBALS['adodb']->Quote($v)." OR";
      }
      $sql_kod=substr($sql_kod,0,-3)." ) ";
   }
   if ($theme!=-1) {
       $theme = $theme=='-999'?'':$theme;
       $sql_kod .= " AND qtema=".$GLOBALS['adodb']->Quote($theme)."";
   }
   if ($listType) {
       $sql_kod .= " AND qtype=".(int) $listType;
   }
  
   $cmsKods = getCMSkods($s[$ss]['cid']);


   $rq1="SELECT * FROM list
      WHERE
       (kod LIKE '{$s[$ss][cid]}-%' OR
        kod = '".implode("' OR kod = '",$cmsKods)."') AND
        last >= {$s[$ss][time1]} AND
        last <= {$s[$ss][time2]}
        $sql_kod
      ORDER BY {$s[$ss][order]} {$s[$ss][desc]}";
   if(!isset($start)) $start = 0;
   $rq2="
      SELECT COUNT(*) FROM list
      WHERE
       (kod LIKE '{$s[$ss][cid]}-%' OR
        kod = '".implode("' OR kod = '",$cmsKods)."') AND
        last >= {$s[$ss][time1]} AND
        last <= {$s[$ss][time2]}
        $sql_kod
   ";
   $res2=sql($rq2,"errTL63");
   $cnt2=sqlres($res2,0,0);
   sqlfree($res2);
   echo show_tb();

   echo ph(_("Все вопросы по курсу"));

   echo "
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <tr><td><a href=$PHP_SELF?c=selectkurs$sess>"._("Текущий курс")."</a>: <b>".cid2title($s[$ss][cid])."</b> ".
   ($s[usermode]?"({$s[$ss][cid]})":"")."
   </td><td align=right>";

echo "
   <a href=$PHP_SELF?c=add&cid={$s[$ss][cid]}&start=$start$sess>"._("Добавить вопрос")."</a><br>
   <a href='teachers/xml_exp_imp.php?oper=2&cid={$s[$ss][cid]}' target='_blank'>"._("Импорт вопросов из текстового файла")."</a>";
echo "</td></tr>".
   (isset($s[$ss][tid_title])?
   "<tr><td colspan=2><a href=test_test.php?c=edit&tid={$s[$ss][tid]}&cid={$s[$ss][cid]}>"._("Текущее задание")."</a>:
   <b>{$s[$ss][tid_title]}</b></td></tr>":"")."
   </table><br>";

   $GLOBALS['controller']->captureFromOb($cont_type);
   if ($s[$ss][cid])
   echo "<div style=\"padding-bottom: 5px;\">
    <div style=\"float: left;\">
        <img src=\"images/icons/small_star.gif\"/>
    </div>
    <div>
        <a style=\"text-decoration: none;\" href=\"{$sitepath}test_list.php?c=add&cid={$s[$ss][cid]}\">создать вопрос</a>
   </div>
   </div>";

   echo "
      <script language='javascript'>
      <!--
        function SelectAll(mark) {
         for(i = 0; i < document.forms['fn_form'].elements.length; i++)
                           {
                      var item = document.forms['fn_form'].elements[i];
                      if (item.name == 'che[]')  {
                      item.checked = mark;
                    };
                                       }
       }

       function changeAction(obj) {
           condTid=(obj.value=='b_addtid');
           if(condTid) {
               putElem('sel_tid');
           }else {
               removeElem('sel_tid');
           }
           document.getElementById('tid').disabled=!condTid;

           condCid=(obj.value=='b_addcid');
           if(condCid) {
               putElem('cidlist');
               var cidSelectObj = document.getElementById('cidlist');
               var cid = cidSelectObj.options[cidSelectObj.selectedIndex].value;
               putElem('test_list');
               showTestList(cid);
           }else {
               removeElem('cidlist');
               removeElem('test_list');
           }

       }

       $sajax_javascript

      //-->
      </script>
   <form action=\"$PHP_SELF\" method=\"post\" name=\"fn_form\">
   <table width=100% class=main cellspacing=0>
   <input type=hidden name=c value=\"main\">
   <input type=hidden name=start value=\"$start\">
   <input type=hidden name=cid value=\"{$s[$ss][cid]}\">
   <input type=hidden name=theme value=\"$theme\">
   <input type=hidden name=listType value=\"".(int) $listType."\">
   <tr>
   <th align='center'><input type='checkbox' name='check_all' onclick=\"SelectAll(checked)\" title='"._("Выделить/убрать все")."'></th>
   <th>".sortrow(1,_("Код"),"kod",$s[$ss][order],$s[$ss][desc],_("сортировка по кодам вопросов"))."</th>
   <th>".sortrow(1,_("Тип"),"qtype",$s[$ss][order],$s[$ss][desc],_("сортировка по типам вопросов"))."</th>
   <th width='100%'>"._("Текст вопроса")."</th>
   <th>".sortrow(1,_("Тема"),"qtema",$s[$ss][order],$s[$ss][desc],_("сортировка по теме"))."</th>
   <th>"
        .sortrow(1,_("Мин"),"balmin",$s[$ss][order],$s[$ss][desc],_("сортировка по минимальному балу за вопрос"))."<br>
       ".sortrow(1,_("Макс"),"balmax",$s[$ss][order],$s[$ss][desc],_("сортировка по максимальному балу за вопрос"))."</th>
   <th>"._("Действия")."</th>
   </tr>";

   $res=sql($rq1,"errTL68");
   $cnt1=sqlrows($res);
   if (!sqlrows($res)) echo "<tr><Td colspan=10>"._("Не найдено ни одного вопроса")."</td></tr>";

   $fn_i=0;


   
   while ($r=sqlget($res)) {      //   echo '<pre>'; exit(var_dump($r['qtype']));
      $is_locked_course = is_course_question_locked($r['kod']);
      $question_perm_edit =
           ($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)
           || ($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN) && ($r['created_by']==$_SESSION['s']['mid'])));
      $fn_i++;
      if($fn_i <= $start) continue;
      if($fn_i > $start +  $s[$ss][limit]) break;
      @include_once("template_test/$r[qtype]-v.php");
      $qhelp=$GLOBALS['v_edit_'.$r['qtype']]['title'];

      if(6==$r['qtype']) {
          $qinfo=$GLOBALS['v_edit_'.$r['qtype']]['info'];
      }

      echo "<tr><td nowrap align=".($s[usermode]?"left":"center").">";
      if ($question_perm_edit && !in_array($r['kod'], $cmsKods)) {
          echo
          "<input type=checkbox name=che[] id=\"fn_che".$fn_i."\" value=\"".html($r[kod])."\"".
             (isset($che[kod2base($r[kod])])?"checked":"").">";
      }
      echo
      "</td><td align='center'>";

      echo "$r[kod]";

      echo "</td>".
      "<td align='center'><a href='#' onclick='return false;' title=\"".$qhelp."\" class=sym1>".getVisualChar($r[qtype])."</td>".
      "<td align=left>";
      if ($question_perm_edit) {
          echo "<a href=\"#\" target='preview' onclick=\"wopen('test_vopros.php?kod=".ue($r[kod])."&cid={$s[$ss][cid]}&mode=2$asess','preview'); return false;\"  title='" . _('Открыть вопрос') . "'>";
      }
	  # Что за петрушка? Тут должна быть переменная $r[qdata] !!! От 20.12.2018 En
	  # Заменил url на qdata
      echo nl2br(wordwrap(strip_tags(qdata2text($r[qdata])),20," ",1));
      if ($question_perm_edit) {
          echo "</a>";
      }
      echo "</td>".
      "<td algn=center>".$r['qtema']."</td>".
      "<td nowrap>$r[balmin]..$r[balmax]</td>".
      "<td nowrap align='right'>".
      (($r['qmoder']&&false)?"<a href='#' onclick='return false;' title=\"".$qinfo."\" >".getIcon("note", $qinfo)."</a>":"");

      if( ($s[perm] >= 2)) {
    	echo "<a href='#' onclick=\"wopen('test_vopros.php?kod=".ue($r[kod])."&cid={$s[$ss][cid]}&mode=2$asess','preview'); return false;\" title='" . _('Открыть вопрос') . "'><img src='{$sitepath}images/icons/look.gif' border='1'></a>&nbsp;";
      }

      if ($question_perm_edit && !$is_locked_course && !in_array($r['kod'], $cmsKods)) {
          echo
          "<a href='$PHP_SELF?c=b_edit&che[]=".ue($r[kod])."$asess'>
          ".getIcon("edit", _("Редактировать вопрос"))." </a> ".
          "<a href='$PHP_SELF?c=delete_vopros&kod=".ue($r[kod])."&CID={$CID}&theme=".urlencode($theme)."&listType=".(int) $listType."&start=$start$asess'
          onclick='return confirm(\"" . _("Вы действительно желаете удалить вопрос?\\nПосле удаления вопрос нельзя будет восстановить.") . "\")'>
          ".getIcon("delete", "Удалить вопрос")."</a> ";
      }
      echo
      "</td></tr>";

   }

   sqlfree($res);

   $res=sql("SELECT tid,title
             FROM test
             WHERE cid={$s[$ss][cid]}
             ORDER BY title","errTL880");

      echo "<tr><td colspan=8>
                                    <table  border='0' cellspacing='0' cellpadding='5' width=100%>
                                      <tr>
                                        <td nowrap width='100%' align='right' valign='top'>
                                        "._("Выполнить действие")."&nbsp;
                                        <select name='sel_action' onChange=\"javascript: changeAction(this);cond=(this.value=='b_addtid');if(cond) {putElem('sel_tid');} else {removeElem('sel_tid');}document.getElementById('tid').disabled=!cond;\">
<option value='b_edit'>"._("Редактировать")."
<option value='b_delete'>"._("Удалить")."
<option value='b_export'>"._("Экспортировать")."
<option value='b_addtid'>"._("Добавить в задание")."
<option value='b_addcid'>"._("Добавить в курс")."
                                            </select>
                                        </td>
                                        <td nowrap style='display:none' id='sel_tid' valign='top'><select name='tid' id='tid' disabled>";
      while ($r=sqlget($res)) {
         echo "<option value=$r[tid]".($s[$ss][tid]==$r[tid]?" selected":"").">".strbig($r[title],40);
      }
										echo "
                                        </select>
                                        </td>
                                        <td nowrap valign='top'>
                                        <select name='cidlist' id='cidlist' style='display:none' onChange=\"showTestList(this.options[this.selectedIndex].value);\">";
      $res=sql("SELECT CID,Title
             FROM Courses
             WHERE is_poll = 0 AND locked = 0 AND CID <> {$s[$ss][cid]} AND CID IN ('".implode("','", $s[tkurs])."')
             ORDER BY Title","errTL880");
										while ($r=sqlget($res)) {
         echo "<option value='{$r['CID']}' >".strbig($r['Title'],40);
      }
										echo "
                                        </select>
                                        </td>
                                        <td nowrap>
                                            <select name='test_id' id='test_list' style='display:none'>
                                            </select>
                                        </td>
                                      </tr>
                                    </table>
      </td></tr>";

   echo "</table><P>";

   echo "<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>";
   echo okbutton();
   echo "</td></tr></table>";


   echo "<br>
   <table width=100% class=main cellspacing=0>
   <tr><td>"._("Страницы:")."&nbsp;";
   for ($i=0,$j=1; $i<$cnt2; $i+=$s[$ss][limit],$j++) {
      if ($start>=$i && $start<$i+$s[$ss][limit]) echo "<b>";
      echo "<a href=$PHP_SELF?start=$i&CID={$CID}&theme=".urlencode($theme)."&listType=".(int) $listType.">[$j]</a> ";
      if ($start>=$i && $start<$i+$s[$ss][limit]) echo "</b>";
   }
   echo "</td></tr>
   <tr><td>";

   echo _("Вопросов на страницу:")." ";
   $nums=array(5,10,20,30,50,100,1000);
   foreach ($nums as $v) {
      if ($s[$ss][limit]==$v) echo "<b>";
      echo "<a href=$PHP_SELF?c=setnum&num=$v&CID={$CID}&theme=".urlencode($theme)."&listType=".(int) $listType."$sess>[$v]</a> ";
      if ($s[$ss][limit]==$v) echo "</b>";
   }
   echo "</td></tr>";

if (!$GLOBALS['controller']){
   if ($s[usermode]) {
      if ($s[$ss][time1]!=$ss_time1 || $s[$ss][time2]!=$ss_time2) echo "<font color=red><b>";
      echo _("Фильтр времени:")." ";
      echo _("с")." ".date("d/m/y H:i",$s[$ss][time1])."
      "._("по")." ".date("d/m/y H:i",$s[$ss][time2]);
      if ($s[$ss][time1]!=$ss_time1 || $s[$ss][time2]!=$ss_time2) echo "</b></font>";
      echo " <a href=$PHP_SELF?c=settime$sess>["._("изменить")."]</a>
            <a href=$PHP_SELF?c=settime3&time1=$ss_time1&time2=$ss_time2$sess>["._("сбросить")."]</a>";

      if ($s[$ss][kod]) echo "<font color=red><b>";
      echo "<br>"._("Фильтр кода")." ".(!$s[$ss][kod]?_("не задан.")." ":_("ЗАДАН:")." ".count($s[$ss][kod])." "._("шт")." ");
      if ($s[$ss][kod]) echo "</b></font>";
      echo " <a href=$PHP_SELF?c=kodedit$sess>["._("изменить")."]</a>
            <a href=$PHP_SELF?c=kodclean$sess>["._("сбросить")."]</a>";
   }

   echo "</td></tr><tr bgcolor=white><td>";

   echo "<br><small>"._("Режим редактирования:")." ".
           ($s[usermode]==0?"<b><u>":"")."<a href=$PHP_SELF?c=setmode&mode=0>["._("базовый")."]</a>".($s[usermode]==0?"</u></b>":"")." ".
           ($s[usermode]==1?"<b><u>":"")."<a href=$PHP_SELF?c=setmode&mode=1>["._("расширенный")."]</a>".($s[usermode]==1?"</u></b>":"").
           " - "._("только для опытных пользователей!")."</small>";
   echo "</td></tr>";
}
 echo "</table>";

   $GLOBALS['controller']->captureStop($cont_type);

   echo show_tb();

   exit;

case "main":


if (isset($b_edit)) {  
   $GLOBALS['controller']->captureFromOb(CONTENT);
//   $GLOBALS['controller']->setView('DocumentPopup');
   echo "<form action=\"{$sitepath}test_list.php\" method=post name='b_edit' >
   <input type=hidden name=c value='b_edit'>";
   if (is_array($che)) {
      foreach ($che as $v) echo "<input type=hidden name=che[] value=\"".html($v)."\">";
   }
   echo "</form><script>";
   include("test_list_formedit.js");
   echo "
   //wopen('','b_edit');
   document.b_edit.submit();
   </script>";
   //refresh("{$sitepath}test_list.php?CID={$_POST['cid']}&start=$start$sess",1);
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   exit();
}


################################################################################################
#
# ЭКСПОРТИРОВАНИЕ ВОПРОСОВ:   c=main & b_export=1
#
################################################################################################


if (isset($b_export)) {

   if (!isset($che) || !is_array($che) || !count($che)) {
     $message = "<P>"._("Вы не передали вопросы, которые хотите экспортировать. Вернитесь назад и отметьте необходимые для экспорта вопросы.");
     echo $message;
     $GLOBALS['controller']->setView('DocumentBlank');
     $GLOBALS['controller']->setMessage($message, JS_GO_URL, 'test_list.php?start=0&CID='.(int) $cid);
     $GLOBALS['controller']->terminate();
     exit();
   }

   echo show_tb();
   echo ph(_("Экспортирование выбранных вопросов"));
   //echo backbutton();

   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo "<form action=export.php method=post>
   $asessf
   <input type=hidden name=c value=\"export\">";

   foreach ($che as $v) {
      echo "<input type=hidden name=qar[] value=\"".html($v)."\">";
   }

   echo "<table width=100% class=main cellspacing=0><tr><th colspan=2>"._("Экспорт вопросов")." (".count($che)." шт.)</th></tr>";
   if (is_array($che) && count($che)) {
       foreach($che as $v) {
		   # Аналогично с 385 строкой: Что за петрушка? Тут должна быть переменная $r[qdata]. В поле url совсем другие значения !!! От 20.12.2018 En
           # Вернул qdata
           $sql = "SELECT qdata FROM list WHERE kod=".$GLOBALS['adodb']->Quote($v)."";
           $res = sql($sql);
           if (sqlrows($res) && $row=sqlget($res)) {
               $parts = explode($brtag,$row['qdata']);
               echo "<tr><td nowrap>{$v}</td><td width=99%>{$parts[0]}</td></tr>";
           }
       }
       echo "<tr><td colspan=2>";
       echo okbutton();
       echo "</td></tr>";
   }
   echo "</table>";

   /*
   echo "<table class=main cellspacing=0><tr><th colspan=2>Выберите способ экспорта (".count($che)." вопросов):</th></tr>
   <tr><td><input type=radio name=send value=1 checked id=l1></td><td><label for=l1>выдать как приложение (скачать файл)</label></td></tr>
   <tr><td><input type=radio name=send value=2 id=l2></td><td><label for=l2>выдать как XML-страницу</label></td></tr>
   <tr><td><input type=radio name=send value=3 id=l3></td><td><label for=l3>выдать как plain-text страницу с XML</label></td></tr>
   <tr><td><input type=radio name=send value=4 id=l4></td><td><label for=l4>выдать как поле textarea с XML</label></td></tr>
   <tr><td colspan=2>";
   echo okbutton();
   echo"</td></tr>
   </table>";
   */
   echo "</form>";
   $GLOBALS['controller']->captureStop(CONTENT);

   echo show_tb();

   return;
}


################################################################################################
#
# УДАЛЕНИЕ ВОПРОСОВ:   c=main & b_delete=1
#
################################################################################################

if (isset($b_delete)) {
   if (!isset($che) || !count($che)) exitmsg(_("Ничего не было отмечено для удаления"),"$PHP_SELF?start=$start$sess");

   if (!$GLOBALS['controller']->enabled){
   echo "<table width=100% height=96% border=0 cellspacing=0 cellpadding=0><tr><td align=center>
   <form action='$PHP_SELF' method=post>
   <input type=hidden name=c value='delete_post'>";
   foreach ($che as $v) echo "<input type=hidden name=che[] value=\"".html($v)."\">";
   echo "
   <h3>"._("Действительно удалить вопросы?")."</h3><br>
   <input type=submit value='"._("Удалить вопросы")." (".count($che)." "._("шт.").")'>
   <br><br><a href=$PHP_SELF?start=$start$sess>"._("Не удалять")."</a></form>
   </td></tr></table>";
   } else {
   		$count = count($che);
   		$str = "";
	    foreach ($che as $v) $str .= "&che[]=" . html($v);
   		$GLOBALS['controller']->setView('DocumentBlank');
   		$GLOBALS['controller']->setMessage(_("Вы действительно желаете удалить")." {$count} "._("вопросов?")."<br>"._("Чтобы отказаться нажмите")." <a href=$PHP_SELF?start=$start>"._("здесь")."</a>", JS_GO_URL, "test_list.php?c=delete_post{$str}&CID={$cid}&theme=".urlencode($theme)."&listType=".(int) $listType);
   		$GLOBALS['controller']->terminate();
   }
      exit;
}




################################################################################################
#
# ДОБАВЛЕНИЕ ВОПРСОВ В ТЕСТ:   c=main & b_addtid=1
#
################################################################################################


if (isset($b_addtid) || isset($b_addtid_x)) {   

   if (!isset($cid)) $cid=$s[$ss][cid];
   intvals("tid cid");
/*   if (!get_test_perm_edit($tid)) {
       exitmsg("У вас не хватает привилегий","$PHP_SELF?CID={$cid}&start=$start$sess$randurl");
   }*/

   /*if (is_course_locked($cid)) {
       exitmsg(LANG_IS_LOCKED_COURSE,"$PHP_SELF?CID={$cid}&start=$start$sess$randurl");
   } */

   if ($tid<1) exitmsg(_("Не выбрано задание для добавления помеченных галочками вопросов."),"$PHP_SELF?CID={$cid}&start=$start$sess$randurl");
   $s[$ss][tid]=$tid;
   if (!isset($che) || !count($che)) exitmsg(_("Ничего не было отмечено для добавления к заданию. Отметьте любое кол-во вопросов, еще раз нажмите кнопку ДОБАВИТЬ, и все помеченные галочками вопросы будут добавлены в выбранное задание."),"$PHP_SELF?start=$start$sess");
/*   if (!isset($s[tkurs][$cid])) {
       //exitmsg("HackDetect: "._("нет прав доступа к чужому курсу"),"$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->setMessage(_('нет прав доступа к чужому курсу'), JS_GO_URL, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }*/
   //pr($che);
   if (isset($_GET['quiz_id'])) {
       $r=sqlval("SELECT * FROM quizzes WHERE quiz_id=$tid","errTL916");

   }elseif(isset($_GET['task_id'])){
        $r=sqlval("SELECT * FROM tasks WHERE task_id=$tid","errTL916");

   } else {
       $r=sqlval("SELECT * FROM test_abstract WHERE test_id=$tid","errTL916");
   }

   if (!is_array($r)) exitmsg(_("Такого теста не существует, либо вы не владеете его курсом"),"$PHP_SELF?start=$start$sess");
   //if ($r[cid]!=$r[cidowner]) exitmsg("Извините, но данный тест запрещен для редактирования, т.к. он был перенесен с другого ")

   //прооверка лимита времени
    /*
   $testTimeLimit = sqlvalue("SELECT timelimit FROM test WHERE tid='$tid'");
   if ($testTimeLimit) {
       $totalTime = 0;
       $sql = "SELECT timetoanswer FROM list WHERE kod IN ('".implode("','",$che)."')";
       $res = sql($sql);
       while ($row = sqlget($res)) {
           $totalTime += (int)$row['timetoanswer'];
       }
       if ($totalTime && $totalTime > $testTimeLimit) {
           $GLOBALS['controller']->setView('DocumentBlank');
           $GLOBALS['controller']->setMessage(_("Ограничение времени выполнения задания меньше чем суммарное ограничение времени ответа на вопросы."),JS_GO_BACK);
           $GLOBALS['controller']->terminate();
           exit();
       }
   }
     * *
     */
   $r[data]=str_replace("%","*",$r[data]);
   $r[data]=str_replace("_","?",$r[data]);


   if (trim($r[data])=="") $r[data]=array(); else $r[data]=explode($GLOBALS['brtag'],$r[data]);

   foreach ($che as $v) {
      if (!validkod($cid,$v)) {
         alert("HackDetect: "._("Вопрос")." [$v] "._("не был добавлен")." (kurs=$cid)");
         continue;
      }
/*      if (!get_question_perm_edit($v)) {
          alert("Вопрос $v не был добавлен. У вас не хватает привилегий");
          continue;
      }*/
      if (!in_array($v,$r[data])) $r[data][]=$v;
   }

   $r[data]=implode($GLOBALS['brtag'],$r[data]);
   $r[data]=str_replace("*","%",$r[data]);
   $r[data]=str_replace("?","_",$r[data]);
   if (isset($_GET['quiz_id'])) {
       $res=sql("
          UPDATE quizzes SET
             data='0',
             questions = questions + 1
          WHERE quiz_id=$tid","errTL921");

       if (dbdriver == 'oci8') {
           $GLOBALS['adodb']->UpdateClob('quizzes','data_',$r[data],"quiz_id=$tid");
       } else {
           $GLOBALS['adodb']->UpdateClob('quizzes','data',$r[data],"quiz_id=$tid");
       }

   }elseif (isset($_GET['task_id'])) {
       $res=sql("
          UPDATE tasks SET
             data='0',
             questions = questions + 1
          WHERE task_id=$tid","errTL921");

       if (dbdriver == 'oci8') {
           $GLOBALS['adodb']->UpdateClob('tasks','data_',$r[data],"task_id=$tid");
       } else {
           $GLOBALS['adodb']->UpdateClob('tasks','data',$r[data],"task_id=$tid");
       }

   } else {
       $res=sql("
          UPDATE test_abstract SET
             data='0',
             questions = questions + 1
          WHERE test_id=$tid","errTL921");

       if (dbdriver == 'oci8') {
           $GLOBALS['adodb']->UpdateClob('test_abstract','data_',$r[data],"test_id=$tid");
       } else {
           $GLOBALS['adodb']->UpdateClob('test_abstract','data',$r[data],"test_id=$tid");
       }

       sql("DELETE FROM tests_questions WHERE test_id = $tid");

       if (strlen($r[data])) {
           $questions = explode($GLOBALS['brtag'], $r[data]);
           if (is_array($questions) && count($questions)) {
               foreach($questions as $questionId) {
                   sql(sprintf("INSERT INTO tests_questions (subject_id, test_id, kod) VALUES (%d, %d, %s)", getField('test_abstract', 'subject_id', 'test_id', $tid), $tid, $questionId));
               }
           }
       }
   }

   /*
   if (isset($s[$ss][jscloseurl])) {
      unset($s[$ss][jscloseurl]);
      echo "<script>window.opener.location.reload(); window.close();</script>";
      return;
   }
   if (isset($s[$ss][goto])) {
      switch ($s[$ss][goto]) {
         case 1:
            refresh($s[$ss][gotourl]);
            unset($s[$ss][goto]);
            unset($s[$ss][gototid]);
            unset($s[$ss][gotourl]);
            exit;
      }
      unset($s[$ss][goto]);
   }
   */

   return 'Ok';

   if ($adding2close) {

   	if($GLOBALS['controller']->enabled) {
   		header("Location: /question/list/test/subject_id/{$cid}/");
        exit();
   	} else {

   	}
      return;
   }

   exitmsg(_("Вопросы добавлены успешно"), "$PHP_SELF?CID={$cid}&start=$start$sess$randurl");
}

################################################################################################
#
# ДОБАВЛЕНИЕ ВОПРСОВ В КУРС:   c=main & b_addtid=1
#
################################################################################################


if (isset($b_addcid)) {    

   if (!isset($cid)) $cid=$s[$ss][cid];
   $test_id = $_POST['test_id'];
   intvals("cidlist cid test_id");

   if ($cidlist<1) {
       $GLOBALS['controller']->setMessage(_('Не выбран курс для добавления помеченных галочками вопросов.'), false, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }
   if (!isset($che) || !count($che)) {
       $GLOBALS['controller']->setMessage(_('Ничего не было отмечено для добавления к заданию. Отметьте любое кол-во вопросов, еще раз нажмите кнопку ДОБАВИТЬ, и все помеченные галочками вопросы будут добавлены в выбранное задание.'), false, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }

   if (!isset($s['tkurs'][$cid]) || !isset($s['tkurs'][$cidlist])) {
       $GLOBALS['controller']->setMessage(_('Отсутствуют права доступа к курсу'), JS_GO_URL, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }

   $r=sqlval("SELECT CID FROM Courses WHERE CID='$cidlist'","errTL916");
   if (!is_array($r)) {
       $GLOBALS['controller']->setMessage(_('Такого курса не существует'), JS_GO_URL, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }

   $r=sqlval("SELECT tid FROM test WHERE tid='$test_id'","errTL916");
   if ($r<0) {
       $GLOBALS['controller']->setMessage(_('Такого теста не существует'), JS_GO_URL, "$PHP_SELF?start=$start$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }

   $kods = array();   
   
   foreach ($che as $v) {
      if (!validkod($cid,$v)) {
         alert("HackDetect: "._("Вопрос")." [$v] "._("не был добавлен")." (kurs=$cid)");
         continue;
      }
      if (!get_question_perm_edit($v)) {
          alert("Вопрос $v не был добавлен. У вас не хватает привилегий");
          continue;
      }
      if (!in_array($v,$kods)) $kods[]=$v;
   }

   //вытащим вопросы
   $kodsdata   = array();
   $newKodsStr = '';
   $insertsql = 'INSERT INTO `list` ';
   $sql = "SELECT * FROM list WHERE kod IN ('".implode("','", $kods)."')";
   $res = sql($sql);
   while ($row = sqlget($res)) {
       $row['created_by'] = (int) $_SESSION['s']['mid'];
       $row['last'] = time();
       $oldKod = $row['kod'];
       $row['timelimit'] = (int)$row['timelimit'];
       $row['kod'] = newQuestion($cidlist);
       //проверим есть ли прикреплённый файл
       if ($fileres = sql("SELECT * FROM file WHERE kod='$oldKod'")) {
           while ($filerow = sqlget($fileres)) {
               sql("INSERT INTO `file` (
                        kod,
                        fnum,
                        ftype,
                        fname,
                        fdata,
                        fdate,
                        fx,
                        fy
                        )
                    VALUES (
                        '{$row['kod']}',
                        '{$filerow['fnum']}',
                        '{$filerow['ftype']}',
                        '{$filerow['fname']}',
                        '',
                        '{$filerow['fdate']}',
                        '{$filerow['fx']}',
                        '{$filerow['fy']}'
                        )");
               $GLOBALS['adodb']->updateBlob('file','fdata',$filerow['fdata'], "kod='{$row['kod']}' AND fnum='{$filerow['fnum']}'");
           }
       }
       $newKodsStr .= $GLOBALS['brtag'].$row['kod'];
       //экранируем всяку бяку
       foreach ($row as $key => $value) {
           $row[$key] = $GLOBALS['adodb']->Quote($value);
       }
	   
	   // exit(var_dump($row));
	   
	   
       $kodsdata[] = "(".join(',', array_keys($row)).") VALUES (".implode(",", $row).")";
   }

   if (is_array($kodsdata) && count($kodsdata)) {
       foreach($kodsdata as $koddata) {
           $res = sql($insertsql.$koddata);
       }
   }
   
   
   
   
   //$insertsql .= implode(",", $kodsdata);

   //$res=sql($insertsql);

   //добавим новые вопросы в тест
   $data = sqlvalue("SELECT `data` FROM test WHERE tid = '$test_id'");
   $data .= $GLOBALS['brtag'].$newKodsStr;
   sql("UPDATE test SET `data` = '$data' WHERE tid = '$test_id'");

   $GLOBALS['controller']->setMessage("Вопросы добавлены успешно", false, "$PHP_SELF?CID={$cid}&start=$start$sess$randurl");
   $GLOBALS['controller']->terminate();
   exit();
}


echo "unknown command";

break; // <----- case "main"

#########################################################################################
#
# РЕДАКТИРОВАНИЕ СПИСКА ВОПРСОВ (или одного)
#
# c=b_edit & che[]=массив_вопросов
#
#########################################################################################

case "b_edit":

   $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $GLOBALS['sitepath'] . "test_list.php";

   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   //$GLOBALS['controller']->setView('Document');
   $GLOBALS['controller']->setHeader(_("Редактирование вопроса(ов)").($s[usermode]?"$vopros[kod]":""));
   $GLOBALS['controller']->setHelpSection('edit_vopros');
   echo "<script>window.focus()</script>";
   echo $html[0];
   echo ph(_("Редактирование вопроса(ов)")." ".($s[usermode]?"$vopros[kod]":""));
   echo "\n\n\n\n\n\n\n\n\n\n\n\n";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo "<script>";
   include("test_list_formedit.js");
   echo "</script>\n\n";
   echo "
   <form action=\"\" method=\"post\" enctype='multipart/form-data' name=evform onSubmit=\"tmp=document.getElementById('file_tmp'); if(tmp)tmp.disabled=true; return true;\">$sessf
   <input type=hidden name=MAX_FILE_SIZE value=20000000>
   <input type=hidden name=\"c\" value='edit_post'>
   <input type=hidden name=\"from\" value=\"edit\">
   <input type=hidden name=\"ref\" value=\"".htmlspecialchars($ref)."\">
   <input type=hidden name=start value=\"$start\">";
   if (isset($adding2tid)) {
      echo "<input type=hidden name=adding2tid value=\"$adding2tid\">";
   }
   if (isset($adding2cid)) {
      echo "<input type=hidden name=adding2cid value=\"$adding2cid\">";
   }
   if (!is_array($che) || !count($che)) exitmsg(_("Не отмечен ни один из вопросов для редактирования. Отметьте галочками один или несколько вопросов, нажмите кнопку РЕДАКТИРОВАТЬ, и откроется страница для редактирования всех помеченных вопросов."),"$PHP_SELF?start=$start$sess");
   foreach ($che as $v) {
      $v=trim($v);
      if (!preg_match("!^([0-9]{1,10})-!s",$v,$ok)) {
         $GLOBALS['controller']->setMessage(_("Вопрос")." $v "._("пропущен, код ошибочен."), JS_GO_URL, $ref);
         $GLOBALS['controller']->terminate();
         exit();
      }
     /* if (!isset($s[tkurs][$ok[1]])) {
         $GLOBALS['controller']->setMessage(_("Редактирование запрещено")."<br/>"._("Данный вопрос был создан на курсе, на котором вы не являетесь преподавателем."), JS_GO_URL, $ref);
         $GLOBALS['controller']->terminate();
         exit();
      }*/
      $res=sql("SELECT * FROM list WHERE kod=".$GLOBALS['adodb']->Quote($v)."","errTL389");
      if (!sqlrows($res)) {
         $GLOBALS['controller']->setMessage(_("Вопрос")." $v "._("пропущен, его не существует в базе данных."), JS_GO_URL, $ref);
         $GLOBALS['controller']->terminate();
         exit();
      }
      $vopros=sqlget($res);
/*      if (!$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)
          && !($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN) && ($vopros['created_by']==$_SESSION['s']['mid']))) {
          echo "<li>Вопрос $v пропущен. У вас не хватает привилегий для его редактирования<p>";
          sqlfree($res);
          continue;
      }*/
       /*
      if (is_course_question_locked($v)) {
          $GLOBALS['controller']->setMessage(_("Невозможно добавить вопрос в заблокированный курс!"), JS_GO_URL, $ref);
          $GLOBALS['controller']->terminate();
          sqlfree($res);
          exit();
          //continue;
      }  */

      sqlfree($res);
      $attach=array();
      $res=sql("SELECT fnum,ftype,fname,fdate,fx,fy,LENGTH(fdata) fsize
                FROM file WHERE kod=".$GLOBALS['adodb']->Quote($v)." ORDER BY fnum","errTL423");
      while ($r=sqlget($res)) $attach[]=$r;
      sqlfree($res);
      show_edit_list($vopros,$attach,$cid);
   }
   echo okbutton(_('Готово'));
   echo "</form>";
   echo $html[1];
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   break;

################################################################################################
#
# SUBMIT ОТ РЕДАКТИРОВАНИЯ ВОПРОСОВ
#
################################################################################################

case "edit_post": // пост от формы редактирования вопросов

   #
   # ОБРАБОТКА ВАРИАНТОВ ОТВЕТА
   #

   $kodsubmit=array();

   foreach ($form as $k=>$v) {
      $strange_code = $k;
      $kod=substr(base2kod($k),0,255);
      $kodsubmit[$kod]=$kod;
      //echo "<li>$kod";

/*      if (!isset($s[tkurs][kodintval($kod)])) {
         alert("HackDetect: "._("Вопрос")." [$kod] "._("не сохранен, отсутствуют права на этот курс."));
         continue;
      }
*/
      $data=sqlval("SELECT * FROM list WHERE kod=".$GLOBALS['adodb']->Quote($kod)."","errTL440");
      if (!$data) { echo "<li>"._("Вопрос")." [$kod] "._("не найден в базе данных"); continue; }
      $type=$data[qtype];
      include_once("template_test/$type-v.php");
      $info=$GLOBALS["v_edit_$type"];
      $vopros=array();

      if (isset($info[string]) && isset($v[string]) && count($v[string])>0) {
         foreach ($info[string] as $kk=>$vv) {
            switch ($vv[0]) {
            case "hidden":
            case "string":
            case "textarea":
               if ($kk == 'map') {//в форму не корректно передаётся карта картинки, потому берём её напрямую из базы
				   # В чем сакральный смысл замены поля qdata на url ??? От 20.12.2018 En
				   # UPD: вернул qdata
                   $dummyQData  = sqlval("SELECT qdata FROM list WHERE kod = '$kod'");
                   $dummyQData  = explode($GLOBALS['brtag'], $dummyQData['qdata']);
                   $vopros[$kk] = $dummyQData[1];
               }else {
               if (isset($v[string][$kk])) $vopros[$kk]=$v[string][$kk]; else $vopros[$kk]="";
               }
               break;
            case "integer":
               $vopros[$kk]=intval($vopros[$kk]);
               break;
            case "checkbox":
               if (isset($v[string][$kk])) $vopros[$kk]=1; else $vopros[$kk]=0;
               //alert($vopros[$kk]);
               break;
            case "radiostr":
               $vopros[$kk]=$v[string][$kk];
               break;
            }
         }
      }

      $vopros[qtema]=$v[qtema];
	  $vopros[qtema_translation]=$v[qtema_translation];
      $vopros[url]=$v[url];

      if (isset($info[variant]) && isset($v[variant]) && count($v[variant])>0) {
         foreach ($v[variant] as $kk=>$vv) {
            if (!isset($vv[kodvar]) || !isset($vv[kodvar]) || trim($vv[kodvar])=="") continue;
            $kodvar=max(0,min(32767,intval($vv[kodvar])));
            //if (!isset($kodvars[$kodvar])) continue;
            foreach ($info[variant] as $kkk=>$vvv) {
               switch ($vvv[0]) {
               case "hidden":
               case "string":
               case "textarea":
                  if (isset($vv[$kkk])) $vopros[$kkk][$kodvar]=$vv[$kkk]; else $vopros[$kkk][$kodvar]="";
                  break;
               case "integer":
                  $vopros[$kkk][$kodvar]=intval($vv[$kkk]);
                  break;
               case "checkbox":
                  if (isset($vv[$kkk])) $vopros[$kkk][$kodvar]=1; else $vopros[$kkk][$kodvar]=0;
                  break;
               case "radiostr":
                  // пропуск
                  break;
               }
            }
         }
      }

      $vopros[kod]=$kod;
      if ($info[balcalc]=='php') {
         list($vopros[balmin],$vopros[balmax])=eval($info[balfunc]);
      }
      else {
         $vopros[balmin]=doubleval($v[balmin]);
         $vopros[balmax]=doubleval($v[balmax]);
      }


      $func="v_php2sql_$type";
      $sqlarr=$func($vopros);

      if(isset($_POST["with_weight_$strange_code"]) && isset($_POST['form'][$strange_code]['weight']) && is_array($_POST['form'][$strange_code]['weight'])) {
              $weight_balmin = 'undefined';
              $weight_balmax = 'undefined';

              foreach($_POST['form'][$strange_code]['weight'] as $key => $value) {
                      if(trim($value) != "") {
                              $weight_to_base[$key + 1] = $value;

                              if (in_array($key,array_keys($_POST['form'][$strange_code]['variant']))) {
                                  switch($type) {
                                      case 1:
                                          if ($weight_balmin=='undefined') {
                                              $weight_balmin = $value;
                                          } else {
                                              $weight_balmin = ($value<$weight_balmin) ? $value : $weight_balmin;
                                          }

                                          if ($weight_balmax=='undefined') {
                                              $weight_balmax = $value;
                                          } else {
                                              $weight_balmax = ($value>$weight_balmax) ? $value : $weight_balmax;
                                          }

                                      break;
                                      case 2:
                                          $weight_balmin = process_min($weight_balmin,$value);
                                          $weight_balmax = process_max($weight_balmax,$value);
                                      break;
                                      case 11:
                                          if ($weight_balmin=='undefined') {
                                              $weight_balmin = $value;
                                          } else {
                                              $weight_balmin = ($value<$weight_balmin) ? $value : $weight_balmin;
                                          }

                                          if ($weight_balmax=='undefined') {
                                              $weight_balmax = $value;
                                          } else {
                                              $weight_balmax = ($value>$weight_balmax) ? $value : $weight_balmax;
                                          }
                                          $_questions_count = 0;
                                          foreach($vopros['variant1'] as $_variant) {
                                              $_variant = trim($_variant);
                                              if (!empty($_variant)) $_questions_count++;
                                          }
                                          $_weight_balmax = $weight_balmax*$_questions_count;
                                      break;
                                  }
                              }


                      }
              }
              if ($weight_balmin=='undefined') $weight_balmin = 0;
              if ($weight_balmax=='undefined') $weight_balmax = 0;
              if (isset($_weight_balmax)) $weight_balmax = $_weight_balmax;
              $sqlarr['balmin'] = $weight_balmin;
              $sqlarr['balmax'] = $weight_balmax;
      }

      if(isset($weight_to_base)&&(is_array($weight_to_base))/*&&(trim($weight_to_base) != "") - WTF?*/) {
              $sqlarr['weight'] = serialize($weight_to_base);
      }
      else {
              $sqlarr['weight'] = "";
      }
      if((isset($is_shuffled))&&(!empty($is_shuffled))) {
              $sqlarr['is_shuffled'] = 1;
      }
      else {
              $sqlarr['is_shuffled'] = 0;
      }
      $sqlarr['timetoanswer'] = $_POST['form'][$strange_code]['timetoanswer'];
      $sqlarr['ordr'] = $_POST['form'][$strange_code]['ordr'];
      update_list($sqlarr);
      unset($weight_to_base);

   }

   //exit(pr($form).pr($vopros).pr($sqlarr));

   #
   # ОБРАБОТКА ПАРАМЕТРОВ ФАЙЛОВ
   #
   //pr($attach);

   if (count($attach))
   foreach ($attach as $k=>$v) {
      $kod=base2kod($k);
/*      if (!isset($s[tkurs][kodintval($kod)])) {
         alert("HackDetect: "._("Параметры файла к вопросу")." [$kod] "._("не сохранены, отсутствуют права на этот курс."));
         continue;
      }*/
      foreach ($attach[$k] as $kk=>$vv) {
         if (!isset($vv[fname]) || strlen(trim($vv[fname]))==0) continue;
         if (isset($vv[delfile]) && $vv[delfile]==="ok") {
            $res=sql("
               DELETE FROM file
               WHERE kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum=".$GLOBALS['adodb']->Quote($vv[fnum])."","errTL656");
            sqlfree($res);

            continue;
         }
         $fname=preg_replace("![^][a-zа-яА-ЯЁ0-9\!@$%^&()_.,-]!i","",$vv[fname]);
         $res=sql("
            UPDATE file SET
            ftype=".$GLOBALS['adodb']->Quote($vv[ftype]).",
            fname=".$GLOBALS['adodb']->Quote($fname)."
            WHERE kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum=".$GLOBALS['adodb']->Quote($vv[fnum])."","errTL651");
         sqlfree($res);
      }
   }


   #
   # ОБРАБОТКА ФАЙЛО АПЛОДА
   #

   $upload=$_FILES[attach_file];

   if (is_array($upload[name]) && count($upload[name]))
   foreach ($upload[name] as $k=>$v) {


      foreach ($upload[name][$k] as $kk=>$vv) {

         $kod=base2kod($k);
/*         if ($upload[name][$k][$kk]=="" || $upload[tmp_name][$k][$kk]=="" || $upload[size][$k][$kk]==0) {
            continue;
         }*/
/*         if (!isset($s[tkurs][kodintval($kod)])) {
            alert("HackDetect: "._("Файл к вопросу")." [$kod] "._("не сохранен, отсутствуют права на этот курс."));
            continue;
         }*/

         $fn=$GLOBALS['wwf']."/temp/tmp_t_".session_id()."_".mt_rand(0,9999999);

         if(!move_uploaded_file($upload[tmp_name][$k][$kk],$fn)){
             continue;
         }
         //D:/SVNData/GAZPROM/public/unmanaged/temp/tmp_t_mpesdfrtd5tfq9qdk6jpll87k0_6189386
         //D:/SVNData/GAZPROM/public/unmanaged/temp/tmp_t_mpesdfrtd5tfq9qdk6jpll87k0_1331540
         //pr($fn); exit();
         if (!file_exists($fn)) {
            alert(_("Не удалось скопировать файл, нет прав записи в")." ".$GLOBALS['wwf']."/temp/");
            continue;
         }

         // Если внешний вопрос и загружается zip, то происходит распаковка zip в content/tests/KOD
         if (($type == 9) && (strstr(strtolower($upload['type'][$k][$kk]),'zip') !== false)) {

             // Распаковка zip в каталог COURSES/courceCID/content/tests/CID-ID
             // и правка путей в course.xml
             if (extractTestPackage($fn,$kod)) {

                 $packagePath = $sitepath."COURSES/course".kodintval($kod)."/content/tests/$kod";
                 $xmlFileName = $wwf."/COURSES/course".kodintval($kod)."/content/tests/$kod/course.xml";
                 if (file_exists($xmlFileName)) {

                     if ($xmlContent = file_get_contents($xmlFileName)) {

                         $xmlContent = preg_replace("/(src=\")\./i", "\$1{$packagePath}", $xmlContent);

                         if ($fp_xml = fopen($xmlFileName,'w')) {
                             fwrite($fp_xml, $xmlContent);
                             fclose($fp_xml);
                         }

                     }
                 }
                 $flashEntryPoint = "FlashExercise.swf";
                 $flashFileName = $wwf."/COURSES/course".kodintval($kod)."/content/tests/{$kod}/{$flashEntryPoint}";
                 if (file_exists($flashFileName)) {
                     sql("UPDATE list SET url =".$GLOBALS['adodb']->Quote("/content/tests/{$kod}/{$flashEntryPoint}")."  WHERE kod = '$kod'");
                 }
             }

         }

//         die();

         $buf=gf($fn);

         $fx=-1;
         $fy=-1;
         if (function_exists("getimagesize")) {
            $imsize=@getimagesize($fn);
            if (is_array($imsize) && count($imsize)>=4 && $imsize[0]>0 && $imsize[1]>0) {
               $fx=$imsize[0];
               $fy=$imsize[1];
               switch ($imsize[2]) {
                  case 1: $attach[$k][$kk][ftype]=2; break; // GIF
                  case 2: $attach[$k][$kk][ftype]=3; break; // JPG
                  case 3: $attach[$k][$kk][ftype]=4; break; // PNG
                  case 4: $attach[$k][$kk][ftype]=6; break; // SWF
               }
            }
         }

         unlink($fn);
         $fsize=strlen($buf);
         $fname=preg_replace("![^][a-zа-яА-ЯёЁ0-9\!@$%^&()_.,-]!ui","",str_replace(' ','_',$upload[name][$k][$kk]));
         $fnum=$attach[$k][$kk][fnum];
         $ftype=$attach[$k][$kk][ftype];
         if ($ftype=='autodetect') {
            if (preg_match("!([^.]+)$!",sl($fname),$ok)) $ext=$ok[1]; else $ext="";
            $ftype=$attach_unknown_type;
            foreach ($attachtype as $kkk=>$vvv) {
               if (in_array($ext,$attachtype[$kkk][2])) { $ftype=$kkk; break; }
            }
         }
         $fnum=abs(intval($fnum));
         $ftype=abs(intval($ftype));

         $cnt=sqlvalue("SELECT COUNT(*) FROM file WHERE kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum=".$GLOBALS['adodb']->Quote($fnum)."","errTL600");

         $data = unpack("H*hex", $buf);

         if (!$cnt) {

         	 if(dbdriver == "mssql" || dbdriver == "mssqlnative")
         	 $rq="
            INSERT INTO file (kod, fname, fnum, ftype, fx, fy, fdate, fdata)
            VALUES (
            ".$GLOBALS['adodb']->Quote($kod).",
            ".$GLOBALS['adodb']->Quote($fname).",
            ".$GLOBALS['adodb']->Quote($fnum).",
            ".$GLOBALS['adodb']->Quote($ftype).",
            '$fx', '$fy', '".time()."',
            0x".$data['hex'].")";
         	 else
         	 $rq="
            INSERT INTO file (kod, fname, fnum, ftype, fx, fy, fdate, fdata)
            VALUES (
            ".$GLOBALS['adodb']->Quote($kod).",
            ".$GLOBALS['adodb']->Quote($fname).",
            ".$GLOBALS['adodb']->Quote($fnum).",
            ".$GLOBALS['adodb']->Quote($ftype).",
            '$fx', '$fy', '".time()."',
            '0')";


            /*'$fy', '" . time() . "',\n\n\n
            \"".addslashes($buf)."\")"; */
         }
         else {
            if(dbdriver == "mssql" || dbdriver == "mssqlnative")
            $rq="
            UPDATE file SET
            fname=".$GLOBALS['adodb']->Quote($fname).",
            fnum=".$GLOBALS['adodb']->Quote($fnum).",
            ftype=".$GLOBALS['adodb']->Quote($ftype).",
            fdata=0x".$data['hex'].",
            fdate=".time()."
            WHERE kod=".$GLOBALS['adodb']->Quote($kod)."";
            else
            $rq="
            UPDATE file SET
            fname=".$GLOBALS['adodb']->Quote($fname).",
            fnum=".$GLOBALS['adodb']->Quote($fnum).",
            ftype=".$GLOBALS['adodb']->Quote($ftype).",
            fdata='0',
            fdate=".time()."
            WHERE kod=".$GLOBALS['adodb']->Quote($kod)."";
         }
         $res=sql($rq,"errTL618");
         sqlfree($res);

         $table = (dbdriver == "oci8") ? 'file_' : 'file';
         $buf =(dbdriver == "mssql" || dbdriver == "mssqlnative") ? "0x".$data['hex'] : $buf;
         global $adodb;
         $adodb->UpdateBlob($table, 'fdata',$buf,"kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum=".$GLOBALS['adodb']->Quote($fnum)."");

      	/*global $adodb;
      	echo "kod='$kod'";
      	var_dump($adodb->UpdateBlob('file','fdata','0x'.$buf,"kod='$kod'"));
        die();  */

      }
   }

//   echo "<xmp>$rq</xmp>";

   //return 'Ok';
   if (isset($adding2tid)) {
   	if ($GLOBALS['controller']->enabled){
   		$GLOBALS['controller']->setView('DocumentBlank');

   		//$GLOBALS['controller']->setMessage(_("Вопрос сохранен в общей базе вопросов по курсу и добавлен в задание"), JS_GO_URL, "?c=main&b_addtid=1&tid=$adding2tid&adding2close=1&cid={$s[$ss][cid]}&che[]=".ue($kod));
   		header("Location: ?c=main&b_addtid=1&tid=$adding2tid&adding2close=1&cid={$s[$ss][cid]}&che[]=".ue($kod));
        exit();
   		$GLOBALS['controller']->terminate();
   	} else {
       exitmsg(_("Вопрос добавлен успешно"), "$PHP_SELF?c=main&b_addtid=1&tid=$adding2tid&adding2close=1&cid={$s[$ss][cid]}&che[]=".ue($kod));
   	}
       return;
   }

   if (isset($adding2tid)) {


   	if ($GLOBALS['controller']->enabled){
   		$GLOBALS['controller']->setView('DocumentBlank');
   		$GLOBALS['controller']->setMessage(_("Вопрос сохранен в общей базе вопросов по курсу и добавлен в задание"), JS_GO_URL, "?c=main&b_addtid=1&tid=$adding2tid&adding2close=1&cid={$s[$ss][cid]}&che[]=".ue($kod));
   		$GLOBALS['controller']->terminate();
   	} else {
       exitmsg(_("Вопрос добавлен успешно"), "$PHP_SELF?c=main&b_addtid=1&tid=$adding2tid&adding2close=1&cid={$s[$ss][cid]}&che[]=".ue($kod));
   	}
       return;
   }

   if (isset($adding2cid)) {

   	if ($GLOBALS['controller']->enabled){
   		$GLOBALS['controller']->setView('DocumentBlank');
        //$GLOBALS['controller']->setMessage(_("Вопрос добавлен успешно").'. '._("Хотите добавить ещё вопрос?"), JS_CLOSE_SELF_GO_URL_OPENER, false, "test_list.php?c=add&start=0", "javascript:opener.location.href='test_list.php?CID=$adding2cid'; window.close();");
   		$GLOBALS['controller']->setMessage(_("Вопрос добавлен успешно").'. '._("Хотите добавить ещё вопрос?"), JS_CLOSE_SELF_GO_URL_OPENER,"test_list.php?c=add&start=0", "test_list.php?c=add&start=0", $GLOBALS['sitapth']."test_list.php?CID=$adding2cid");
   		$GLOBALS['controller']->terminate();
   	} else {
       exitmsg("Вопрос добавлен успешно", "test_list.php?CID=$adding2cid");
   	}
       return;
   }

   if (isset($_POST['from']) && ($_POST['from']=='edit')) {
       return 'Ok';
       $GLOBALS['controller']->setView('DocumentBlank');
   	   $GLOBALS['controller']->setMessage(_("Вопрос успешно сохранен"), JS_GO_URL, $ref); //JS_GO_URL,'javascript:window.close();');
   	   $GLOBALS['controller']->terminate();
       return;
   }

   if ($GLOBALS['controller']->enabled){
   		$GLOBALS['controller']->setView('DocumentBlank');
   		$GLOBALS['controller']->setMessage(_("Вопрос сохранен в общей базе вопросов по курсу"), JS_GO_URL, 'test_list.php?start=0');
   		$GLOBALS['controller']->terminate();
   } else {
   		echo "<script>window.close();</script>";
   }
   return;
   //exit;


################################################################################################


case "delete_vopros":

   $cid=kod2cid($kod);
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("ошибочный номер курса")." (errTL1628)");
       $GLOBALS['controller']->setMessage(_('Выбран некорректный номер курса'));
       $GLOBALS['controller']->terminate();
       exit();
   }

   if (!get_question_perm_edit($kod)) {
       exitmsg("У вас не хватает привилегий","$PHP_SELF?CID={$CID}&start=$start&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   }

   if (is_course_question_locked($kod)) {
       exitmsg(LANG_IS_LOCKED_COURSE,"$PHP_SELF?CID={$CID}&start=$start&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   }
   $res=sql("DELETE FROM list WHERE kod=".$GLOBALS['adodb']->Quote($kod)."","errTL1629");
   sqlfree($res);

   // Удаление файлов вопроса если была распаковка архива с данными вопроса
   $packageDir = $wwf."/COURSES/course".kodintval($kod).'/'."content/tests/$kod";
   if (file_exists($packageDir)) removeDir($packageDir);

   // Удаление файлов из таблицы file
   $res=sql("DELETE FROM file WHERE kod=".$GLOBALS['adodb']->Quote($kod)."");
   sqlfree($res);

   refresh("$PHP_SELF?CID={$CID}&start=$start&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   return;


case "setmode":

   $s[usermode]=abs(intval($mode))%2;
   location("$PHP_SELF?$sess");
   exit;

case "setnum":

   $s[$ss][limit]=abs(intval($num))%5001;
   if ($s[$ss][limit]<5) $s[$ss][limit]=5;
   location("$PHP_SELF?CID={$CID}&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   exit;

case "setorder":

   if (isset($ordermenu[$order])) $s[$ss][order]=$order;
   location("$PHP_SELF?$sess");
   exit;

case "setdesc":

   if ($desc==0) $s[$ss][desc]=''; else $s[$ss][desc]=" DESC ";
   location("$PHP_SELF?$sess");
   exit;

case "settime":

   echo show_tb();
   echo "<h3>"._("Ограничение времени при просмотре вопросов")."</h3>";
   echo "
   <a href=$PHP_SELF?$sess>&lt;&lt; "._("назад")."</a>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Ограничение времени при просмотре вопросов"));
   echo "<P>
   "._("Быстрый выбор:")."<br>
   <li><a href=$PHP_SELF?c=settime3&time1=$ss_time1&time2=$ss_time2$sess>"._("Снять ограничение по дате")."</a>
   <li><a href=$PHP_SELF?c=settime3&time1=".(time()-3600)."&time2=".mktime(0,0,0,1,1,2034)."$sess>"._("За последний час")."</a>
   <li><a href=$PHP_SELF?c=settime3&time1=".(time()-3600*24)."&time2=".mktime(0,0,0,1,1,2034)."$sess>"._("За последний день")."</a>
   <li><a href=$PHP_SELF?c=settime3&time1=".(time()-3600*24*7)."&time2=".mktime(0,0,0,1,1,2034)."$sess>"._("За последнюю неделю")."</a>
   <li><a href=$PHP_SELF?c=settime3&time1=".(time()-3600*24*31)."&time2=".mktime(0,0,0,1,1,2034)."$sess>"._("За последний месяц")."</a>
   <form action=$self method=get>$sessf
   <input type=hidden name=c value='settime2'>
   "._("Произвольный выбор времени")."<br>"._("от")."
   <input type=text name=time1 value=\"".date("d/m/Y H:i",$s[$ss][time1])."\">
   "._("до")."
   <input type=text name=time2 value=\"".date("d/m/Y H:i",$s[$ss][time2])."\"><br><br>
   <input type=submit value='"._("Установить")."'>
   </form>

   ";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;


case "settime2":

   if (preg_match("!([0-9]{1,2})[^0-9]+([0-9]{1,2})[^0-9]+([0-9]{1,4})[^0-9]+([0-9]{1,2})[^0-9]+([0-9]{1,2})!",$time1,$ok))
   {
      $s[$ss][time1]=mktime($ok[4],$ok[5],0,$ok[2],$ok[1],$ok[3]);
      //echo "1=".mktime($ok[4],$ok[5],0,$ok[2],$ok[1],$ok[3]);
      //pr($ok);
   }

   if (preg_match("!([0-9]{1,2})[^0-9]+([0-9]{1,2})[^0-9]+([0-9]{1,4})[^0-9]+([0-9]{1,2})[^0-9]+([0-9]{1,2})!",$time2,$ok))
   {
      $s[$ss][time2]=mktime($ok[4],$ok[5],0,$ok[2],$ok[1],$ok[3]);
      //echo "2=".mktime($ok[4],$ok[5],0,$ok[2],$ok[1],$ok[3]);
      //pr($ok);
   }

   location("$PHP_SELF?$sess");
   exit;


case "settime3":

   $s[$ss][time1]=abs(intval($time1));
   $s[$ss][time2]=abs(intval($time2));
   location("$PHP_SELF?$sess");
   exit;



case "kodedit":

   echo show_tb();

   $buf=implode("\r\n",$s[$ss][kod]);
   $buf=str_replace("%","*",$buf);
   $buf=str_replace("_","?",$buf);

   echo "<h3>"._("Фильтр кодов при просмотре вопросов")."</h3>
   <a href=$PHP_SELF?$sess>&lt;&lt; "._("назад")."</a>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Фильтр кодов при просмотре вопросов"));
   echo "
   <P>
   "._("Впишите список масок кодов или конкретные коды вопросы, на которые
   вы хотите поставить фильтр. Тогда в окне просмотра вопросов будут
   только эти описанные вопросы. Для составления масок используйте")."
   <font color=red><b>?</b></font> и <font color=red><b>*</b></font>.
   "._("Маски отделяйте друг от друга пробелами или новыми строчками.")."<P>

   <form action=$self method=post>$sessf
   <input type=hidden name=c value='kodset'>
   <textarea name=buf rows=10 cols=50 style='width: 100%'>".html($buf)."</textarea>
   <input type=submit value='"._("Установить новый фильтр")."'>
   </form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;


case "kodset":


   $buf=preg_replace("![^a-zA-Z0-9*? \n-]!","",$buf);
   $buf=trim(preg_replace("![ \n]+!"," ",$buf));
   $buf=str_replace("*","%",$buf);
   $buf=str_replace("?","_",$buf);
   $buf=explode(" ",$buf);
   $s[$ss][kod]=$buf;
   location("$PHP_SELF?$sess");
   exit;


case "kodclean":

   $s[$ss][kod]=array();
   location("$PHP_SELF?$sess");
   exit;



case "selectkurs":

   echo show_tb();
   echo ph(_("Выбор другого курса для редактирования вопросов"));
   echo "
   <form action=$PHP_SELF method=post name=m>$sessf
   <input type=hidden name=c value=\"selectkurs_submit\">
   <span class=\"tests\">"._("Выберите другой курс:")."</span><br><br>
   <select name=newcid size=14 style=\"width:100%\">";

   $res=sql("SELECT * FROM Courses WHERE cid IN (".implode(",",$s[tkurs]).") ORDER BY CID","errTT243");
   while ($r=sqlget($res)) echo "<option value=$r[CID]>$r[Title]".($s[usermode]?"&nbsp;($r[CID])":"");

   echo "</select><br><br>
            <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
                        <tr>
                            <td align=\"right\" valign=\"top\"><input type=\"image\" name=\"ok\" onmouseover=\"this.src='".$sitepath."images/send_.gif';\" onmouseout=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\"></td>
                        </tr>
            </table>
   </form>";

   echo show_tb();
   exit;


case "selectkurs_submit":

   intvals("newcid");
   if ($newcid==0) exitmsg(_("Ничего не выбрано"),"$PHP_SELF?c=selectkurs$sess");
   if (!isset($s[tkurs][$newcid])) {
       //exit("HackDetect: "._("нет прав перейти на чужой курс"));
       $GLOBALS['controller']->setMessage(_('нет прав перейти на чужой курс'), JS_GO_URL, 'test_list.php');
       $GLOBALS['controller']->terminate();
       exit();
   }
   $s[$ss][cid]=$newcid;
   refresh("$PHP_SELF?$sess");
   exit;


case "delete_post":

   if (!is_array($che) || !count($che)) exit("errTL680");
   $che_tmp = $che;
   if (is_course_question_locked(array_pop($che))) {
       exitmsg(LANG_IS_LOCKED_COURSE,"$PHP_SELF?CID={$CID}&start=$start&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   }
   $che = $che_tmp;

   $rq.="DELETE FROM list WHERE kod IN ( ";
   foreach ($che as $v) {
      if (!isset($s[tkurs][kodintval($v)])) {
         alert("HackDetect: "._("Вопрос")." [$v] "._("не удален, вы не владеете этим курсом"));
         continue;
      }
      if (!get_question_perm_edit($v)) {
          alert(sprintf(_("Вопрос %s не удалён. У вас не хватает привилегий"), $v));
          continue;
      }
      $kods2del[] = $v;
      $rq.="".$GLOBALS['adodb']->Quote($v).",";
   }
   $rq=substr($rq,0,-1)." )";
   if (is_array($kods2del) && count($kods2del)) {
       $res=sql($rq,"errTL690");
       sqlfree($res);
   }
   refresh("$PHP_SELF?CID={$CID}&start=$start&theme=".urlencode($theme)."&listType=".(int) $listType."$sess");
   exit;


################################################################################################
#
# Добавить вопрос
#
################################################################################################

case "add":   

   if (!isset($cid)) $cid=$s[$ss][cid];
   /*intvals("cid");
   if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: нет прав на этот курс","$PHP_SELF?$sess");

   if (!$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN)
       && !$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)) {
       exitmsg("У вас не хватает привилегий","$PHP_SELF?CID={$cid}&start=$start$sess");
   }*/

   if (isset($goto)) {  
      $s[$ss]['goto']=intval($goto);
      $s[$ss]['gotourl']=$gotourl;
      $s[$ss]['gototid']=$gototid;
   }

  // echo "|||||||||". show_tb()."|||||||||";
  
 
   echo ph(_("Добавить вопрос"));
   echo "<a href=test_list.php?start=$start$sess>"._("Вернуться ко всем вопросам")."</a><P>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Добавить вопрос"));
   $GLOBALS['controller']->setHelpSection('add_vopros');

   $tid = isset($_GET['tid']) ? $_GET['tid'] : 0;
   
   // $reflFunc = new ReflectionFunction('html_new_question');
   // exit(var_dump( $reflFunc->getFileName() . ':' . $reflFunc->getStartLine() ));

   html_new_question($cid,$tid,$tid,1);

   $GLOBALS['controller']->captureStop(CONTENT);
// Тут какая-то шляпа с этой функцией. Завтра найти.
 //  exit(var_dump( show_tb() ));
   $GLOBALS['controller']->terminate();
   break;



################################################################################################
#
# ПОСТ от добавления вопроса
#
# если есть adding2tid=номер - то вопрос добавить в это задание
#
################################################################################################


case "add_submit":
   // вставка нового вопроса

//   insertQuestion( $cid, $kod, )
   $upload=$_FILES['f_attach'];
   //pr($upload); exit;

   intvals("cid");

  /* if (!isset($s[tkurs][$cid]))
      exitmsg("HackDetect: "._("вы не можете создавать вопросы на чужих курсах"),"$PHP_SELF?$sess");

   if (!isset($kod) || strlen($kod)<2)
      exitmsg(_("Вы не ввели код нового вопроса"),"$PHP_SELF?c=add$sess");

   if (!isset($type) || !isset($qtypes[$type]))
      exitmsg("Вы не задали тип нового вопроса","$PHP_SELF?c=add$sess");


   if (!$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)
       && !$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN)) {
       exitmsg("У вас не хватает привилегий","$PHP_SELF?$sess");
   }*/


   //sql("LOCK TABLES `list` WRITE, conf_cid WRITE, file WRITE");

  // echo $kod."<br>";

   if ($kod=="autoindex") {  // если код вопроса создается автоматически
      $kod = newQuestion( $cid );
   }
   else {
      $kod=trim($kod);
      if (!preg_match("!^$cid-!si",$kod)) {
         alert(_("Выбран недопустимый код вопроса. Вопрос должен начатся с номера курса и
         символа тире (минус). После номера и тире допишите необходимые символы в код
         вороса. Например, можно придумать такой код:")."
         $cid-lesson".mt_rand(1,99)."-q".mt_rand(1,999),"$PHP_SELF?c=add$sess");
         close_window();
      }
      if (preg_match("![^a-zA-Z0-9_-]!",$kod)) {
         alert(_("Код вопроса должен состоять только из анлийских букв (большие
         и маленькие буквы различаются), цифр, символов 'тире' (минус) и 'подчеркивание'."),
         "$PHP_SELF?c=add$sess");
         close_window();
      }
      $cnt=sqlvalue("SELECT COUNT(*) FROM list WHERE kod=".$GLOBALS['adodb']->Quote($kod)."","errTL756");
      if ($cnt>0) {
         alert(_("К сожалению, вопрос с кодом")." $kod "._("уже существует в база данных.
         Если вы хотите изменить этот вопрос - воспользуйтесь редактированием вопросов.
         Чтобы создать вопрос, придумайте любой другой код."));
         close_window();
      }
   }

   if (strlen($f_tema)>255) alert(_("Вы ввели слишком длинную тему. Максимальная длина 255 символов. Тема укорочена до этой границы."));
   if (strlen($f_tema_en)>255) alert(_("Вы ввели слишком длинную тему. Максимальная длина 255 символов. Тема укорочена до этой границы."));

   include_once("template_test/$type-v.php");
   $func="v_php2sql_$type";
   $vopros=$GLOBALS["v_edit_$type"]['default'];
   $vopros['kod']=$kod;
   $vopros['balmin']=0;
   $vopros['balmax']=1;
   $vopros['vopros']=$f_vopros;
   $vopros['vopros_en']=$f_vopros_en;
   //if ( $vopros['balmin']== $vopros['balmax'] ) $vopros['qmoder']=1;
   $arrsql=$func($vopros);

   if((isset($is_shuffled))&&(!empty($is_shuffled))) {
                   $arrsql['is_shuffled'] = 1;
   }
   else {
                   $arrsql['is_shuffled'] = 0;
   }


   $rq="INSERT INTO list (";
   $arrsql['last']         = time();
   $arrsql['qtema']        = $f_tema;
   $arrsql['qtema_translation'] = $f_tema_en;
   $arrsql['timetoanswer'] = (int) $arrsql[timetoanswer];

   foreach ($arrsql as $k=>$v) {
      $rq.="`$k`,";
   }
   $rq=substr($rq,0,-1);

   $rq.= ",created_by) values (";

   global $adodb;
   foreach ($arrsql as $k=>$v) {
      $rq.= $adodb->Quote($v).",";
   }
   $rq=substr($rq,0,-1);
   $rq.= ",'".(int) $_SESSION['s']['mid']."')";


//   $vopros['vopros'].=$rq;
 //   echo "INSERT: $rq <BR>";
   $res=sql($rq,"errTL778");
   sqlfree($res);


   #
   # Обработка файлоаплода
   #
   for ($iii=0; $iii<1; $iii++) {
      if ($upload[name]=="" || $upload[tmp_name]=="" || $upload[size]==0) break;
		  $fn=$GLOBALS['wwf']."/temp/tmp_t_".session_id()."_".mt_rand(0,9999999);//$fn="$tmpdir/tmp_t_".session_id()."_".mt_rand(0,9999999);
      move_uploaded_file($upload[tmp_name],$fn);
      if (!file_exists($fn)) {
         alert(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir");
         break;
      }
      $buf=gf($fn);

      $fx=-1;
      $fy=-1;
      if (function_exists("getimagesize")) {
         $imsize=@getimagesize($fn);
         if (is_array($imsize) && count($imsize)>=4 && $imsize[0]>0 && $imsize[1]>0) {
            $fx=$imsize[0];
            $fy=$imsize[1];
            switch ($imsize[2]) {
               case 1: $attach[$k][$kk][ftype]=2; break; // GIF
               case 2: $attach[$k][$kk][ftype]=3; break; // JPG
               case 3: $attach[$k][$kk][ftype]=4; break; // PNG
               case 4: $attach[$k][$kk][ftype]=6; break; // SWF
            }
         }
      }

      unlink($fn);
      $fsize=strlen($buf);
      $fname=preg_replace("![^][a-zа-яА-ЯёЁ0-9\!@$%^&()_.,-]!i","",$upload[name]);
      $fname='comment_file_'.$fname;
      $fnum=1;
      if (preg_match("!([^.]+)$!",sl($fname),$ok)) $ext=$ok[1]; else $ext="";
      $ftype=$attach_unknown_type;
      foreach ($attachtype as $k=>$v) {
         if (in_array($ext,$attachtype[$k][2])) { $ftype=$k; break; }
      }
      $ftype=abs(intval($ftype));

      /*
      $data = unpack("H*hex", $buf);

      $rq="
      INSERT INTO file (kod, fname, fnum, ftype, fx, fy, fdata, fdate)
      values (
      '".addslashes($kod)."',
      '".addslashes($fname)."',
      '".addslashes($fnum)."',
      '".addslashes($ftype)."',
      $fx,
      $fy,
      0x".$data['hex'].",
      '".time()."')";

      $res=sql($rq,"errTL1586");
      */

      $data = unpack("H*hex", $buf);

      if(dbdriver == "mssql" || dbdriver == "mssqlnative")
      $rq="
      INSERT INTO file (kod, fname, fnum, ftype, fx, fy, fdata, fdate)
      values (
      ".$GLOBALS['adodb']->Quote($kod).",
      ".$GLOBALS['adodb']->Quote($fname).",
      ".$GLOBALS['adodb']->Quote($fnum).",
      ".$GLOBALS['adodb']->Quote($ftype).",
      $fx,
      $fy,
      0x".$data['hex'].",
      '".time()."')";
      else
      $rq="
      INSERT INTO file (kod, fname, fnum, ftype, fx, fy, fdata, fdate)
      values (
      ".$GLOBALS['adodb']->Quote($kod).",
      ".$GLOBALS['adodb']->Quote($fname).",
      ".$GLOBALS['adodb']->Quote($fnum).",
      ".$GLOBALS['adodb']->Quote($ftype).",
      $fx,
      $fy,
      0x".$data['hex'].",
      '".time()."')";

      $res=sql($rq,"errTL1586");

      $table = (dbdriver == "oci8") ? 'file_' : 'file';
      $buf =(dbdriver == "mssql" || dbdriver == "mssqlnative") ? "0x".$data['hex'] : $buf;
      if(dbdriver!="mysql")
		$adodb->UpdateBlob($table, 'fdata',$buf,"kod=".$GLOBALS['adodb']->Quote($kod)."");

      sqlfree($res);
      break;
   }

   refresh("$PHP_SELF?che[]=".ue($kod)."&c=b_edit".(!empty($adding2tid)?"&adding2tid=$adding2tid":"").(!empty($adding2cid)?"&adding2cid=$adding2cid":"")."$sess");

   exit();





case "sortrow":

  switch ($label) {
     case 1:
        $sortrows=explode(" ","kod qtype qtema balmax balmin last");
        if (!in_array($sortname,$sortrows)) exit("Error row name");
        if ($s[$ss][order]==$sortname) {
           if ($s[$ss][desc]=="") $s[$ss][desc]=" DESC "; else $s[$ss][desc]="";
        }
        $s[$ss][order]=$sortname;
        break;
     default:
        exit("unknown label for sortrow");
  }

  refresh($url);
  exit;



case "course_browse":

   $GLOBALS['controller']->setView('DocumentPopup');
   $GLOBALS['controller']->setHeader(_('Выбор модуля'));
   $GLOBALS['controller']->captureFromOb(CONTENT);

   if (!defined('COURSES_DIR_PREFIX')) {
       define('COURSES_DIR_PREFIX','');
   }

   function show_course_dir($path,$cid,$formname) {
      $path.="/";
      $path=preg_replace("!//+!","/",$path);
      $addon = $GLOBALS['wwf'].'/';
      if (strlen(COURSES_DIR_PREFIX)) {
          $addon = $GLOBALS['wwf'].COURSES_DIR_PREFIX.'/';
      }
      $urlname=str_replace($addon."COURSES/course$cid/mods/","~/",$path);
      $url=$path;
      $dir=@opendir($path);
      if (!$dir) return;
      while ($fn=readdir($dir)) {
         if ($fn[0]==".") continue;
         if (is_dir($path.$fn)) {
            echo "<b>$fn</b>:<ul>";
            show_course_dir($path.$fn,$cid,$formname);
            echo "</ul>";
            continue;
         }
         echo "<input type=radio name=formname value='$formname' onclick='lu.value=\"$urlname$fn\"'>
         <A href='$url$fn' target=_blank>$fn</a><br>";
      }
   }

   $addon = $GLOBALS['wwf'].'/';
   if (strlen(COURSES_DIR_PREFIX)) {
       $addon = $GLOBALS['wwf'].COURSES_DIR_PREFIX.'/';
   }

   $cid=intval($cid);

   $path=$addon."COURSES/course$cid/mods/";
   $dir=@opendir($path);
   if (!$dir) exit("");

   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];
   echo ph(_("Выбор модуля"));
   echo "<font class=10>
   "._("Найдите нужный файл и ссылка на него будет вставлена автоматически")."
   <form action=$PHP_SELF name='urlform'>
   <input type=hidden name=c value=\"browse_post\">
   <input type=hidden name=cid value=\"$cid\">
   <input type=hidden name=urlname id=lu value=\"\">
   ";

   while ($fn=readdir($dir)) {
      if ($fn[0]==".") continue;
      if (!is_dir($path.$fn) || strval(intval($fn))!==$fn || $fn<1) continue;
      $fn=intval($fn);
      $t=sqlvalue("SELECT Title FROM mod_list WHERE ModID=$fn","errTL1235");
      if ($t) echo "<li><b>html($t)</b> <small>($fn)</small><ul>";
      else continue;
      show_course_dir($path.$fn,$cid,$formname);
      echo "</ul>";
   }
   closedir($dir);
   echo "</ul>";
   echo okbutton();
   echo "</form>";
/*   echo "</ul>
   <p align=right><input type=\"image\" name=\"ok\"
   onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
   onmouseout=\"this.src='".$sitepath."images/send.gif';\"
   src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\"></p>
   </form>";
*/



   echo $html[1];

   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   break;



case "browse_post":

   if($urlname!="")
   echo $name."
   <script>
   x=opener.document.getElementById(\"url_$formname\")
   x.value=\"$urlname\"
   window.close()
   </script>";
   else
   echo $name."
   <script>
   window.close()
   </script>";

   break;

case "delete_attach":

   intvals("fnum");
   $cid=kod2cid($kod);
   //if (!isset($s[tkurs][$cid])) exit("HackDetect: err1644");
   if ($_SESSION['s']['perm'] < 2) exit();

   $res=sql("DELETE FROM file WHERE kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum=$fnum","errTL1647");
   sqlfree($res);

   // Удаление файлов вопроса если была распаковка архива с данными вопроса
   $packageDir = $wwf."/COURSES/course".kodintval($kod).'/'."content/tests/$kod";
   if (file_exists($packageDir)) removeDir($packageDir);

   echo "<script>opener.location.reload(); window.close()</script>";

   break;

}





#########################################################################################
#
# ПОКАЗ ФОРМЫ РЕДАКТИРОВАНИЯ ВОПРОСЫ
#
#########################################################################################

function show_edit_list ($vopros, $attach) {

   global $attachtype,$s,$test_list_qincr,$test_list_qall, $image_maxx, $image_maxy;



   if( (isset($vopros['weight'])) && (trim($vopros['weight']) != "") ) {
           $with_weight = 1;
           $vopros_weight = unserialize(stripslashes($vopros['weight']));
   }
   elseif(isset($_GET['quiz_id'])) {
           $with_weight = 1;
   }
   else{
   		$with_weight = 0;
   }


   $kod=$vopros[kod];
   $base=kod2base($vopros[kod]);
   $cid=kodintval($kod);

//   global $switch0,$switch1,$border0,$border1;

//   $switch0="#ffffff"; // цвет фона выключенной галочки "вариант включен"
//   $switch1="#aaaacc"; // цвет фона активной галочки "вариант включен"
//   $border0="#cccccc"; // цвет фона бордюра неактивных элементов ввода текста
//   $border1="#883300"; // цвет фона бордюра активных элементов ввода текста

   $type=intval($vopros[qtype]);
   include_once("template_test/{$type}-v.php");
   $info=$GLOBALS["v_edit_".$type];

   if (!isset($info)) {
      echo "<P>"._("Ошибка в программе: нет данных о струкруре типа вопроса")." N$type. "._("Показать форму редактирования для вопроса")." $vopros[kod] "._("невозможно.")."<P>";
      return;
   }
   $func="v_sql2php_$type";
   $data=$func($vopros);
   //$data_en=$func($vopros_en);
   //pr($data);
   $vkey_rand=array();

   // вставлка инклюдов в форму редактировая вопроса
   $path="template_test/";
   if (count($info['includes']))
   foreach ($info['includes'] as $v) {
      if ($v[0]!="formedit") continue;
      switch ($v[2]) {
         case "include_once": { include_once($path.$v[1]); break; }
         case "include": { include($path.$v[1]); break; }
         case "readfile": { readfile($path.$v[1]); break; }
      }
   }




   ###############################
   #
   # <Основные параметры>
   #

   echo "\n\n\n\n\n\n<table width=740 class=main cellspacing=0>";

   $tdclass=" class=shedaddform ";
   $inputclass=" class=lineinput2 ";
   $istr=($s[usermode]) ? "" : "</tr><tr class=questt>";

   echo "\n\n<tr><th align=right colspan=3>".((empty($_GET['task_id'])) ? _("Редактирование вопроса") : _("Редактирование варианта задания"))."</th></tr>";

   //echo "<tr><td colspan=2>";

   //
   // вопрос
   //
   echo "\n\n<tr><td $tdclass colspan=3>". ((empty($_GET['task_id'])) ? _("Формулировка вопроса:") : _("Формулировка варианта задания:")) . "<br>";
   echo "<textarea name='form[$base][string][vopros]' $inputclass style='width:100%;height:50px' rows=4 cols=50>".
      html($data[vopros])."</textarea>\n";
   echo "</td></tr>";

 /*  echo "\n\n<tr><td $tdclass colspan=3>". ((empty($_GET['task_id'])) ? _("Формулировка вопроса(en):") : _("Формулировка варианта задания(en):")) . "<br>";
   echo "<textarea name='form[$base][string][vopros_en]' $inputclass style='width:100%;height:50px' rows=4 cols=50>".
      html($data_en[vopros_en])."</textarea>\n";
   echo "</td></tr>";*/
  
   // начало 2й вложенной таблицы
   //if(!isset($_GET['quiz_id'])){
	   echo "<tr><td colspan=2>".
	   "<table width=100% border=0 cellspacing=0 cellpadding=0>";
   //}

   //
   // тема
   //
   //if(!isset($_GET['quiz_id'])){
	   echo "\n\n<tr><td width=1% nowrap $tdclass>"._("Тема")."</td><td $tdclass>";
	   echo "<input class=lineinput size=40 type=text name='form[$base][qtema]' value=\"".
	      html($vopros[qtema])."\" maxlength=255>";
	   echo "</td></tr>";
	   
	   	   echo "\n\n<tr><td width=1% nowrap $tdclass>"._("Тема(en)")."</td><td $tdclass>";
	   echo "<input class=lineinput size=40 type=text name='form[$base][qtema_translation]' value=\"".
	      html($vopros[qtema_translation])."\" maxlength=255>";
	   echo "</td></tr>";
   //}

   //
   // порядок следования
   //<input class=lineinput size=5 type=text name='form[$base][balmax]' value=\"$vopros[balmax]\">
    echo "\n\n<tr><td width=1% nowrap $tdclass>"._("Порядок следования")."</td><td $tdclass>";
    echo "<input class=lineinput size=5 type=text name='form[$base][ordr]' value=\"$vopros[ordr]\">";
    echo "</td></tr>";
   //
   // время прохождения вопроса
   //
   if(!isset($_GET['quiz_id'])){
	   echo "\n\n<tr><td width=1% nowrap $tdclass>".((empty($_GET['task_id'])) ? _("Время на ответ (в мин.):") : _("Время на решение (в мин.):"))."</td><td $tdclass>";
	   echo "<input class=lineinput size=40 type=text name='form[$base][timetoanswer]' value=\"".
	      html($vopros[timetoanswer])."\" maxlength=255>&nbsp;(0 - "._("без ограничения").")";
	   echo "</td></tr>";
   }
   else{
   		echo "<input type=hidden name='form[$base][timetoanswer]' value=0>";
   }

   //
   // ссылка
   //
   if(!isset($_GET['quiz_id'])){
	   if (!in_array($vopros['qtype'],array(10))) {
	           echo "\n\n<tr><td $tdclass>"._("Ссылка")."</td><td $tdclass>";
	           echo "<input class=lineinput size=40 type=text id='url_$base' name='form[$base][url]' value=\"".
	              html($vopros[url])."\">";
	           echo "<!--input class=s8 style='width:75px' type=button value='  "._("Обзор")."...  ' ".
	           "onClick='wopen(\"test_list.php?c=course_browse&cid=$cid&formname=$base$asess\",\"cbrowse\");return 0'-->";
	           echo "</td></tr>";
	   }
   }
   //
   // баллы
   //
   $cheid="x".substr(md5(microtime().mt_rand(0,9999999)),0,7);
   echo "<script language='javascript'>
                     function change_form_$base(whereClick) {
                             var s = document.getElementsByName('form[$base][weight][]');";
   if ($vopros['qtype']!=2)
   echo "                    var p = document.getElementsByName('form[$base][string][otvet]');";
   echo "
                             switch(whereClick) {
                                     case 'weight':
                                             for(i=0;i<s.length;i++) {
                                                 try {
                                                     s[i].disabled = false;";
  if ($vopros['qtype']!=2)
  echo "                                             p[i].disabled = true;";
  echo "                                         } catch (e) {
                                                 // error
                                                 }
                                             }";
  if (!isset($_GET['quiz_id']))             echo "document.getElementById('bal_interval_$base').style.visibility = 'hidden';";
  if ($vopros['qtype']==2)
  echo "                                     var p;
                                             for(i=0;i<s.length;i++) {
                                                if (p = document.getElementsByName('form[$base][variant]['+i+'][otvet]')) {
                                                    try {
                                                        p[0].disabled = true;
                                                    } catch(e) {
                                                        // error
                                                    }
                                                }
                                             }";
  echo "
                                     break;
                                     case 'true_answer':
                                             for(i=0;i<s.length;i++) {
                                                 try {
                                                     s[i].disabled = true;";
  if ($vopros['qtype']!=2)
  echo "                                             p[i].disabled = false;";
  echo "                                         } catch(e) {
                                                  // error
                                                 }
                                             }";
  if (!isset($_GET['quiz_id']))             echo "document.getElementById('bal_interval_$base').style.visibility = 'visible';";
  echo "                                     var p;
                                             for(i=0;i<s.length;i++) {
                                                if (p = document.getElementsByName('form[$base][variant]['+i+'][otvet]')) {
                                                    try {
                                                        p[0].disabled = false;
                                                    } catch (e) {
                                                        // error
                                                    }
                                                }
                                             }
                                             var cheid;
                                             for(i=0;i<s.length;i++) {
                                                cheid = '".$cheid."_'+i;
                                                val=((document.getElementById(cheid+'_xswitch').checked)?0:1);
                                                document.getElementById(cheid+'_otvet').disabled=val;
                                                document.getElementById(cheid+'_variant').disabled=val;
                                                document.getElementById(cheid+'_kodvar').disabled=val;
                                             }
                                             ";
  echo "
                                     break;
                             }

                     }
                   </script>";
   //echo "form[$base][string][otvet]";
//   if($vopros[qtype] == 1) {

  if(in_array($vopros[qtype],array(1,2))) {
  	if(!isset($_GET['quiz_id'])){
  		echo "<tr><td colspan='2'><nobr>
           <input type='radio' id=\"with_weight\" name='with_weight_$base'
           onClick=\"javascript: change_form_$base('weight');\" ".($with_weight?"checked":"")." /> " . _("с весами ответа")
  		. "
           <input type='radio' name='with_weight_$base' ".($with_weight?"":"checked")."
           onClick=\"javascript: change_form_$base('true_answer');\" /> " . _("с правильным ответом").'
           </nobr><td></tr>';
  	}
  	else{
  		echo '<input type="hidden" name="with_weight_'.$base.'" value=1>';
  	}
  }

   if (in_array($vopros['qtype'],array(11))) {
       echo "<tr><td colspan=2><input type=\"hidden\" name=\"with_weight_$base\" value=\"1\"></td></tr>";
   }

  // echo "\n\n<tr><td $tdclass colspan=2>";

   if(!isset($_GET['quiz_id'])){
   	   echo "<tr id='bal_interval_$base' style='visibility: ".($with_weight?"hidden":"visible")."'><td><span>"._("Диапазон баллов:")." </td><td>";

	   if ($info[balcalc]=='php') {
	      echo _("от")." $vopros[balmin] "._("до")." $vopros[balmax] ("._("автоподсчет").")";
	   }
	   else {
	      echo "

	      "._("от")." <input class=lineinput size=5 type=text name='form[$base][balmin]' value=\"$vopros[balmin]\">
	      "._("до")." <input class=lineinput size=5 type=text name='form[$base][balmax]' value=\"$vopros[balmax]\">
	      ";
	   }

	   echo "</span>";
	   if (in_array($vopros['qtype'], array(12, 9))) {
	       echo "<input type='hidden' name = 'is_shuffled' value='1' checked />";
	   }
	   echo "</td></tr>";
   }

   if (!in_array($vopros['qtype'], array(12, 9, 6, 4))) {
   		if(!isset($_GET['quiz_id'])){
			
			$is_shuffled_checked = '';
			
			if($vopros['is_shuffled']) {
				$is_shuffled_checked = 'checked';
			} else {
				$action_name = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
				if($action_name == 'new'){
					$is_shuffled_checked = 'checked';
				}
			}
			
   	       echo "<tr><td colspan='2'>
   	       <input type='checkbox' name='is_shuffled' value='1' ".$is_shuffled_checked." /> ".
   	       _("Перемешивать ответы на вопрос")."</td></tr>";
   		}
   		else{
   			echo '<input type="hidden" name="is_shuffled" value=0>';
   		}
   }
   //
   // опциональные поля
   //

   foreach ($info[string] as $k=>$v) {

              if ($v[0]=="radiostr" || $k=="vopros") continue;
       $name="form[$base][string][$k]";
       $id=md5($name);
       $xname="name='$name' id='$id'";
       if ($v[0]=="hidden") {
          echo "<input type=hidden $xname value=\"".html($data[$k])."\">";
          continue;
       }
       echo "\n\n<tr><td $tdclass";
       switch ($v[0]) {
          case "integer":
             echo ">".xparse("",$v[1],get_defined_vars())."</td><td $tdclass>";
             echo "<input type=text $xname $inputclass value=\"".html($data[$k])."\" size=5>\n";
             break;
          case "string":
             echo ">".xparse("",$v[1],get_defined_vars())."</td><td $tdclass>";
             echo "<input type=text $xname $inputclass value=\"".html($data[$k])."\" size=40>\n";
             break;
          case "textarea":
             echo ">".xparse("",$v[1],get_defined_vars())."</td><td $tdclass>";
             echo "<textarea $xname $inputclass rows=4 cols=40>".html($data[$k])."</textarea>\n";
             break;
          case "checkbox":
             echo " colspan=2>";
             echo "<label for=$id><input type=checkbox $xname value=1 ".($data[$k]>0?"checked":"").">\n";
             echo xparse("",$v[1],get_defined_vars())."</label>";
             break;
       }
       echo "</td></tr>";
   }


   //конец 2й вложенной таблицы
   //if(!isset($_GET['quiz_id'])){
   	   echo "</table></td></tr>";
   //}
   //
   // картинка
   //
   $html="";
   if (count($attach) && (substr($attach[0]['fname'],0,13) == 'comment_file_')) {
      $v=$attach[0];
      if (($v[ftype]==2 || $v[ftype]==3 || $v[ftype]==4)) {
         $url = $GLOBALS['sitepath']."test_attach.php?mode=img&num=$v[fnum]&".v_attach_url($kod)."$asess";
         if ($v[fx]>0 && $v[fy]>0) {
            $dx = doubleval($image_maxx) ? doubleval($v[fx])/doubleval($image_maxx) : 0;
            $dy = doubleval($image_maxy) ? doubleval($v[fy])/doubleval($image_maxy) : 0;
            if ($dx<1 && $dy<1) $dd=1; else $dd=max($dx,$dy);
            $imx=round(doubleval($v[fx])/doubleval($dd));
            $imy=round(doubleval($v[fy])/doubleval($dd));
            $html="<img src='$url' width=$imx height=$imy alt='"._("предварительный просмотр")."' border=0>";
         }
         else {
            $html="<img src='$url' width=$image_maxx height=$image_maxy alt='"._("предварительный просмотр")."' border=0>";
         }
         $html="<a target=_blank href='$url$sess'>$html</a>";
      }
   }

   echo "<td align=right width=$image_maxx>$html</td>";

   echo "</tr></table><P>";


   #############################
   #
   # <Варианты ответов>
   #

   //pr($info);
   //pr($data);
   if (isset($info[variant]) && !in_array($vopros['qtype'], array(9))) {
   		// убираем столбец "правильный вариант" для опросов
   		if(isset($_GET['quiz_id'])){
      		unset($info['variant']['otvet']);
   		}
      $msg1=_("Варианты ответов на вопрос");
      if ($vopros['qtype'] == 10) $msg1 = _("Строка параметров вызова программы-тренажера");
      if (isset($info[msg_editwindow])) $msg1=$info[msg_editwindow];

      echo "<table width=740 class=main cellspacing=0>
      <tr>
      <th colspan=4 align=center><b>$msg1</b></th>
      </tr>";

      if ($vopros['qtype'] == 10) {
              echo "<tr><td colspan=3>"._("Используйте синтаксис")." \"<b>[UID]</b>\" "._("для задания идентификатора пользователя")."</td></tr>";
      }

      echo "<tr bgcolor=#eeeeee>";
//      if($vopros['qtype'] == 1) {
      echo ($s[usermode]?"<th>"._("Номер")."<br>"._("варианта")."</th>":"");
      if(in_array($vopros['qtype'],array(1,2,11))) {
              echo "<th>"._("Вес ответа")."</th>";
//                 ($s[usermode]?"<th>Номер<br>варианта</th>":"");
      }
      $f_i = 0;
      foreach ($info[variant] as $k=>$v) {
                echo "<th id='caption_answer_table_$f_i'>$v[1]</th>";
        $f_i++;

      }


      echo "<th>"._("Вкл./выкл.")."<br>"._("вариант")."</th>";

      echo "</tr>";
      $varkey=array_keys($info[variant]);
      $vkey=@array_keys($data[$varkey[0]]);
      if (isset($varkey[1]) && isset($data[$varkey[1]]) && count($data[$varkey[1]])>count($data[$varkey[0]])) {
         $vkey=@array_keys($data[$varkey[1]]);
      }
      $buf=array();
      if (count($info[variant]))
      foreach ($info[variant] as $k=>$v) {
         if (is_array(count($data[$k])) && count($data[$k]))
         foreach ($data[$k] as $kk=>$vv) {
            $buf[$k][]=$vv;
         }
      }
      $bufkod=array();
      $vkeymax=-32768;
      //$cheid="x".substr(md5(microtime().mt_rand(0,9999999)),0,7); см чуть выше
      $intMax = ($vopros['qtype'] == 10) ? 1 : max(count($vkey)+$test_list_qincr,$test_list_qall);
      for ($i=0; $i<$intMax; $i++) {
         if (isset($vkey[$i]) && strlen($vkey[$i])>0) {
            if ($vkeymax<$vkey[$i]) $vkeymax=$vkey[$i];
            $vkey_this=$vkey[$i];
         }
         else {
            $vkey_this=max(1,++$vkeymax);
            if ($vkey_this>32767) {
               $vkey_this=mt_rand(1,32767);
               for ($j=0; $j<100 && (in_array($vkey_this,$vkey) || in_array($vkey_this,$vkey_rand)); $j++) {
                  $vkey_this=mt_rand(1,32767);
               }
               $vkey_rand[]=$vkey_this;
            }
            $vkeymax=$vkey_this;
         }
         $up=0;
         if ($i<$data[varcount]) {
                  $up=1;
         }
         echo "\n\n<tr class=tests >";
         $tmp1=$info[variant];
         $tmp1[xswitch]=1;
         $tmp1[kodvar]=1;
         echo
            ($s[usermode]?"<td align=center class=questt>":"").
            "<input id='{$cheid}_{$i}_kodvar' maxlength=7 class=lineinput ".
            "type=".($s[usermode]?"text":"hidden")." size=5 ".
            "name=form[$base][variant][$i][kodvar] value=\"".html($vkey_this)."\"".
            " style='width:100%;text-align:center;'".
            ($up?"":" disabled ").">".
            ($s[usermode]?"</td>":"");
            if(in_array($vopros['qtype'],array(1,2,11))) {
                if(isset($_GET['quiz_id'])&&!empty($_GET['quiz_id'])){
                        $vopros_weight[$i+1] = ($vopros_weight[$i+1] < 1) ? 1 : $vopros_weight[$i+1];
                    echo "<td class=questt><input type='text' size='2' onChange=\"if(this.value < 1) this.value=1;\" name='form[$base][weight][]' ".($with_weight?"":"disabled")." value=\"".$vopros_weight[$i+1]."\" /></td>";
                }else{
                    echo "<td class=questt><input type='text' size='2' name='form[$base][weight][]' ".($with_weight?"":"disabled")." value=\"".$vopros_weight[$i+1]."\" /></td>";
                }
            }
        echo "<script language='javascript'>
                          if(".$with_weight.") {
                                  var p = document.getElementsByName('form[$base][string][otvet]');
                                  for(i = 0; i < p.length; i++) {
                                          p[i].disabled = true;";
        if ($vopros['qtype']==2)
        echo "                            pp = document.getElementsByName('form[$base][variant]['+i+'][otvet]');
                                          pp[0].disabled = true;";
        echo "
                                  }
                          }
              </script>";

        foreach ($info[variant] as $k=>$v) {
             $name="name='form[$base][variant][$i][$k]'";
             $tmp=" $name ";
             //$stylewidth=" style='width:$v[2]px' ";
             $style100=" style='width:100%;' " ;
             echo "<td align=center class=questt";
             if ($v[0]=="integer" || $v[0]=="checkbox" || $v[0]=="radiostr") echo " width=2% ";
             echo " onclick='if (!document.getElementById(\"{$cheid}_{$i}_xswitch\").checked) { document.getElementById(\"{$cheid}_{$i}_xswitch\").click(); {$cheid}_{$i}_$k.focus(); }' >";
             switch ($v[0]) {
                case "integer":  echo "<input  $is_disabled  ".($up?"":" disabled ")." id='{$cheid}_{$i}_$k' type=text $tmp value=\"".html($data[$k][$vkey_this])."\" size=4>"; break;
                case "string":   echo "<input  $is_disabled  ".($up?"":" disabled ")." id='{$cheid}_{$i}_$k' type=text $tmp value=\"".html($data[$k][$vkey_this])."\" $stylewidth>"; break;
                case "textarea": echo "<textarea $is_disabled ".($up?"":" disabled ")." id='{$cheid}_{$i}_$k' $tmp rows=2 cols=40 $stylewidth>".html($data[$k][$vkey_this])."</textarea>"; break;
                case "checkbox": echo "<input $is_disabled   ".($up?"":" disabled ")." id='{$cheid}_{$i}_$k' type=checkbox $name value=1  ".($data[$k][$vkey_this]>0?"checked":"")." $style100>"; break;
                case "radiostr": echo "<input $is_disabled   ".($up?"":" disabled ")." id='{$cheid}_{$i}_$k' type='radio' size='2' name='form[$base][string][$k]' value='$vkey_this' ".($data[$k]==$vkey_this?"checked":"")." >"; break;
             }

             echo "</td>";
         }
         echo "<td class=questt>";
         echo "<input type=checkbox id={$cheid}_{$i}_xswitch ".
              " style='width:100%;'".
              ($up?" checked":"").
              " onClick=\"val=(this.checked?0:1); ";
         foreach ($tmp1 as $k=>$v) {
            if ($k!="xswitch") echo "{$cheid}_{$i}_$k.disabled=val; ";
         }
         echo "if ((elm = document.getElementById('with_weight')) && elm.checked) {$cheid}_{$i}_otvet.disabled = true;";
         echo "\" ></td>";
         echo "</tr>";
      }
      echo "</table><P>";
   } // if

   if ($with_weight) echo "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n change_form_$base('weight'); \n//-->\n</script>";
 //  echo attachedFilesList( $s, $attach, $attachtype );
//}
//function attachedFilesList( $s, $attach, $attachtype ){
   #################################
   #
   # <Присоединенные файлы>
   #
   if (!in_array($vopros['qtype'],array(10))) {
           echo "<br><table width=740 class=main cellspacing=0>
           <tr><th colspan=99><b>"._("Присоединенные файлы")."</b></th></tr>";

           //
           // старые присоед. файлы
           //
           if ($s[usermode]) {
              echo  "
              <tr>
              <th align=center><small>"._("Номер")."</small></th>
              <th align=center><small>"._("Тип")."</small></th>
              <th align=center><small>"._("Имя файла")."</small></th>
              <th align=center><small>"._("Размер")."</small></th>
              <th align=center><small>"._("Изменен")."</small></th>
              <th align=center><small>"._("Операции")."</small></th>
              </tr>";

              $i=0;
              $nummax=0;
              foreach ($attach as $k=>$v) {
                 $url=v_attach_url($vopros[kod]);

                 // поле номера файла
                 echo  "
                 <tr class=tests><td width=2% class=questt>
                 <input type=hidden name='attach[$base][$i][fnum]' value=\"$v[fnum]\">
                 <input disabled class=lineinput type=text name='attach[$base][$i][-]' value=\"$v[fnum]\" size=4>
                 </td>";

                 // поле типа файла
                 echo  "<td width=5% class=questt><select class=lineinput name='attach[$base][$i][ftype]' size=1>";
                 foreach ($attachtype as $kk=>$vv)
                    echo  "<option value=$kk".($v[ftype]==$kk?" selected":"").">$vv[0]";
                 echo  "</select></td>";

                 // поле имя файла
                 echo  "<td class=questt><input class=lineinput type=text name='attach[$base][$i][fname]'
                 value=\"".html($v[fname])."\" size=20></td>";

                 //  показ размера, даты и т.д.
                 echo  "
                 <td class=questt>$v[fsize] ".($v[fx]>0?"($v[fx]x$v[fy])":"")."</td>
                 <td class=questt>".date("d-m-Y",$v[fdate])."</td>
                 <td class=questt>

                 <a target=_blank href='".$GLOBALS['sitepath']."test_attach.php"."?mode=linkopen&num=$v[fnum]&$url$sess'>["._("открыть")."]</a>
                 <a href='".$GLOBALS['sitepath']."test_attach.php"."?mode=download&num=$v[fnum]&$url$sess'>["._("скачать")."]</a>";
                 if ($i==0 && $type==7)
                    echo " <a href='#' onclick=\"wopen('/test_mapedit.php?cid=$cid&kod=$base&reload=1$sess');document.evform.submit();\">["._("изменить")."&nbsp;"._("карту")."]</a>";

                 // поле удаления файла
                 echo  " <nobr>"._("удалить").":<input type=checkbox name='attach[$base][$i][delfile]' value='ok'></nobr></td>";

                 echo  "</tr>";
                 if ($nummax<$v[fnum]) $nummax=$v[fnum];
                 $i++;
              }
           }
           else {
              $i=0;
              foreach ($attach as $k=>$v) {
                 $url=v_attach_url($vopros[kod]);
                 echo  "<tr class=tests><td width=32% class=questt>
                 <a target=_blank href='".$GLOBALS['sitepath']."test_attach.php"."?mode=linkopen&num=$v[fnum]&$url$sess'>$v[fname]</a>
                 </td>
                 <td width=32%>";

                 if ($i==0 && $type==7)
                    echo  " <a href='#' onclick=\"wopen('/test_mapedit.php?cid=$cid&kod=$base&reload=1$sess');//document.evform.submit();\">"._("изменить")."&nbsp;"._("области")."</a> | ";
                 echo  "
                 <a href='".$GLOBALS['sitepath']."test_attach.php"."?mode=download&num=$v[fnum]&$url$sess'>"._("скачать")."</a>
                 </td>
                 <td align=right width=32%>$v[fsize] ($v[fx]x$v[fy])
                 <td align=center width=30><a href='#' onclick=\"if (!confirm('"._("Действительно удалить файл из вопроса?")."')) return false; wopen('/test_list.php?c=delete_attach&kod=".ue($kod)."&fnum=$v[fnum]$asess','w1649',10,10);return false;\">".getIcon('delete')."</a>
                 </td></tr>";
                 if ($nummax<$v[fnum]) $nummax=$v[fnum];
                 $i++;
              }
           }
           $nummax++;

           if ($s[usermode]) $jmax=6; else $jmax=1-$i;
           if (!$s[usermode] && $vopros[qtype] == 8) $jmax=6;
           // вывод доолнительных полей
           for ($j=0; $j<$jmax; $j++,$i++) {
              echo "<tr class=tests>";
              if ($s[usermode]) {
                 echo  "
                 <td width=2% class=questt><input type=".($s[usermode]?"text":"hidden")."
                 class=lineinput name='attach[$base][$i][fnum]' value=\"".($nummax+$j)."\" size=4></td>
                 <td width=5% class=questt>
                 <select class=lineinput name='attach[$base][$i][ftype]' size=1>";
                 echo  "<option value=autodetect>Auto";
                 foreach ($attachtype as $kk=>$vv) echo "<option value=$kk>$vv[0]";
                 echo "</select>
                 </td>";
              }
              else {
                 echo  "<input type=hidden name='attach[$base][$i][fnum]' value=\"".($nummax+$j)."\">";
                 echo  "<input type=hidden name='attach[$base][$i][ftype]' value='autodetect'>";
              }
              echo  "
              <td colspan=10 class=questt><small></small>
              <input class=lineinput type=file name='attach_file[$base][$i]' size=25></td>
              </tr>";
           }

           echo "</table><P>";
   } else {
           echo "<table width=100% class=main cellspacing=0>
           <tr><th align=center colspan=2 class=th3><b>"._("Программа-тренажер")."</b></th></tr>";
                if ($vopros['url']) {
                        echo "<tr><td align=center colspan=2 class=questt><b>"._("Выбрано:")."</b>{$vopros['url']}</td></tr>";
                }
        echo "
           <tr><td class=questt>
           <input class=lineinput type=file name='file_tmp' size=25 onChange=\"javascript:document.getElementById('hid_prog_path').value=this.value;\">
           <input type=hidden name='form[$base][url]' id='hid_prog_path' size=25 value={$vopros['url']}><br>
           </td>
           <td class=questt width='50%'><b>"._("Внимание!")."</b> "._("Программа-тренажер должна быть установлена в эту директорию на каждом клиентском компьютере.")."
           </td>
           </tr>
           </table><P>";
   }
   //echo $tmp;
 //  return ( $tmp );

   if (in_array($vopros['qtype'],array(11))) {
       echo "
       <script type=\"text/javascript\">
       <!--
       var s = document.getElementsByName('form[$base][weight][]');
       for(i=0;i<s.length;i++) {
           s[i].disabled = false;
       }
       //-->
       </script>";
   }

   }


/**
* Распаковка архива вопроса
* тип вопросов: 9
*/
function extractTestPackage($zipFileName, $kod) {

            global $wwf;

             $workDir = getcwd();
             chdir($wwf."/COURSES/course".kodintval($kod));
             $packageDir = "content/tests/$kod";
             if(!file_exists($packageDir)) mkdirs($packageDir);
             chdir($wwf."/COURSES/course".kodintval($kod).'/'.$packageDir);

             $zip = zip_open($zipFileName);
             if ($zip) {
                 while ($zip_entry = zip_read($zip)) {
                     if (zip_entry_open($zip, $zip_entry, "r")) {

                         $fSize = zip_entryize($zip_entry);
                         $eName = zip_entry_name($zip_entry);
                         $eName = str_replace("\\", "/", $eName);

                         $pathinfo = pathinfo($eName);
                         if (!file_exists($pathinfo['dirname'])) mkdirs($pathinfo['dirname']);

                         if($fSize==0) {
                             $s= dirname($eName);
                             if(!file_exists($eName)) {
                                @mkdirs($eName);
                             }
                         }
                         else  {
                            @$buf=zip_entry_read($zip_entry, $fSize);
                            @$fp = fopen(to_translit($eName), "wb+");
                            @fwrite($fp,$buf);
                            @fclose($fp);
                         }
                         zip_entry_close($zip_entry);
                     }
                 }

                 $ret = true;

             } else $ret = false;

             zip_close($zip);

             chdir($workDir);

             return $ret;

}

function get_all_test_array($cid) {
    $sql = "SELECT tid, title FROM test WHERE cid = '$cid'";
    $res = sql($sql);
    $retArr = array();
    while ($row = sqlget($res)) {
        $retArr[$row['tid']] = $row['title'];
    }
    return $retArr;
}

?>