<?


/*

Можно ответить только на 1 вариант (radio).

array (

   vopros => "вопрос"     <-- вопрос

   variant => array (     <-- варианты ответов
      a => "ответ 1"
      b => "овтет 2"
      c => "овтет 3"
   )

   otvet => b             <-- код верного варианта

)

*/


$GLOBALS['v_edit_1']=array(
   'title'=>_("одиночный выбор"),
   'info'=>_("Вопрос может содержать несколько вариантов ответа, среди которых
выбрать можно только один. При редактировании вопроса отметьте
правильный вариант галочкой."),
   'balcalc'=>'user',
   'qmoder'=>0,
   'goodotvet'=>1,
   'string'=>array(
      'vopros'=>array("textarea",_("Формулировка вопроса"),0),
      'otvet'=>array("radiostr",_("Верный вариант"),5),
   ),
   'variant'=>array (
      'otvet'=>array("radiostr",_("Правильный вариант"),5),
      'variant'=>array("textarea",_("Вариант ответа"),450),
   ),
   'default'=>array(
      'vopros'=>"",
      'variant'=>array('1'=>'','2'=>'','3'=>''),
      'otvet'=>1,
      'balmin'=>0,
      'balmax'=>1,
   )
);




function v_sql2php_1(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr=trim($data[0]);
   $var=array();
   $otv=0;
   $varcount=0;
   $goodotvet=array();
   if (count($data)>1) {
      for ($i=1; $i<count($data); $i+=2) {
         $var[$data[$i]]=trim($data[$i+1]);
         $varcount++;
      }
      $otv=trim($vopros[adata]);
   }
   if (isset($var[$otv])) $goodotvet['gotvet'][]=$var[$otv];
   else {
      $goodotvet['gerror1']="NOTGOOD";
      $goodotvet['gwarn']=1;
   }
   if (count($var)==0) $goodotvet['gerror1']="EMPTY";
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'variant'=>$var,
      'otvet'=>$otv,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>1,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
  );
}

//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_1( $arr ) {
   //
   global $brtag,$brremove;
   $x=array();
   $y=array();
   $x[]=trim(brremove($arr[vopros]));
   foreach ($arr[variant] as $k=>$v) {     // в variant находится строка варианта ответа - их много
      $x[]=trim(brremove($k)); //echo "$k: $v<BR>"
      $x[]=trim(brremove($v));
   }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>1,
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


function v_vopros_1(&$vopros,$tm,$number,&$attach) {
// выводит вопрос на экран

   global $s, $mode;
   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;

   //$v_text=v_bbparse($kod,$vopros[vopros]);

   $v_text=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
      include("$tm-v-top.html");


   $out=ob_get_contents();
   ob_end_clean();

   $v_i = (defined('DEBUG_SELENIUM_RANDOMANSWERS') && DEBUG_SELENIUM_RANDOMANSWERS) ? rand(1, count($vopros[variant])) : 1;
   $cnt = 0;
   $arrVariant = array();



   foreach ($vopros[variant] as $k=>$v) {
      $checked = false;
      if (isset($vopros['otvets'][0]) && ($vopros['otvets'][0]==$k)) $checked = true;
   	  ob_start();
      $v_i_rand = $v_i++%count($vopros[variant]);
      $v_text=v_bbparse($kod,$v);
      $v_ans=$k;
      include("$tm-v-line.html");
      $arrVariant[] = ob_get_contents();
	  ob_end_clean();
   }

   if ($mode == 1) {
		srand ((float)microtime()*1000000);
		if($vopros['is_shuffled'] == 1) {
			shuffle ($arrVariant);
		}
   }
   foreach ($arrVariant as $val) {
   		$out .= $val;
   }

   ob_start();
   include("$tm-v-bottom.html");
   $out .= ob_get_contents();
   ob_end_clean();

   return $out;
}


function v_otvet_1(&$vopros,$tm,$number,&$attach,&$form) {
   /*
   echo "VOPROS";
   pr($vopros);
   echo "FORM";
   pr($form);
   */

   $doklad=array();
   $skip=0;

   if ($skip==0 && !isset($form[otvet])) {
      $doklad[error][]=_("Ответ на вопрос не получен");
      if (TEST_TYPE == _('Опрос')) {
          alert(sprintf(_("Ответ на вопрос N %s не получен."), $number+1));
      } else {
          alert(sprintf(_("Ответ на вопрос N %s не получен. За этот вопрос зачисляю баллов: %s."), $number+1, $vopros[balmin]));
      }
      $skip=1;
      $bal=0;
      $good=0;
      $otv[]=-400;
   }

   if ($skip==0 && !isset($vopros[variant][$form[otvet]])) {
      $doklad[error][]="HackDetect: "._("попытка передать неверный код ответа");
      alert("HackDetect: "._("попытка передать неверный код ответа").": ".html($form[otvet])."
              "._("За этот вопрос зачисляю баллов").": $vopros[balmin].");
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
    $doklad['qtype'] = 1;

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