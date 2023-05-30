<?


/*

Вопрос на соответствие. Кол-во обоих вариантов должно быть одинаковое.
Оценка BALMAX ставится при полном верном соответствии в ответе учащегося,
иначе BALMIN.

array (
   
   vopros => "вопрос"           <-- вопрос
  
   variant1 => array (          <-- варианты фраз
      a => "фраза 1"
      b => "фраза 2"
      c => "фраза 3"
   )
   
   variant2 => array (          <-- варианты соответствий (столько ко же, сколько variant1)
      a => "соответствие один"
      b => "соответствие два"
      c => "соответствие три"
   )

)

*/


$GLOBALS['v_edit_12']=array(
   'title'=>_("на упорядочивание"),
   'info'=>_("Вопрос для упорядочивания списка выражений в определённом порядке."),
   'balcalc' => "user",
   'goodotvet'=>1,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
   ),
   'variant' => array (
      'variant1'  => array("textarea",_("Выражение"), 450),
   ),
   'default'=>array(
      'vopros'=>"",
      'variant1'=>array('1'=>'','2'=>'','3'=>''),
      'balmin'=>0,
      'balmax'=>1,
   ),
   'msg_editwindow'=>_("Упорядочивание списка выражений"),
);






function v_sql2php_12(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr=$data[0];
   $v1=array();
   $v2=array();
   $varcount=0;
   $goodotvet=array();
   if ((count($data)-1)%3!=0) err(_("Ошибка структуры данных в вопросе:")." error=1, type=3, kod=$vopros[kod]",__FILE__,__LINE__);
   for ($i=1; $i<count($data); $i+=3) {
      $v1[$data[$i]]=trim($data[$i+1]);
      $varcount++;
      $goodotvet['gotvet'][]=$v1[$data[$i]];
   }
   if (count($v1)==0) $goodotvet['gerror1']="EMPTY";
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'variant1'=>$v1,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>12,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_12($arr) {
   global $brtag,$brremove;
   $x=array();
   $y=array();
   $x[]=trim(brremove($arr[vopros]));
   if (count($arr[variant1]))
      foreach ($arr[variant1] as $k=>$v) {
         $x[]=$k;
         $x[]=trim(brremove($arr[variant1][$k]));
         $x[]= '';
      }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>12,
      'qmoder'=>0,
      'balmax'=>$arr[balmax],
      'balmin'=>$arr[balmin],
      'qdata'=>implode($brtag,$x),
      'adata'=>"",
      'qtema'=>$arr[qtema],
      'url'=>$arr[url],
      'timetoanswer'=>$arr[timetoanswer],
      'is_shuffled' => 1
   );
   return $out;
}




function v_vopros_12(&$vopros,$tm,$number,&$attach) {
   global $s;
   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;

   //$v_text=v_bbparse($kod,$vopros[vopros]);
   $v_text=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   include("$tm-v-top.html");
  
   $newvar=array();
   foreach ($vopros[variant1] as $k=>$v) $newvar[mt_rand(10000,99999)]=$k;
   ksort($newvar);     

   $v_i=0;             
   foreach ($newvar as $newkey) {
      $v_i++;
      $v_text=v_bbparse($kod,$vopros[variant1][$newkey]);
      $v_ans = md5($vopros['kod'].$newkey);
      include("$tm-v-line.html");
   }

   include("$tm-v-bottom.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}


function v_otvet_12(&$vopros,$tm,$number,&$attach,$form) {
   
   $doklad=array();
   
   $ok=1;
   $bal=0;
   $otv=array();
   
   $intNumRight = 0;
   $intNumAll = 0;

   foreach ($vopros[variant1] as $k=>$v) {
   	  $intNumAll++;
      $md5 = md5($vopros['kod'].$k);
   	  if (!isset($form[$md5])) {
         $doklad[error][]="HackDetect: "._("Не передан ответ на вариант")." N$k.";
         alert("HackDetect: "._("Не передан один из вариантов ответа на вопрос")." $number. ".sprintf(_("Зачисляю за весь вопрос %s баллов."), $vopros[balmin]));
         $otv[]=-400;
         $ok=0;
         continue;
      }
      if ($form[$md5]=="") { 
         $doklad[main][]=_("Ничего не выбрано на вариант")." N$k [".strip_tags($vopros[variant1][$k])."]";
         $doklad[info][]=_("Вариант пропущен");
         $doklad[good][]=0;
         $otv[]=-402;
         $ok=0;
         continue;
      }
      $otv[]=$form[$md5];
      $doklad[main][]=sprintf(_("Варианту N%s [%s] выбран порядок [%s]"), $k, strip_tags(strbig($vopros[variant1][$k],20)), $form[$md5]);
      if ($form[$md5] == $k) {
         $doklad[info][]=_("Правильно");
         $doklad[good][]=1;
         $intNumRight++;         
      }
      else { 
         $doklad[info][]=_("Неправильно");
         $doklad[good][]=0;
         $ok=0;
      }
   }
   
   $bal = (($vopros[balmax]-$vopros[balmin])*$intNumRight/$intNumAll) + $vopros['balmin'];
   $good = $intNumRight*100/$intNumAll;
   
/*   if ($ok==1) {
      $bal=$vopros[balmax];
      $good=100;
   }
   else {
      $bal=$vopros[balmin];
      $good=0;
   }
*/
   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,'info'=>"",'doklad'=>$doklad);
   if (tdebug) {
      pr($vopros);
      pr($form);
      pr($out);
   }
   
   return $out;
}






?>