<?php

require_once APPLICATION_PATH .  '/modules/els/lesson/forms/Lesson.php';


class HM_Form_Poll extends HM_Form_Multi
{
    protected $_namespace = 'multiform';
    
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $form1 = new HM_Form_PollStep1();
        $this->addSubForm($form1, 'step1');
        $form2 = new HM_Form_PollStep2();
        if ($form2->getElements()) {
            $this->addSubForm($form2, 'step2');
        }

        $form3 = new HM_Form_PollStep3();
        if (!$form2->getElements()) {
            $form3->setDefault('prevSubForm', 'step1');
        }
        $this->addSubForm($form3, 'step3');    
        
        if($_SESSION[$this->_namespace]['step1']['event_id'] == HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER){
            $this->removeSubForm('step3');
            $form2->getElement('submit')->setLabel(_('Готово'));
        }
        parent::init();
        $this->prepare();
    }

    public function prepare()
    {
        $step1 = $this->getSubForm('step1');
        $step2 = $this->getSubForm('step2');
        $step3 = $this->getSubForm('step3');
        
        //Удаляем условия запуска
        $step1->removeElement('Condition');       
        $step1->removeDisplayGroup('ConditionLessonGroup');
        $step1->removeElement('cond_progress');
        $step1->removeElement('cond_avgbal');
        $step1->removeElement('cond_sumbal');
        $step1->removeElement('cond_sheid');
        $step1->removeElement('cond_mark');       
        
        $step1->removeElement('teacher'); 
        $step1->removeElement('vedomost'); 
        
        $step1->removeElement('beginDate'); 
        $step1->removeElement('currentDate'); 
        $step1->removeElement('beginTime'); 
        $step1->removeElement('endDate'); 
        $step1->removeElement('endTime'); 
        $step1->removeElement('beginDate'); 
        $step1->removeElement('beginDate');
        
        $step1->removeElement('allowTutors');
        $step1->removeElement('formula_penalty');
        
        $step1->removeElement('required');
        $step1->removeElement('max_ball');
        
        
        $step1->removeElement('GroupDate');
        $step1->removeDisplayGroup('DateLessonGroup');
        
        $step1->addElement('RadioGroup', 'GroupDate', array(
            'Label' => '',
        	'Value' => HM_Lesson_LessonModel::TIMETYPE_RELATIVE,
            //'Required' => true,
            'MultiOptions' => array(
                                    HM_Lesson_LessonModel::TIMETYPE_FREE      => _('Без ограничений'),
                                    HM_Lesson_LessonModel::TIMETYPE_RELATIVE  => _('Относительный диапазон')
                              ),
            'form' => $step1,
            'dependences' => array(HM_Lesson_LessonModel::TIMETYPE_FREE => array(),
                                   HM_Lesson_LessonModel::TIMETYPE_RELATIVE => array('beginRelative', 'endRelative')
                             )
        ));
        
        
        $step1->addElement('select', 'notice', array(
            'Label' => _('Когда отправлять автоматическое уведомление'),
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => HM_Lesson_Poll_PollModel::getNotices(),
            'onChange' => "if($('#notice').val() == ".HM_Lesson_Poll_PollModel::NOTICE_REPEAT.") $('#notice_days').removeAttr('disabled'); else $('#notice_days').attr('disabled', 'disabled')"
        ));

        $step1->addElement('text', 'notice_days', array(
            'Label' => 'N',
            'required' => false,
            'validators' => array('int'),
            'filters' => array('int'),
            'style' => 'width:40px',
            'disabled' => true
        ));
        
        $step1->addDisplayGroup(
            array('GroupDate',
            	'beginDate',
                'currentDate',
                'beginTime',
                'endDate',
                'endTime',
                'recommend',
                'beginRelative',
                'endRelative',
                'notice',
                'notice_days'
            ),
            'DateLessonGroup',
            array('legend' => _('Ограничение времени запуска'))
        );

        
        $step1->removeElement('cancelUrl');
        $step1->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'feedback', 'controller' => 'poll', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
        ));
       
        $step1->getElement('submit')->setOrder(500);
               
        $step1->getElement('event_id')->setLabel(_('Тип опроса'));
        $step1->getElement('event_id')->setOptions(array('multiOptions' => HM_Event_EventModel::getExcludedTypes()));

        $step2->getDisplayGroup('LessonGroup')->setLegend(_('Инструмент'));
        if($_SESSION[$this->_namespace]['step1']['event_id'] == HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER){
            $step2->addElement('hidden', 'all', array(
                'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Value' => 1
            ));
            
        }
        
        if ($step3) {
            $all = $step3->getElement('all');
            $students = $step3->getElement('students');
            if ($students) {

                $students->setOptions(
                    array(
                         'jQueryParams' => array(
                             'remoteUrl' => $this->getView()->url(array('module' => 'feedback', 'controller' => 'ajax', 'action' => 'students-list'))
                         )));
                $switch = $step3->getElement('switch');
                $switch->setOptions(
                    array(
                         'MultiOptions' => array(0 => _('Все прошедшие обучение'), 1 => _('Список прошедших обучение')),
                    )
                );
                $all->setOptions(
                                  array(
                                       'Label' => _('Автоматически назначать всем тем, кто пройдет обучение после назначения опроса'),
                                       'Required' => false,
                                       'Value' => 1,
                                  ));

                if($_SESSION[$this->_namespace]['step1']['event_id'] == HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER){
                    $step3->removeElement('subgroups');
                    $students->setOptions(
                        array(
                             'jQueryParams' => array(
                                 'remoteUrl' => $this->getView()->url(array('module' => 'feedback', 'controller' => 'ajax', 'action' => 'students-list-for-leader'))
                             )));
                }

            }
        }

    }
    

}