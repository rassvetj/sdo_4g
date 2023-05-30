<?


/*

Просто текст (вопрос) и ссылка для загрузки файла. Будет вставлен аттач N1.
Начислится 0 баллов и 100% правильности.

array (
   
   vopros => "вопрос"

)

*/


$GLOBALS['v_edit_4']=array(
   'title'=>_("с прикрепленным файлом"),
   'info'=>
_("Вопрос служит только для возможности скачать учащимся присоединенного
файла или ознакомиться с текстом вопроса. Данный вопрос никак не 
оценивается, зачисляется 0 баллов и 100% правильности. Вопрос полезен,
если нужно создать ознакомительный вопрос, на который не требуется
отвечать учащемуся."),
   'balcalc'=>'php',
   'balfunc'=>'return array(0,0);',
   'qmoder'=>0,
   'goodotvet'=>0,
   'string'=>array(
      'vopros'=>array("textarea",_("Формулировка вопроса")),
   ),
   'default'=>array(
      'vopros'=>"",
      'balmin'=>0,
      'balmax'=>0,
   )
);



function v_sql2php_4(&$vopros) {
   $goodotvet=array(
      'ginfo'=>_("Правильных ответов в таком типе вопросов не бывает, т.к. вопрос не содержит возможность ввода ответа, а служит только для показа текста вопроса или прикрепленного файла(ов).")
   );
   return array(
      'vopros'=>$vopros[qdata],
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>4,
      'varcount'=>0,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_4($arr) {
   global $brtag,$brremove;
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>4,
      'qmoder'=>0,
      'balmax'=>$arr[balmax],
      'balmin'=>$arr[balmin],
      'qdata'=>trim(brremove($arr[vopros])),
      'adata'=>"",
      'qtema'=>$arr[qtema],
      'url'=>$arr[url],
      'timetoanswer'=>$arr[timetoanswer],
   );
   return $out;
}


function v_vopros_4(&$vopros,$tm,$number,$attach) {
   global $s;

   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;

   $v_text=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   //$v_url="test_attach.php?mode=download&num=1&".v_attach_url($kod).$GLOBALS[sess];
   
   include("$tm-v-main.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}


function v_otvet_4(&$vopros,$tm,$number,&$attach,$form) {

   $doklad=array();
   //$doklad[error][]="Данный тип вопросов не оценивается.";
   $out=array('bal'=>0,'otv'=>array(),'good'=>100,'info'=>"",'doklad'=>$doklad);
   return $out;

}




?>