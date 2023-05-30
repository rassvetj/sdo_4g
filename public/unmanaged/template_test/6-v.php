<?

/*

Свободный ответ на вопрос. После тестирования учитель может зачислить
или не зачислить указанное в 'otvet' число баллов. Так же может выбрать
любое число баллов в диапазоне BALMIN ... BALMAX.

array (
   
   vopros => "вопрос"         <-- вопрос
  
)

*/


$GLOBALS['v_edit_6']=array(
   'title'=>_("свободный ответ"),
   'info'=>_("Данный тип вопроса требует проверки ответов непосредственно преподавателем.
В поле 'вопрос' введите формулировку вопроса. Учащийся сможет разместить свой ответ  в 
большом текстовом поле, либо в прикрепленном файле, либо использовать оба этих варианта.
Далее  ответ в виде текста и файла попадает в список вопросов, отложеннных для проверки.
Рассматривая ответы, преподаватель сам оценивает правильность каждого из них."),
   'balcalc' => "user",
   'qmoder' => 1,
   'goodotvet'=>0,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
   ),
   'default'=>array(
      'vopros'=>"",
      'balmin'=>0,
      'balmax'=>1,
   )
);



function v_sql2php_6(&$vopros) {
   $goodotvet=array(
      'ginfo'=>_("Правильных ответов в таком вопросе не бывает, т.к. этот вопрос с ручной обработкой ответов. Правильность ответов и прикрепленных файлов проверяют преподаватели курса.")
   );
   return array(
      'vopros'=>$vopros[qdata],
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'otvet'=>$vopros[adata],
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>6,
      'varcount'=>0,
      'goodotvet'=>$goodotvet,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'timetoanswer'=>$vopros[timetoanswer],
   );
}


//
// дополнительные элементы:
//    kod, balmin, balmax
//
function v_php2sql_6($arr) {
   global $brtag,$brremove;
   $out=array(
      'kod'=>$arr[kod],
      'qtype'=>6,
      'qmoder'=>1,
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

function v_vopros_6(&$vopros,$tm,$number,&$attach) {
   global $s;
   $all  = $_REQUEST['all'];
   $cid = $_REQUEST['cid'];
   $quiz = $_REQUEST['quiz_id'];
   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;
   //$v_vopros=v_bbparse($kod,$vopros[vopros]);
   $v_vopros=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   if (isset($vopros['otvets'])) {
       $sql = "SELECT text, filename FROM logseance WHERE kod='".addslashes($kod)."' AND stid='".(int) $_SESSION['s']['stid']."'";       
       $res = sql($sql);
       while($row = sqlget($res)) {
           $answer = $row['text'];
           if (($answer[0]=="'") && ($answer[strlen($answer)-1]=="'")) $answer = substr($answer,1,-1);
           if (!empty($row['filename'])) $filename = $row['filename'];
       }
   }
   if (!empty($all) and !empty($cid))
   {
         $sql = "SELECT freeanswer_data FROM quizzes_results WHERE question_id='".addslashes($kod)."' AND subject_id='".(int)$cid."'";
         $res = sql($sql);
         while($row = sqlget($res))
         {
            $answer_data[] = $row['freeanswer_data'];
         }
   }
   include("$tm-v-main.html");

   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}

function v_otvet_6(&$vopros,$tm,$number,&$attach,&$form) {
   global $HTTP_POST_FILES,$tmpdir,$s;

   $ok=1;
   $text=trim($form[otvet]);
   $buf="";
   $varname="userfile_$number";
   $tmpname=$tmpdir."/tmp_".session_id();

   if (trim($HTTP_POST_FILES[$varname][tmp_name])=="" || trim($HTTP_POST_FILES[$varname][name])=="") {
      $ok=0;
   }

   @move_uploaded_file($HTTP_POST_FILES[$varname][tmp_name],$tmpname);
   if ($ok && filesize($tmpname)>2*1024*1024) {
      alert(_("Нельзя загружать файлы более 2Мб."));
      $ok=0;
   }
   if ($ok) $buf=@gf($tmpname);

   if (file_exists($tmpname)) {
      if (!unlink($tmpname)) putlog("v_otvet_6: "._("не могу удалить")." $tmpname",__FILE__,__LINE__);
   }
   $filename=preg_replace("![^][a-zа-яА-ЯЁ0-9\!@$%^&()_.,-]!i","",$HTTP_POST_FILES[$varname][name]);


/*
   $res=sql("DELETE FROM seance 
             WHERE stid=$s[stid] AND kod='".addslashes($vopros[kod])."'","v_otvet_6 err2");
   sqlfree($res);
   $rq="INSERT INTO seance SET 
      stid=$s[stid],
      mid=$s[mid],
      cid=$s[cid],
      tid=$s[tid],
      kod='".addslashes($vopros[kod])."',
      attach='".addslashes($buf)."',
      filename='".addslashes($filename)."',
      text='".addslashes($text)."'";
   $res=sql($rq,"v_otvet_6 err1");
   sqlfree($res);
   unset($rq);
*/
   //echo "<xmp>\n\n$buf";
   //exit;
/*
   $res=sql("INSERT INTO logseance SET
      stid=$s[stid],
      mid=$s[mid],
      cid=$s[cid],
      tid=$s[tid],
      kod='".addslashes($vopros[kod])."',
      number=$number,
      time=0,
      bal=0,
      balmax=$vopros[balmax],
      balmin=$vopros[balmin],
      good=0,
      vopros='',
      otvet='',
      attach='".addslashes($buf)."',
      filename='".addslashes($filename)."',
      text='".addslashes($text)."'   
   ");
*/   


   
   $doklad=array();

   $bal=0;
   $good=0;

   if (strlen($buf)>0 || strlen($text)>0) {
      // Если на вопрос что-то вводили (текст/файл), то пометить вопрос,
      // от чего он попадет в таблицу seanse и будет ждать модерирования.
      // Сейчас же начисляется 0 баллов, в не зависимости от BALMIN/BALMAX.
      $s[moder]=1;
      $doklad[moder]=1;
   }
   else {
      // Если на вопрос ничего не ввели, то зачислить сразу BALMIN баллов,
      // в таблицу seance вопрос не попадет, т.к. проверять нечего.
      $bal=$vopros[balmin];
      $doklad[moder]=0;
   }
   
   if (strlen($buf)>0) {
      $doklad_main =sprintf(_("Приложен файл %s байт, имя: %s; "), strlen($buf), substr($filename,0,20));
      $doklad[info][]="n/a";
      $doklad[filename]=$filename;
      $doklad[attach]=&$buf;
   }
   if (strlen($text)>0) {   
      $doklad_main .= sprintf(_("приложен текст %s байт"), strlen($text));
      $doklad[info][]="n/a";
      $doklad[text]=$text;
   }
   if (strlen($text)==0 && strlen($buf)==0) {
      $doklad[error][]=sprintf(_("Ни текста, ни аттач-файла не было передано, начисляю %s баллов."), $vopros[balmin]);
   }
   $doklad[main][] = $doklad_main;
    $doklad['qtype'] = 6;

   $out=array('bal'=>$bal,'otv'=>array(),'good'=>$good,'info'=>"",'doklad'=>$doklad);
   return $out;

}




?>