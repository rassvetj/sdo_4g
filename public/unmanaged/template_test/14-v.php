<?php

$GLOBALS['v_edit_14']=array(
   'title'=>_("заполнение пропусков"),
   'info'=>_("заполнение пропусков"),
   'default'=>array(
      'vopros'=>"",
      'text'=>array('','',''),
      'balmin'=>0,
      'balmax'=>1,
   )
);

function v_sql2php_14(&$vopros) {
   $data=explode($GLOBALS[brtag],$vopros[qdata]);
   $vopr = "";
   $text = array();
   $goodotvet=array();
   $j = 1;
   for ($i=0; $i<count($data); $i++) {
      $text[]=$data[$i];
      if ($arr = unserialize($data[$i])) {
         $goodotvet['gotvet'][] = "Ответ №{$j}: " . implode("; ", $arr['right']);
         $j++;
      }
      elseif (!$i) {
          $vopr = trim($data[$i]);
      }
   }
   return array(
      'vopros'=>$vopr,
      'balmin'=>$vopros[balmin],
      'balmax'=>$vopros[balmax],
      'text'=>$text,
      'qtema'=>$vopros[qtema],
      'url'=>$vopros[url],
      'type'=>5,
      'varcount'=>$varcount,
      'goodotvet'=>$goodotvet,
      'timetoanswer'=>$vopros[timetoanswer],
   );
}

/*function v_php2sql_14($arr) {
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
      'qtype'=>14,
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
}*/

function v_vopros_14(&$vopros,$tm,$number,&$attach) {
   global $s;
   ob_start();
   $kod=$vopros[kod];
   $v_number=$number+1;
   $v_vopros=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);
   $v_text=$vopros[text];
   include("$tm-v-top.html");
   //if (isset($vopros['otvets_info'])) $vopros['otvets_info'] = explode(';',$vopros['otvets_info']);
   include("$tm-v-line.html");
   include("$tm-v-bottom.html");
   $out=ob_get_contents();
   ob_end_clean();

   return $out;
}

function v_otvet_14(&$vopros,$tm,$number,&$attach,&$form) {
   // формирует массив ответа для записи в БД
   $doklad=array();
   $otv=array();
   $otv2=array();

   /*pr($form[otvet]);
   $tmp = unserialize($vopros[text][1]);
   pr($tmp['right']);
   pr($vopros);die();*/

   $ball=0;
   $answers_count = 0;
   $answers_correct = 0;
   
   foreach ($vopros[text] as $k=>$v) {
       if ($arr = unserialize($v)) {
           $answers_count++;
           if (!$arr['dd']) {
               if ( ($form['otvet'][$k] == $arr['right'][0] && $arr['match-case']) ||
                       (strtolower($form['otvet'][$k]) == strtolower($arr['right'][0]) && !$arr['match-case']) ) {
                   $answers_correct++;
                   $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($form[otvet][$k],0,1024));
                   $doklad[info][]=_("Правильно");
                   $doklad[good][]=1;
                   $otv[]=1;
               }
               else {
                   $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($form[otvet][$k],0,1024));
                   $doklad[info][]=_("Неправильно");
                   $doklad[good][]=0;
                   $otv[]=0;
               }
               $otv2[]=substr($form[otvet][$k],0,1024);
           }
           else {
               if (!$arr['multiple']) {
                   $answer = $arr['stubs'][$form['otvet'][$k]];
                   if (in_array($answer, $arr['right'])) {
                       $answers_correct++;
                       $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($answer,0,1024));
                       $doklad[info][]=_("Правильно");
                       $doklad[good][]=1;
                       $otv[]=1;
                   }
                   else {
                       $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr($answer,0,1024));
                       $doklad[info][]=_("Неправильно");
                       $doklad[good][]=0;
                       $otv[]=0;
                   }
                   $otv2[]=substr($answer,0,1024);
               }
               else {
                   $answer = array();
                   if(is_array($form['otvet'][$k])){
                       foreach ($form['otvet'][$k] as $var) {
                           $answer[] = $stubs[$var];
                       }
                   }
                   $intersect = array_intersect($answer, $arr['right']);
                   
                   if ( (count($answer) == count($arr['right'])) && (count($arr['right']) == count($intersect)) ) {
                       $answers_correct++;
                       $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr(implode("; ", $answer),0,1024));
                       $doklad[info][]=_("Правильно");
                       $doklad[good][]=1;
                       $otv[]=1;
                   }
                   else {
                       $doklad[main][]=_("Введено:")." ".htmlspecialchars(substr(implode("; ", $answer),0,1024));
                       $doklad[info][]=_("Неправильно");
                       $doklad[good][]=0;
                       $otv[]=0;                       
                   }
                   $otv2[]=substr(implode("; ", $answer),0,1024);
               }
               
           }
           
       }
   }
   
   $ratio = $answers_count ? $answers_correct/$answers_count : 0;
   $bal = ($vopros[balmax] - $vopros[balmin]) * $ratio + $vopros['balmin'];
   $good = $ratio == 1 ? 100 : 0;

   $out=array(  'bal'=>$bal,
                'otv'=>$otv,
                'good'=>$good,
                'info'=>substr(implode(";",$otv2),0,255),
                'doklad'=>$doklad );
   
   return $out;
}




?>