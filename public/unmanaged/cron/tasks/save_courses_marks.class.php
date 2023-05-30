<?php

class CCronTask_save_courses_marks extends CCronTask_interface {

    function init() {

    }

    function run() {
        require_once("{$GLOBALS['wwf']}/formula_calc.php");
        require_once("{$GLOBALS['wwf']}/move2.lib.php");

        /* Курсы с выставленной итоговой формулой */
        $query1 = "SELECT CID, formula_id FROM Courses WHERE formula_id > 0";
        $res1 = sql($query1);
        while ($row1 = sqlget($res1)) {
            $row1['CID'] = intval($row1['CID']);

            /* Студенты по данному курсу */
            $query2 = "SELECT MID FROM Students WHERE CID = {$row1['CID']}";
            $res2 = sql($query2);
            while ($row2 = sqlget($res2)) {
                /* Всего занятий у студента */
                $query_count1 = "SELECT schedule.`SHEID`, `Title` , `Icon`, schedule.begin
                                FROM `schedule`, scheduleID, `EventTools`
                                WHERE `vedomost` = 1
                                AND `CID` = {$row1['CID']}
                                AND EventTools.TypeID=schedule.typeID
                                AND schedule.SHEID = scheduleID.SHEID
                                AND scheduleID.MID = '{$row2['MID']}'
                                ORDER BY schedule.begin";
                $res_count1 = sql($query_count1);
                $count1 = sqlrows($res_count1);
                
                /* Всего пройденных занятий */
                $count2 = 0;
                $r = array(-1 => 0);
                $rmark = array();
                while ($row_count2 = sqlget($res_count1)) {
                    $r[]= intval($row_count2['SHEID']);
                }
                $query_ = "SELECT `SHEID`, test_date, `V_STATUS` as mark, `scheduleID`.`SHEID` as SHEID
                          FROM `scheduleID`
                          WHERE `scheduleID`.`SHEID` IN (".implode($r,", ").")
                          AND scheduleID.MID={$row2['MID']}
                          ORDER BY `scheduleID`.`SHEID` ASC";
                $res_ = sql($query_);
                while ($row_ = sqlget($res_)) {
                    $rmark[$row_['SHEID']] = $row_['mark'];
                }
                for ($j=0; $j<$count1; $j++) {
                    if (isset($rmark[$r[$j]])) {
                        if (intval($rmark[$r[$j]]) > 0) {
                            $count2++;
                        }
                    }
                }

                /* Процент прохождения учебного плана */
                $percent = $count1 > 0 ? ($count2/$count1) * 100 : 0;
                
                //echo "CID={$row1['CID']}, MID={$row2['MID']}, {$percent}% ({$count2}/{$count1})<br/>";

                /*Если 100% учебного плана */
                if (intval($percent) == 100) {
                    saveCourseMark($row1['CID'], $row2['MID'], getCourseMarkByFormula($row2['MID'], $row1['CID'], $row1['formula_id']), $GLOBALS['markGr']);
                }
            }
        }
    }
}

?>