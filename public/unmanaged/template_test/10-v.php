<?
/*

Заполнение формы - вписать надостающие слова в фразу. В случае полного
совпадения начисляется BALMAX.

array (

   vopros => "вопрос"         <-- вопрос

   text1 => "ФразаДо"

   text2 => array (
      "слово1",
      "слово2",
      "слово3",
      ....
   )

   text3 => array(
      "ФразаПосле1",
      "ФразаПосле2",
      "ФразаПосле3",
      ...
   )

)

Получится:  ФразаДо | _____слово1 | ФразаПосле1 | _____слово2 | ФразаПосле2 | ....

*/


$GLOBALS['v_edit_10']=array(
   'title'=>_("тренажер"),
   'info'=>_("Этот тип вопроса предназначен для создания СПЕЦИАЛИЗИРОВАННЫХ тестов, связанных с запуском внешней программы-тренажера."),
   'balcalc' => "user",
   'goodotvet'=>1,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
   ),
   'variant' => array (
      'text2'  => array("string",_("Параметры вызова"),500)
   ),
   'default'=>array(
      'vopros'=>"",
      'text1'=>"",
      'text2'=>array('1'=>''),
      'balmin'=>0,
      'balmax'=>1,
   )
);

function v_sql2php_10(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr=trim($data[0]);
   $text1=trim($data[1]);
   if ((count($data)-2)%2!=0) err(_("Ошибка структуры данных в вопросе:")." error=1, type=5, kod=$vopros[kod]",__FILE__,__LINE__);
   $varcount=0;
   $goodotvet=array();
   for ($i=2,$y=1; $i<count($data); $i+=2,$y++) {
      $text2[]=trim($data[$i]);
      $text3[]=trim($data[$i+1]);
      $varcount++;
      $goodotvet['gotvet'][]=_("параметр")." N$y:  ".trim($data[$i]);
   }
   if (count($text2)==0) $goodotvet['gerror1']="EMPTY";
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'text1'=>$text1,
      'text2'=>$text2,
      'text3'=>$text3,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>10,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_10($arr) {
  // заносит результаты ответа в БД
   global $brtag,$brremove;
   $x=array();
   $x[]=trim(brremove($arr[vopros]));
   $x[]=trim(brremove($arr[text1]));
   if (is_array($arr[text2])) {
   foreach ($arr[text2] as $k=>$v) {
      $x[]=trim(brremove($arr[text2][$k]));
      $x[]=trim(brremove($arr[text3][$k]));
   }
   }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>10,
      'qmoder'=>0,
      'balmax'=>$arr[balmax],
      'balmin'=>$arr[balmin],
      'qdata'=>implode($brtag,$x),
      'adata'=>"",
      'qtema'=>$arr[qtema],
      'url'=>$arr[url],
      'timetoanswer'=>$arr[timetoanswer],
   );
   return $out;
}



function v_vopros_10(&$vopros,$tm,$number,&$attach) {
   // формирует и выводит вопрос
   global $s;
   ob_start();
   //pr($vopros);
   $kod=$vopros[kod];
   $v_number=$number+1;

   //$v_vopros=v_bbparse($kod,$vopros[vopros]);
   $v_vopros=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   $v_text1=$vopros[text1];//v_bbparse($kod,$vopros[text1]);

   if (count($vopros[text2])){
     $v_i=0;
     foreach ($vopros[text2] as $k=>$v) {
      $v_text2=$vopros[text2][$k];
//      $v_text2=v_bbparse($kod,$vopros[text2][$k]);
      $v_text3=$vopros[text3][$k]; //v_bbparse($kod,$vopros[text3][$k]);
	  $v_text1 .= " {$v_text3}";
      $v_i++;
     }
//     $v_text1 = substr($v_text1, 0, -1);
   }
   include("$tm-v-top.html");
   include("$tm-v-bottom.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}

function isOk_10( $vv, $otvet ){
// проверяет находится ли ответ в диапазоне задаваемом vv
// otvet - то что ввел пользователь
// vv - шаблон ответа
// [1..0]
//  $vv = "[5:30.3]";
//  $otvet ="5.0	";
 /*$OK=1;
  $vvv=explode("&", $vv);
  foreach($vvv as $v){
    $ok=0;
    if( $vv == $otvet )
      $ok=1;
    else{

      $tmp="^ *\\[([0-9]+(\\.[0-9]+)?)(:([0-9]+(.[0-9]+)?))?\\] *$";

      if( eregi( $tmp, $vv, $p ) ){
//      $k="(".$p[1].")(".$p[2].")(".$p[3].")(".$p[4].")(".$p[5].")".$p[0]."<BR>";
        if( isset($p[4]))
          if( $otvet >= $p[1] && $otvet <= $p[4] )
            $ok.=1;
        else // только один элемент
          if( $otvet == $p[1] )
            $ok.=1;//"Ok<BR>";
      }
    }
    if( $ok==0 ) $OK=0;
  }
  return( $OK.$k );*/
  return 1;
}


function v_otvet_10( &$vopros, $tm, $number, &$attach, &$form ) {
   // обрабатывает ответы на правильность - неправильность - и выводит на экран для проверки преподавалетем
   $doklad=array();

   $bal=0;
   $ok=1;
   $otv=array();
   $otv2=array();

   $w = (count($vopros[text2])) ? ($vopros[balmax] - $vopros[balmin]) / count($vopros[text2]) : 1; // вес(доля) каждого ответа

   $i=0;
   $ball=0;
   if (is_array($vopros[text2])){
   foreach ($vopros[text2] as $k=>$v) {
      $vars=explode(";",sl(trim($v)));
      $otvet=strval(sl(trim($form[otvet][$i])));
      $found=0;
      foreach ($vars as $vv) {
         $vv=strval(trim($vv));
         if ( isOk_10( $vv, $otvet ) ) {     // проверка правильности
            $found=1;
            break;
         }
      }

      $v_text=$vopros[text3][$k];

      if ($found) {  // если тек вариант верен
         $otv[]=1;
         $doklad[main][]="$v_text ".substr($form[otvet][$i],0,1024);
         $doklad[info][]="Ok";
         $doklad[good][]=1;
         $ball+=$w;    // добавим вес этого варианта
      }
      else {
         $otv[]=0;
         $ok=0;
         $doklad[main][]="$v_text ".substr($form[otvet][$i],0,1024);
         $doklad[info][]="Err";
         $doklad[good][]=0;
      }
      $otv2[]=substr($form[otvet][$i],0,1024);
      /////////////////


      ////////////////
      $i++;
   }
   }
   if ($ok==1) {  // если ответ полный
      $bal=$vopros[balmax];
      $good=100;
   }
   else {    // если ответ ЧАСТИЧНО полный
      $bal=$vopros[balmin]+$ball;
      $good=0;
   }

   $out=array('bal'=>$vopros[balmax],'otv'=>_("Задание выполнено"),'good'=>100,
      'info'=>_("Задание выполнено"),'doklad'=>_("Задание выполнено"));
//   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,
//      'info'=>substr(implode(";",$otv2),0,255),'doklad'=>$doklad );

   if (tdebug) {
      pr($vopros);
      pr($form);
      pr($out);
   }

   return $out;

}




?>