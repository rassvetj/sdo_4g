<?php
class HM_Section_SectionService extends HM_Service_Abstract
{
    public function getSectionsMaterials($subjectId)
    {
        $sections = $this->fetchAll($this->quoteInto('subject_id = ?', $subjectId), 'order ASC');
        $lessons = $this->getService('Lesson')->fetchAll($this->quoteInto(array(
            'CID = ?',
            ' AND isfree = ?',
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_FREE
        )), 'order ASC');

        $coursesArr = $resourcesArr = array();
        if ($courses = $this->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subjectId)) {
            foreach ($courses as $course) {
                $coursesArr[$course->CID] = $course;
            }
        }

        if ($resources = $this->getService('Resource')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subjectId)) {
            foreach ($resources as $resource) {
                $resourcesArr[$resource->resource_id] = $resource;
            }
        }

        // сортировка fetchAllDependenceJoinInner не работает..(
        // и вообще здесь нужен fetchAllDependenceJoinLeft
        // $sections = $this->fetchAllDependenceJoinInner('Lesson', $this->quoteInto('subject_id = ?', $subjectId), array('self.order ASC', 'Lesson.order ASC'));

        /*
         * костыль ((
         * т.к. нигде не контролируется значение удаленных section_id в schedule (lesson)
         * при удалении раздела остаются никуда не привязанные занятия и при выводе они просто отбрасываются
         * пробегаюсь по занятиям и присваюваю им в модели id дефолтной секции.
         */
        
        $defaultSectionId = 0;
        foreach ($sections as $section) {
            if($section->name == ''){
                $defaultSectionId = $section->section_id;
            }
        }
        
        if($defaultSectionId == 0){
            $defaultSectionId = $sections->current()->section_id;
        }
                
        $sections_ids = $sections->getList('section_id', 'section_id');
        $lessonArr = array();
        foreach ($lessons as $lesson) {
            if(!$sections_ids[$lesson->section_id]){
                $lesson->section_id = $defaultSectionId;
            }
        }
        
        
        foreach ($sections as $section) {
            $lessonArr = array();
            foreach ($lessons as $lesson) {
                if ($lesson->section_id != $section->section_id) continue;

                $moduleId = $lesson->getModuleId();
                $arr = ($lesson->typeID == HM_Event_EventModel::TYPE_COURSE) ? $coursesArr : $resourcesArr;
                $lesson->material = isset($arr[$moduleId]) ? $arr[$moduleId] : false;

                $lessonArr[] = $lesson;
            }
            $section->lessons = $lessonArr;
        }
        
        return $sections;
    }

    public function getDefaultSection($subjectId)
    {
        $sections = $this->fetchAll(array('subject_id = ?' => $subjectId), 'order', 1);
        if (!count($sections)) {
            $section = $this->createDefaultSection($subjectId);
        } else {
            $section = $sections->current();
        }
        return $section;
    }

    public function createDefaultSection($subjectId)
    {
        return $this->insert(array(
            'subject_id' => $subjectId,
            'name' => '',
            'order' => 1,
        ));
    }

    public function getCurrentOrder($section)
    {
        $materials = $this->getService('Lesson')->fetchAll($this->quoteInto(
            array('CID = ?', ' AND section_id = ?', ' AND isfree = ?'),
            array($section->subject_id, $section->subject_id, HM_Lesson_LessonModel::MODE_FREE)
        ), 'order DESC', 1);

        if (count($materials)) {
            return $materials->current()->order;
        }
        return 0;
    }

    public function getCurrentSectionOrder($subjectId)
    {
        $sections = $this->fetchAll(array(
            'subject_id = ?' => $subjectId,
        ), 'order DESC', 1);

        if (count($sections)) {
            return $sections->current()->order;
        }
        return 0;
    }

    public function setMaterialsOrder($sectionId, $materials)
    {
        if (is_array($materials)) {
            foreach ($materials as $order => $lesson_id) {
                $this->getService('Lesson')->updateWhere(array(
                    'section_id' => $sectionId,
                    'order' => $order,
                ), array(
                    'SHEID = ?' => $lesson_id,
                ));
            }
            return true;
        }
    }

    public function copy($section, $toSubjectId = null)
    {
        if ($section) {

            if (null !== $toSubjectId) {
                $section->subject_id = $toSubjectId;
            }
            $newSection = $this->insert($section->getValues(null, array('section_id')));

            return $newSection;
        }
        return false;
    }

    public function getSectionsLessons($subjectId, $addingWhere, &$titles)
    {
        $sections = $this->fetchAll($this->quoteInto('subject_id = ?', $subjectId), 'order ASC');

        if (!count($sections)) {
            $this->createDefaultSection($subjectId);
            $sections = $this->fetchAll($this->quoteInto('subject_id = ?', $subjectId), 'order ASC');
        }

        if (count($sections)) {
            $this->getService('Lesson')->updateWhere(
                array('section_id' => $sections->current()->section_id),
                $this->quoteInto('CID = ? AND (section_id = 0 OR section_id IS NULL)', $subjectId)
            );
        }

        $lessons = $this->getService('Lesson')->fetchAllDependence(
        		array('Assign', 'Teacher'),
        		array(
        			'CID = ?' => $subjectId,
                    'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
    		        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        		) + $addingWhere,
        		array('order', 'begin', 'SHEID')
        );

        if (count($sections) && count($lessons)) {
            foreach ($sections as $section) {
                $lessonArr = array();
                foreach ($lessons as $lesson) {
                    if ($lesson->section_id != $section->section_id) continue;

                    $lessonArr[$lesson->SHEID] = $lesson;
                    $titles[$lesson->SHEID] = $lesson->title;
                }
                $section->lessons = $lessonArr;
            }
        }
        return $sections;
    }

    public function setLessonsOrder($sectionId, $materials)
    {
        if (is_array($materials)) {
            $section = $this->getOne($this->find($sectionId));
            if ($section) {
                foreach ($materials as $order => $lesson_id) {
                    $order = $section->order . $order;
                    $this->getService('Lesson')->updateWhere(array(
                        'section_id' => $sectionId,
                        'order' => $order,
                    ), array(
                        'SHEID = ?' => $lesson_id,
                    ));
                }
                return true;
            }
        }
        return false;
    }

}