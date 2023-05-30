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


$GLOBALS['v_edit_5']=array(
   'title'=>_("заполнение формы"),
   'info'=>_("Составьте какой-либо текст с одним или несколькими  пропущенными словами,
значениями или диапазонами значений.
Эти пропущенные слова и должны будут ввести учащиеся. Сначала введите
параметры 'основной вопрос' и 'начальный текст', затем -  любое количество
параметров 'пропущенное слово' и 'последующий текст'. Чтобы закодировать
фразу 'Каждый _____ желает знать, где ______ фазан', нужно вписать слова
до первого пропуска ('Каждый') в поле 'начальный текст', далее слово 'охотник'
в первую строку колонки 'пропущенное слово' и затем последующий текст
('желает знать, где') в поле 'последующий текст' первой строки вариантов. Слова
('сидит' и 'фазан.')нужно ввести аналогичным образом во вторую строку. Если
учащийся введет все пропущенные слова верно, то начисляется максимальное
количество баллов, иначе минимальное баллов. Если ответ частично верен, то
начисляется часть от максимального балла.
Слова можно вводить в любом регистре, пробелы по краям слов не обрабатываются.
Для савнения значений как чисел, заключите значения в квадратные скобки - [5.0].
Для указания диапазона укажите границы через двоеточие [1.1:7.77]"),
   'balcalc' => "user",
   'goodotvet'=>1,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
      'text1'   => array("string",_("Начало&nbsp;фразы")),
   ),
   'variant' => array (
      'text2'  => array("string",_("Вариант заполнения пропуска (через \";\")"),250),
      'text3'  => array("string",_("Последующий текст"),250),
   ),
   'default'=>array(
      'vopros'=>"",
      'text1'=>"",
      'text2'=>array('1'=>'','2'=>'','3'=>''),
      'text3'=>array('1'=>'','2'=>'','3'=>''),
      'balmin'=>0,
      'balmax'=>1,
   )
);





function v_sql2php_5(&$vopros) {
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
      $goodotvet['gotvet'][]=_("слово")." N$y:  ".trim($data[$i]);
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
      'type'=>5,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_5($arr) {
   global $brtag,$brremove;
   $x=array();
   $x[]=trim(brremove($arr[vopros]));
   $x[]=trim(brremove($arr[text1]));
   foreach ($arr[text2] as $k=>$v) {
      $x[]=trim(brremove($arr[text2][$k]));
      $x[]=trim(brremove($arr[text3][$k]));
   }
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>5,
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

function v_vopros_5(&$vopros,$tm,$number,&$attach) {
   global $s;
   ob_start();
   $kod=$vopros[kod];
   //echo "<pre>";
   //print_r($vopros);
   //echo "</pre.";
   $v_number=$number+1;
   $v_vopros=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   $v_text1=$vopros[text1];//v_bbparse($kod,$vopros[text1]);
   include("$tm-v-top.html");

   //$v_text3 = "";
   //include("$tm-v-line.html");
   $v_i = 0;
   if (isset($vopros['otvets_info'])) $vopros['otvets_info'] = explode(';',$vopros['otvets_info']);
   if (count($vopros[text2]))
       foreach ($vopros[text2] as $k=>$v) {
                //$v_text2 = v_bbparse($kod,$vopros[text2][$k]);
                //$v_text3 = $vopros[text3][$k]; //.isOk($v_text2,"1"); //v_bbparse($kod,$vopros[text3][$k]);
                $v_text3 = $vopros[text3][$k];
                //if($v_text3 != "")
                   include("$tm-v-line.html");
                $v_i++;
       }
   include("$tm-v-bottom.html");
   $out=ob_get_contents();
   ob_end_clean();
   return $out;
}

function isOk( $vv, $otvet ){
// проверяет находится ли ответ в диапазоне задаваемом vv
// otvet - то что ввел пользователь
// vv - шаблон ответа
// [1..0]
//  $vv = "[5:30.3]";
//  $otvet ="5";
  $OK=1;
  $vvv=explode("&", $vv);
  foreach($vvv as $v){
  	//if(ereg(" ", $v))
  	//{
  		//if(!isset($vse))
  		//{
	  		$i = 0;
	  		
  			//$zn_usl = explode(" ", $v);
  			$zn_usl[0] = $v;
 
  			foreach ($zn_usl as $zn_usl_key => $zn_usl_val)
  			{
	  			if(isset($tmp))
  					unset($tmp);
	 			$tmp = explode("|", $zn_usl_val);
  				$vse[$zn_usl_key]["us"] = $tmp[1];
	  			$vse[$zn_usl_key]["zn"] = $tmp[0];
	  			$i++;
  			}
  		//}
  	//}
  	for($j=0; $j<=$i; $j++)
  	{
  		if(strtolower($vse[$j]["us"])!=strtoupper($vse[$j]["us"]))
  		{
  			$vse[$j]["us"] = "\"".$vse[$j]["us"]."\"";
  			$vse[$j]["us"] = str_replace("=", "\"==\"", $vse[$j]["us"]);
  		}
  		else
  		{
  			$vse[$j]["us"] = str_replace("=", "==", $vse[$j]["us"]);
  		}
  		
		if(eval("return ".$vse[$j]["us"].";"))
		{
			$v = $vse[$j]["zn"];  			
		}
  	}
  	
    $ok=0;
    $eval = (string)@eval("return ".$v.";");
    if( $vv == $otvet )
      $ok=1;
    elseif($otvet == $eval && strlen($eval))
      $ok=1;
    elseif(eregi("^{(\-?[0-9]*\.?[0-9]*)-(\-?[0-9]*\.?[0-9]*)}$", $v, $otvet_diap))
    {
    //	die();
        if (preg_match('/-?[0-9]+\.?[0-9]*/', $otvet)) {
            if((double)$otvet>=$otvet_diap[1] && (double)$otvet<=$otvet_diap[2])
                $ok=1;
        }
    }
    else
    {
      $tmp="^ *\\[([0-9]+(\\.[0-9]+)?)(:([0-9]+(.[0-9]+)?))?\\] *$";

      if( eregi( $tmp, $vv, $p ) ){
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

  return( $OK.$k );
}

function v_otvet_5(&$vopros,$tm,$number,&$attach,&$form) {
   // формирует массив ответа для записи в БД
   $doklad=array();

   $bal=0;
   $ok=1;
   $otv=array();
   $otv2=array();

   $w = ($vopros[balmax] - $vopros[balmin]) / count($vopros[text2]) ; // вес(доля) каждого ответа

   $i=0;
   $ball=0;
   foreach ($vopros[text2] as $k=>$v) {
      $vars=explode(";",sl(trim($v)));
      $otvet=strval(sl(trim($form[otvet][$i])));
      $found=0;
      foreach ($vars as $vv) {
         $vv=strval(trim($vv));
         $ff=isOk( $vv, $otvet );
         if ( $ff ) {     // проверка правильности
            $found=1;
            break;
         }
      }

      if ( $found ) {  // если тек вариант верен
         $otv[]=1;
         $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($form[otvet][$i],0,1024));
         $doklad[info][]=_("Правильно");
         $doklad[good][]=1;
         $ball+=$w;    // добавим вес этого варианта
      }
      else {
         $otv[]=0;
         $ok=0;
         $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($form[otvet][$i],0,1024));
         $doklad[info][]=_("Неправильно");
         $doklad[good][]=0;
      }
      $otv2[]=substr($form[otvet][$i],0,1024);
      $i++;
   }

   if ($ok==1) {  // если ответ полный
      $bal=$vopros[balmax];
      $good=100;
   }
   else {    // если ответ ЧАСТИЧНО полный
      $bal=$vopros[balmin]+$ball;
      $good=0;
   }

   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,
      'info'=>substr(implode(";",$otv2),0,255),'doklad'=>$doklad);

   if (tdebug) {
      pr($vopros);
      pr($form);
      pr($out);
   }

   return $out;

}




?>