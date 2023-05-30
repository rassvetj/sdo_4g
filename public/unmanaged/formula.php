<?
// версия 2.1 ДК
// добавлено
// создание таблицы соответсвий % от максмально балла - оценке
//
   //include("1.php");

   if (isset($_REQUEST['cid'])) {
       $cid = (int) $_REQUEST['cid'];
   }

   if (isset($_REQUEST['c'])) {
       $c = $_REQUEST['c'];
   }

   if (isset($_REQUEST['id'])) {
       $id = $_REQUEST['id'];
   }

   $GLOBALS['controller']->setView('DocumentContent');

   //include("test.inc.php");
   include("formula_calc.php");

//   if (isset($_GET['CID']) && $_GET['CID']) {
//       refresh("{$sitepath}formula.php?c=selectkurs_submit&newcid={$_GET['CID']}");
//       exit();
//   }

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($_SESSION['s']['perm']<2) {
       exitmsg(_("У вас нет соответствующего уровня доступа"),$GLOBALS['sitepath']);
   }
   //if ($s[perm]!=2) exitmsg(_("К этой странице могут обратится только: преподаватель"),"/?$sess");

   $ss="test_e1";

   //if (!isset($s[$ss][cid]) || !isset($s[tkurs][$s[$ss][cid]])) {
   //   if ($c!="selectkurs" && $c!="selectkurs_submit") $c="selectkurs";
   //}

//   $cid=$s[$ss][cid];

   $fo_type = array(
      1 => _("автоматическое выставление оценок за тест"),
      //2 => _("Условие окончания обучения"),
      3 => _("автоматическое формирование групп по результатам теста"),
      4 => _("итоговая оценка за курс"),
      5 => _(" штраф за несвоевременное выполнение занятия"),
   );

   if ($_SESSION['s']['perm'] == 2) {
       unset($fo_type[4]); // препод не создает формулу для итоговой оценки
   }
   if (($_SESSION['s']['perm'] == 2) && $cid && !in_array($cid,$s['tkurs'])) {
       refresh("{$GLOBALS['sitepath']}");
       exit();
   }

switch ($c) {

case "":

   //echo show_tb();
   echo ph(_("Формулы"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $filter_kurses = selCourses($s[tkurs],$cid,true);
   //$GLOBALS['controller']->addFilter(_("Курс"),'cid',$filter_kurses,$cid,false);
   /*echo "
   <form action=$GLOBALS[PHP_SELF] method=post>
   <input type=hidden name=c value='new_post'>
   <input type=hidden name=cid value=\"".(int) $cid."\">
   <table width=100% class=main cellspacing=0>
      <tr>
         <th colspan=2>"._("Добавить новую формулу")."</th>
      </tr>
      <tr>
         <td>". _("Название").":
         </td>
         <td>
         <input type=text name=name size=40 style=\"width: 300px;\" value=\""._("введите название")."\">
         </td>
      </tr>
      <tr>
         <td colspan=2>".okbutton()."</td>
      </tr>
   </table>
   </form><br>";*/
   if ($cid || ($s['perm'] >= 3)) {
       $smarty = new Smarty_els();
       $smarty->assign('caption', _("создать формулу"));
       $smarty->assign('url', $GLOBALS['sitepath']."formula/list/index/subject_id/$cid/?c=add&cid=$cid");
       $smarty->assign('style', "");
       $smarty->display('common/add_link.tpl');
   }

   if ($cid) {
   echo "
   <h3>" . _('Формулы на курсе') . "</h3>
   <table width=100% class=main cellspacing=0><tr>";
   echo "<th align=center>"._("Имя")."</th>";
   echo "<th align=center nawrap=nowrap>"._("Формула")."</th>";
   echo "<th align=center>"._("Область")."</th>";
   echo "<th align=center>"._("Действия")."</th>";
   echo "</tr>";

   $res=sql("SELECT * FROM formula WHERE CID='".(int) $cid."' AND type IN (" . implode(',', array_keys($fo_type)) . ") ORDER BY type","errFM50");

   while ($r=sqlget($res)) {
      echo "<tr class=questt align=center>
      <td align=left>$r[name]</td>
      <td nowrap=nowrap><tt>".html()."<BR>";
      $text="";
      if( $r[type]==2 )
        $mark=finishCourseFormula( $r[formula], $text, getPeopleData( 0, 0 ) );
      elseif(($r[type] == 1) || ($r['type'] == 4))
        $mark=viewFormula($r[formula], $text, 0, 100, 50, $r['type']); // вывод формулы в таблице
      else {
        $mark = viewGrFormula($r[formula],$r['type']);
      }
      echo $text;
      echo "<td>".$fo_type[$r[type]]."</td>
      <td>";
    //  if (($s['perm'] >= 3) || $r['cid']) {

      echo "
      <a href=\"$PHP_SELF?c=edit&id=$r[id]&cid={$cid}\">".getIcon('edit', _('Редактировать формулу'))."</a>
      <a href=\"{$GLOBALS['sitepath']}formula/list/delete/subject_id/$cid/formula_id/{$r['id']}\" onClick=\"if (confirm('"._("Вы действительно желаете удалить формулу?")."')) return true; else return false;\">".getIcon('delete', _('Удалить формулу'))."</a>";
   //   }
      echo "</td></tr>";
   }
   if (!sqlrows($res)){
   		echo "<tr><td colspan='4' align='center'>" . _("не создано ни одной формулы") . "</td></tr>";
   }

   echo "</table>";
   } // if cid

   // GLOBAL FORMULS
   if(isset($currentRole) && in_array($currentRole, array('teacher', 'dean'))) {

	   echo '<hr width="100%">';
	   echo "
	   <h3>" . _('Общие формулы') . "</h3>
	   <table width=100% class=main cellspacing=0><tr>";
	   echo "<th align=center>"._("Имя")."</th>";
	   echo "<th align=center nawrap=nowrap>"._("Формула")."</th>";
	   echo "<th align=center>"._("Область")."</th>";
	   if ($s['perm'] >= 3) echo "<th align=center>"._("Действия")."</th>";
	   echo "</tr>";

	   $res=sql("SELECT * FROM formula WHERE CID=0 AND type IN (" . implode(',', array_keys($fo_type)) . ") ORDER BY type","errFM50");

	   while ($r=sqlget($res)) {
	      echo "<tr class=questt align=center>
	      <td align=left>$r[name]</td>
	      <td nowrap=nowrap><tt>".html()."<BR>";
	      $text="";
	      if( $r[type]==2 )
	        $mark=finishCourseFormula( $r[formula], $text, getPeopleData( 0, 0 ) );
	      elseif(($r[type] == 1) || ($r['type'] == 4))
	        $mark=viewFormula($r[formula], $text, 0, 100, 50, $r['type']); // вывод формулы в таблице
	      else {
	        $mark = viewGrFormula($r[formula],$r['type']);
	      }
	      echo $text;
	      echo "<td>".$fo_type[$r[type]]."</td>";
          if ($s['perm'] >= 3) {
              echo "
                  <td>	      
                  <a href=\"$PHP_SELF?c=edit&id=$r[id]&cid={$cid}\">".getIcon('edit', _('Редактировать формулу'))."</a>
                  <a href=\"{$GLOBALS['sitepath']}formula/list/delete/subject_id/$cid/formula_id/{$r['id']}\" onClick=\"if (confirm('"._("Вы действительно желаете удалить формулу?")."')) return true; else return false;\">".getIcon('delete', _('Удалить формулу'))."</a>
                  </td>";
	   }
         echo "</tr>";	   
	   }
	   if (!sqlrows($res)){
	   		echo "<tr><td colspan='4' align='center'>" . _("не создано ни одной глобальной формулы") . "</td></tr>";
	   }

	   echo "</table>";

   }

   $GLOBALS['controller']->captureStop(CONTENT);
   //echo show_tb();
   $GLOBALS['controller']->terminate();

   break;


case "add":
    $GLOBALS['controller']->setHeader(_("Создание формулы"));
    $GLOBALS['controller']->setHelpSection('add');
case "edit":

   //echo show_tb();

   if ((int)$id) {
   intvals("id");
   $r=sqlval("SELECT * FROM formula WHERE id=$id","errEV116");
   if (!is_array($r)) exitmsg(_("Нет такой формулы"),"$PHP_SELF?$sess");
   }

   echo ph(_("Редактирование формулы"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   if (!$GLOBALS['controller']->getHeader()) {
   $GLOBALS['controller']->setHeader(_("Редактирование формулы"));
   $GLOBALS['controller']->setHelpSection('edit');
   }

   $toolTip = new ToolTip();
   echo "
   <!-- <p align=right><a href=$PHP_SELF?c=del&id=$id onClick='return confirm(\"Delete?\");'>"._("Удалить формулу")."</a></p>-->
   <script language=\"JavaScript\" type=\"text/javascript\">
   <!--
        function putFormulaExample() {
            var formula = document.getElementById('formula');
            var formula_type;
            if (formula) {
                for(var i=1;i<=6;i++) {
                    formula_type = document.getElementById('r'+i);
                    if (formula_type && formula_type.checked) {
                        switch(i) {
                            case 1:
                                formula.value = '0-50:1("._("Плохо").");\\n51-100:2("._("Хорошо").");';
                            break;
                            case 2:
                                formula.value = 'progress>90,level>4,total>20:"._("ЗАЧЕТ")."';
                            break;
                            case 3:
                                formula.value = '0-50:"._("плохие").";\\n51-100:"._("хорошие").";';
                            break;
                            case 4:
                                formula.value = '-50:3("._("Удовлетворительно").");\\n51-75:4("._("Хорошо").");\\n76-90:5("._("Отлично").");\\n91-:5+("._("Блестяще").");';
                            break;
                            case 5:
                                formula.value = '0-7:0.5;\\n8-30:0.9;';
                            break;
                            case 6:
                                formula.value = '0:Компетенция не развита;\\n1:Компетенция развита недостаточно;\\n2:Базовый уровень развития;\\n3:Сильный уровень развития;';
                            break;
                        }
                        break;
                    }
                }
            }
        }
   // -->
   </script>
   <form action='{$GLOBALS['sitepath']}formula/list/index/subject_id/$cid' method=post>
   <input type=hidden name=id value=\"$id\">
   <input type=hidden name=cid value=\"".(int) $cid."\">
   <input type=hidden name=c value=\"edit_post\">
   <table width=100% class=main cellspacing=0>
      <!--tr><th colspan=2>"._("Редактирование")."</th></tr-->
   <tr><td>"._("Имя")."</td>
   <td><input type=text name=form[name] size=60 value=\"".html($r[name])."\"></td></tr>
   <tr><td>"._("Формула")."<br>(" . _('это поле можно оставить пустым, в таком случае оценка за занятие будет равна проценту успешности') . ")</td>
   <td width=70%>
   <table border=0 cellpadding=0 cellspacing=0>
   <tr><td>
   <textarea rows=6 cols=60 id=\"formula\" name=form[formula]>".html($r[formula])."</textarea></td>";
   echo "<td>";
   echo $toolTip->display('formula');
   echo "</td></tr></table>";
   echo "<br><a href=\"javascript:void(0)\" onClick=\"putFormulaExample()\"> "._("вставить пример")."</a>";
   echo "
   </td>
   <tr><td>"._("Область")."</td>
   <td>";
//   <br>Пример оценивания: <i>0-50:1;51-100:2;</i>. Если диапазоны не заданы, то % переводятся в баллы
//   <br>Пример окончания обучения: progress>90,level>4,total>20:ЗАЧЕТ</i>
//   <br>Пример формирования групп: <i>0-50:плохие;50-100:хорошие;</i>

   if (!$id) {
       $r['type'] = key($fo_type);

   }

   foreach ($fo_type as $k=>$v)
      echo "<input type=radio name=form[type] value=$k ".(($r[type]==$k)?"checked":"")." id=\"r$k\">".
      " <label for=r$k>$v</label><br>";
   echo "</td></tr>";
   echo "</table>
        <table border='0' align='right'>
            <tr>
                <td>".button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath']."formula/list/index/subject_id/$cid")."</td>
                <td>".okbutton()."</td>
            </tr>
        </table>
        <div style='clear: both;'></div>
   </form>
   ";

   $GLOBALS['controller']->captureStop(CONTENT);
   //echo show_tb();
   $GLOBALS['controller']->terminate();

   break;


case "edit_post":
   intvals("id");
   $form = $_POST['form'];
   $post=array();
   $post['name']=$form['name'];
   //$post[formula]=str_replace(array("\r\n","\n"),'',$form[formula]);
   $post['formula']=$form['formula'];
   $post[type]=intval($form[type]);
   if ((int)$id) {
   $rq="UPDATE `formula` SET ";
   foreach ($post as $k=>$v) $rq.="`$k`=".$GLOBALS['adodb']->Quote($v).",";
   $rq=substr($rq,0,-1)." WHERE `id`=$id";
   if (!empty($post['name'])) {
       $res=sql($rq,"errFM119");
       sqlfree($res);
   }
   }else {
       if(empty($form['name'])) $form['name'] = _('Без названия');
       $sql = "INSERT INTO `formula` (
                   cid,
                   name,
                   formula,
                   type)
               VALUES (
                   ".$GLOBALS['adodb']->Quote($cid).",
                   ".$GLOBALS['adodb']->Quote($form['name']).",
                   ".$GLOBALS['adodb']->Quote($form['formula']).",
                   ".$GLOBALS['adodb']->Quote($form['type'])."
                   )";
       sql($sql);
   }
   refresh($GLOBALS['sitepath'].'formula/list/index/subject_id/'.$cid);
   break;


case "new_post":


   if (!empty($name)) {
       $res=sql("INSERT INTO formula (name, type, CID) values (
                    ".$GLOBALS['adodb']->Quote($name).",
                    1,
                    '".(int) $cid."')","errFM185");
       sqlfree($res);
       refresh("$PHP_SELF?cid=".(int) $cid."&c=edit&id=".sqllast()."{$sess}");
   }
   refresh($sitepath.'formula.php');
   break;


case "del":

   intvals("id");
   $res=sql("DELETE FROM formula WHERE id=$id","errFM193");
   sqlfree($res);
   refresh("{$GLOBALS['sitepath']}formula/list/index/subject_id/$cid");
   break;

case "selectkurs":

   echo show_tb();
   echo ph(_("Выбор курса для редактирования формул"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Выбор курса для редактирования формул"));
   echo "
   <form action=$PHP_SELF method=post name=m>$sessf
   <input type=hidden name=c value=\"selectkurs_submit\">
   <span class=\"tests\">"._("Выберите курс:")."</span><br><br>
   <select name=newcid size=14 style=\"width:100%\">";

   $res=sql("SELECT * FROM Courses WHERE cid IN (".implode(",",$s[tkurs]).") ORDER BY Title","errTT243");
   while ($r=sqlget($res)) echo "<option value=$r[CID]>$r[Title]".($s[usermode]?"&nbsp;($r[CID])":"");

   echo "</select><br><br>
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr>
   <td align=\"right\" valign=\"top\">".okbutton()."</td>
   </tr>
   </table>
   </form>";

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   exit;


case "selectkurs_submit":

   intvals("newcid");
   if ($newcid==0) exitmsg(_("Ничего не выбрано"),"$PHP_SELF?c=selectkurs$sess");
   if (!isset($s[tkurs][$newcid])) exit("HackDetect: "._("нет прав перейти на чужой курс"));
   $s[$ss][cid]=$newcid;
   refresh("$PHP_SELF?$sess");
   exit;


}



?>