<?php
interface HM_Service_Schedulable_Interface
{
    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null);

    public function getLessonModelClass();
    
    public function onLessonUpdate($lesson, $form);
}
