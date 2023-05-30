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


$GLOBALS['v_edit_9']=array(
   'title'=>_("внешний объект"),
   'info'=>_("Этот тип вопроса предназначен для создания СПЕЦИАЛИЗИРОВАННЫХ тестов. Для этого сам тест
   можно оформить, к примеру, как FLASH объект, который работает как черный ящик для eLearning Server.
   В этом случае FLASH объект передает набор параметров - результатов тестирования, а eLearning Server
   их принимает, обрабатывает и записывает в БД. Для корректной работы имена параметров, передаваемых
   из ОБЪЕКТА должны соответсвовать полям в правой колонке ввода (при формировании вопроса).
   Значения переданных параметров будут проверяться по шаблонам в левой колонке ввода.
"),
   'balcalc' => "user",
   'goodotvet'=>1,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
      'text1'   => array("string",_("Подпись")),
   ),
   'variant' => array (
      'text2'  => array("string",_("Вариант заполнения пропуска (через \";\")"),250),
      'text3'  => array("string",_("Имя параметра"),250),
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


function v_sql2php_9(&$vopros) {
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
      'type'=>9,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_9($arr) {
  // заносит результаты ответа в БД
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
      'qtype'=>9,
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



function v_vopros_9(&$vopros,$tm,$number,&$attach) {
   // формирует и выводит вопрос
   global $s;
   ob_start();
   //pr($vopros);
   $kod=$vopros[kod];
   $v_number=$number+1;

   //$v_vopros=v_bbparse($kod,$vopros[vopros]);
   
   /**
   * Если вопрос с внешнии обьектом содержит пэкэйдж...
   */
   $v_vopros = v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   if ($v_vopros2 = parsePackage($vopros['kod'])) $v_vopros = $v_vopros2;
   $v_text1=$vopros[text1];//v_bbparse($kod,$vopros[text1]);
   
   if (count($vopros[text2])){
     include("$tm-v-param-top.html");
     $v_i=0;
     foreach ($vopros[text2] as $k=>$v) {
      $v_text3=$vopros[text3][$k]; //v_bbparse($kod,$vopros[text3][$k]);
      include("$tm-v-param-line.html");
      $v_i++;
     }
     include("$tm-v-param-bottom.html");
   }
   include("$tm-v-top.html");
   if (count($vopros[text2])){
     $v_i=0;
     foreach ($vopros[text2] as $k=>$v) {
      $v_text2=v_bbparse($kod,$vopros[text2][$k]);
      $v_text3=$vopros[text3][$k]; //v_bbparse($kod,$vopros[text3][$k]);
      include("$tm-v-line.html");
      $v_i++;
     }
   }
   include("$tm-v-bottom.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}

function isOk_9( $vv, $otvet ){
// проверяет находится ли ответ в диапазоне задаваемом vv
// otvet - то что ввел пользователь
// vv - шаблон ответа
// [1..0]
//  $vv = "[5:30.3]";
//  $otvet ="5.0    ";
 $OK=1;
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
  return( $OK.$ok );
}


function v_otvet_9( &$vopros, $tm, $number, &$attach, &$form ) {
   // обрабатывает ответы на правильность - неправильность - и выводит на экран для проверки преподавалетем
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
         if ( isOk_9( $vv, $otvet ) ) {     // проверка правильности
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

    $doklad['qtype'] = 9;
   if ($ok==1) {  // если ответ полный
      $bal=$vopros[balmax];
      $good=100;
   }
   else {    // если ответ ЧАСТИЧНО полный
      $bal=$vopros[balmin]+$ball;
      $good=0;
   }

   $out=array('bal'=>$bal,'otv'=>$otv,'good'=>$good,
      'info'=>substr(implode(";",$otv2),0,255),'doklad'=>$doklad );

   if (tdebug) {
      pr($vopros);
      pr($form);
      pr($out);
   }

   return $out;

}

/**
* Обрабатывает пэкэйдж и запускает его
*/
function parsePackage($kod) {        

    $packageIndex = ''; 
    $v_vopros = false;
    
    $indexFiles = array('test.swf','test_800x600.swf');
       
    $packageDir = "COURSES/course".kodintval($kod).'/'."content/tests/{$kod}";
    foreach($indexFiles as $index) {
                       
        if (file_exists($GLOBALS['wwf'].'/'.$packageDir.'/'.$index)) {
            $packageIndex = $GLOBALS['sitepath'].$packageDir.'/'.$index;
            break;
        }
       
    }    
        
    if (!empty($packageIndex)) {
        
       $packageIndex_parts = pathinfo($packageIndex);
       
       switch ($packageIndex_parts['extension']) {
           
       case 'swf':           
       
           $packagePath = urlencode($GLOBALS['sitepath'].$packageDir);      
/*           $v_vopros="<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" ".
           "codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/".
           "swflash.cab#version=5,0,0,0\" quality='high' WIDTH='800' HEIGHT='600' ID='Movie' swLiveConnect='true'>".
           "<param name='movie' value='$packageIndex?path=$packagePath' /></object>";       
*/           
           $v_vopros="<object id=\"Movie\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" ".
           "codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/".
           "swflash.cab#version=6,0,0,0\" quality='high' width='800' height='600' align=''>".
           "<param name='movie' value='$packageIndex?path=$packagePath'>
           <param name=\"quality\" value=\"high\">
           <param name=\"menu\" value=\"false\">
           <param name=\"bgcolor\" value=\"#FFFFFF\">
           <embed width='800' height='600' src='$packageIndex?path=$packagePath' quality=\"high\" bgcolor=\"#FFFFFF\" align=\"\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\">
           </object>";       
                      
       break;
       
       }        
        
    }
    
    return $v_vopros;
}

?>