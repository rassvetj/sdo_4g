<?php

class HM_Section_SectionTable extends HM_Db_Table
{
    protected $_name = "sections";
    protected $_primary = "section_id";
    protected $_sequence = 'S_100_SECTIONS';


    protected $_dependentTables = array(
        'HM_Subject_Course_CourseTable',
        'HM_Subject_Resource_ResourceTable',
    );

    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'onDelete' => self::CASCADE,
            'propertyName' => 'subjects'
        ),
        'Lesson' => array(
            'columns' => 'section_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns' => 'section_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'lessons'
        ),
    );
}