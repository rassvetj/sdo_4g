<?php
class HM_Form_Subjects extends HM_Form {

    public function init() {
        $model = new HM_Subject_SubjectModel(null);

        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('subjects');

        if (!($subjectId = $this->getParam('subject_id', 0))) {
            $subjectId = $this->getParam('subid', 0);
        }

        /*$front      = Zend_Controller_Front::getInstance();
        $request    = $front->getRequest();
        $action     = $request->getActionName();

        if ( $action == 'edit' ) {
            $this->setAttrib('onSubmit', 'if (confirm("'._('При изменении времени обучения автоматически меняются все даты занятий, которые выходят за дату окончания курса. Продолжить?').'")) return true; return false;');
        }*/

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module'     => 'subject',
                        'controller' => 'list',
                        'action'     => 'index',
                        'base'       => $this->getParam('base', 0)
                    ),
                    NULL,
                    TRUE)
            )
        );

        $this->addElement('hidden',
            'subid',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'base',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $this->getParam('base', 0)
            )
        );

        $this->addElement('hidden',
            'base_id',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $subjectId
            )
        );

		#if($this->getParam('base', 0) == 2){
			$this->addElement('text', 'external_id', array(
				'Label' => _('id из 1С'),
				'Required' => false,
				'Validators' => array(
					array('StringLength',
						false,
						array('min' => 1, 'max' => 255)
					)
				),
				'Filters' => array('StripTags')
			)
			);
		#}


// #4379 - в master'е не должно быть упоминаний SAP
//        $this->addElement('text', 'external_id', array(
//            'Label' => _('ID курса в SAP'),
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength',
//                    45,
//                    1
//                )
//            ),
//            'Filters' => array('StripTags')
//        )
//        );

        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );
		
		// Text title translation field 
        $this->addElement('text', 'name_translation', array(
            'Label' => _('Перевод (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 0, 'max' => 255)
                )
            ), 
            'Filters' => array('StripTags'),
            'class' => 'wide' 
        )
        );		
		

        $this->addElement('text', 'shortname', array(
            'Label' => _('Краткое название'),
            // #16815
			'Description' => _('Краткое название необходимо для "хлебных крошек" и планов занятий'),
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 24)
                )
            ),
            'Filters' => array('StripTags'),
        )
        );
		
        $this->addElement('text', 'shortname_translation', array(
            'Label' => _('Краткое название (перевод)'),
			'Description' => _('Краткое название необходимо для "хлебных крошек" и планов занятий'),
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 0, 'max' => 24)
                )
            ),
            'Filters' => array('StripTags'),
        )
        );		
		

        $this->addElement('text', 'code', array(
            'Label' => _('Код'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags')
        )
        );
        
        $this->addElement('text', 'year_of_publishing', array(
            'Label' => _('Год издания'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags')
        ));
        
        $this->addElement('text', 'hours_total', array(
            'Label' => _('Часы'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
        
        $this->addElement('select', 'zet', array(
            'Label' => _('ЗЕТ'),
            'Required' => false,
            'multiOptions' => HM_Subject_SubjectModel::getZetValues(),
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
        
        $this->addElement('select', 'exam_type', array(
            'Label' => _('Форма контроля'),
            'Required' => false,
            'multiOptions' => HM_Subject_SubjectModel::getExamTypes(),
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
		
		
		$this->addElement('select', 'semester', array(
            'Label'            => _('Семестр'),
            'Required'         => false,
            'multiOptions'     => HM_Subject_SubjectModel::getSemesterList(),
            'Validators'     => array('Int'),
            'Filters'         => array('Int')
        ));

        

        $collection = $this->getService('Supplier')->fetchAll(null, 'title');
        $providers = ($collection) ? $collection->getList('supplier_id', 'title') : array();
        $providers[0] = _('не указан');
        $this->addElement('select', 'supplier_id', array(
            'Label' => _('Провайдер'),
            'Required' => false,
            'multiOptions' => $providers,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        )
        );

        $this->addElement('radio', 'type', array(
            'Label' => _('Тип'),
            'Required' => false,
            'multiOptions' => $model->getTypes(),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => HM_Subject_SubjectModel::TYPE_DISTANCE
        )
        );

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitizeRich'),
        ));
		
        $this->addElement($this->getDefaultWysiwygElementName(), 'description_translation', array(
            'Label' => _('Описание (перевод)'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitizeRich'),
        ));		


        $regTypes = HM_Subject_SubjectModel::getRegTypes();
        $this->addElement('select', 'reg_type',
            array(
                'Label'        => _('Тип регистрации'),
                'Required'     => false,
                'multiOptions' => $regTypes,
				'Value'        => HM_Subject_SubjectModel::REGTYPE_SAP, //--только назначение
                'Validators'   => array('Int'),
                'Filters'      => array('Int')
            )
        );



/*        if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_BASE, HM_Subject_SubjectModel::BASETYPE_PRACTICE))) {
            $regTypes = HM_Subject_SubjectModel::getRegTypes();

           if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_BASE))){
                unset($regTypes[HM_Subject_SubjectModel::REGTYPE_FREE]);
            }

            $this->addElement('select', 'reg_type',
                array(
                    'Label'        => _('Тип регистрации'),
                    'Required'     => false,
                    'multiOptions' => $regTypes,
                    'Validators'   => array('Int'),
                    'Filters'      => array('Int')
                )
            );

        }else{
            $this->addElement('hidden',
                'reg_type',
                array(
                    'Required' => true,
                    'Validators' => array('Int'),
                    'Filters' => array('Int'),
                    'Value' => HM_Subject_SubjectModel::REGTYPE_SAP
                )
            );
        }*/



        /*if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
            $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getSessionProcessIds()));
        }else{
            $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getTrainingProcessIds()));
        }

        $processList = $processes->getList('process_id', 'name');

        if($isBase != HM_Subject_SubjectModel::BASETYPE_BASE){
            $processList = array(0 => _('Без согласования')) + $processList;
        }*/

        $this->addElement('select', 'claimant_process_id',
            array(
                'Label'        => _('Тип согласования'),
                'Required'     => false,
                //'multiOptions' => $processList,
                'Validators'   => array('Int'),
                'Filters'      => array('Int')
            )
        );


        //if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
        //Для сессий
        $this->addElement('RadioGroup', 'period', array(
            'Label' => '',
            'Value' => HM_Subject_SubjectModel::PERIOD_FREE,
            //'Required' => true,
            'MultiOptions' => HM_Subject_SubjectModel::getPeriodTypes(),
            'form' => $this,
            'dependences' => array(
                                 HM_Subject_SubjectModel::PERIOD_FREE => array(),
                                 HM_Subject_SubjectModel::PERIOD_DATES => array('begin', 'end', 'period_restriction_type'),
                                 HM_Subject_SubjectModel::PERIOD_FIXED => array('longtime'),
                             )
        ));

        $this->addElement('DatePicker', 'begin', array(
            'Label' => _('Дата начала'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );

        $this->addElement('DatePicker', 'end', array(
            'Label' => _('Дата окончания'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
                array(
                    'DateGreaterThanFormValue',
                    false,
                    array('name' => 'begin')
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );

        $this->addElement('text', 'longtime', array(
            'Label' => _('Количество дней'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int')
            )
        );

        $this->addElement('select', 'period_restriction_type', array(
                'Label' => _('Тип ограничения'),
                'Required' => false,
                'Description' => nl2br(_('При строгом ограничении времени курса слушатели и прошедшие обучение могут входить в учебный курс строго в указанный диапазон дат. В остальное время доступ пользователей блокируется. В момент наступления даты окончания курса все слушатели автоматически переводятся в прошедшие обучение.
При нестрогом ограничении даты начала и окончания курса носят рекомендательный характер. Слушатели могут входить в курс до даты начала и после даты окончания курса. По истечении времени курса слушатели не переводятся автоматически в прошедшие обучение.
В случае подтверждения преподавателем даты начала и окончания курса также носят рекомендательный характер. Факт начала обучения по курсу, включая рассылку уведомлений и предоставление доступа слушателям, подтверждается преподавателем вручную. Факт окончания курса, означающий фиксацию итоговых оценок и перевод всех слушателей в прошедшие обучение, также подтверждается преподавателем.')),
                'multiOptions' => HM_Subject_SubjectModel::getPeriodRestrictionTypes(),
            )
        );

// более не используется; добавлен филдсет "Результаты обучения"
//         $this->addElement('checkbox', 'auto_done', array(
//                 'Label' => _('Автоматически переводить в прошедшие обучение'),
//                 'Required' => false,
//                 'Description' => _('Если флажок установлен, слушатели данного курса будут автоматически переводиться в прошедшие обучение по факту изучения ими всех материалов курса (применимо только для тех курсов, у которых все материалы находятся в свободном доступе).'),
//             )
//         );

        //}else{

        //Для базовых
/*      $this->addElement('hidden',
            'period',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Value' => HM_Subject_SubjectModel::PERIOD_FREE
            )
        );*/


        //}

        $this->addElement('text', 'price', array(
            'Label' => _('Стоимость'),
            'Required' => false
            )
        );

        $this->addElement('select', 'price_currency', array(
            'Label' => _('Валюта'),
            'Required' => false,
            'multiOptions' => HM_Currency_CurrencyModel::getFullNameList(),
            'Validators' => array('Alpha'),
            'Filters' => array('Alpha'),
            'Value'   =>HM_Currency_CurrencyModel::getDefaultCurrency()
        )
        );

        $this->addElement('text', 'plan_users', array(
            'Label' => _('Планируемое количество слушателей'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        )
        );

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Загрузить иконку из файла'),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => null,
        )
        );

        if ($subjectId != 0) {
            /** @var HM_Subject_SubjectModel $subj */
            $subj = $this->getService('Subject')->getById($subjectId);
            $icon = $subj->getUserIcon();
        }
        $this->addElement('serverFile', 'server_icon', array(
                'Label' => _('Выбрать иконку из файлов на сервере'),
                'Value' => $icon,
                'preview' => $icon,
            )
        );

        $price = $this->getElement('price');
        $price -> addValidator('Float',false,array('locale' => 'en'));

        $collection = $this->getService('Room')->fetchAll(null, 'name');
        $rooms = array('');
        if ($collection) {
            foreach ($collection->getList('rid', 'name') as $rid => $name) {
            	$rooms[$rid] = $name; // preserve keys
            }
        }

//        $this->addElement('UiMultiSelect', 'rooms',
        $this->addElement('select', 'rooms',
            array(
                'Label' => _('Место проведения'),
                'Required' => false,
                'Filters' => array(
                    'Int'
                ),
                'multiOptions' => $rooms,
//                'class' => 'multiselect',
            )
        );

   /*
    * #7633
    *
        $check   = '';
        $uncheck = '';

        foreach (array_keys(HM_Subject_SubjectModel::getFreeAccessElements()) as $freeElement) {
            $check   .= "$('#access_elements-$freeElement').attr('checked',true);";
            $uncheck .= "$('#access_elements-$freeElement').attr('checked',false);";
        }
    */


// в 4.2 вообще откажемся от режимов
//        $this->addElement('RadioGroup', 'access_mode', array(
//            'Label' => '',
//        	'Value' => HM_Subject_SubjectModel::MODE_FREE,
//            //'Required' => true,
//            'MultiOptions' => HM_Subject_SubjectModel::getModes(),
//            'form' => $this,
//            'dependences' => array(
//                                 HM_Subject_SubjectModel::MODE_REGULATED => array(),
//                                 HM_Subject_SubjectModel::MODE_FREE      => array('access_elements', 'mode_free_limit'),
//                             ),
//            'Description' => _('В курсе со свободным режимом прохождения слушатели имеют неограниченный доступ к материалам, используемым на данном курсе (можно отдельно управлять доступом к учебным модулям, информационным ресурсам и тестам).') . '<br>' .
//            _('В курсе с использованием плана занятий слушатели получают доступ к материалам только через план занятий, предварительно созданный преподавателем.'),
//
////#7633            'onClick' => "if(this.value == 1) {	$check } else {	$uncheck }"
//// todo #6360
////            'Description' => array(
////                                 HM_Subject_SubjectModel::MODE_REGULATED => '',
////                                 HM_Subject_SubjectModel::MODE_FREE      => ''
////                             ),
//        ));



        /*
         * #7633
         *
          $temp = $this->addElement('multiCheckbox',
                          'access_elements',
                          array(
                            'separator' => '<br/><br/>',
				            'Required' => false,
				            'Label' => '',
				            'MultiOptions' => HM_Subject_SubjectModel::getFreeAccessElements(),
                            'Value' => array_keys(HM_Subject_SubjectModel::getFreeAccessElements())
                          )
            );
        */

        $factory = $this->getService('MarkStrategyFactory');
        $markTypes = $factory->getValues();
        $subjectResultsGroupArray = array('mark_type');
        $markTypeDependences = array();
        foreach ($markTypes as $key => $type) {
            $markTypeDependences[$key] = $factory->getStrategy($factory->getType($key))->addTypeElements($this);
            $subjectResultsGroupArray = array_merge($subjectResultsGroupArray, $markTypeDependences[$key]);
        }

        $this->addElement('RadioGroup', 'mark_type', array(
            'Label' => '',
            'Value' =>HM_Mark_StrategyFactory::MARK_BRS,
            //'Value' =>HM_Mark_StrategyFactory::MARK_WEIGHT,
			
            //'Required' => true,
            'MultiOptions' => $markTypes,
            'form' => $this,
            'dependences' => $markTypeDependences
        ));

        /*$this->addElement('select', 'scale_id', array(
            'Label' => _('Шкала оценивания'),
            'multiOptions' => $this->getService('Scale')->fetchAll(array('scale_id IN (?)' => HM_Scale_ScaleModel::getBuiltInTypes()), 'scale_id')->getList('scale_id', 'name'),
            'Validators' => array('Int'),
            'Filters' => array('Int')
        )
        );



        $this->addElement('checkbox', 'auto_mark', array(
            'Label' => _('Автоматически выставлять итоговую оценку за курс'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement('text', 'threshold', array(
            'Label' => _('Порог прохождения'),
            'Description' => _('Пороговое значение (в процентах от максимально возможного результата за курс), при достижении которого итоговая оценка "Пройдено успешно" автоматически выставляется за курс.'),
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1)),
                array('LessThan', false, array(101))
            ),
            'filters' => array('int'),
            'disabled' => true,
            'class' => 'indent',
        ));

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND  cid = 0'),
                array(HM_Formula_FormulaModel::TYPE_SUBJECT)
            ),
            'name'
        );
        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement('select', 'formula_id', array(
            'Label' => _('Формула для выставления итоговой оценки'),
            'required' => false,
            'disabled' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => $formulas,
            'class' => 'indent',
        ));

        $this->addElement('checkbox', 'auto_graduate', array(
            'Label' => _('Автоматически переводить в прошедшие обучение'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));*/

		$external_id = '';
		if ($subjectId != 0) {            
            $subj = $this->getService('Subject')->getById($subjectId);            			
			if(!empty($subj->external_id)){
				$external_id = ' элемента: '. $subj->external_id;	
			}			
        }


		
        $this->addDisplayGroup(array(
            'cancelUrl',
            'subid',
// #4379 - в master'е не должно быть упоминаний SAP
//            'external_id',
            'external_id',
			'name',
			'name_translation',
            'shortname',
            'shortname_translation',
            'year_of_publishing',
            'hours_total',
            'semester',
            'zet',
            'exam_type',
            'code',
        	'supplier_id',
            //'type',
            'icon',
            'server_icon',
            'description',
            'description_translation',
            //'price',
        ),
            'subjectSubjects1',
            array('legend' => _('Общие свойства'.$external_id))
        );

// в 4.2 отказываемся от переключателя режимов; останется один хороший режим
//        $this->addDisplayGroup(
//            array(
//                'access_mode',
//            	'access_elements'
//            ),
//            'subjectModeGroup',
//            array('legend' => _('Режим прохождения курса'))
//        );

        $this->addDisplayGroup(array(
            /*'begin',
            'end',*/
            'type',
            'reg_type',
            'claimant_process_id',
            'rooms',
            'plan_users',
            'price',
            'price_currency'
        ),
            'subjectSubjects2',
            array('legend' => _('Организация обучения'))
        );

        //if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
        $this->addDisplayGroup(
            array(
                'period',
                'begin',
                'end',
                'longtime',
                'period_restriction_type',
                'auto_done',
            ),
            'subjectPeriodGroup',
            array('legend' => _('Ограничение времени обучения'))
        );

        $this->addDisplayGroup(
            $subjectResultsGroupArray,
            'subjectResultsGroup',
            array('legend' => _('Итоговая оценка за курс'))
        );
		
		
        //}


        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $subjectId);  // echo '<pre>'; exit(var_dump($classifierElements));
        $this->addClassifierDisplayGroup($classifierElements);

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}