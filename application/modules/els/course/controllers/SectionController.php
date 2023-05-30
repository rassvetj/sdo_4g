<?php
class Course_SectionController extends HM_Controller_Action
{
    const COURSE_TYPE_LOCAL = 'Учебный курс';

    private $_subjectId = 0;
    private $_courseId  = 0;
    private $_key       = 0;

    /**
     * @var HM_Course_Item_ItemService
     */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();

        $this->setDefaultService($this->getService('CourseItem'));

        $this->_subjectId = $subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_courseId = $courseId = (int) $this->_getParam('course_id', 0);
        $this->_key = $key = (int) $this->_getParam('key', 0);

        // Запуск из учебного курса
        if ($this->_subjectId > 0) {
            $this->_initSubjectExtended();
        }

        // Запуск из учебного модуля
        if (!$this->_subjectId && ($this->_courseId > 0)) {
            $this->_initCourseExtended();
        }

    }

    private function _initSubjectExtended()
    {
        // Запуск из учебного курса
        if (!$this->isAjaxRequest() && ($this->_subjectId > 0) && ($this->_courseId > 0)) {
            $subject = $this->_getSubject();

            $this->view->setExtended(
                array(
                    'subjectName' => 'Subject',
                    'subjectId' => $this->_subjectId,
                    'subjectIdParamName' => 'subject_id',
                    'subjectIdFieldName' => 'subid',
                    'subject' => $subject
                )
            );

            if ($this->_courseId > 0) {
                $course = $this->_getCourse();
                if ($course) {
                    $this->getService('Unmanaged')->setSubHeader($course->Title);
                }
            }
        }
    }

    private function _initCourseExtended()
    {
        if (!$this->isAjaxRequest() && !$this->_subjectId && ($this->_courseId > 0)) {
            $course = $this->_getCourse();

            $this->view->setExtended(
                array(
                    'subjectName' => 'Course',
                    'subjectId' => $this->_courseId,
                    'subjectIdParamName' => 'course_id',
                    'subjectIdFieldName' => 'CID',
                    'subject' => $course
                )
            );

            if ($course) {
                $this->getService('Unmanaged')->setSubHeader($course->Title);
            }

        }
    }


    private function _getSubject()
    {
        if (null === $this->_subject) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId > 0) {
                $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
            }
        }

        return $this->_subject;
    }

    private function _getCourse()
    {
        if (null === $this->_course) {
            $courseId = (int) $this->_getParam('course_id', 0);
            if ($courseId > 0) {
                $this->_course = $this->getOne($this->getService('Course')->find($courseId));
            }
        }
        return $this->_course;
    }

    public function indexAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';

        $default = new Zend_Session_Namespace('default');
        if ($this->_subjectId && !isset($default->grid['subject-index-courses'][$gridId])) {
            $default->grid['subject-index-courses'][$gridId]['filters']['subid'] = $this->_subjectId; // по умолчанию показываем только учебные модули этого курса
        }

        if (!$this->isAjaxRequest()) {
            if (!$this->_getParam("order{$gridId}", false)) {
                $this->_setParam("order{$gridId}", 'Title_ASC');
            }
        }

        $select = $this->getService('Course')->getSelect();

        $values = array(
            'c.CID',
            'c.Title',
            'c.chain',
            'c.new_window',
            'c.format'
        );

        if ($this->_subjectId > 0) {
            $values = array(
                'c.CID',
                'subid' => 's.subject_id',
                'c.Title',
                'c.chain',
                'c.new_window',
                'c.format'
            );
        }


        $select->from(
                array('c' => 'Courses'),
                $values
            )
            ->where('c.Status IN (?)', array(HM_Course_CourseModel::STATUS_ACTIVE, HM_Course_CourseModel::STATUS_STUDYONLY))
            ->where('c.CID <> ?', $this->_courseId)
            ->where('c.format = ?', HM_Course_CourseModel::FORMAT_FREE);

        if ($this->_subjectId > 0) {
            $select
            ->joinLeft(array('s' => 'subjects_courses'), "c.CID = s.course_id AND subject_id = '".$this->_subjectId."'", array())
            ->where('(s.subject_id = ? OR s.subject_id IS NULL)', $this->_subjectId)
            ->where('c.chain IS NULL OR c.chain = 0 OR c.chain = ?', $this->_subjectId);
        }


        $grid = $this->getGrid($select,
                               array(
                                   'CID' => array('hidden' => true),
                                   'new_window' => array('hidden' => true),
                                   'subid' => array('hidden' => true),
                                   'Title' => array('title' => _('Название')),
                                   'chain' => array(
                                       'title' => _('Место хранения'),
                                       'callback' => array(
                                           'function' => array($this, 'updateTypeColumn'),
                                           'params' => array('{{chain}}', $this->_subjectId)
                                       )
                                   	),
                                   'format' => array(
                                       'title' => _('Формат'),
                                       'callback' => array(
                                           'function' => array($this, 'updateFormatColumn'),
                                           'params' => array('{{format}}')
                                       )
                                   )
                               ),
                               array(
                                   'Title' => null,
                                   'chain' => array(
                                       'values' => array(
                                           $this->_subjectId => _(self::COURSE_TYPE_LOCAL),
                                           0 => _('База знаний')
                                       )
                                   ),
                                   'format' => array('values' => HM_Course_CourseModel::getFormats())
                               ),
                               $gridId);

        if ($this->_subjectId > 0) {
            $grid->setGridSwitcher(array(
                array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subid' => $this->_subjectId)),
                array('name' => 'global', 'title' => _('все, включая учебные модули из Базы знаний'), 'params' => array('subid' => null), 'order' => 'subid', 'order_dir' => 'DESC'),
            ));

            $grid->setClassRowCondition("'{{subid}}' != ''", "selected");
        }

        $grid->addMassAction(
            array('module' => 'course', 'controller' => 'section', 'action' => 'link', 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId, 'key' => $this->_key),
            _('Подключить в учебный модуль'),
            _('Вы уверены?')
        );

        $grid->updateColumn('Title',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{Title}}', '{{CID}}', '{{new_window}}')
                )
            )
        );

        $this->view->subjectId = $this->_subjectId;
        $this->view->courseId  = $this->_courseId;
        $this->view->key = $this->_key;
        $this->view->isGridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateName($title, $courseId, $newWindow)
    {
        return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'course', 'action' => 'index', 'course_id' => $courseId)). '">'. $title.'</a>';
    }

    public function updateTypeColumn($gridSubjectId, $subjectId)
    {
        if ($gridSubjectId == $subjectId) {
            $return = _('Учебный курс');
        } else {
            $return = _('База знаний');
        }
        return "<span class='nowrap'>{$return}</span>";
    }

    public function updateFormatColumn($format)
    {
        return HM_Course_CourseModel::getFormat($format);
    }

    public function linkAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';
        $ids = explode(',' ,$this->_getParam('postMassIds_'.$gridId, ''));

        if (count($ids)) {

            foreach($ids as $id) {
                $course = $this->getOne($this->getService('Course')->find($id));
                if ($course) {

                    $item = $this->_defaultService->append(
                        array(
                            'title' => $course->Title,
                            'cid' => $this->_courseId,
                            'module' => 0
                        ),
                        $this->_key
                    );

                    $key = $item->oid;

                    $items = array(); $level = 0;

                    $children = $this->_defaultService->getChildrenLevel($id, -1, false);

                    if (count($children)) {
                        foreach($children as $child) {

                            if ($child->level > $level) {
                                $level = $child->level;
                                $items[$level] = $key;
                                $key = $item->oid;
                            }

                            if ($child->level < $level) {
                                $level = $child->level;
                                for($i = 1; $i <= ($level - $child->level); $i++) {
                                    $key = array_pop($items);
                                }
                            }

                            $item = $this->_defaultService->append(
                                array(
                                    'title' => $child->title,
                                    'cid' => $this->_courseId,
                                    'module' => $child->module,
                                    'vol1' => $child->vol1,
                                    'vol2' => $child->vol2
                                ),
                                $key
                            );
                        }
                    }
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Учебные модули успешно подключены'));
        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_key, 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId));

    }

}