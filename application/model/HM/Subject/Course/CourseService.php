<?php
class HM_Subject_Course_CourseService extends HM_Service_Abstract
{

    /**
     * Возвращает массив с моделями, в которых subject_id -> парент
     * @param unknown_type $courseId
     * @return multitype:
     */
    public function getCourseParent($courseId){
        $ret = array();

        $ret = $this->fetchAll(array('course_id = ?' => $courseId));

        return $ret;
    }

    public function copy($fromSubjectId, $toSubjectId)
    {
        $result = array(); // возвращается массив ассоциаций "оригиналИД"=>"копияИД"

        $links = $this->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));
        $this->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));
        
        if (count($links)) {
            //копирование локальных модулей
            $courseIds    = $links->getList('course_id');
            $localCourses = $this->getService('Course')->fetchAll($this->quoteInto(array('CID IN (?)', ' AND chain <> ? AND chain IS NOT NULL'), array($courseIds, 0)));

            if (count($localCourses)) {
                foreach ($localCourses as $localItem) {
                    $oldID = $localItem->CID;
                    unset($courseIds[$localItem->CID], $localItem->CID);
                    $data = $localItem->getValues();
                    $data["chain"] = $toSubjectId;
                    $data["tree"]  = '';
                    $newCourse = $this->getService('Course')->insert($data);
                    // копирование содержимого курса
                    $this->getService('CourseItem')->copyItem($oldID, $newCourse->CID);

                    if ($newCourse) {
                        $courseIds[$newCourse->CID] = $newCourse->CID;
                        $result[$oldID]             = $newCourse->CID;
                    }
                }
            }
            foreach($courseIds as $link) {
                $this->getService('Subject')->linkCourse($toSubjectId, $link);
            }
        }
        return $result;
    }


}