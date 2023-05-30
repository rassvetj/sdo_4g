<?php
class StudyGroups_ListController extends HM_Controller_Action_Crud //HM_Controller_Action_Crud
{
    protected $service     = 'StudyGroup';
    protected $idParamName = 'group_id';
    protected $idFieldName = 'group_id';
    protected $id          = 0;
    protected $_form;

    protected $programmCache = array();
    
    public function init()
    {
        parent::init();
        $this->_setForm(new HM_Form_StudyGroup());

        if (!$this->isAjaxRequest()) {
            $this->id = $this->_request->getParam('subject_id',0);
            $subject = $this->getOne($this->getService('Subject')->find( $this->id));
            if ($subject) {
                $this->view->setExtended(
                    array(
                        'subjectName'        => 'Subject',
                        'subjectId'          => $this->id,
                        'subjectIdParamName' => 'subject_id',
                        'subjectIdFieldName' => 'subid',
                        'subject'            => $subject
                    )
                );
            }
        }
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Учебная группа успешно создана'),
            self::ACTION_UPDATE => _('Учебная группа успешно обновлёна'),
            self::ACTION_DELETE => _('Учебная группа успешно удалёна'),
            self::ACTION_DELETE_BY => _('Учебные группы успешно удалены')
        );
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;
    }

    public function indexAction()
    {
        
		$isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		
		$sorting = $this->_request->getParam("ordergrid");
        if ($sorting == '') {
            $this->_request->setParam("ordergrid", 'name_ASC');
        }

        $select = $this->getService('StudyGroup')->getSelect();
              
        $select->from(
            array('study_groups'),
            array(
                'group_id',
                'name',
				'id_external',
                'faculty',
                'year',
                'education_type',
                'speciality',
                'course',
                'duration',
                'foundation_year',				
            )
        )->joinLeft(
                array('study_groups_users'),
                'study_groups.group_id = study_groups_users.group_id',
                array('students' => 'COUNT(study_groups_users.user_id)')
        )->joinLeft(
                array('study_groups_programms'),
                'study_groups.group_id = study_groups_programms.group_id',
                array('programms' => 'GROUP_CONCAT(study_groups_programms.programm_id)')
        )->group(array(
            'study_groups.group_id',
            'study_groups.name',
			'study_groups.id_external',
            'study_groups.faculty',
            'study_groups.year',
            'study_groups.education_type',
            'study_groups.speciality',
            'study_groups.course',
            'study_groups.duration',
            'study_groups.foundation_year',            
        ));
		
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}

        $grid = $this->getGrid(
            $select,
            array(
                'group_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url(array(
                        'module' => 'study-groups',
                        'controller' => 'users',
                        'action' => 'index',
                        'gridmod' => null,
                        'group_id'  => ''
                    ), null, true) . '{{group_id}}'.'">'. '{{name}}</a>'
                ),
                'id_external' => array(
                    'title' => _('ID группы из 1С')
                ),
				'students' => array(
                    'title' => _('Количество слушателей')
                ),
                'faculty' => array(
                    'title' => _('Факультет')
                ),
                'year' => array(
                    'title' => _('Учебный год')
                ),
                'education_type' => array(
                    'title' => _('Вид образования')
                ),
                'speciality' => array(
                    'title' => _('Специальность')
                ),
                'course' => array(
                    'title' => _('Текущий курс')
                ),
                'duration' => array(
                    'title' => _('Срок обучения')
                ),
                'foundation_year' => array(
                    'title' => _('Год основания группы')
                ),
                'programms' => array(
                    'title' => _('Назначена на программы'),
		            'callback' => array(
		                'function'=> array($this, 'programmsCache'),
		                'params' => array('{{programms}}', $select)
		            )
                )
            ),
            array(
                'group_id' => null,
                'name' => null,
                'id_external' => null,
                'students' => null,
                'faculty' => null,
                'year' => null,
                'education_type' => null,
                'speciality' => null,
                'course' => null,
                'duration' => null,
                'foundation_year' => null,
                'programms' => array('callback' => array(
                    'function' => array($this, 'filterProgramms'),
//                    'params'   => array('fieldName' => 't2.CID')
                ))
                //'type' => array('values' => HM_StudyGroup_StudyGroupModel::getTypes())
            )
        );

        $grid->addAction(
            array('module' => 'study-groups', 'controller' => 'list', 'action' => 'edit'),
            array('group_id'),
            $this->view->icon('edit')
        );
        $grid->addAction(
            array('module' => 'study-groups', 'controller' => 'list', 'action' => 'delete'),
            array('group_id'),
            $this->view->icon('delete')
        );

		if(!$isSetEmptyQuery){
			$programms = $this->getService('Programm')->fetchAll(array('programm_type != ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT)); // асесьмент назначается через профили должностей
			$programms = $programms->getList('programm_id', 'name');
		}
		
        $grid->addSubMassActionSelect(array(
                $this->view->url(
                    array('action' => 'assign-programm', 'parent' => $orgId)
                )
            ),
            'programm_id',
            $programms
        );

        /* #14760
        $grid->addMassAction(array(
                'controller' => 'courses',
                'module' => 'study-groups' ,
                'action' => 'assign-course',
                'parent' => $orgId
            ),
            _('Назначить группу на учебные курсы'),
            _('Вы уверены?')
        );



        $userId = $this->getService('User')->getCurrentUserId();
        //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
        $full_collection = $this->getService('Dean')->getSubjectsResponsibilities($userId);
        if (count($full_collection)) {
            $all_courses = $full_collection->getList('subid', 'name', _('Выберите курс'));
        }
        $grid->addSubMassActionSelect(array(
                $this->view->url(
                    array('controller' => 'courses',
                          'module' => 'study-groups',
                          'action' => 'assign-course',
                          'parent' => $orgId)
                )
            ),
            'courseId[]',
            $all_courses
        );

        $grid->addMassAction(
            array(
                'controller' => 'courses',
                'module' => 'study-groups' ,
                'action' => 'unassign-course',
                'parent' => $orgId
            ),
            _('Отменить назначение группы на учебные курсы'),
            _('Вы уверены?')
        );

        $grid->addSubMassActionSelect(
            $this->view->url(
                array(
                    'controller' => 'courses',
                    'module' => 'study-groups' ,
                    'action' => 'unassign-course',
                    'parent' => $orgId
                )
            ),
            'courseId[]',
            $all_courses
        );*/

        // Учебные программы
			if(!$isSetEmptyQuery){
				$programms = $this->getService('Programm')->fetchAll(null, 'name');
			}
            if (count($programms)) {
                $grid->addMassAction(
                    array(
                        'module' => 'study-groups',
                        'controller' => 'programms',
                        'action' => 'assign-programm',
                    ),
                    _('Hазначить группу на учебные программы'),
                    _('Вы уверены?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'study-groups',
                            'controller' => 'programms',
                            'action' => 'assign-programm',
                        )
                    ),
                    'programmId[]',
                    $programms->getList('programm_id', 'name')
                );

                $grid->addMassAction(
                    array(
                        'module' => 'study-groups',
                        'controller' => 'programms',
                        'action' => 'unassign-programm',
                    ),
                    _('Отменить назначение группы на учебные программы'),
                    _('Вы уверены?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'study-groups',
                            'controller' => 'programms',
                            'action' => 'unassign-programm',
                        )
                    ),
                    'programmId[]',
                    $programms->getList('programm_id', 'name')
                );
            }


        $grid->addMassAction(array(
                'module' => 'study-groups',
                'controller' => 'list',
                'action' => 'delete-by'
            ),
            _('Удалить'),
            _('Вы уверены?')
        );

//        $grid->updateColumn('type',
//            array(
//                'callback' =>
//                array(
//                    'function' => array($this, 'updateType'),
//                    'params' => array('{{type}}')
//                )
//            )
//        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function subjectAction()
    {
        // Получаем ид курса
        $subjectId = $this->_request->getParam('subject_id',0);
        if ($subjectId < 1) {
            $this->_redirectToIndex();
        }


        $this->view->subjectId = $subjectId;
        $select = $this->getService('StudyGroup')->getSelect();
        $select->from(
            array('sg' => 'study_groups'),
            array('sg.*'))
            ->joinLeft(
                array('sgc' => 'study_groups_courses'),
                'sg.group_id = sgc.group_id',
                array('')
            )
            ->joinLeft(
                array('study_groups_users'),
                'sg.group_id = study_groups_users.group_id',
                array('students' => 'COUNT(study_groups_users.user_id)')
            )
            ->where('sgc.course_id = ?', $subjectId)
            ->group(array('sg.group_id', 'sg.name', 'sg.type'));;

        // hack
        $grid = $this->getGrid(
            $select,
            array(
                'group_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url(array(
                        'module' => 'study-groups',
                        'controller' => 'users',
                        'action' => 'index',
                        'gridmod' => null,
                        'subject_id'  => $subjectId,
                        'group_id'  => ''
                    ), null, true) . '{{group_id}}'.'">'. '{{name}}</a>'
                ),
                'type' => array(
                    'title' => _('Тип'),
                    'hidden' => true
                ),
                'students' => array('title' => _('Количество слушателей'))
            ),
            array(
                'group_id' => null,
                'name' => null,
                'type' => array(
                    'values' => HM_StudyGroup_StudyGroupModel::getTypes()
                ),
                'students' => null
            )
        );

//        $grid->addAction(
//            array('module' => 'study-groups', 'controller' => 'list', 'action' => 'edit'),
//            array('group_id'),
//            $this->view->icon('edit')
//        );
//        $grid->addAction(
//            array('module' => 'study-groups', 'controller' => 'list', 'action' => 'delete'),
//            array('group_id'),
//            $this->view->icon('delete')
//        );
//
//        $grid->addMassAction(array(
//                'module' => 'study-groups',
//                'controller' => 'list',
//                'action' => 'delete-by'
//            ),
//            _('Удалить'),
//            _('Вы уверены?')
//        );

        $grid->updateColumn('type',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateType'),
                    'params' => array('{{type}}')
                )
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateType($type)
    {
        $types = HM_StudyGroup_StudyGroupModel::getTypes();
        return $types[$type];
    }

    public function create(Zend_Form $form)
    {
        $item = $this->getService('StudyGroup')->insert(array(
            'name' => $form->getValue('name'),
            'type' => HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM //$form->getValue('type') - закоментированны автоматические группы
        ));
        if($item->type == HM_StudyGroup_StudyGroupModel::TYPE_AUTO) {
            $this->_saveAutoParams($item, $form);
        }
    }

    private function _saveAutoParams($item, $form)
    {
        $this->getService('StudyGroupAuto')->deleteBy(array(
            'group_id = ?' => $item->group_id
        ));
        $departments = $form->getValue('departments');
        $positions = $form->getValue('positions');
        if(is_array($departments) && is_array($positions)) {
            foreach($departments as $departmentId) {
                foreach($positions as $positionCode) {
                    $this->getService('StudyGroupAuto')->insert(array(
                        'group_id' => $item->group_id,
                        'position_code' => $positionCode,
                        'department_id' => $departmentId
                    ));
                }
            }
        }
    }

    public function update(Zend_Form $form)
    {
        $item = $this->getService('StudyGroup')->update(array(
            'group_id' => $this->_request->getParam('group_id'),
            'name' => $form->getValue('name'),
            'type' => $form->getValue('type')
        ));
        if($item->type == HM_StudyGroup_StudyGroupModel::TYPE_AUTO) {
            $this->_saveAutoParams($item, $form);
        }
    }

    public function setDefaults(Zend_Form $form)
    {
        $groupId = (int) $this->_getParam('group_id', 0);
        $group = $this->getOne($this->getService('StudyGroup')->find($groupId));
        if ($group) {
            $values = $group->getValues();
            $values['departments'] = $this->getService('StudyGroup')->getDepartments($groupId);
            $values['positions'] = $this->getService('StudyGroup')->getPositions($groupId);
            $form->setDefaults(
                $values
            );
        }
    }

    public function delete($id)
    {
        $this->getService('StudyGroup')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam($this->idParamName, 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }


    protected function _redirectToIndex()
    {
        $this->_redirector->gotoRoute(array(
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => 'index'
        ), null, true);
    }
    
    // duplicated from HM_Controller_Action_Assign
    public function programmsCache($field, $select){
    
    	if($this->programmCache === array()){
    		$smtp = $select->query();
    		$res = $smtp->fetchAll();
    		$tmp = array();
    		foreach($res as $val){
    			$tmp[] = $val['programms'];
    		}
    		$tmp = implode(',', $tmp);
    		$tmp = explode(',', $tmp);
    		$tmp = array_unique($tmp);
    		$this->programmCache = $this->getService('Programm')->fetchAll(array('programm_id IN (?)' => $tmp), 'name');
    	}
    
    	$fields = array_filter(array_unique(explode(',', $field)));
    
    	if (count($fields)) {
    		$programms = $this->getService('Programm')->fetchAll(array('programm_id IN (?)' => $fields), 'name');
    		$fields = array();
    		if (count($programms)) {
    			foreach ($programms as $programm) {
    				$fields[] = $programm->programm_id;
    			}
    		}
    	}
    
    
    	$result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . $this->getService('Programm')->pluralFormCount(count($fields)) . '</p>') : array();
    	foreach($fields as $value){
    		$tempModel = $this->programmCache->exists('programm_id', $value);
    		$result[] = sprintf('<p><a href="%s">%s</a></p>', $this->view->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'index', 'programm_id' => $tempModel->programm_id)), $tempModel->name);
    	}
    
    	if($result)
    		return implode('',$result);
    	else
    		return _('Нет');
    }
    
    
    public function filterProgramms($data)
    {
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }

        $select = $data['select'];
        
        $select->joinInner(array('p_filter' => 'programm'),
            'p_filter.programm_id = study_groups_programms.programm_id',
            array()
        );
        $select->where($this->quoteInto(
            "p_filter.name LIKE ?",
            '%'.$data['value'].'%'
        ));
    }
    
}