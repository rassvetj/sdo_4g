<?
/*include "../../application/modules/els/subject/controllers/ListController.php";
    if (isset($_REQUEST['lng'])) {
        $lng = $_REQUEST['lng'];
    }
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	*/
	$lng = $_COOKIE['lng'];
include("1.php");
define("HARDCODE_WITHOUT_SESSION", true);
include "../../application/cmd/cmdBootstraping.php";

if (isset($_REQUEST['vopros'])) {
    $vopros = $_REQUEST['vopros'];
}

if (isset($_REQUEST['mode'])) {
    $mode = (int) $_REQUEST['mode'];
}

if (isset($_REQUEST['cid'])) {
    $cid = (int) $_REQUEST['cid'];
}

if (isset($_REQUEST['kod'])) {
    $kod = $_REQUEST['kod'];
}

if (isset($_REQUEST['skip_questres'])) {
    $skip_questres = $_REQUEST['skip_questres'];
} else {
    $skip_questres = 0;
}

include("test.inc.php");

if ($_SESSION['boolInFrame']) {
    $constTarget = TARGET_THIS;
}
else {
    $constTarget = TARGET_TOP;
}

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setView('DocumentBlank');

if (!empty($s['ttitle'])) {
	if ($lng == 'eng' && $s[ttitle_translation]!='')
	$GLOBALS['controller']->setHeader(_("Задание:").' '.$s['ttitle_translation']);
else $GLOBALS['controller']->setHeader(_("Задание:").' '.$s['ttitle']);
} else {
	$GLOBALS['controller']->setHeader(_("Просмотр вопроса"));
	$GLOBALS['controller']->setHelpSection("vopros");
}
#
# ПРОВЕРКА ДОПУСКА - имеет ли человек право смотреть что-то в базе
# и откуда взять код вопроса (из сессии, из переменных или других мест)
#
$ask=array();
$ckod=array();
$number=0;
if ( isset($teachertest) )
     echo "<H1>TEACHER</H1>";
if (!isset($mode))
     $mode=1;

// Скрипт с созданием нэймспейса к открываемому всплывающему окну, 
// ссылку на которое генерирует функция prepareInnerLinks из файла unmanaged/test.inc.php
// при переходе к следующему вопросу всплывающее окно закрывается.
// Костыль, но что уж поделать... тут такое намутили до меня...
echo '<script type="text/javascript">
  
  window.hm = window.hm || {};
  window.hm.test = window.hm.test || {};
  window.hm.test.closeOpenedWindow = function() {
    if (hm.test.openedWindow) {
      hm.test.openedWindow.close();
      delete hm.test.openedWindow;
    }
  }
  
</script>';

switch ( $mode ) {
         case 1: // вызов из сеанса тестирования
              $GLOBALS['controller']->setHelpSection('testing');
              if ($s[me]==2)
                  exitmsg(_("Сеанс тестирования закончен. Не нажимайте кнопку BACK (Назад) в вашем браузере!"),"test_end.php?$sess",0,$constTarget);
              if ($s[me]!=1)
                  exitmsg(_("Сеанс тестирования закончен или не начинался."),"./?$sess",0,$constTarget);
              // проверка оставшегося времени
              if ($s[timelimit]>0) {
                  if (ceil($s[timelimit]-doubleval(time()-$s[start])/60)<1) {
                      result_test(5);
                      $GLOBALS['controller']->setMessage(_("К сожалениею, закончилось время, отпущенное на решение задания. Сейчас вы перейдете на страницу результатов. В ведомость записывается результат тестирования 'прервано по лимиту времени' и оценка только тех вопросов, которые вы уже успели решить."), JS_GO_URL, "test_end.php?vopros=".md5(microtime()).$sess);
//                      refresh("test_end.php?vopros=".md5(microtime()).$sess,0,$constTarget);
                      return;
                  }
              }
             if ($skip_questres==1)
                 $s[questres]=0; // если юзер хочет, может вырубить промеж. результаты
             // $s[ckod] - индексный массив, содержит коды, которые нужно показать сейчас,
             //            на одной странице
             if (is_array($s[ckod]) && count($s[ckod])) {
                 $rq="SELECT * FROM list WHERE kod IN (";
                 foreach ($s[ckod] as $k=>$v)
                          $rq.="'".ad($v)."',";
                 $rq=substr($rq,0,-1).")";
                 $res=sql($rq,"err1");
                 while ($r=sqlget($res)) {
                        $ask[$r[kod]]=$r;
//                        echo $r['qtema'];
                 }
                 sqlfree($res);
             }
             $ckod=$s[ckod];
             $key0=array_keys($ckod);
             $number=num_array($s[akod],$ckod[$key0[0]]);
         break;
         case 2: // вызов из произвольного места, номер показываемого вопроса в $kod
             header('Content-Type: text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding);
             $GLOBALS['controller']->setView('DocumentContent');
             $GLOBALS['controller']->setHelpSection('testing');
             if (!isset($kod) || !isset($cid) || (!isset($s[tkurs][kodintval($kod)]) && ($_SESSION['s']['perm'] < 2))) {
                 //даже не спрашивайте что такое test_e1 (смотри лучше test_list.php строка 20)
                 $cmsVoprosIds = getCMSkods($_SESSION['s']['test_e1']['cid']);
                 if (!in_array($kod, $cmsVoprosIds)) {
                   exitmsg(_("Доступ к данному виду показа вопросов имеют только преподаватели
                            курса, на который записан вопрос. Либо вы не преподаватель, либо
                            вы пытаетесь смотерть вопросы другого курса."),0,$constTarget);
                 }
             }
              echo "<form name=m>";
              $tmp=sqlval("SELECT * FROM list WHERE kod='".addslashes($kod)."'","errTV153");
              if (!is_array($tmp)) {
                   $GLOBALS['controller']->setView('DocumentBlank');
                   $GLOBALS['controller']->setMessage(_("Вопрос не найден в базе данных"),JS_GO_URL,"javascript:window.close();");
                   $GLOBALS['controller']->terminate();
                   exit();
              }
              $ask[$kod]=$tmp;
              $ckod[]=$kod;
              $number=0;

         break;
         case 3: // показ результатов ответа на вопросы предыдущей страницы
              $html=path_sess_parse(create_new_html(0,0));
              $html=explode("[ALL-CONTENT]",$html);
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setHelpSection('pre_result');
              $GLOBALS['controller']->setHeader(_("Промежуточные результаты"));
              $GLOBALS['controller']->captureFromOb(CONTENT);
              echo $html[0];
              $GLOBALS['controller']->captureFromOb(TRASH);
              echo ph(_("Промежуточные результаты"),"");
              $GLOBALS['controller']->captureStop(TRASH);
              if (!$s[questres]) exit("HackDetect: "._("ваш тест не позволяет смотреть промежуточные результаты"));
              echo "<b>"._("Данные по вопросу(ам), которые были вам заданы только на предыдущей странице:")."</b>
                     <ul>
                      <li>"._("Набрано баллов:")." <b>{$s[lastquest][bal]}</b>
                      <li>"._("Минимальный балл:")." {$s[lastquest][balmin]}
                      <li>"._("Максимальный балл:")." {$s[lastquest][balmax]}
                      <li>"._("Кол-во вопросов:")." {$s[lastquest][qty]}
                     </ul>
                     <b>"._("Данные по всем вопросам, на которые вы отвечали в этом задании:")."</b>
                     <ul>
                      <li>"._("Набранно баллов всего:")." <b>$s[bal]</b>
                      <li>"._("Минимальный балл:")." $s[balmin]
                      <li>"._("Максимальный балл:")." $s[balmax]
                      <li>"._("Кол-во пройденных вопросов:")." ".count($s[adone])."
                     </ul>";
              if (count($s[lastquest][url])) {
                  echo "<b>"._("Ссылки к заданным вопросам:")."</b><ul>";
                  foreach ($s[lastquest][url] as $v) {
                           if (substr($v,0,2)=="~/") {
                               $v=substr($v,1,strlen($v));
                               $v="/COURSES/course$s[cid]/mods$v";
                           }
                           echo "<li><a href='$v' target=_blank>$v</a>";
                  }
                  echo "</ul>";
              }
              echo "<form action=$GLOBALS[PHP_SELF] method=get>
                    <input type=hidden name=vopros value='".md5(microtime())."'>
                    <input type=submit value='"._("Перейти к следующим вопросам")." &gt;&gt;'><br>
                    <label for=sk><input type=checkbox name=skip_questres value=1 id=sk>
                    "._("Не показывать промежуточные результаты (только для текущего задания)")."</label>
                    </form>";
              echo $html[1];
              $GLOBALS['controller']->captureStop(CONTENT);
              $GLOBALS['controller']->terminate();
              return;
         default:
                 exit("errTV148: "._("ошибочный метод вызова данного файла"));
}



   #
   # НАЧАЛО ПОКАЗА ВОПРОСА
   #
   # к этому месту требуются переменные:
   #
   #    $ask, массив:
   #       код => массив данных вопроса из базы
   #    $ckod, массив:
   #       индекс 0,1,2... => код задаваемого вопроса, в порядке следования
   #           этих кодов будут расположены вопросы на экране (на случай,
   #           когда на одной странице показываются несколько вопросов)
   #    $nubmer, число
   #       порядковый номер вопроса. Если на странице несколько вопросов,
   #       все вопросы будут иметь увеличивающиеся номера.
   #



$checkkod=md5(implode(" | ",$ckod));
$kodlist=array();
$html=path_sess_parse(create_new_html(0,0));
$html=explode("[ALL-CONTENT]",$html);
echo $html[0];
switch ($mode) {
        case 1:
             if ($_SESSION['s']['test']['current']['ModID']) {
                echo "<table width=100% border=0 cellpadding=15 cellspacing=0><tr><td>";
             }
             include("template_test/_pagetop.html");
        break;
        case 2:
             //echo ph("Демонстрация внешнего вида вопроса","");
        break;
}

$iforeach=0;
//
// Проход по списку вопросов, которые нужно рисовать в данный момент
//
$s['timelimit_question'] = 0;
$s['start_question'] = time();
foreach ($ckod as $k=>$v) {
         // очередная строка с вопросом из SQL
         $vv=$ask[$v];


         //if(!isset($s['random_vars']))
         //{

         // <ЗАМЕНА>
         // в $vv['qdata']
         // [X] на случайное значение из диапазона test.random_vars
		 $res=sql("SELECT * FROM test WHERE tid='$s[tid]'");
		 $r=sqlget($res);
		 $random_vars = $r['random_vars']; // строка с описанием случайных величин
		 sqlfree($res);

   		 if(eregi("\;", $random_vars))
   		 {
   			$vars_array = explode(";", $random_vars);
   		 }
   		 else
   		 {
	   		$vars_array[0] = $random_vars;
   		 }
   		 foreach ($vars_array as $value)
   		 {
   		 	if($value!="")
   		 	{
   		 		$value_title = array();
   		 		ereg(":([A-Z]+)", $value, $value_title);
   		 		$value_title[0] = substr($value_title[0],1); // строка с названием переменной
   		 		$value = ereg_replace(":([A-Z]+)", "", $value); // строка -1.2-2 || 12,23,12 || "12","23","12"

   		 		if(eregi("\,", $value)) // перечисление строк или чисел
   		 		{
   		 			$i = 0;
   					$value_vars = explode(",", $value);


   					foreach ($value_vars as $tt_key => $tt_value)
   					{
   						if($tt_value!="")
   						{


   						if(eregi("^\-?[0-9]*\.?[0-9]*$", $tt_value))
   						{
   							$array[$i] = $tt_value;
   						}
   						else
   						{
   							$array[$i] = substr($tt_value, 1, strlen($tt_value)-2);
   						}
   						$i++;


   						}
   					}
   					// $array содержит список значений случайной переменной $value_title[0]
   					if(@$s['random_vars'][$value_title[0]]=="")
   					$s['random_vars'][$value_title[0]] = $array[rand(0, count($array)-1)];
   		 		}
   		 		else // диапазон чисел
   		 		{
   		 			eregi("^(\-?[0-9]*\.?[0-9]*)-(\-?[0-9]*\.?[0-9]*)$", $value, $tt_value);
   		 			// $tt_value[1] содержит нижний предел, $tt_value[2] содержит верхний предел случайной переменной $value_title[0]

					$val_1 = $tt_value[1];
					$val_2 = $tt_value[2];


if(ereg("\.", $val_1))
	$val_1_dot = substr($val_1, strpos($val_1, ".")+1);
else
	$val_1_dot = "";

if(ereg("\.", $val_2))
	$val_2_dot = substr($val_2, strpos($val_2, ".")+1);
else
	$val_2_dot = "";


if(strlen($val_1_dot)>strlen($val_2_dot))
	$val_2_dot = str_pad($val_2_dot, strlen($val_1_dot), "0", STR_PAD_RIGHT);
elseif(strlen($val_1_dot)<strlen($val_2_dot))
	$val_1_dot = str_pad($val_1_dot, strlen($val_2_dot), "0", STR_PAD_RIGHT);

$rand_st = rand((int)$val_1, (int)$val_2);

if($rand_st==(int)$val_1 && $rand_st==(int)$val_2)
{
	if($val_1_dot!="" && $val_2_dot!="")
		$rand_nd = rand($val_1_dot, $val_2_dot);
}
elseif($rand_st==(int)$val_1)
{
	if($val_1_dot!="")
		$rand_nd = rand($val_1_dot, 9);
}
elseif($rand_st==(int)$val_2)
{
	if($val_2_dot!="")
		$rand_nd = rand(0, $val_2_dot);
}
else
{
	$rand_nd = rand(0, pow(10, strlen($val_1_dot)));
}

if(strlen($val_1_dot)>strlen($rand_nd))
{
	$rand_nd = str_pad($rand_nd, strlen($val_1_dot), "0", STR_PAD_LEFT);
}

/*//echo strlen($rand_nd)." ".$rand_nd{strlen($rand_nd)-1}."<br>";
while($rand_nd{strlen($rand_nd)-1}=="0")
{
	echo strlen($rand_nd).$rand_nd{strlen($rand_nd)-1}."<br>";
	$rand_nd = substr($rand_nd, 0, strlen($rand_nd)-1);
}*/

if($rand_nd=="")
	$res = $rand_st;
else
	$res = $rand_st.".".$rand_nd;

   		 			if(@$s['random_vars'][$value_title[0]]=="")
   		 			$s['random_vars'][$value_title[0]] = $res;
   		 		}
   		 	}
   		 }
         // </ЗАМЕНА>
        // }

         if( isset($s['random_vars']) && (is_array($s['random_vars']) )) {
	         foreach ($s['random_vars'] as $var_key => $var_value)
	         	$vv['qdata'] = str_replace("[". $var_key ."]", $var_value, $vv['qdata']);
         }


         $s['timelimit_question'] += $vv['timelimit'];
         //echo "<li>DEBUG: balmin=$vv[balmin] balmax=$vv[balmax]";
         //
         // Поиск Аттачей и информации о них
         //
         $attach=array();
         //echo $vv[kod];
         $res=sql("SELECT kod,fnum,ftype,fname,fdate,LENGTH(fdata) as fsize
                   FROM file WHERE kod='".ad($vv[kod])."' ORDER BY fnum","err2");
         while ($r=sqlget($res)) {
                $attach[]=$r;
         }

         sqlfree($res);
         //
         // Подготовка к вызову функции вопроса
         //
         //echo "----- $vv[kod] -----<br>";
//         echo $vv[qtema];
         $tm="template_test/".QTYPE_PREFIX.$vv[qtype];
         $php="$tm-v.php";
         //$number=num_array($s[akod],$vv[kod]);
         $kodlist[]=$number;
         $func1="v_sql2php_".QTYPE_PREFIX.$vv[qtype];
         $func2="v_vopros_".QTYPE_PREFIX.$vv[qtype];
         if (!file_exists($php)) {
              echo "<br><br><br>"._("Не найдена функция для вопроса типа")." QTYPE=".QTYPE_PREFIX.$vv[qtype];
              continue;
         }
         // Показ вопроса
         //
         require_once($php);
         $vv['qdata'] = str_replace("./COURSES/","/COURSES/",$vv['qdata']);
         $arr_vopros=$func1($vv);

         $arr_vopros['vopros']=prepareInnerLinks(prepareGeshi($arr_vopros['vopros']));
         if (is_array($arr_vopros['variant'])) {
             foreach($arr_vopros['variant'] as $key => $variant) {
                 $arr_vopros['variant'][$key] = prepareInnerLinks(prepareGeshi($variant));
             }
         }
         if (is_array($arr_vopros['text'])) {
             foreach($arr_vopros['text'] as $key => $variant) {
                 $arr_vopros['text'][$key] = prepareInnerLinks($variant);
             }
         }

         foreach ($vv as $k1=>$v1)
                  $arr_vopros[$k1]=$v1;
         if ($iforeach>0) {
             include("template_test/_pagehr.html");
         }
         //echo "<P align=right><input type=hidden id='ischecked_$number' value=0></P>";
         echo "<input type=hidden id='ischecked_$number' value=0>";
         if ( isset($number_next) )
              $class_hidden="class=hidden2";

         if (is_array($_SESSION['s']['adone']) && (($key = array_search($v,$_SESSION['s']['adone']))!==false)) {
             $arr_vopros['otvets'] = $_SESSION['s']['aotv'][$key];
             $arr_vopros['otvets_info'] = $_SESSION['s']['ainfo'][$key];
         }
         echo "<span id='q$number' $class_hidden>".$func2($arr_vopros,$tm,$number,$attach)."</span>";
         $iforeach++;
         $number++;

         $tmp1=trim($arr_vopros[url]);
         $tmp2=$tmp1;
         $cid=kodintval($arr_vopros[kod]);
         if (!empty($tmp1)) {
              $stmp="<p>"._("Комментарии:")." <a href='$tmp1' target=_blank>$tmp2</a><p>";
              if (substr($tmp1,0,2)=="~/") {
                  $tmp1=substr($tmp1,1,strlen($tmp1));
                  $tmp1= $sitepath."COURSES/course$cid/mods$tmp1";
                  $tmp2=_("см. в учебных материалах");
                  $stmp="<p>"._("Комментарии:")." <a href='$tmp1' target=_blank>$tmp2</a><p>";
              }
              if (substr($tmp1,0,1)=="!") {
                  $tmp1=substr($tmp1,1,strlen($tmp1));
                  $stmp="<p>"._("Комментарии к ответу:")." <i>$tmp1</i><p>";
              }
         }

         if ($mode == 2 && strlen($tmp1)>0) {
             echo $stmp;//"<p>Комментарии: <a href='$tmp1' target=_blank>$tmp2</a><p>";
         }
////////////////////////////////////////////////
         if ($mode == 2 ) { //|| $mode == 1 ) {  // ПОКАЗ ОТВЕТОВ
             // если вопрос содержит веса ответов
             if (!empty($arr_vopros['weight'])) {
                 $arr_vopros['weight'] = unserialize(stripslashes($arr_vopros['weight']));
                 if (is_array($arr_vopros['weight']) && count($arr_vopros['weight'])) {
                     $arr_vopros[goodotvet] = array();
                     if (is_array($arr_vopros['variant']) && count($arr_vopros['variant']))
                     foreach($arr_vopros['variant'] as $k=>$variant) {
                         $arr_vopros[goodotvet][gotvet][] = $variant." ["._("Вес:")." {$arr_vopros['weight'][$k]}]";
                     }
                 }
             }
             $info=$GLOBALS["v_edit_".QTYPE_PREFIX.$vv[qtype]];
             if ($info[goodotvet] && isset($arr_vopros[goodotvet]) && ($arr_vopros['qtype'] != 10)) {
                echo "<P ALIGN=RIGHT>";
                $number_next=$number;
                $number1=$number-1;
                echo "<script>$(document).ready(function(){\$('.a$number1').hide();})</script>";
                echo "<input type=button id=\"b$number1\" $class_hidden value="._('Правильный ответ(ы):')."
                        onClick=\"$('#b$number1').hide();$('.a$number1').show();setGray( 'q$number1' );\">";
                if ($arr_vopros['type'] != 10) {
                     echo "<div class='a$number1' >";
                     echo _("Правильный ответ(ы):")."<br><hr width=100% size=1>";
                     //helpalert("Сведения о правильных ответах показываются только преподавателям.", "Правильный ответ(ы)");
                     //pr($arr_vopros);
                     if (isset($arr_vopros[goodotvet][ginfo]) && !isset($arr_vopros[goodotvet][gerror1])) {
                         echo $arr_vopros[goodotvet][ginfo];
                         echo "<P>";
                     }
                     if (count($arr_vopros[goodotvet][gotvet])>0) {
                         foreach ($arr_vopros[goodotvet][gotvet] as $v) {
                                  echo "<li>".$v."</li>";
                         }
                          echo "<P>";
                     }
                     if (isset($arr_vopros[goodotvet][gerror1])) {
                         if ($arr_vopros[goodotvet][gerror1]==="EMPTY") {
                             $arr_vopros[goodotvet][gerror1]=_("Отсутствуют варианты ответа на вопрос.");
                             $arr_vopros[goodotvet][gerror2]=_("Введите один или более вариантов ответа.");
                             $arr_vopros[goodotvet][gwarn]=1;
                         }
                         if ($arr_vopros[goodotvet][gerror1]==="NOTGOOD") {
                             $arr_vopros[goodotvet][gerror1]=_("Ни один вариант ответа не является правильным.");
                             $arr_vopros[goodotvet][gerror2]=_("Отметьте один/несколько вариантов как правильные.");
                             $arr_vopros[goodotvet][gwarn]=1;
                         }
                         echo "<P>"._("Обнаружена ошибка в этом вопросе, возникшая в следствии неправильного ввода вопроса преподавателем:")."<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <font color=red><b>{$arr_vopros[goodotvet][gerror1]}</b></font><P>
                                "._("Устранить ошибку этого вопроса можно на странице его редактирования так:")."<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <font color=red><b>{$arr_vopros[goodotvet][gerror2]}</b></font><P>";
                     }
                     if (isset($arr_vopros[goodotvet][gwarn])) {
                         echo "<font color=red><b>"._("Внимание!")." <u>"._("НЕОБХОДИМО")."</u> "._("устранить ошибку в этом вопросе,
                                т.к. если вы включите этот вопрос в какое-либо задание, то
                                ответить на вопрос будет нельзя, в следствии чего
                                прохождение ВСЕГО ЗАДАНИЯ, включающего этот вопрос, для учащихся будет невозможно!")."</b></font><P>";
                     }
                     echo "</div>";
                 }
             }
         }
}


switch ( $mode ) {
         case 1:
             include("template_test/_pagebottom.html");
             if ($_SESSION['s']['test']['current']['ModID']) {
                 echo "</td></tr></table>";
             }
         break;
         case 2:
              if ($arr_vopros['type'] != 10) {
                  showQuestionStatistic( $tid, $cid, $kod );
              }
         break;
}


if (0 && tdebug) {
    foreach ($s[ckod] as $v) {
             pr($ask[$v]);
    }
    pr($s);
}
echo $html[1];
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>