<?php
class HM_Formula_FormulaService extends HM_Service_Abstract
{
    public function getById($formulaId)
    {
        static $formulaCache = array();

        if (!isset($formulaCache[$formulaId])) {
            $formulaCache[$formulaId] = $this->getOne($this->find($formulaId));
        }

        return $formulaCache[$formulaId];
    }
    /**
     * Переводит значения формулы в аобсолютные величины
     * в рамках значений от $scaleMin до $scaleMax
     * @param $formula строковое представление вормулы
     * @param $scaleMin минимальное значение шкалы
     * @param $scaleMax максимальное значение шкалы
     * @return array|bool ключами массива являются числовые представления оценок,
     *                    в качестве значений - соответствующие абсолютные величины шкалы
     * @todo: Оценки приводятся к числовому представления, с нечисловыми оценками будет магия.
     */
    public function getFormulaMarksByScale($formula, $scaleMin, $scaleMax)
    {
        $marks   = array();
        $formula = rtrim($formula, ';');
        if (!strlen($formula)) return false;

        $items = explode(';', $formula);

        foreach ($items as $item) {
            list($null, $mark) = explode(':', $item);
            $marks[] = (int) $mark;
        }

        if (!count($marks)) return false;

        $step      = ($scaleMax - $scaleMin)/(count($marks) - 1);
        $result[0] = $scaleMin;

        for ($i = 1; $i <= (count($marks) - 1); $i++ ) {
            $result[$i] = $step*$i;
        }

        $result[(count($marks) - 1)] = $scaleMax;

        return array_combine($marks, $result);
    }

    public function getPenaltyDays($timestamp_event, $timestamp_base)
    {
        return ceil(($timestamp_base - $timestamp_event)/86400);
    }

    public function getPenalty($formula_id, $value)
    {
        $formula = $this->find($formula_id)->current();
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
        return false;
    }

    /** from unmanaged/formula_calc.php
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
            $s = trim($s);
            $s = trim($s,";");
            $scoreRange=explode(";", $s);
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
            $mark = $this->findMatchedFormulaRange($scoreRange,$count);
            // если дробные части не входят в промежутки. берем только целую часть.
            if(!$mark){
                $mark = $this->findMatchedFormulaRange($scoreRange,floor($count));
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


}

