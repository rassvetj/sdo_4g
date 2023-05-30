<?php
/**
 * Created by PhpStorm.
 * User: CuTHuK
 * Date: 01.04.14
 * Time: 16:01
 */

class HM_Mark_MarkStrategy extends HM_Service_Primitive
{
    // кэширующие функции для работы calcTotalValue (иногда может вызываться много раз
    protected $_lessonAssignCache = array();

    protected function _getLessonAssign($userId, $subjectId)
    {
        $cache = &$this->_lessonAssignCache;

        if (!isset($cache[$subjectId])) {
            $cache[$subjectId] = array();
        }

        if (!isset($cache[$subjectId][$userId])) {

            $lessonAssignService = $this->getService('LessonAssign');

            $cache[$subjectId][$userId] = $lessonAssignService->fetchAllDependenceJoinInner('Lesson', $lessonAssignService->quoteInto(array(
                'self.MID = ? AND ',
                'Lesson.CID = ? AND ',
                'Lesson.isfree = ? AND ',
                'Lesson.vedomost = 1'
            ), array(
                $userId,
                $subjectId,
                HM_Lesson_LessonModel::MODE_PLAN
            )));
        }

        return $cache[$subjectId][$userId];

    }

    protected function _loadLessonAssignCache($subjectId)
    {
        $cache = array();

        $lessonAssigns = $this->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $this->getService('LessonAssign')->quoteInto(array(
            'Lesson.CID = ? AND ',
            'Lesson.isfree = ? AND ',
            'Lesson.vedomost = 1'
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_PLAN
        )));

        foreach ($lessonAssigns as $lessonAssign) {
            $mid = $lessonAssign->MID;

            if (!isset($cache[$mid])) {
                $cache[$mid] = array();
            }

            $cache[$mid][] = $lessonAssign;
        }

        $this->_lessonAssignCache[$subjectId] = $cache;

        return $cache;
    }

    protected function _getAllEvents()
    {
        static $cache = null;

        if ($cache === null) {
            $cache = $this->getService('Event')->fetchAll();
        }

        return $cache;
    }

    /**
     * Подсчитывает лучший результат среди текущих слушаталей курса
     *
     * @param $subjectId
     * @return int|number
     */
    public function calcMaxTotalValueOfStudents($subjectId)
    {
        $cache = $this->_loadLessonAssignCache($subjectId);
        $max = 0;

        foreach ($cache as $mid => $lessonAssigns) {
            $userTotal = $this->calcTotalValue($subjectId, $mid);

            if ($userTotal > $max) {
                $max = $userTotal;
            }
        }

        return $max;
    }

    public function getLessonAssign($userId, $subjectId)
    {
        return $this->_getLessonAssign($userId, $subjectId);
    }
    
    public function getBestScore($score, $lessonAssign) {
        if($lessonAssign->V_STATUS && $lessonAssign->V_STATUS > $score){
            $score = $lessonAssign->V_STATUS;
        }
        return $score;
    }
} 