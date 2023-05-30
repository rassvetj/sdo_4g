<?php
/**
 * Created by PhpStorm.
 * User: CuTHuK
 * Date: 18.03.14
 * Time: 13:39
 */

interface HM_Mark_MarkBehavior {

    public function setUserScore($userId, $scheduleId, $score, $courseId, $automatic);
    public function onLessonScoreChanged($subjectId, $userId);
    public function calcTotalValue($subjectId, $userId, $throwExceptionIfLessonStatusIsNA);
    public function calcMaxTotalValue($subjectId);
    public function getValue();
    public function addTypeElements(HM_Form &$form);
} 