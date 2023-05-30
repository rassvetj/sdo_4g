<?


/*

Можно ответить на любое кол-во вариантов, но BALMAX зачислится если
ответить только и только на правильные варианты.
За правильные ответы нужно вписывать положительное число баллов.
Баллы начисляются только при выборе всех не нулевых вариантов.
Если выбрать хотя бы один нулевой вариант - начисляется 0 баллов.

array (

   vopros => "вопрос"     <-- вопрос

   variant => array (     <-- варианты ответов
      a => "ответ 1"
      b => "овтет 2"
      c => "овтет 3"
   )

   otvet => array (       <-- 0/1 - флаг правильности варианта (это не баллы!)
      a => 1              <-- правильный вариант
      b => 0              <-- не правильный вариант
      c => 0              <-- не правильный вариант
      d => 1              <-- правильный вариант
   )

)

*/



$GLOBALS['v_edit_2']=array(
   'title'=>_("несколько верных вариантов"),
   'info'=>_("Вопрос может содержать несколько верных вариантов ответа.
Ответ считается правильным только в том случае, если учащийся отметил галочками все
верные варианты. Если учащийся не отметит хотя бы один из указанных преподавателем
вариантов  или отметит не все  верные варианты, то вопрос в целом не заcчитывается, т.е.
зачисляется минимальное количество баллов. Если учащийся отмечает галочками только
варианты с правильными ответами, то он получает максимальное  количество баллов."),
   'balcalc' => "user",
   'goodotvet'=>1,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
   ),
   'variant' => array (
      'otvet'    => array("checkbox",_("Правильный вариант"),5),
      'variant'  => array("string",_("Вариант ответа"),450),
   ),
   'default'=>array(
      'vopros'=>"",
      'variant'=>array('1'=>'','2'=>'','3'=>''),
      'otvet'=>array('1'=>0),
      'balmin'=>0,
      'balmax'=>1,
   )
);




function v_sql2php_2(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr=$data[0];
   $var=array();
   $otv=array();
   $varcount=0;
   $goodotvet=array();
   if (trim($vopros[adata])!="" && count($data)>0) {
      for ($i=1; $i<count($data); $i+=2) {
         $var[$data[$i]]=$data[$i+1];
      }
      $data=explode($GLOBALS[brtag],$vopros[adata]);
      if (count($data)!=count($var)) {
         pr($data);
         pr($var);
         err(_("Ошибка структуры данных в вопросе:")." type=2, kod=$vopros[kod]",__FILE__,__LINE__);
      }
      $i=0;
      foreach ($var as $k=>$v) {
         $otv[$k]=$data[$i++];
         $varcount++;
         if ($otv[$k]) $goodotvet['gotvet'][]=$var[$k];
      }
   }
   if (count($goodotvet['gotvet'])==0) {
      $goodotvet['ginfo']=_("Ни один ответ не помечен как правильный (это нормальная ситуация). Чтобы ответить на этот вопрос правильно (и заработать максимальный балл), учащийся во время тестировании должен снять все галочки (вернее, ни одну не ставить).");
   }
   if (count($otv)==0) $goodotvet['gerror1']="EMPTY";
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'variant'=>$var,
      "otvet"=>$otv,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>2,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_2($arr) {
   global $brtag,$brremove;
   $x=array();
   $y=array();
   $x[]=trim(brremove($arr[vopros]));
   foreach ($arr[variant] as $k=>$v) {
      $x[]=trim(brremove($k));
      $x[]=trim(brremove($v));
      if ($arr[otvet][$k]>0) $y[]=1; else $y[]=0;
   }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>2,
      'qmoder'=>0,
      'balmax'=>$arr[balmax],
      'balmin'=>$arr[balmin],
      'qdata'=>implode($brtag,$x),
      'adata'=>implode($brtag,$y),
      'qtema'=>$arr[qtema],
      'url'=>$arr[url],
      'timetoanswer'=>$arr[timetoanswer],
   );
   return $out;
}



function v_vopros_2(&$vopros,$tm,$number,&$attach) {
   global $s;
   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;

   //$v_text=v_bbparse($kod,$vopros[vopros]);
   $v_text=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   include("$tm-v-top.html");

   foreach ($vopros[variant] as $k=>$v) {
      $rand[$k]=mt_rand(0,9999);
   }
   asort($rand);

   $v_i=0;
   foreach ($rand as $k=>$v) {
      $v=$vopros[variant][$k];
      $v_i++;
      $v_text=v_bbparse($kod,$v);
      $v_ans=$k;
      include("$tm-v-line.html");
   }

   include("$tm-v-bottom.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}


function v_otvet_2(&$vopros,$tm,$number,&$attach,$form) {
   /*
   echo "VOPROS";
   pr($vopros);
   */
   //echo "--------FORM-------";
   //pr($form);
   //echo "-------------------<br>";

   $doklad=array();

   $ok=1;
   $bal=0;
   $otv=array();
   $w = 1 / count ( $v ); // вес(доля) каждого ответа
   foreach ($vopros[otvet] as $k=>$v) { // для всех ответов и для текущего ответа v (v=0 v=1) под номером k
      $doklad[main][]=(isset($form[$k])?_("отмечен"):_("не отмечен"))." "._("вариант")." N$k [".strbig($vopros[variant][$k],20)."]";
      if ($v>0 && isset($form[$k]) || $v==0 && !isset($form[$k])) {
         $doklad[info][]=_("Правильно");
         $doklad[good][]=1;
      }
      else {
         $doklad[info][]=_("Неправильно");
         $doklad[good][]=0;
      }
      if ( $v > 0  && !isset($form[$k]) ) $ok=0;    // ответ следовало выбрать а он НЕ выбран
      if ( $v > 0  &&  isset($form[$k]) ) $bal+=$v;  // ответ следовало выбрать и он выбран
      if ( $v == 0 &&  isset($form[$k]) ) $ok=0;    // ответ НЕ следовало выбрать а он выбран
      if (isset($form[$k]))
        $otv[]=1;
      else
        $otv[]=0;
      //echo "<li>k=$k , v=$v , ok=$ok , bal=$bal<br>";
   }

   if ($ok==0) {
      $bal=$vopros[balmin];
      $good=0;
   }
   else {
      $bal=$vopros[balmax];
      $good=100;
   }

   /*
   echo "
   bal=$bal<br>
   otv=$otv<br>
   good=$good<br>
   bmax=$bmax<br>
   bmin=$bmin<br>
   ";
   */
   //pr($otvvar);
   //pr($otvbal);


   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,'info'=>"",'doklad'=>$doklad);


   //pr($out); exit;
   return $out;

}




?>