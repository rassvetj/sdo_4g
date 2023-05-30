<?php

class Subject_MaterialsController extends HM_Controller_Action_Subject implements Es_Entity_EventViewer {

    protected $_subjectId = 0;
    protected $_subject = 0;

    public function init() {
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_subject = $this->getService('Subject')->find($this->_subjectId)->current();
        return parent::init();
    }

    public function indexAction() {
        $this->view->from = $this->_getParam('from', '');
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $this->view->setHeaderOptions(array(
                'pageTitle' => _('Все материалы'),
                'panelTitle' => $this->view->getPanelShortname(array('subject' => $this->_subject, 'subjectName' => 'subject')),
            ));
        }

        $sections = $this->getService('Section')->getSectionsMaterials($this->_subjectId);
        $this->view->sections = $sections;
        $this->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                $this,
                array('filter' => $this->getFilterByRequest($this->getRequest()))
        );
    }

    public function getFilterByRequest(\Zend_Controller_Request_Http $request) {
        $factory = $this->getService('ESFactory'); 
        /*@var $filter Es_Entity_AbstractFilter */
        $filter = $factory->newFilter();
        
        $userId = (int)$this->getService('User')->getCurrentUserId();
        $filter->setUserId($userId);

        $subject = $request->getParam('subject_id', null);
        if ($subject !== null) {
            $group = $this->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$subject
            );
        }
        if ($group->getId() !== null) {
            $filter->setGroupId($group->getId());
        }
        /*@var $eventType Es_Entity_AbstractEventType */
        $eventType = $factory->newEventType();
        $eventType->setId(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ADD_MATERIAL);
        $filter->setEventType($eventType);
        return $filter;
    }

    public function editAction() {
        $form = new HM_Form_Materials();
        $request = $this->getRequest();
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($lesson = $this->getService('Lesson')->find((int) $this->_getParam('SHEID', 0))->current()) {
            $form->setDefaults(array(
                'subject_id' => $subjectId,
                'SHEID' => $lesson->SHEID,
                'title' => $lesson->title,
                'descript' => $lesson->descript,
            ));
        } else {
            $this->_flashMessenger->addMessage(_('Материал не найден'));
            return false;
        }

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $this->getService('Lesson')->updateWhere(array(
                'title' => $form->getValue('title'),
                'descript' => $form->getValue('descript'),
                    ), array(
                'SHEID = ?' => $form->getValue('SHEID'),
            ));

            $this->_flashMessenger->addMessage(_('Материалы курса успешно обновлены'));
            $this->_redirector->gotoSimple('index', 'materials', 'subject', array('subject_id' => $subjectId));
        }
        $this->view->form = $form;
    }

    public function editSectionAction() {
        $form = new HM_Form_Sections();
        $request = $this->getRequest();
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $sectionId = (int) $this->_getParam('section_id', 0);

        $sections = $this->getService('Section')->find($sectionId);
        if (count($sections)) {
            $section = $this->getService('Section')->getOne($sections);
            $form->setDefaults(array(
                'subject_id' => $subjectId,
                'section_id' => $section->section_id,
                'name' => $section->name,
            ));
        }

        if ($request->isPost() && $form->isValid($request->getPost())) {

            if ($sectionId) {
                $this->getService('Section')->updateWhere(array(
                    'name' => $form->getValue('name'),
                        ), array(
                    'section_id = ?' => $sectionId,
                ));
            } else {
                $order = $this->getService('Section')->getCurrentSectionOrder($subjectId);
                $this->getService('Section')->insert(array(
                    'name' => $form->getValue('name'),
                    'subject_id' => $subjectId,
                    'order' => ++$order,
                ));
            }
            $this->_flashMessenger->addMessage(_('Группа материалов успешно сохранена'));
            if ($this->_getParam('return', '') == 'lesson') {
                $this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $subjectId, 'switcher' => 'my'));
            } else {
                $this->_redirector->gotoSimple('index', 'materials', 'subject', array('subject_id' => $subjectId));
            }
        }
        $this->view->form = $form;
    }

    public function deleteSectionAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $sectionId = (int) $this->_getParam('section_id', 0);
        if ($sectionId) {
            $this->getService('Section')->deleteBy(array(
                'section_id = ?' => $sectionId,
            ));
        }
        $this->_flashMessenger->addMessage(_('Группа материалов успешно удалена'));
        if ($this->_getParam('return', '') == 'lesson') {
            $this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $subjectId, 'switcher' => 'my'));
        } else {
            $this->_redirector->gotoSimple('index', 'materials', 'subject', array('subject_id' => $subjectId));
        }
    }

    public function orderSectionAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $sectionId = $this->_getParam('section_id', array());
        $materials = $this->_getParam('material', array());
        echo $this->getService('Section')->setMaterialsOrder($sectionId, $materials) ? 1 : 0;
    }

}
