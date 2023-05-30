<?

   include("1.php");
   include("test.inc.php");
   $GLOBALS['controller']->setView('documentblank');
   $GLOBALS['controller']->captureFromOb(CONTENT);

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) exitmsg(_("К этой странице могут обратится только: преподаватель,  представитель учебной администрации, администратор"),"/?$sess");
   //if (count($s[tkurs])==0) exitmsg(_("Хоть вы и преподаватель, но в данное время не владеете ни одним курсом."),"/?$sess");

   $ss="test_e1";

   //intvals("cid");

   $cid = intval($_REQUEST['cid']);
   $kod = $_REQUEST['kod'];
   $reload = $_REQUEST['reload'];
   //if (!isset($s[tkurs][$cid])) exit("HackDetect: "._("нет прав редактировать данный вопрос"));
   $kod=base2kod($kod);
   if (kodintval($kod)!==$cid) exit("errTM31: hackdetect");
   $base=kod2base($kod);
   
   echo '<script src="/unmanaged/js/test_mapedit.js" type="text/javascript"></script>';

switch ($_REQUEST['c']) {

case "":

   if (isset($reload)) {
      refresh("test_mapedit.php?cid=$cid&kod=$base",1);
      echo _("загрузка...");
      exit;
   }

   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];
   echo ph(_("Редактирование карты на картинке"));

   $rv=sqlval("SELECT * FROM list WHERE kod='".addslashes($kod)."'","errTM98");
   if (!is_array($rv)) exit("errTM99: "._("такого вопроса нет"));

   $tm="template_test/7";
   include_once("$tm-v.php");
   $vopros=v_sql2php_7($rv);

   $rf=sqlval("SELECT fnum,fname,fdate,ftype FROM file WHERE kod='".addslashes($kod)."' ORDER BY fnum","errTM41");
   if (!is_array($rf)) exit("errTM42: "._("к этому вопросу должна прилагаться картинка.
      Сейчас ее нет - редактировать карту картинки невозможно"));

   $GLOBALS['controller']->setHeader(_("Вопрос:")."&nbsp;".v_bbparse_vopros($kod,$vopros,$vopros[vopros],1,array()));
   
   /*echo "<Font class=s8>Файл: N$rf[fnum] /
      Тип: {$attachtype[$rf[ftype]][0]} /
      Изменена: ".date("d/m/Y",$rf[fdate])." /
      Имя: $rf[fname]</font>";*/

   echo "<form action=$PHP_SELF method=post name=main>$asessf
   <input type=hidden name=cid value=\"$cid\">
   <input type=hidden name=kod value=\"$base\">
   <input type=hidden name=c value=\"post\">
   ";

   $url="test_attach.php?mode=img&num=$rf[fnum]&".v_attach_url($kod)."$sess";

   $htmlmap=v7_map($kod,$vopros[map]);
   echo $htmlmap;

   echo "<table border=0 cellspacing=0 cellpadding=0><tr>
   <td><div ondblclick='mapclickedit(event);return false;'>".
   "<img id='image' src='$url' USEMAP='#map_$base' ISMAP border=0></div></td></tr></table>";

   $map=v7_sql2map($vopros[map]);
   echo "<P>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <tr><td>"._("Укажите области для вариантов:")."</td><td align=right>
   <span title='"._("Вставить прямоугольник (требует 4 координаты: x1:y1 и x2:y2)")."' class=\"tooltip\">&nbsp;</span><a href='#' onclick=\"wordclick(event,'RECT'); return false;\">RECT</a>&nbsp;
   <span title='"._("Вставить полигон (требует набор пар координат: x1:y1, x2:y2, x3:y3, ...)")."' class=\"tooltip\">&nbsp;</span><a href='#' onclick=\"wordclick(event,'POLY'); return false;\">POLY</a>&nbsp;
   <span title='"._("Вставить круг (требует координаты ценрта и радиус: x:y и radius)")."' class=\"tooltip\">&nbsp;</span><a href='#' onclick=\"wordclick(event,'CIRC'); return false;\">CIRC</a>
   </td></tr></table><P>";


   $i=1;
   foreach ($vopros[variant] as $k=>$v) {
      echo "
      <table width=100% border=0 cellspacing=0 cellpadding=0><tr><Td colspan=2>
      <b><input type=radio onclick='return 0' name=forclick id=forclick$k></b> ".
      ($vopros[otvet]==$k?"<b>":"").
      v_bbparse($kod,$v).
      ($vopros[otvet]==$k?"</b>":"").
      "</td></tr><tr>
      <td width=5% align=right>&nbsp;</td>
      <td width=95%><input type=text name=maptext[$k] id=tx$k style='width:100%' onclick='mapfocus($k)' value=\"";
      if (is_array($map[$k]))
      foreach ($map[$k] as $kk=>$vv) {
         echo "$vv[type] ".str_replace(","," ",$vv[xy])." $vv[title]; ";
      }
      echo "\"></td></tr></table>";
      $i++;
   }
   $GLOBALS['controller']->page_id = -1;
   $GLOBALS['controller']->setHelpSection(
       "<p>
           "._("Щелчок (click) на изображении - выбор области, соотвествующей варианту")."<br>
           "._("Двойной щелчок (double click) на изображении - вставка координат в строку ввода").
       "</p>"
       );

   echo "<p>
   <input type=hidden name=c2 value=\"ok\">
   <table width='200' border='0' cellspacing='1' cellpadding='1' align='right'><tr>
   <td>".okbutton(_("Применить"),"onclick=\"document.main.c2.value='update';document.main.submit();return false;\"")."</td>
   <td>".okbutton("","onclick=\"document.main.c2.value='ok'\"")."</td></tr></table>";


   echo $html[1];

   $keys=array_keys($vopros[variant]);
?>
   <script>
   id=document.getElementById('tx<?=$keys[0]?>');
   document.main.focus();
   id.focus();
   curmap='<?=$keys[0]?>';
   function mapfocus(n) {
      curmap=n;
   }
   function mapclick(base,num) {
      //alert(base+" "+num);
      id=document.getElementById('forclick'+num);
      if (!id) return;
      id.click();
   }
   function mapclickedit(e) {
      
      // Tested in IE 6, FireFox 1.5, Opera 9.02

      id=document.getElementById('tx'+curmap);
      image = document.getElementById('image');
      
      var x = y = 0;

      if (image && (e.clientX != undefined) && (e.clientY != undefined)) {
          x = e.clientX-image.x; y = e.clientY-image.y;
          var offset = $('#image').offset();
          x = Math.round(e.clientX-offset.left); y = Math.round(e.clientY-offset.top);
      }

      if ((e.offsetX != undefined) && (e.offsetY != undefined)) {
          x = e.offsetX; y = e.offsetY;
      }
      id.value+=" "+x+" "+y;
      id.focus();
   }
   function wordclick(e,word) {
      id=document.getElementById('tx'+curmap);
      id.value+=word+" ";
      id.focus();
   }
   </script>
<?

   /*
   echo "<br><br><br><P>Карта картинки:<br>
   <textarea name=mapedit rows=10 cols=40 style='width:100%'>".html($vopros[map])."</textarea>
   <P>
   Синтаксис составление областей карты:<br>
   [Вариант1] [ТипОбласти] [Координаты] [Подсказка] <b>;</b><br>
   [Вариант2] [ТипОбласти] [Координаты] [Подсказка] <b>;</b><br>
   [Вариант3] [ТипОбласти] [Координаты] [Подсказка] <b>;</b><br>
   <P>
   Описание:
   <ul>
   <li>Вариант: число, номер варианта ответа в вопросе, от 1 и больше
   <li>ТипОбласти: RECT, CIRCLE или POLY
   <li>Координаты: несколько чисел через пробел (их описание ниже)
   <li>Подсказка: всплывающая текстовая надпись над областью (можно не задавать)
   </ul>
   Описание координат для разных типов областей:
   <ul>
   <li>RECT - прямоугольник. Задаются 4 числа, координаты углов: X1 Y1 X2 Y2
   <li>CIRCLE - окружность. Задаются 3 числа, координаты центра и радиус: X Y Radius
   <li>POLY - произвольный многоугольник. Задается любое число пар координат
   по периметру этого многоугольника:
   X1 Y1 X2 Y2 ...  Xn Yn
   </ul>
   После каждой из записей нужно ставить точку с запятой.
   <P>
   Пример:<br>
   1 RECT 10 10 20 20 Вариант 1;<br>
   2 CIRCLE 30 50 15 Вариант 2;<br>
   <P>";
   */


   break;


case "post":

   $rv=sqlval("SELECT * FROM list WHERE kod='".addslashes($kod)."'","errTM111");
   if (!is_array($rv)) exit("errTM112: "._("такого вопроса нет"));

   include_once("template_test/7-v.php");
   $vopros=v_sql2php_7($rv);
   $mapedit="";

   foreach ($_REQUEST['maptext'] as $k=>$v) {
      $v=trim($v);
      if ($v=="") continue;
      $tmp="";
      foreach (preg_split("!;!s",$v) as $vv) {
         $vv=trim($vv);
         if ($vv=="") continue;
         $tmp.="$k $vv;";
      }
      $mapedit.=$tmp;
      if (substr($tmp,-1)!=";") $mapedit.=";\n"; else $mapedit.="\n";
   }
   $vopros[map]=substr($mapedit,0,5000);
   $vopros[kod]=$kod;

   $arr=v_php2sql_7($vopros);
   update_list($arr);

   if ($c2=="update") {
       exit(refresh("$PHP_SELF?c=&cid=$cid&kod=".ue($base)."$asess"));
   }
   echo "<script>window.close()</script>";

   break;


}
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>