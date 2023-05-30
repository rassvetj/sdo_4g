<?php
class HM_Form_LessonStep1 extends HM_Form_SubForm
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('lessonStep1');

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($this->getParam('subject_id', 0)));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
        ));
        /**
         * Открыта ли страница по ссылке из списка, а не из грида
         * 0 - нет
         * y - да
         * <int> - из списка по пользователю, ID пользователя
         **/
        $this->addElement('hidden', 'fromList', array(
            'Required' => false,
            'Value'    => 0,
        ));

        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )

        ));
		
		// Text field for name translation		
        $this->addElement('text', 'title_translation', array(
            'Label' => _('Перевод (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )

        ));		

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        /*$collection = $this->getService('Subject')->fetchAll(null, 'name');
        $subjects = $collection->getList('subid', 'name');

        $this->addElement('select', 'subject_id', array(
            'Label' => _('Учебный курс'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, 0)
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $subjects
        ));

        */

        /*$collection = $this->getService('Event')->fetchAll(null, 'TypeName');
        $events = $collection->getList('TypeID', 'TypeName', _('Выберите инструмент обучения'));*/
        /*$subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($this->getParam('subject_id', 0))
        );*/

        $this->addElement('select', 'event_id', array(
            'Label' => _('Тип занятия'),
            'Required' => true,
            'Validators' => array(
                'Int'
                //array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'Filters' => array('Int'),
            'MultiOptions' => ($subject ? $subject->getEventTypes() : array(_('Нет'))),
            'OnChange' => "if (this.value == 999) {this.value = 1000; return false;} if (this.value == Number('".HM_Event_EventModel::TYPE_POLL."')) $('#vedomost').attr('disabled', true); else $('#vedomost').attr('disabled', false);"
        ));


        $this->addElement('DatePicker', 'beginDate', array(
            'Label' => _('Дата начала'),
//            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateLessThanFormValue', false, array('name' => 'endDate'))
             ),
            'id' => "beginDate",
            'Filters' => array('StripTags')
        ));

        $this->addElement('DatePicker', 'currentDate', array(
            'Label' => _('Дата'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 50, 1)
             ),
            'id' => "beginDate2",
            'Filters' => array('StripTags')
        ));

        $this->addElement('uiTimePicker', 'beginTime', array(
            'Label' => _('Время начала'),
//            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/')
             ),
            'Filters' => array(

            )
        ));

        $this->addElement('DatePicker', 'endDate', array(
            'Label' => _('Дата окончания'),
//            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateGreaterThanFormValue', false, array('name' => 'beginDate'))
             ),
            'id' => "endDate",
            'Filters' => array('StripTags')
        ));


        $this->addElement('uiTimePicker', 'endTime', array(
            'Label' => _('Время окончания'),
//            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/'),
                array('DateTimeGreaterThanFormValues', false, array('minDateName' => 'currentDate', 'minTimeName' => 'beginTime', 'dateName' => 'currentDate'))
             ),
            'Filters' => array(

            )
        ));



        /*$teachers = array(0 => _('Нет'));
		$moderators = array(0 => _('Нет'));
        $collection = $this->getService('Teacher')->fetchAllDependence(
            'User',
            $this->getService('Teacher')->quoteInto('CID = ?', $this->getParam('subject_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $teacher = $item->getUser();
                if ($teacher) {
                    $teachers[$teacher->MID] = $teacher->getName();
                    $moderators[$teacher->MID] = $teacher->getName();
                }
            }
        }

        
        $collection = $this->getService('Student')->fetchAllDependence(
            'User',
            $this->getService('Student')->quoteInto('CID = ?', $this->getParam('subject_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $moderator = $item->getUser();
                if ($moderator) {
                    $moderators[$moderator->MID] = $moderator->getName();
                }
            }
        }

        asort($moderators);
        $moderators_list = array(0 => _('Нет'));
        foreach ($moderators as $key=>$value) {
            $moderators_list[$key] = $value;
        }

        if($subject->isBase()){
            $tLabel = _('Автор');
        }else{
            $tLabel = _('Преподаватель');
        }

        $this->addElement('select', 'teacher', array(
            'Label' => $tLabel,
            'Required' => false,
            'Validators' => array(
                'Int',
                //array('GreaterThan', false, array(0))
            ),
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => $teachers
        ));

        $this->addElement('select', 'moderator', array(
            'Label' => _('Модератор'),
            'Required' => false,
            'Validators' => array(
                'Int',
                //array('GreaterThan', false, array(0))
            ),
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => $moderators_list
        ));*/

        $this->addElement('select', 'vedomost', array(
            'Label' => _('Занятие на оценку'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => array(
                0 => _('Нет'),
                1 => _('Да')
            )
        ));

        if ($subject->mark_type == HM_Mark_StrategyFactory::MARK_BRS) {
			
            $maxBallSum = $this->getService('Subject')->getMaxBallSum($subject->subid);
            
            $descr = _('Сумма баллов за обязательные занятия не должна превышать 100.00 баллов, ')
                    . _('осталось запланировать ') . (100.00-$maxBallSum) . _(' баллов');
            
            $this->addElement('text', 'max_ball', array(
                'Label' => _('Максимальный балл за занятие'),
                'Description' => $descr,
                'Required' => true,
                
            ));
            $this->removeElement('vedomost');
            $this->addElement('hidden', 'vedomost', array('Value'=> 1));
            
            $maxBall = $this->getElement('max_ball');
            $maxBall->addValidator('Float',false,array('locale' => 'en'));
            $maxBall->addValidator('LessThan',false,array('max' => 100.01));
            $maxBall->getValidator('LessThan')->setMessage("'%value%' больше 100", Zend_Validate_LessThan::NOT_LESS);
			/*
			$this->addElement('text', 'max_ball_academic', array(
                'Label' 		=> _('Максимальный балл за академическую активность'),
                'Description' 	=> $descr,
                'Required' 		=> false,                
            ));
			
			$this->addElement('text', 'max_ball_practic', array(
                'Label' 		=> _('Максимальный балл за за выполнение практического задания'),
                'Description' 	=> $descr,
                'Required' 		=> false,                
            ));
			*/
        }
		

        #if ($this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_TUTOR) {

        #    $this->addElement('checkbox', 'allowTutors', array(
        #        'Label' => _('Разрешено выставление оценок тьютором'),
        #        'Required' => false,
        #        //'Validators' => array('Int'),
        #        'Filters' => array('Int'),
        #        'Checked' => false
        #    ));

        #} else {
            $this->addElement('hidden', 'allowTutors', array(
                'Required' => false,
                'Value' => true
            ));
        #}
        
		if ($subject->mark_type == HM_Mark_StrategyFactory::MARK_BRS) {
            $this->addElement('checkbox', 'required', array(                
				'Label' => _('Обязательное'),
                'Required' => false,
                //'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Checked' => true,
            ));
        }
		
		$this->addElement('checkbox', 'isCanMarkAlways', array(                
			'Label' => _('Можно выставлять оценку без файла от студента'),
			'Required' => false,			
			'Filters' => array('Int'),
			'Checked' => false,
        ));
		
		$this->addElement('checkbox', 'isCanSetMark', array(                
			'Label' 	=> _('Можно выставлять оценку в занятии с типом "тест"'),
			'Required' 	=> false,			
			'Filters' 	=> array('Int'),
			'Checked' 	=> false,
        ));
		
		
        // проверка на зависимость.
        if ( $this->getParam('lesson_id', 0) && count($this->getService('Lesson')->fetchAll($this->getService('Lesson')->quoteInto('cond_sheid = ?',$this->getParam('lesson_id', 0))))) {
            $vedomostElement = $this->getElement('vedomost');
            $vedomostElement->setOptions(array('OnChange' => "if (this.value == 0) { alert('" . _('У данного занятия имеются зависимости.') . "'); this.value = 1;}"));
        }


/*
        $this->addElement('select', 'all', array(
            'Label' => _('Автоматически назначать всем новым пользователям'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => array(
                0 => _('Нет'),
                1 => _('Да')
            )
        ));
*/



        $groupDateArray = HM_Lesson_LessonModel::getDateTypes();
        // Если базовый то скрываем ненужные поля
        if($subject->isBase()){
            unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_DATES]);
            unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_TIMES]);

            $this->removeElement('beginDate');
            $this->removeElement('currentDate');
            $this->removeElement('beginTime');
            $this->removeElement('endDate');
            $this->removeElement('endTime');

            $this->addElement('hidden', 'all', array(
                'Required' => false,
                'Value' => true
            ));

        }

        $this->addElement('RadioGroup', 'GroupDate', array(
            'Label' => '',
        	'Value' => HM_Lesson_LessonModel::TIMETYPE_DATES,
            //'Required' => true,
            'MultiOptions' => $groupDateArray,
            'form' => $this,
            'dependences' => array(HM_Lesson_LessonModel::TIMETYPE_FREE => array(),
                HM_Lesson_LessonModel::TIMETYPE_DATES => array('beginDate', 'endDate'),
                HM_Lesson_LessonModel::TIMETYPE_TIMES => array('currentDate', 'beginTime', 'endTime'),
                HM_Lesson_LessonModel::TIMETYPE_RELATIVE => array('beginRelative', 'endRelative')
            )
        ));

        $this->addElement('text', 'beginRelative',
            array(
            	'Label' => _('День начала'),
            	'Description' => _('Эти дни отсчитываются от даты начала обучения конкретного слушателя по курсу. Если использовать отрицательные значения, дни будут отсчитываться от плановой даты окончания обучения слушателя.'),
                'Validators' => array(
                    'Int'
                ),
                'Value' => 1,
                'Filters' => array('Int')
            )
        );
        $this->addElement('text', 'endRelative',
            array(
            	'Label' => _('День окончания'),
                'Description' => _('Эти дни отсчитываются от даты начала обучения конкретного слушателя по курсу. Если использовать отрицательные значения, дни будут отсчитываться от плановой даты окончания обучения слушателя.'),
            	'Validators' => array(
                	'Int'
                ),
                'Value' => 1,
                'Filters' => array('Int')
            )
        );


        $this->addElement('RadioGroup', 'Condition', array(
            'Label' => '',
            'Value' => HM_Lesson_LessonModel::CONDITION_NONE,
            //'Required' => true,
            'MultiOptions' => HM_Lesson_LessonModel::getConditionTypes(),
            'form' => $this,
            'dependences' => array(HM_Lesson_LessonModel::CONDITION_NONE => array(),
                HM_Lesson_LessonModel::CONDITION_PROGRESS => array('cond_progress'),
                HM_Lesson_LessonModel::CONDITION_AVGBAL => array('cond_avgbal'),
                HM_Lesson_LessonModel::CONDITION_SUMBAL => array('cond_sumbal'),
                HM_Lesson_LessonModel::CONDITION_LESSON => array('cond_sheid', 'cond_mark')
            )
        ));

        $this->addElement('text', 'cond_progress',
            array(
//            	'Label' => _('Занятие доступно, если процент выполнения занятий >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $this->addElement('text', 'cond_avgbal',
            array(
//            	'Label' => _('Занятие доступно, если средний балл по курсу >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $this->addElement('text', 'cond_sumbal',
            array(
//            	'Label' => _('Занятие доступно, если суммарный балл по курсу >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $lessons = array(0 => _('Выберите занятие'));
        $collection = $this->getService('Lesson')->fetchAll($this->getService('Lesson')->quoteInto(
            array('CID = ?', ' AND typeID NOT IN (?)', ' AND SHEID <> ?', ' AND vedomost = ?', ' AND isfree = ?'),
            array($this->getParam('subject_id', 0), array(HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER, HM_Event_EventModel::TYPE_POLL), $this->getParam('lesson_id', 0), 1, HM_Lesson_LessonModel::MODE_PLAN)
        ), 'title');
        if (count($collection)) {
            $lessons = $collection->getList('SHEID', 'title', _('Выберите занятие'));
        }

        $this->addElement('select', 'cond_sheid', array(
            'Label' => _('занятие'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $lessons
        ));

        $this->addElement('text', 'cond_mark',
            array(
            	'Label'      => _('оценка'),
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Оценка должна быть больше нуля"))))
                ),
                'Filters'    => array('Int'),
                'Value'      => 1
            )
        );

        //echo $this->getElement('all')->render();

        $this->addElement('textarea',
                          'descript',
                          array(
                                'Label'      => _('Краткое описание'),
                                'Required'   => false,
                                'Validators' => array(),
                                'Filters'    => array('StripTags')
                          ));
						  
        $this->addElement('textarea',
                          'descript_translation',
                          array(
                                'Label'      => _('Краткое описание (перевод)'),
                                'Required'   => false,
                                'Validators' => array(),
                                'Filters'    => array('StripTags')
                          ));						  


        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND (cid = ?', 'OR cid = 0)'),
                array(HM_Formula_FormulaModel::TYPE_PENALTY, $subject->subid)
            ),
            'name'
        );

        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement('select', 'formula_penalty', array(
            'Label' => _('Штраф за несвоевременное выполнение задания'),
            'required' => false,
            /*'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),*/
            //'filters' => array('int'),
            'multiOptions' => $formulas,
            //'Value' => 0
        ));


        /*$this->addElement('RadioGroup', 'recommend', array(
            'Label' => _('123'),
            //'Description' => _('При установке флажка все фиксированные значения приобретают статус рекомендуемых'),
            'Required' => false,
            'Validators' => array('Int'),
            'MultiOptions' => array(
                0 => _('Строгое ограничение'),
                1 => _('Нестрогое ограничение')
            ),
            'form' =>$this,
            //'value' => 0,
            'dependences' => array( 0 => array(),
                1 => array('formula_penalty')
            )
        ));*/

        $tt = $this->addDisplayGroup(
            array('cancelUrl',
                  'lesson_id',
                  'title',
				  'title_translation',
                  'subject_id',
                  'event_id',
                  'max_ball',
				  'max_ball_academic',
				  'max_ball_practic',
                  'required',
				  'isCanMarkAlways',
				  'isCanSetMark',
                  //'teacher',
				  //'moderator',
                  'vedomost',
                  'allowTutors',
                  'icon',
                  'server_icon',
                'icon2',
                  'descript',
                  'descript_translation',
                  'section_id'
                  //'all'
            ),
            'CommonLessonGroup',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(
            array(
                'GroupDate',
            	'beginDate',
                'currentDate',
                'beginTime',
                'endDate',
                'endTime',
                'beginRelative',
                'endRelative',
                'formula_penalty'
            ),
            'DateLessonGroup',
            array('legend' => _('Ограничение времени запуска'))
        );

        $this->addDisplayGroup(
            array('Condition',
            	'cond_progress',
                'cond_avgbal',
                'cond_sumbal',
                'cond_sheid',
                'cond_mark'
            ),
            'ConditionLessonGroup',
            array('legend' => _('Условия запуска'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Далее')));


       /* $this->addDisplayGroup(
            array(
                'cancelUrl',
                'lesson_id',
                'title',
                'subject_id',
                'event_id',
                'teacher',
                'beginDate',
                'beginTime',
                'endDate',
                'endTime',
                'recommend',
                'vedomost',
                'all',
                'submit'
            ),
            'LessonGroup',
            array('legend' => _('Параметры занятия'))
        );*/


        parent::init(); // required!
    }

  /*  public function getElementDecorators($alias, $first = 'ViewHelper'){
    /*    if($alias == 'recommend'){
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );

        }else{
            return parent::getElementDecorators($alias, $first);
    //    }



    }*/


    public function isValid($data) {
        // дополнительная валидация относительных дат: значения однознаковые, окончание всегда больше начала
        if ( $data['beginRelative'] && $data['endRelative'] ) {
            $element = $this->getElement('endRelative');
            $element->addValidator('GreaterOrEqualThanValue',false,array('name' => 'beginRelative'));

            // если хотя бы одно число отрицательное, то оба значения д.б. < 0
            if ( min(intval($data['beginRelative']),intval($data['endRelative'])) < 0) {
                $element->addValidator('LessThan',false,array('max' => 0));
                $this->getElement('beginRelative')
                     ->addValidator('LessThan',false,array('max' => 0));
            }
        }
        return parent::isValid($data);
    }

}
