<?php

$GLOBALS['v_edit_leader6']=array(
   'title'=>_("табличный ввод"),
   'info'=>_("Вопрос на поиск соответствий столбцов таблицы ее строкам. Каждой строке может соответствовать только один столбец, при этом один столбец может быть поставлен в соответствие произвольному числу строк."),
   'balcalc' => "php",
   'goodotvet'=>0,
   'string'  => array(
      'vopros'   => array("textarea",_("Формулировка вопроса")),
   ),
   'variant' => array (
      'variant1'  => array("string",_("Строки"),250),
      'variant2'  => array("string",_("Столбцы"),250),
   ),
   'default'=>array(
      'vopros' => "",
      'variant1' => array('1'=>'','2'=>'','3'=>''),
      'variant2' => array('1'=>'','2'=>'','3'=>''),
      'balmin' => 0,
      'balmax' => 1,
   ),
   'msg_editwindow'=>_("табличный ввод"),
);

function v_sql2php_leader6(&$vopros) {

    $data=explode($GLOBALS[brtag],$vopros[qdata]);
    $vopr=$data[0];
    $users=array(); // slaves
    $answers=array(); // answers

    global $s, $adodb;
    $goodotvet=array();

    if ((count($data)-1)%2!=0) err(_("Ошибка структуры данных в вопросе:")." error=1, type=3, kod=$vopros[kod]",__FILE__,__LINE__);
    for ($i=1; $i<count($data); $i+=2) {
/*        if (!empty($data[$i])) {
            $v1[$varcount]=trim($data[$i]);
        }*/
        if (!empty($data[$i+1])) {
            $answers[]=trim($data[$i+1]); // $answers[$data[$i]]=trim($data[$i+1])
        }
    }

    $res = sql('SELECT DISTINCT '.$adodb->Concat('People.LastName', "' '", 'People.FirstName', "' '",'People.Patronymic').' fio, People.MID
    			FROM dean_poll_users
    			INNER JOIN People ON (People.MID = dean_poll_users.student_mid)
    			WHERE
    			    dean_poll_users.lesson_id = '.$s['sheid'].' AND dean_poll_users.head_mid = '.$s['mid'].'
                    AND People.MID NOT IN (
                        SELECT DISTINCT junior_id FROM quizzes_results
                        WHERE lesson_id = '.$s['sheid'].' AND user_id = '.$s['mid'].' AND question_id = '.$adodb->Quote($vopros[kod]).'
                    )'
                );
    while($row = sqlget($res)){
    	$row['fio'] = (isset($row['fio'])) ? $row['fio'] : $row['FIO']; // oracle hack
    	$users[$row['MID']] = $row;

    }
    echo '<div style="text-align:left">';
    $answers = array(_('свободный ответ'));
    //pr($users); pr($answers);

    if (count($users)==0) $goodotvet['gerror1'] = "EMPTY";
    return array(
        'vopros'=>$vopr,
        'balmin'=>$vopros[balmin],
        'balmax'=>$vopros[balmax],
        'variant1'=>$users,
        'variant2'=>$answers,
        'qtema'=>$vopros[qtema],
        'url'=>$vopros[url],
        'type'=>leader1,
        'varcount'=> count($answers), // or users i nafiga ono woobshe nado?
        'goodotvet'=> 0,
        'timetoanswer'=>$vopros[timetoanswer],
    );
}

function v_php2sql_leader6($arr) {
    global $brtag,$brremove;

    $x=array();
    $y=array();
    $x[]=trim(brremove($arr[vopros]));
    if (count($arr[variant1])) {
        $count = max(array(count($arr['variant1']),count($arr['variant2'])))*2;
        for($i=1;$i<=$count;$i+=2) {
            $x[$i]=trim(brremove(array_shift($arr[variant1])));
            $x[$i+1]=trim(brremove(array_shift($arr[variant2])));
        }
    }
    $out=array(
        'kod'=>$arr[kod],
        'qtype'=>leader1,
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

function v_vopros_leader6(&$vopros,$tm,$number,&$attach) {
    global $s;

    ob_start();
    if (!empty($vopros['weight'])) {
        $weight = unserialize($vopros['weight']);
    }
    $kod=$vopros[kod];
    $v_number=$number+1;

    $v_vopros=v_bbparse_vopros($kod,$vopros,$vopros[vopros],$number,$attach);

    $answer = '';
    if (isset($vopros['otvets'])) {
        $sql = "SELECT text FROM logseance WHERE kod='".addslashes($kod)."' AND stid='".(int) $_SESSION['s']['stid']."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $answer = $row['text'];
            if (($answer[0]=="'") && ($answer[strlen($answer)-1]=="'")) $answer = substr($answer,1,-1);
        }
    }

    include("$tm-v-main.html");

    $out=ob_get_contents();
    ob_end_clean();

    return $out;
}

function v_otvet_leader6(&$vopros,$tm,$number,&$attach,$form) {

    // обработка результатов

    $doklad = array(); $bal = 0; $otv = array(); $good = 100;

    if (!empty($vopros['weight'])) {
        $weight = unserialize($vopros['weight']);
    }

    if (is_array($vopros['variant2']) && count($vopros['variant2'])) {
        foreach($vopros['variant2'] as $k=>$v) {
            $doklad['weights'][$k+1] = $weight[$k+1];
        }
    }

    if (is_array($vopros['variant1']) && count($vopros['variant1'])
        && is_array($doklad['weights']) && count($doklad['weights'])) {
        foreach($vopros['variant1'] as $k=>$v) {
            // не передан ответ
            if (!isset($form[$k])) {
                $doklad[error][] = _("Не передан ответ на вариант")." N$k.";
                $doklad['good'][$k] = 0;
                $good = 0;
                continue;
            }

            $otv[$k] = $form[$k];
            $doklad['otv'][$k] = $form[$k];
            $doklad['main'][$k] = sprintf(_("По слушателю %s выбран вариант %s"), $vopros[variant1][$k]['fio'], $form[$k]);
            $doklad['good'][$k] = 1;
            $bal += (int) $doklad['weights'][$form[$k]+1];
        }
    }

    $doklad['text'] = $form['otvet'];
    $doklad['variant1'] = $vopros['variant1'];
    $doklad['variant2'] = $vopros['variant2'];
    $doklad['qtype'] = $vopros['qtype'];
    $doklad['vopros'] = $vopros['vopros'];
    $doklad['qtema'] = $vorpos['qtema'];

    $out = array('bal' => $bal, 'otv' => $otv, 'good' => $good, 'info' => "",'doklad' => $doklad);

    if (tdebug) {
        pr($vopros);
        pr($form);
        pr($out);
    }

    return $out;

}

?>