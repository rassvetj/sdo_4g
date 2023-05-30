<?php
interface HM_Lesson_LessonModel_Interface
{
    public function getType();

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE);

    public function isExternalExecuting();

    public function getExecuteUrl();

    public function getResultsUrl($options = array());
    
    public function isResultInTable();
    
    public function isFreeModeEnabled();

    public function onFinish($result);

    public function onStart();
}