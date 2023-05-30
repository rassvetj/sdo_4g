<?

/*

Можно ответить только на 1 вариант, кликнув мышкой на картинку с картой.

array (

   vopros => "вопрос"     <-- вопрос

   variant => array (     <-- варианты ответов
      0 => "ответ 1"
      1 => "овтет 2"
      2 => "овтет 3"
   )

   otvet => 0             <-- код верного варианта

   map => строка          <-- код карты
                                   0 rect 0 0 123 234 заголовок для варианта 1;
                                   1 circle 160 160 90 90 заголовок для варианта 2;

)

*/


$GLOBALS['v_edit_7']=array(
   'title'=>_("выбор по карте на картинке"),
   'info'=>_("Вопрос содержит несколько вариантов ответа, среди которых
выбрать можно только один. К вопросу обязательно должна прилагаться картинка
для выбора нужного варианта ответа на ней. При редактировании вопроса отметьте
правильный вариант галочкой, загрузите картинку для вопросу и задайте
карту - координатные области на картинке, относящиеся к каждому из вариантов ответа."),
   'balcalc'=>'user',
   'qmoder'=>0,
   'goodotvet'=>1,
   'string'=>array(
      'vopros'=>array("textarea",_("Формулировка вопроса"),0),
      'otvet'=>array("radiostr",_("Верный вариант"),5),
      'map'=>array("hidden",_("Карта: список координат для областей ответа")." &lt;<a href=\"javascript:mapedit('{\$cid}','<?=kod2base(\$kod);?>','<?=count(\$cid);?>')\"><b>"._("редактировать")."</b></a>&gt;"),
      'showvars'=>array("checkbox",_("Показывать варианты ответа (иначе только картинку)"),0),
   ),
   'variant'=>array (
      'otvet'=>array("radiostr",_("Правильный вариант"),"10"),
      'variant'=>array("string",_("Вариант ответа"),"450"),
   ),
   'default'=>array(
      'vopros'=>"",
      'variant'=>array('1'=>'','2'=>'','3'=>''),
      'otvet'=>1,
      'balmin'=>0,
      'balmax'=>1,
      'showvars'=>1,
   ),
   'includes'=>array(
      array("formedit","7-js-formedit.html","include_once"),
   )
);



function v_sql2php_7(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr=trim($data[0]);
   $map=trim($data[1]);
   $showvars=trim($data[2]);
   $var=array();
   $otv=0;
   $varcount=0;
   $goodotvet=array();
   if (count($data)>3) {
      for ($i=3; $i<count($data); $i+=2) {
         $var[$data[$i]]=trim($data[$i+1]);
         $varcount++;
      }
      $otv=trim($vopros[adata]);
      if (isset($var[$otv])) $goodotvet['gotvet'][]=$var[$otv];
   }
   if (!isset($goodotvet['gotvet'])) {
      $goodotvet['gerror1']="NOTGOOD";
      $goodotvet['gwarn']=1;
   }
   if (count($otv)==0) $goodotvet['gerror1']="EMPTY";
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'variant'=>$var,
      'otvet'=>$otv,
      'map'=>$map,
      'showvars'=>$showvars,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>7,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}

//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_7($arr) {
   global $brtag,$brremove;
   $x=array();
   $y=array();
   $x[]=trim(brremove($arr[vopros]));
   $x[]=trim(brremove($arr[map]));
   $x[]=intval($arr[showvars]);
   foreach ($arr[variant] as $k=>$v) {
      $x[]=trim(brremove($k));
      $x[]=trim(brremove($v));
   }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>7,
      'qmoder'=>0,
      'balmax'=>$arr[balmax],
      'balmin'=>$arr[balmin],
      'qdata'=>implode($brtag,$x),
      'adata'=>trim($arr[otvet]),
      'qtema'=>trim($arr[qtema]),
      'url'=>trim($arr[url]),
      'timetoanswer'=>$arr[timetoanswer],
   );
   return $out;
}


function v_vopros_7(&$vopros,$tm,$number,&$attach) {
   global $s,$asess;
   ob_start();
   $kod=$vopros[kod];
   $base=kod2base($kod);
   $v_number=$number+1;

   echo "\n\n\n<script>if (!codes || !codes.length) { var codes=new Array(); }\ncodes[$number]='";
   $v_i=0;
   foreach ($vopros[variant] as $k=>$v) {
      $v_i++;
      echo " $k=".md5("$number $v_i")." ";
   }
   echo "';\n";

?>

function mapclick(base,num,number) {

   var n=codes[number].indexOf(" "+num+"=");

   if (n==-1) {alert('errJS266'); return}
<?
    if ($vopros[showvars]) {
?>
   var name=codes[number].substr(n+num.length+2,32);
<?
    } else {
?>
   var name = "hidden" + number;
<?
    }
?>
   var id=document.getElementById(name);

   if (id==null) {alert('errJS267'); return}
<?
    if ($vopros[showvars]) {
?>
   var name=codes[number].substr(n+num.length+2,32);
<?
    } else {
?>
   var name = "hidden" + number;
<?
    }
?>
   id.select();
   id.click();
   document.getElementById('ischecked_'+number).value=1
<?
    if (!$vopros[showvars]) {
?>
   id.value = num;
<?
    }
?>
}
</script>
<?


   $v_text=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,array()); //$attach);
   include("$tm-v-top.html");

   echo "<tr><td colspan=10>";

   $url=$GLOBALS['sitepath']."test_attach.php?mode=img&num={$attach[0][fnum]}&".v_attach_url($kod)."$asess";
   $htmlmap=v7_map($kod,$vopros[map],$number);
   echo $htmlmap;

   echo "<div width=190%>".
   "<img src='$url' USEMAP='#map_$base' ISMAP border=0></div>";

   echo "</td></tr>";

   $v_i=0;
   if (!$vopros[showvars]) include("$tm-v-line-hidden.html");
   foreach ($vopros[variant] as $k=>$v) {
      $checked = false;
      if ($vopros['otvets'][0]==$k) $checked = true;
      $v_i++;
      $v_text=v_bbparse($kod,$v);
      $v_ans=$k;
      if ($vopros[showvars]) {
         include("$tm-v-line.html");
      }
      else {
//         include("$tm-v-line-hidden.html");
      }
   }

   include("$tm-v-bottom.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}


function v_otvet_7(&$vopros,$tm,$number,&$attach,&$form) {

   $doklad=array();
   $skip=0;

   if ($skip==0 && !isset($form[otvet])) {
      $doklad[error][]=_("Ответ на вопрос не получен");
      alert(sprintf(_("Ответ на вопрос N %s не получен. За этот вопрос зачисляю баллов: %s."), $number+1, $vopros[balmin]));
      $skip=1;
      $bal=0;
      $good=0;
      $otv[]=-400;
   }

   if ($skip==0 && !isset($vopros[variant][$form[otvet]])) {
      $doklad[error][]="HackDetect: "._("попытка передать неверный код ответа.");
      alert(" HackDetect: "._("попытка передать неверный код ответа:")." ".html($form[otvet])."
              "._("За этот вопрос зачисляю баллов:")." $vopros[balmin].");
      $skip=1;
      $bal=0;
      $good=0;
      $otv[]=-401;
   }

   if (!$skip) {
      $doklad[main][]=_("Выбран вариант")." N$form[otvet]: ".strip_tags($vopros[variant][$form[otvet]]);
      if (trim($vopros[otvet])==trim($form[otvet])) {
         $bal=$vopros[balmax];
         $good=100;
         $doklad[info][]=_("Правильно");
         $doklad[good][]=1;
      }
      else {
         $bal=$vopros[balmin];
         $good=0;
         $doklad[info][]=_("Неправильно");
         $doklad[good][]=0;
      }
      $otv[]=$form[otvet];
   }

    $doklad['qtype'] = 7;

   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,'info'=>"",'doklad'=>$doklad);

   if (tdebug) {
      pr($vopros);
      pr($form);
      pr($out);
   }

   return $out;

}


#
# Взять карту, вырезать параметры и вернуть в спец. массиве $ok
# $map=array (
#    название области => array(
#       порядковый номер1 => array (
#          type => тип
#          xy   => координаты через запятую
#          title=> подсказка
#       )
#       порядковый номер2 => array( ... )
#       порядковый номер3 => array( ... )
#    )
# )
# Одна область может иметь несколько описаний.
#
function v7_sql2map($sqlmap) {

   preg_match_all("!([0-9]+)[\r\n ]+(RECT(?:ANGLE)?|CIRC(?:LE)?|POLY(?:GON)?)[\r\n ]+([0-9, ]+)(.*?);!is",$sqlmap,$ok);
   $map=array();

   foreach ($ok[1] as $k=>$v) {
      $tmp=array();
      $tmp[type]=$ok[2][$k];
      $tmp[title]=$ok[4][$k];
      $tmp[xy]=preg_replace("! +!",",",trim($ok[3][$k]));
      $map[$ok[1][$k]][]=$tmp;
   }

   return $map;
}

#
# Конвертация карты в HTML код
#
function v7_map($kod,$sqlmap,$number=-1) {

   $base=kod2base($kod);
   $out="\n\n\n<MAP NAME='map_$base'>\n";
   $map=v7_sql2map($sqlmap);

   foreach ($map as $k=>$v) {
      foreach ($v as $kk=>$vv) {
         $title=empty($vv[title])?"":" title=\"$vv[title]\"";
         $out.="<AREA SHAPE='$vv[type]' COORDS='$vv[xy]' ".
         "HREF=\"javascript:mapclick('$base','$k',$number)\"$title>\n";
      }
   }

   return $out."</MAP>\n\n\n";
}




?>