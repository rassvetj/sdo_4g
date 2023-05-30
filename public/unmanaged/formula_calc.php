<?
// версия 2.1 ДК
// создание таблицы соответсвий % от максмально балла - оценке
//
require_once($_SERVER['DOCUMENT_ROOT'].'/national.php');

function viewGrFormula($s,$type=0){

         $formula_array = explode(";", $s);
         if(is_array($formula_array)) {

            $return_str = "<table border=0>";
            foreach($formula_array as $key => $value) {
                 if(trim($value) == "") {
                         continue;
                 }

                 $tmp = explode(":", $value);

                 if ($type==5) {
                    // Формула штрафа за несвоевременное выполнение занятия
                    $percent = (int) (100-round($tmp[1]*100));
                    if ($percent<0) $percent=0;
                    $return_str .= "<tr><td>".$tmp[0]." "._("дней после окончания")."</td><td nowrap>  -> </td><td>".(int) $percent."%</td></tr>";
                 } else
                    $return_str .= "<tr><td>".$tmp[0]." "._("баллов")."</td><td nowrap>  -> </td><td>".$tmp[1]."</td></tr>";

            }
            $return_str .= "</table>";
         }
         echo $return_str;
}

// целое число дней между датой окончания занятия
function getPenaltyDays($timestamp_event, $timestamp_base){
	return floor(($timestamp_base - $timestamp_event)/86400);
}

function getPenaltyFormula($id) {
    if ($id) {
        $sql = "SELECT formula FROM formula WHERE id='".(int) $id."'";
        $res = sql($sql);
        if (sqlrows($res) && $row = sqlget($res)) return $row['formula'];
    }
}

function viewPenaltyFormula($formula, $value) {
    if (!empty($formula)) {
        $parts=explode(";", trim($formula,";") );
        if (count($parts)) {
        	$return_single_day = false;
            foreach($parts as $part) {
                $temp = explode(":", $part);
                $rezz[3] = $temp[1];
                // если используется синтаксис "6:0.9" - только количество дней, без диапазона
                if ((count($temp_2 = explode("-", $temp[0])) == 1)){
                	$value_formula = round($temp_2[0], 2);
                	// если текущий псевдо-диапазон перекрывает предыдущий
                	if ((!isset($value_previous) || ($value_formula < $value_previous)) && ($value <= $value_formula)) {
                		$return_single_day = $rezz[3];
                		$value_previous = $value_formula;
                	}
                } else {
	                $rezz[1] = $temp_2[0];
	                $rezz[2] = $temp_2[1];
	                $min = round($rezz[1],2);
	                $max = round($rezz[2],2);
				    if (($value >= $min) && ($value <= $max)) return $rezz[3];
                }
            }
            return $return_single_day;
        }
    }
}

/**
 * Выбирает оценку изходя из набора диапазонов и кол-во баллов/%
 *
 * @author Artem Smirnov <tonakai.personal@gmail.com>
 * @date 11.01.2013
 *
 * @param $scoreRange набор диапазонов оценок
 * @param $count кол-во набранных баллов
 *
 * @return null|float оценка за тест.
 */
function findMatchedFormulaRange($scoreRange, $count)
{
    $mark = null;
    foreach($scoreRange as $order => $rangeArray)
    {
        if(!is_array($rangeArray)){continue;}
        if (($scoreRange[$order]['min'] !== null) && ($scoreRange[$order]['max'] !== null)) {
            $matched = (($count >= $scoreRange[$order]['min']) && ($count <= $scoreRange[$order]['max']));
        } elseif ($scoreRange[$order]['min']) {
            $matched = ($count >= $scoreRange[$order]['min']);
        } elseif ($scoreRange[$order]['min']) {
            $matched = ($count <= $scoreRange[$order]['max']);
        } else {
            $matched = true;
        }
        if ($matched) {
            $mark=$rangeArray[3];
            if(isset($rangeArray[5]))
            {
                $GLOBALS['markGr']=$rangeArray[5];
            }
        }
    }
    return $mark;
}


function viewFormula($s, &$text, $minb, $maxb, $ball, $formula_type=1){
   //echo "minb: ".$minb.", maxb:".$maxb.", ball:".$ball;
   // формирование формулы для каждой оценки
   // вывод в таблицу значений по формуле
   // подсчет оценки
   //$s - текст формулы,
   // &$text - формула для отображения,
   // $minb, $maxb, $ball
   $i=0;
   $mark = 0;
   if ($minb<0) $minb = 0;
   if ($ball<0) $ball = 0;
   $del=$maxb - $minb;

   if($del==0)
     $count=0;
   else
     $count = ($ball-$minb)*100/$del;
   $str="";
    /**
     * Переписана функция разбиения формулы.
     *
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 11.01.2013
     */
    if( strlen($s) ) {
       $scoreRange=explode(";", $s = trim($s,";") );
       foreach($scoreRange as $order => $rangeString)
       {
           //string format expected "min-max:score(text)"
           if(preg_match("/^([^-]*)-([^:]*):([^\(]*)(\((.*)\))?$/", $rangeString, $scoreRange[$order]) == 1)
           {
               $scoreRange[$order] = array_map("trim",$scoreRange[$order]);
               $units = ((in_array($formula_type, array(3, 4)))?_("баллов"):'%');
               $rangeArray = $scoreRange[$order];
               $str.= "<TR><TD>{$rangeArray[1]} .. {$rangeArray[2]} {$units}<TD><TD>-></TD><TD style='white-space: normal;'> {$rangeArray[3]} {$rangeArray[4]}</TD></TR>";
               $scoreRange[$order]['min'] = ($rangeArray[1] !== '') ? round($rangeArray[1],2) : null;
               $scoreRange[$order]['max'] = ($rangeArray[2] !== '') ? round($rangeArray[2],2) : null;
           }
       }
       $mark = findMatchedFormulaRange($scoreRange,$count);
       // если дробные части не входят в промежутки. берем только целую часть.
       if(!$mark){
            $mark = findMatchedFormulaRange($scoreRange,floor($count));
       }
   }
   else{
    $str.=" ".((in_array($formula_type, array(3, 4)))?_("баллов"):'%')." -> *";
    $mark=$count;
   }
   $text="<table>".$str."</table>";
   // нужно текстовое поле
   return $mark;
//   return round($mark);
}


//условие окончания обучения
// по событию - преподаватель \
// по условию

function getPeopleData( $cid, $mid ){
 // РАСЧИТЫВАЕТ ПОКАЗАТЕЛИ УСПЕВАЕМОСТИ СТУДЕНТА
 if( ( $cid > 0) && ( $mid > 0 ) ){
/*   $req="SELECT *
   FROM `scheduleID`, `schedule`
     WHERE `scheduleID`.`SHEID`= `schedule`.`SHEID` )
     AND scheduleID.MID=$mid
     AND `vedomost` = 1";
   $res=sql($req," ERR- getPeopleData");
   while( $r=sqlget( $res ) ){
     $r[]
   }
  */
   $data['progress']=100;
 }else{
   $data['progress']=100;
   $data['final']=100;
   $data['total']=100;
   $data['level']=100;
 }
 return( $data );
}

function applyFinishCourseFormula( $cid, $mid_data ){
  // проверяет выполняется хотябы одно условие окончания обучения для человека с даными data

   $res=sql("SELECT * FROM formula WHERE (CID='".$cid."' OR CID=0) AND type=2","errFM5011");

   while ( $r = sqlget($res) ) {

     $out = finishCourseFormula( $r[formula], $text, $mid_data );

     if( $out!="" ) return( $out );
     $i++;
   }
   return("");
}


function finishCourseFormula( $str, &$text, $data ){
  // progress > 100%, final>4,total > 70,uspev>3:Ok
  // % зачтеных занятий
  // средний балл
  // всего набранных баллов (накопленым итогом)

  // ???набран указнный балл

//   $data=getPeopleData( $cid, $mid );

  $ss=explode(":", $str );
  if( count ($ss) > 1 ){

  $ok=$ss[1];

  $conds=explode(",", $ss[0] );
  if( count ($conds) > 0 ){
    foreach( $conds as $k=>$cond ){

      if( $k > 0 ) $out.="<BR><u>"._("И")."</u> "; else $out.="<u>"._("ЕСЛИ")."</u> ";

      if ( eregi( "^([".get_national_letters_set()."]+)(<|>|=)([0-9]+)$", $cond, $rezz)){
        switch( $rezz[1] ){
         case "progress": // % выполнения уч. плана
            $x=$data['progress'];
            $out.="% "._("выполненого")." ";
//            echo " выполнено $x !";
         break;
         case "total": // сумма оценок
            $x=$data['total'];
            $out.=_("суммарно набрано")." ";
//            echo " набрано $x !";
         break;
         case "level": // средний балл (ср. успеваемость)
            $x=$data['level'];
            $out.=_("ср. оценка")." ";
//            echo " ср. оц. $x !";
         break;
         case "final": // в тестах    НЕ ИСПОЛЬЗУЕТСЯ!!!!!!!!
            $x=$data['final'];
//            $out.="набрано всего баллов ";
         break;
       }
       //  обработка оперции сравнения
       switch( $rezz[2] ){
         case "<":
           if( $x >= $rezz[3] ) $ok="";
           $out.=_("менее")." ".$rezz[3];
         break;
         case "=":
           if( $x != $rezz[3] ) $ok="";
           $out.="=".$rezz[3];
         break;
         case ">":
           if( $x <= $rezz[3] ) $ok="";
           $out.=_("более")." ".$rezz[3];
         break;
       }
      }
    }
  }
  }
  $text="<P ALIGN=LEFT>".$out."<BR><u>"._("ТО")."</u>: ".$ok."";


  return( $ok );
}

/**
* Возвращает array[mark] = grMark
*/
function getFormulaGrMarks($formula) {
    $ret = array();
    if (!empty($formula)) {
        $parts = explode(';',trim($formula,';'));
        if (is_array($parts) && count($parts)) {
            foreach($parts as $v) {
                $parts_of_part = explode(':',$v);
                if (!empty($parts_of_part[1])) {
                    if (preg_match("/^(.*)\((.*)\)$/",trim($parts_of_part[1]),&$matches)) {
                        $ret[(int) $matches[1]] = $matches[2];
                    }
                }
            }
        }
    }
    return $ret;
}

function parseFormulasGrMarks($cid=0) {
    $ret = array();
    if ($cid>0) $sql = " AND (CID='".(int) $cid."' OR CID='0') ";

    $sql = "SELECT formula, id FROM formula WHERE type='1' {$sql}";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $ret[$row['id']] = getFormulaGrMarks($row['formula']);
    }
    return $ret;
}

/**
* Возвращает текстовую версию оценки для формулы типа 1
*/
function getFormulaIdBySheid($sheid) {
    $sql = "SELECT params FROM schedule WHERE SHEID='".(int) $sheid."' LIMIT 1";
    $res = sql($sql);
    if (sqlrows($res)) {
        $row = sqlget($res);
        $formulaId = getIntVal($row['toolParams'],"formula_id=");
        if ($formulaId>0) {
            return $formulaId;
        }
    }
}

function getCourseMarkFormulasArray($cid) {
    if ($cid) {
        $sql = "SELECT id, name FROM formula WHERE (CID='".(int) $cid."' OR CID='0') AND type='4'";
        $res = sql($sql);
        while($row=sqlget($res)) {
            $rows[$row['id']] = $row['name'];
        }
    }
    return $rows;
}

function getCourseMarkByFormula($mid,$cid,$formula_id) {
    if ($mid && $formula_id) {
        $sql = "SELECT formula FROM formula WHERE id='".(int) $formula_id."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            return viewFormula($row['formula'],$text,0,100,getCourseRating($cid,$mid));
        }
    }
}

?>