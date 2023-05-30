<?

   include("1.php");
   require_once "../../application/cmd/cmdBootstraping.php";

   if (isset($_POST['checkkod'])) {
       $checkkod = $_POST['checkkod'];
   }

   if (isset($_POST['form'])) {
       $form = $_POST['form'];
   }

   include("test.inc.php");
   
   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess",0,$constTarget);
   if ($s[me]==2) exitmsg(_("Сеанс тестирования закончен. Не нажимайте кнопку BACK (Назад) в вашем браузере!"),"test_end.php?$sess",0,$constTarget);
   if ($s[me]!=1) exitmsg(_("Сеанс тестирования закончен или не начинался."),"./?$sess",0,$constTarget);

   if (!isset($checkkod))
      exitmsg(_("Неправильный вызов страницы."),"test_vopros.php?$sess",0,$constTarget);
   if ($checkkod!=md5(implode(" | ",$s[ckod])))
      exitmsg(_("Не нажимайте кнопки REFRESH (Обновить) или BACK (Назад), иначе вы можете случайно отключиться от сеанса тестирования. Если вы намеренно хотите прервать тест, воспользуйтесь кнопкой 'ПРЕРВАТЬ ТЕСТИРОВАНИЕ' внизу страницы."),"test_vopros.php?$sess",0,$constTarget);

   //echo "SESS=".$sess;


   $rq="SELECT * FROM list WHERE ";//kod IN (";
   if(count($s[ckod])>0) {
      $rq .= "kod IN (";
      foreach ($s[ckod] as $k=>$v) {
         $rq.="'".ad($v)."',";
     }
     $rq = substr($rq,0,-1).")";
   }
   else
      $rq .= "0>0";

   $res=sql($rq,"err1");
   $ask=array();
   while ($r=sqlget($res)) {
      $ask[$r[kod]]=$r;
   }
   sqlfree($res);

   if (tdebug) { echo "FORM"; pr($form); echo "<hr size=1 noshade>"; }

   // сохранить текущий результат, чтобы сделать "инфо о предыдущем вопросе(ах)"
   $s[lastquest]=array(
      'bal'=>0, // набрано балов
      'good'=>0, // сумма %процента правильности всех вопросов (м.б. больше 100%)
      'qty'=>0, // сколько было вопросов
      'balmax'=>0, // диапазоны (модерируемость вопроса не влияет)
      'balmin'=>0,
      'moder'=>0, // сколько вопросов было с модерированием
      'url'=>array(), // URL к вопросам
   );

   foreach ($s[ckod] as $k=>$v) {
      $vv=$ask[$v];
      //echo "SQL"; pr($vv);

      //
      // Поиск Аттачей и информации о них
      //
      $attach=array();
      $res=sql("SELECT kod,fnum,ftype,fname,fdate,LENGTH(fdata) as fsize
                FROM file WHERE kod='".ad($vv[kod])."' ORDER BY fnum","err2");
      while ($r=sqlget($res)) {
         $attach[]=$r;
      }
      sqlfree($res);

      $tm="template_test/".QTYPE_PREFIX.$vv[qtype];
      $php="$tm-v.php";
      $func1="v_sql2php_".QTYPE_PREFIX.$vv[qtype];
      $func2="v_otvet_".QTYPE_PREFIX.$vv[qtype];
      $func3="v_vopros_".QTYPE_PREFIX.$vv[qtype];



      if (!file_exists($php)) {
         echo "<br><br><br>"._("Не найдена функция для ответа типа")." QTYPE=".QTYPE_PREFIX.$vv[qtype];
         continue;
      }

      include_once($php);

      $number=num_array($s[akod],$vv[kod]);
      $arr_vopros=$func1($vv);
      $dump1=$arr_vopros;
      foreach ($vv as $k1=>$v1) $arr_vopros[$k1]=$v1;

      if( isset($s['random_vars']) && is_array($s['random_vars']) ) {
              foreach ($s['random_vars'] as $var_key => $var_value)
                      foreach ($arr_vopros as $arr_key => $arr_value)
                         $arr_vopros[$arr_key] = str_replace("[". $var_key ."]", $var_value, $arr_vopros[$arr_key]);
      }

      if (is_array($s['random_vars'])) {
      foreach ($s['random_vars'] as $var_key => $var_value)
              foreach ($arr_vopros as $arr_key => $arr_value)
                 $arr_vopros[$arr_key] = str_replace("[". $var_key ."]", $var_value, $arr_vopros[$arr_key]);
      }

      $ok=$func2($arr_vopros,$tm,$number,$attach,$form[$number]);

      if ($s['adaptive'] && isset($ok['good']) && !in_array($ok['good'], array(1,true,100)) && $s['test_id'] && (count($s['aneed'])>1)) {
           // Добавляем вопрос из той же темы при неверном варианте ответа
           $questions = getField('test_abstract', 'data', 'test_id', (int) $s['test_id']);
           if (strlen($questions)) {
               $questions = explode($GLOBALS['brtag'], $questions);
               if (count($questions)) {
                   $questions = array_diff($questions, array($arr_vopros['kod']));
                   if (count($s['adone'])) {
                       $questions = array_diff($questions, $s['adone']);
                   }

                   //if (count($s['aneed'])) {
                       //$questions = array_diff($questions, $s['aneed']);
                   //}

               }

               if (count($questions) && isset($arr_vopros['qtema'])) {
                   $sql = "SELECT kod FROM list WHERE kod IN ('".join("','", $questions)."') AND qtema LIKE ".$GLOBALS['adodb']->Quote($arr_vopros['qtema'])/*.' LIMIT 1'*/;
                   $res = sql($sql);
                   $row = sqlget($res);
                   if($row) {

                       if (isset($s['aneed'][$s['test_position']+1])) {
                           if (!in_array($row['kod'], $s['aneed'])) {
                               $oldKod = $s['aneed'][$s['test_position']+1];
                               if ($oldKod && ($index = array_search($oldKod, $s['akod']))) {
                                   $s['aneed'][$s['test_position']+1] = $row['kod'];
                                   $s['akod'][$index] = $row['kod'];
                               }
                           } else {
                               if ($s['aneed'][$s['test_position']+1] != $row['kod']) {
                                   $oldKod = false;
                                   if ($index = array_search($row['kod'], $s['aneed'])) {
                                       $oldKod = $s['aneed'][$s['test_position']+1];
                                       if ($oldKod) {
                                           $s['aneed'][$index] = $s['aneed'][$s['test_position']+1];
                                           $s['aneed'][$s['test_position']+1] = $row['kod'];

                                           if ($index = array_search($row['kod'], $s['akod'])) {
                                               $s['akod'][$index] = $oldKod;

                                               if ($index = array_search($oldKod, $s['akod'])) {
                                                   $s['akod'][$index] = $row['kod'];
                                               }
                                           }
                                       }

                                   }

                               }
                           }
                       }
                   }

                   $s['balmax2_true'] = get_maxbal_by_kods($s['akod']);
                   $s['balmax_true'] = get_nomoder_maxbal_by_kods($s['akod']);
                   $s['balmin2_true'] = get_minbal_by_kods($s['akod']);
                   $s['balmin_true'] = get_nomoder_minbal_by_kods($s['akod']);

               }
           }
       }

      $html_vopros=$func3($arr_vopros,$tm,$number,$attach);


      if (tdebug) {

         exit("<hr size=1 noshade>TDEBUG STOP ($func2)");

         echo "<hr size=1 noshade><h3>$vv[kod] - type $vv[qtype]</h3>SQL = ";
         pr($vv);
         echo "\$arr_vopros = sql2php (\$SQL) = ";
         pr($dump1);
         echo " foreach (\$vv as \$k1=>\$v1) \$arr_vopros[\$k1]=\$v1;<br>\$arr_vopros = ";
         pr($arr_vopros);
         echo "\$FORM[\$number] (number=$number) = ";
         pr($form[$number]);
         echo "$ok=\$fuct2(\$arr_vopros,$tm,$number,\$attach,\$form[\$number])<br>\$ok = ";
         echo "<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>";
         pr($ok);
         echo "SESSION";
         pr($s);
         echo "</td><td>";
         exit;
      }

           if ($s['timelimit_question']>0) {
              if (ceil($s['timelimit_question']-doubleval(time()-$s['start_question'])/60)<1) {
                 alert(_("К сожалению, закончилось время, отпущеное на решение данного вопроса. Ответ не засчитан."),0,$constTarget);
                 $ok['bal'] = 0;
                 $ok['doklad']['good'] = 0;
                 $ok['doklad']['info'] = _("Закончилось время");
                 $ok['doklad']['info'] = _("Закончилось время, отпущеное на решение данного вопроса.");
                 $ok['good'] = 0;
                 $ok['info'] = '';
                 unset($ok['otv']);
              }
           }

			// сохраняем результаты только при прохождении студентом
           saveotvet($vv,$number,$html_vopros,$ok);
      result_test_intermediate();

      //pr($ok);exit;

//die();
      if (tdebug) {
         //pr($ok);pr($s);echo "</td></tr></table>";
      }

      //echo "<li>$vv[kod] / type: $vv[qtype] / moder: $s[moder]";

   }



   // проверка оставшегося времени
   if ($s[timelimit]>0) {
      if (ceil($s[timelimit]-doubleval(time()-$s[start])/60)<1) {
         alert(_("К сожалению, закончилось время, отпущенное на решение задания. Сейчас вы перейдете на страницу результатов. В ведомость записывается результат тестирования 'прервано по лимиту времени' и оценка только тех вопросов, которые вы уже успели решить."),0,$constTarget);
         result_test(5);
         refresh("test_end.php?vopros=".md5(microtime()).$sess,0,$constTarget);
         return;
      }
   }

   // не наступил ли конец тестирования
   if (is_test_end()) {
  //    if ( isset ( $sheid ) ) echo "<H1>ЗАНЯТИЕ $sheid</H1>";
      result_test();
      if (tdebug) {
         echo "REFRESH: <a href=test_end.php?vopros=".md5(microtime()).$sess.">test_end.php?vopros=".md5(microtime()).$sess."</a>";
      }
      else {
//         md5(microtime())
  //       echo "SESS=".$sess;
//         header("Location: test_end.php?vopros=".md5(microtime()).$sess);
         ///////////////////////////////////
//         if( $save_statistic ){
            saveAutoMark( 123 ); // сохраняет оценку
//         }
         ///////////////////////////////////
         refresh("test_end.php?vopros=".md5(microtime()).$sess,0,$constTarget);
      }
      return;
   }

   // записать новые текущие вопросы
   test_switch();

   // и отослать на страницу вопросов
   if (tdebug) {
      echo "REFRESH: <a href=test_vopros.php?vopros=".md5(microtime()).$sess.">test_vopros.php?vopros=".md5(microtime()).$sess."</a>";
   }
   else {
      if ($s[questres])
         refresh("{$sitepath}test_vopros.php?mode=3&vopros=".md5(microtime()).$sess,0,$constTarget);
//         header("Location:test_vopros.php?mode=3&vopros=".md5(microtime()).$sess,0,$constTarget);
      else
         refresh("{$sitepath}test_vopros.php?vopros=".md5(microtime()).$sess,0,$constTarget);
//         header("Location:test_vopros.php?vopros=".md5(microtime()).$sess,0,$constTarget);
   }
   return;


#
# Зачислить набранные за вопрос очки в базу и сессию, удалить вопрос из $s[ckod],
# добавить вопрос в $s[adone], код ответа в $s[agood], набранные балы в $s[abal],
# выбранные коды ответа в $s[aotv], и: abalmax, abalmin, abalmax2, abalmin2.
#
# Скопировать модерируемые вопросы в таблицу seance.
#
# Записать доклад о вопросе и правильности ответа в logseance
#
# ПЕРЕМЕННЫЕ:
#
#   $vopros - данные вопроса
#   $number - порядковый номер вопроса в сеансе учащегося
#   $html_vopros - HTML текст вопроса
#   $ok - стандартный массив информации об ответе, описан в dev.elearn.ru-struct.txt
#
function saveotvet($vopros,$number,$html_vopros,$ok) {
   global $s;
   global $adodb;
   //pr($vopros); pr($ok); die();

   $_SESSION['s']['test_position']++;
   if (!is_array($ok[otv])) {
      putlog("saveotvet: "._("Переменная")." \$ok "._("не массив! Срочно открыть")." template_test/... "._("и исправить баг!")." :) kod=$vopros[kod] type=$vopros[type]");
      $ok[otv]=array();
   }
   
   $question_exists = array_search($vopros['kod'],$_SESSION['s']['adone']);

   foreach ($s[aneed] as $k=>$v) {
      if (strval($v)==strval($vopros[kod])) unset($s[aneed][$k]);
   }
   foreach ($s[ckod] as $k=>$v) {
      if (strval($v)==strval($vopros[kod])) unset($s[ckod][$k]);
   }

   if ($question_exists!==false) {
       $question_lastbal = $_SESSION['s']['abal'][$question_exists];
       //$s[abal][$question_exists]=doubleval($ok[bal]);
       $s[aotv][$question_exists]=$ok[otv];
       $s[ainfo][$question_exists]=substr($ok[info],0,255);       
       
       // Очистка от предыдущей инфы
       $_SESSION['s']['bal'] -= $question_lastbal;
   } else {
       $s[adone][]=$vopros[kod];
       //$s[abal][]=doubleval($ok[bal]);
       $s[aotv][]=$ok[otv];
       $s[ainfo][]=substr($ok[info],0,255);
   }

   //
   // Если вопрос был моредируемым, но на него никак не ответили, то
   // этот вопрос нужно считать не модерируемым!
   //
   if(isset($vopros['weight']) && (trim($vopros['weight'])!= "" ) ) {
          $weight = unserialize(stripslashes($vopros['weight']));
          if (!in_array($vopros['qtype'],array(11))) {
            $ok[bal] = 0;
            $ok['doklad']['weights'] = array();
          }
          switch($vopros['qtype']) {
              case 1:  
                $s[bal] += doubleval($weight[$ok['otv'][0]]);
                $ok[bal] += doubleval($weight[$ok['otv'][0]]);
                $ok['doklad']['weights'][] = $weight[$ok['otv'][0]];
                
                $weight_balmax = @max($weight);
                $weight_balmin = @min($weight);
                                                
                if ($question_exists===false) {
                    $s[balmax] = $s[balmax2] = process_max($s[balmax],$weight_balmax);
                    $s[balmin] = $s[balmin2] = process_min($s[balmin],$weight_balmin); 
                
                    $s[abalmax][] = $weight_balmax;
                    $s[abalmin][] = $weight_balmin;
                    $s[abalmax2][] = $weight_balmax;
                    $s[abalmin2][] = $weight_balmin;
                }
              break;
              case 2:
                if (is_array($ok['otv']) && count($ok['otv'])) {
                    foreach($ok['otv'] as $otv_num=>$otv_value) {
                        $ok['doklad']['weights'][] = $weight[$otv_num+1];
                        if ($otv_value) {
                            $s[bal] += doubleval($weight[$otv_num+1]);
                            $ok[bal] += doubleval($weight[$otv_num+1]);
                        }
                        
                    }
                }
                
                if (is_array($weight) && count($weight)) {
                    $weight_balmin = 'undefined';
                    $weight_balmin = 'undefined';
                    foreach($weight as $k=>$w) {
                        $weight_balmin = process_min($weight_balmin,$w);
                        $weight_balmax = process_max($weight_balmax,$w);                                              
                    }
                }
                
                if ($question_exists===false) {
                    $s[abalmin][] = $weight_balmin;
                    $s[abalmin2][] = $weight_balmin;
                    $s[abalmax][] = $weight_balmax;
                    $s[abalmax2][] = $weight_balmax;
                    $s[balmax] = $s[balmax2] = process_max($s[balmax],$weight_balmax);
                    $s[balmin] = $s[balmin2] = process_min($s[balmin],$weight_balmin); 
                }
                
              break;
              case 11:  
              
                $s[bal] += doubleval($ok['bal']);
                $ok[bal] = doubleval($ok['bal']);                              
                
                $weight_balmax = @max($ok['doklad']['weights']) * count($ok['doklad']['variant1']);
                $weight_balmin = @min($ok['doklad']['weights']);
                                                
                if ($question_exists===false) {
                    $s[balmax] = $s[balmax2] = process_max($s[balmax],$weight_balmax);
                    $s[balmin] = $s[balmin2] = process_min($s[balmin],$weight_balmin); 
                
                    $s[abalmax][] = $weight_balmax;
                    $s[abalmin][] = $weight_balmin;
                    $s[abalmax2][] = $weight_balmax;
                    $s[abalmin2][] = $weight_balmin;
                }
              break;
          }
          if ($val = ($weight_balmax-$weight_balmin)) {
              $ok[good] = (int) ((($ok[bal]-$weight_balmin)*100)/$val);
          }
          if ($question_exists!==false)
            $s[abal][$question_exists] = doubleval($ok[bal]);
          else
            $s[abal][] = doubleval($ok[bal]);
   }
   else {
      
    if (!($vopros[qmoder] && $ok[doklad][moder])) {
      if ($question_exists===false) {
          $s[abalmax][]=$vopros[balmax];
          $s[abalmin][]=$vopros[balmin];
          $s[balmax] = process_max($s[balmax],$vopros[balmax]);
          $s[balmin] = process_min($s[balmin],$vopros[balmin]); 
      }
      //$s[balmax]+=doubleval($vopros[balmax]);
      //$s[balmin]+=doubleval($vopros[balmin]);
    }
    if ($question_exists!==false)
        $s[abal][$question_exists] = doubleval($ok[bal]); 
    else
        $s[abal][] = doubleval($ok[bal]); 
    $s[bal]+=doubleval($ok[bal]);
    if ($question_exists===false) {
        $s[abalmax2][]=$vopros[balmax];
        $s[abalmin2][]=$vopros[balmin];
        $s[balmax2] = process_max($s[balmax2],$vopros[balmax]);
        $s[balmin2] = process_min($s[balmin2],$vopros[balmin]); 
    }
    
    //$s[balmax2]+=doubleval($vopros[balmax]);
    //$s[balmin2]+=doubleval($vopros[balmin]);
   }

   if ($question_exists!==false)
       $s[agood][$question_exists]=intval($ok[good]);
   else
       $s[agood][]=intval($ok[good]);

   //
   // Сбор статистики о текущей группе вопросов
   //
   $s[lastquest][bal]+=doubleval($ok[bal]);
   $s[lastquest][balmax] = process_max($s[lastquest][balmax],$vopros[balmax]);
   $s[lastquest][balmin] = process_min($s[lastquest][balmin],$vopros[balmin]);
   //$s[lastquest][balmax]+=doubleval($vopros[balmax]);
   //$s[lastquest][balmin]+=doubleval($vopros[balmin]);
   $s[lastquest][good]+=doubleval($ok[good]);
   $s[lastquest][qty]++;
   if ($vopros[qmoder]) $s[lastquest][moder]++;
   if ($vopros[url]!="") $s[lastquest][url][]=$vopros[url];


    // AHTUNG!! MANAGED WAS HERE!
    if($s['perm'] > 1 && LESSON_TYPE_ID != 2055 && LESSON_TYPE_ID != 2056 && LESSON_TYPE_ID != 2057){
        return;
    }
    // END OF AHTUNG

   if (($question_exists!==false) && $_POST['dont_delete_attach'] && !strlen($ok[doklad][attach])) {
       $sql = "SELECT attach, filename FROM logseance WHERE stid='".(int) $_SESSION['s']['stid']."' AND kod='".addslashes($vopros[kod])."'";
       
       $res = sql($sql);
       while($row = sqlget($res)) {
           $ok[doklad][attach] = $row['attach'];
           $ok[doklad][filename] = $row['filename'];
       }
   }
   
   //
   // Формирование отчета
   //
   if (is_array($ok['doklad']) && array_key_exists('attach', $ok['doklad'])) {
       $attach = $ok[doklad][attach]; 
       unset($ok[doklad][attach]);
   }
   if (is_array($ok['doklad']) && array_key_exists('text', $ok['doklad'])) {
       $text=$ok[doklad][text]; 
       unset($ok[doklad][text]);
   }
   if (is_array($ok['doklad']) && array_key_exists('filename', $ok['doklad'])) {
       $filename=$ok[doklad][filename]; 
       unset($ok[doklad][filename]);
   }
   $dat = time();

   sql("DELETE FROM logseance WHERE stid='".(int) $_SESSION['s']['stid']."' AND kod='".addslashes($vopros[kod])."'");

   if ((strlen($attach) || strlen($text)) && (!in_array($vopros['qtype'],array(11)))) {
      $res=sql("DELETE FROM seance WHERE stid='$s[stid]' AND kod='".addslashes($vopros[kod])."'","saveotvet errTo287");
      sqlfree($res);     
   
      $data_attach = @unpack("H*hex", $attach);

      if (dbdriver == "mssql")
      $rq="
      INSERT INTO seance (stid, mid, cid, tid, kod, attach, filename, text, time)
      VALUES (
      '$s[stid]',
      '$s[mid]',
      '$s[cid]',
      '$s[tid]',
      ".$adodb->Quote($vopros[kod]).",
      0x".$data_attach['hex'].",
      ".$adodb->Quote($filename).",
      ".$adodb->Quote($text).",
      ".$adodb->DBTimeStamp($dat).")";
      else
      $rq="     
      INSERT INTO seance (stid, mid, cid, tid, kod, attach, filename, text, time)
      VALUES (
      '$s[stid]',
      '$s[mid]',
      '$s[cid]',
      '$s[tid]',
      ".$adodb->Quote($vopros[kod]).",
      '0',
      ".$adodb->Quote($filename).",
      ".$adodb->Quote($text).",
      ".$adodb->DBTimeStamp($dat).")";
     
      $res=sql($rq,"saveotvet errTo270");
      sqlfree($res);
      unset($rq);
      $table_f =(dbdriver == "mssql") ? "'seance'" : "seance";
      $attach =(dbdriver == "mssql") ? "0x".$data_attach['hex'] : $attach;
      if($attach)
      $adodb->UpdateBlob($table_f, 'attach',$attach,"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod]));   
   }
   

   if (in_array(dbdriver,array("mssql","oci8")))
   $rq = 
      "INSERT INTO logseance (sheid,stid, mid, cid, kod, number, time, bal, balmax, balmin, good, vopros, otvet, attach, filename, text, qtema)
      VALUES (
      '$s[sheid]',
      '$s[stid]',
      '$s[mid]',
      '$s[cid]',
      ".$adodb->Quote($vopros[kod]).",
      '$number',
      '".time()."',
      $ok[bal],
      '$vopros[balmax]',
      '$vopros[balmin]',
      ".(int) $ok[good] /*Должно быть целое*/.",
      '0',
      '0',
      '0',
      ".$adodb->Quote($filename).",
      '0',
      ".$adodb->Quote($vopros['qtema']).")";
     else
     $rq = 
      "INSERT INTO logseance (sheid,stid, mid, cid, kod, number, time, bal, balmax, balmin, good, vopros, otvet, attach, filename, text, qtema)
      VALUES (
      '$s[sheid]',
      '$s[stid]',
      '$s[mid]',
      '$s[cid]',
      ".$adodb->Quote($vopros[kod]).",
      '$number',
      '".time()."',
      '$ok[bal]',
      '$vopros[balmax]',
      '$vopros[balmin]',
      ". (int) $ok[good] /*Должно быть целое*/.", 
      '0',
      '0',
      '0',
      ".$adodb->Quote($filename).",
      '0',
      ".$adodb->Quote($vopros['qtema']).")";
      $res = sql($rq,"eRRto270");
      sqlfree($res);
      unset($rq);
      
      /*
       * 
       * HI FROM MANAGED! 
       * I'M AHTUNG NAMED HM_Poll_Result_
       * IF LESSONS TYPE IS POLL (lesson->typeID == HM_Event_EventModel::TYPE_POLL) I'LL CARE ABOUT THIS DATA
       * 
       */

      if((($s['perm'] <= 1 && $s['perm'] > 0) || ($s['perm'] == 2 && LESSON_TYPE_ID == 2057)) && (LESSON_TYPE_ID != 2056)){ //LESSON_TYPE_ID == 2055
          if(TEST_TYPE == _('Опрос') && TEST_ID && $s[mid] && $s[sheid]){
              //$teacher_where = (LESSON_TYPE_ID == 2057) ? " AND magic_date_begin = '' AND magic_date_end = ''" : "";
              // чистим старое
              sql("DELETE FROM quizzes_results WHERE
                    user_id = ".$s[mid]." AND
                    lesson_id = ".$s[sheid]." AND
                    question_id = ".$adodb->Quote($vopros[kod])." AND
                    quiz_id = ".TEST_ID." AND
                    subject_id = ".$s[cid]
                    //.$teacher_where
              );

              // множественный ответ
              if($ok['doklad']['qtype'] == 2){
                  foreach($ok['otv'] as $number => $selected){
                      if($selected)
                          sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, quiz_id, subject_id) VALUES 
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", ".($number+1).", ".TEST_ID.", ".$s[cid].")");
                  }
              }
                  // одиночный ответ
              elseif($ok['doklad']['qtype'] == 1){
                  sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, quiz_id, subject_id) VALUES 
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", ".($ok['otv'][0]).", ".TEST_ID.", ".$s[cid].")");

              }
                  // свободный ответ
                  //elseif($answer = $_POST['form'][0]['otvet']){
              elseif($ok['doklad']['qtype'] == 6) {
                  $answer = $text;
                  sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, freeanswer_data, quiz_id, subject_id) VALUES 
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", 0, ".$adodb->Quote($answer).", ".TEST_ID.", ".$s[cid].")");
              }
          }
      	
      }
      // опрос руководителей
      elseif(LESSON_TYPE_ID == 2056){

          $juniors = implode(', ', array_keys($ok['otv']));
          // чистим старое
          if (count($ok['otv'])) {
          sql("DELETE FROM quizzes_results WHERE
                    user_id = ".$s[mid]." AND
                    lesson_id = ".$s[sheid]." AND
                    question_id = ".$adodb->Quote($vopros[kod])." AND
                    quiz_id = ".TEST_ID." AND
                    subject_id = ".$s[cid]." AND
                    junior_id IN (".$juniors.")"
          );
          }

          if(TEST_TYPE == _('Опрос') && TEST_ID && $s[mid] && $s[sheid] && count($ok['otv'])){
              // одиночный ответ
              if($ok['doklad']['qtype'] == 1){
                  foreach($ok['otv'] as $junior => $answer){
                      sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, quiz_id, subject_id, junior_id) VALUES
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", ".($answer+1).", ".TEST_ID.", ".$s[cid].", ".$junior.")");
                  }
              }

              // множественный ответ
              elseif($ok['doklad']['qtype'] == 2){
                  foreach($ok['otv'] as $junior => $answer){
                      foreach ($answer as $id => $checked)
                        sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, quiz_id, subject_id, junior_id) VALUES
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", ".($id+1).", ".TEST_ID.", ".$s[cid].", ".$junior.")");
                  }

              }
                  // свободный ответ
                  //elseif($answer = $_POST['form'][0]['otvet']){
              elseif($ok['doklad']['qtype'] == 6) {
                  $answer = $text;
                  foreach($ok['otv'] as $junior => $answer){
                      sql("INSERT INTO quizzes_results
	      					(user_id, lesson_id, question_id, answer_id, freeanswer_data, quiz_id, subject_id, junior_id) VALUES
	      					(".$s[mid].", ".$s[sheid].", ".$adodb->Quote($vopros[kod]).", 0, ".$adodb->Quote($answer).", ".TEST_ID.", ".$s[cid].", ".$junior.")");
                  }
              }
          }

      }
      
      /*
       * 
       * BYYYYYE!
       * 
       */
      
      $table_f =(dbdriver == "mssql") ? "logseance" : "logseance";
      $attach =(dbdriver == "mssql") ? "0x".$data_attach['hex'] : $attach;
      $html_vopros = (dbdriver == 'mssql') ? str_replace("'","\"",$html_vopros) : $html_vopros;
      if($attach)
      $adodb->UpdateBlob($table_f, 'attach',$attach,"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod]));   
      if($html_vopros)
      $adodb->UpdateClob($table_f,'vopros',prepareGeshi($html_vopros),"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod]));
      if($text)
      $adodb->UpdateClob($table_f,'text',$text,"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod])); 
      if($r = serialize($ok[doklad]))
      $adodb->UpdateClob($table_f,'otvet',serialize($ok[doklad]),"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod])); 
//      $adodb->UpdateClob($table_f,'otvet',$adodb->Quote(serialize($ok[doklad])),"stid='".addslashes($s['stid'])."' AND kod=".$adodb->Quote($vopros[kod])); 
     }
 //".$adodb->Quote($html_vopros).",
       //".$adodb->Quote($text).")";
             //".$adodb->Quote(serialize($ok[doklad])).",
   /*$res=sql("INSERT INTO logseance (stid, mid, cid, kod, number, time, bal, balmax, balmin, good, vopros, otvet, attach, filename, text)
     values (
      $s[stid],
      $s[mid],
      $s[mid],
      '".addslashes($vopros[kod])."',
      $number,
      ".time().",
      $ok[bal],
      $vopros[balmax],
      $vopros[balmin],
      $ok[good],
      '".addslashes($html_vopros)."',
      '".addslashes(serialize($ok[doklad]))."',
      '".addslashes($attach)."',
      '".addslashes($filename)."',
      '".addslashes($text)."')","errTo227");*/   
   
   //echo "<H1>".$ok[otv]."</H1>";




?>