<?php
class User_IndexController extends HM_Controller_Action_User {
	
	protected $_currentLang = 'rus';

    public function softAction()
    {
        $mid = $this->_getParam('user_id');
        $select = $this->getService('User')->getSelect();
        $select->from('sessions', array(
            'stop',
            'browser_name',
            'browser_version',
            'flash_version',
            'os',
            'screen',
            'cookie',
            'js',
            'java_version',
            'silverlight_version',
            'acrobat_reader_version',
            'msxml_version',
        ));
        $select->where($this->quoteInto('mid = ?', $mid));
        $select->order('stop DESC');
        $select->limit(1);
        $info = $select->query()->fetch();
        
        if ($info && !empty($info['browser_name'])) {
            $this->view->systemInfo = array(
                'browser' => array(
                    'name'  => $info['browser_name'],
                    'value' => $info['browser_version'],
                ),
                'flash' => array(
                    'value' => $info['flash_version']
                ),
                'os' => array(
                    'value' => $info['os']
                ),
                'screen' => array(
                    'value' => $info['screen']
                ),
                'cookie' => array(
                    'value' => $info['cookie']
                ),
                'js' => array(
                    'value' => $info['js']
                ),
                'java' => array(
                    'value' => $info['java_version']
                ),
                'silverlight' => array(
                    'value' => $info['silverlight_version']
                ),
                'acrobat_reader' => array(
                    'value' => $info['acrobat_reader_version']
                ),
                'msxml' => array(
                    'value' => $info['msxml_version']
                ),
            );
        } else {
            $this->view->systemInfo = false;
        }
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/checksw/style.css');
    }

    public function studyHistorySubjectAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        $userId = (int) $this->_getParam('user_id');
        $url = Zend_Registry::get('serviceContainer')->getService('Subject')->getDefaultUri($subjectId);
        if ($userId == $this->getService('User')->getCurrentUserId()) {
        $this->_flashMessenger->addMessage(array(
            'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
            'message' => _('Прошедшие курсы доступны в ограниченном режиме. Невозможно запускать занятия на оценку, сервисы взаимодействия доступны только в режиме чтения.'),
        ));
        }
        $this->_redirector->gotoUrl($url);
    }

    public function studyHistoryAction()
    {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);		
		
        $userId = $this->_userId;
        if (!$this->_hasParam('ordergrid_history') && !$this->isAjaxRequest()) {
            $this->_setParam('ordergrid_history', 'end_ASC');
        }

        $select = $this->getService('Graduated')->getSelect();
        $select->from(
                    array('g' => 'graduated'),
                    array('g.SID', 's.name', 's.name_translation', 's.type', 'g.begin', 'g.end', 's.subid', 'progress' => 'g.progress', 'mark' => 'm.mark')
                )
                ->joinInner(array('s' => 'subjects'), 'g.CID = s.subid', array())
                ->joinLeft(array('m' => 'courses_marks'), 'g.CID = m.cid AND g.MID = m.mid', array())
                ->where('g.MID = ?', $userId);

        $grid = $this->getGrid(
            $select,
            array(
                'SID' => array('hidden' => true),
                'subid' => array('hidden' => true),
                'scale_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название учебного курса'),
                    'decorator' => $this->view->cardLink(
						$this->view->url(array(
												'module' => 'subject',
												'controller' => 'list',
												'action' => 'card',
												'subject_id' => ''
											)
										).'{{subid}}',
										_('Карточка учебного курса')).' <a href="'.$this->view->url(array('module' => 'user',
																											'controller' => 'index',
																											'action' => 'study-history-subject',
																											'subject_id' => ''
																										 ), null, true, false) . '{{subid}}">{{name}}</a>'
																										
                ),
                'type' => array(
                    'title' => _('Тип курса'),
                    'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{type}}'))
                ),
                'begin' => array('title' => _('Дата начала обучения')),
                'end' => array('title' => _('Дата окончания обучения')),
                //'progress' => array('title' => _('Прогресс, %')),
                'progress' => array('hidden' => true),
                'certificate_id' => array('title' => _('Номер сертификата')),
                'mark' => array(
                    'title' => _('Итоговая оценка'),
                    'callback' => array('function' => array($this, 'updateMark'), 'params' => array('{{mark}}', '{{scale_id}}'))
                )
            ),
            array(
                'sid' => null,
                'name' => null,
                'type' => array('values' => HM_Subject_SubjectModel::getTypes()),
                'begin' => array('render' => 'Date'),
                'end' => array('render' => 'Date'),
                'mark' => null
            ),
            'grid_history'
        );
		
        $grid->updateColumn('name',
			array('callback' =>
				array('function' => array($this, 'updateName'),
					  'params' => array('{{name}}', '{{name_translation}}')
				)
			)
		);		
		
        $grid->updateColumn('certificate_id', array('callback' => array('function' => array($this,'updateCertificateNumber'),
        																'params' => array('{{certificate_id}}'))));
        $grid->updateColumn('begin', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            )
        )
        );

        $grid->updateColumn('end', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            )
        )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }


    public function updateType($type)
    {
        $types = HM_Subject_SubjectModel::getTypes();
        return $types[$type];
    }

    public function pollsHistoryAction(){

        if (!$this->_getParam('ordergrid', '') && !$this->isAjaxRequest()) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

    	$userId = $this->_userId;
        $select = $this->getService('LessonAssign')->getSelect();
        $select
        		//->distinct()
                ->from(
                    array('sch' => 'schedule'),
                    array(
                        'name' => 'sj.name',
						'name_translation' => 'sj.name_translation',
   	                    'title' => 'sch.Title',
   	                    'sheid' => 's.SHEID',
   	                    'created' => 's.created',
   	                    'stop' => 'log.stop',
                        'status' => 'log.status',
                        'percent' => 'log.free',
                        'balmax' => 'log.balmax',
                        'balmin' => 'log.balmin',
                        'bal' => 'log.bal',
                        'balmax2' => 'log.balmax2',
                        'balmin2' => 'log.balmin2'
                    )
                )
                ->joinLeft(
                    array('log' => 'loguser'),
                    sprintf('log.sheid = sch.SHEID AND log.mid = %d', $userId),
                    array()
                )
        		->joinLeft(
                    array('s' => 'scheduleID'),
                    sprintf('sch.SHEID = s.SHEID AND s.MID = %d', $userId),
                    array()
                )
                ->join(
                	array('sj' => 'subjects'),
                	'sj.subid = sch.CID',
                	array()
                )
                ->where('sch.typeID IN (?)', array_keys(HM_Event_EventModel::getFeedbackPollTypes()))
                ->where($this->getService('LessonAssign')->quoteInto(array('(log.mid = ?', ' OR s.MID = ?)'), array($userId, $userId)))
                ->where('log.mid IS NOT NULL OR s.MID IS NOT NULL')
                //->order(array('sj.name', 'sch.Title'))
                ;

        $grid = $this->getGrid($select,
            array(
            	'name' => array('title' => _('Название курса')),
                'title' => array('title' => _('Название опроса')),
                // 'title_translation' => array('hidden' => true),
                'name_translation' => array('title' => 'Translation'),
                'created' => array('title' => _('Дата назначения опроса')),
                'stop' => array('title' => _('Дата заполнения опроса')),
                'status' => array('title' => _('Статус')),
            	'percent' => array('title' => _('Средний процент выполнения')),
	            'balmax' => array('hidden' => true),
	            'balmin' => array('hidden' => true),
	            'bal' => array('title' => _('Средний балл')),
	            'balmax2' => array('hidden' => true),
	            'balmin2' => array('hidden' => true),
                'sheid' => array('hidden' => true),
                'stid' => array('hidden' => true)

            ),
            array(
            	'name' => null,
				'name_translation' => null,
                'title' => null,
                // 'title_translation' => null,
                'created' => array('render' => 'Date'),
                'status' => array('values' => HM_Test_Result_ResultModel::getStatuses()),
                'stop' => array('render' => 'DateTimeStamp'),
                'percent' => null,
                'bal' => null
            )
        );

        $grid->updateColumn('name_translation',
			array('callback' =>
				array('function' => array($this, 'updateTranslation'),
					  'params' => array('{{name_translation}}')
				)
			)
		);
		
        $grid->updateColumn('status',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultModel, 'getStatus'),
	                    'params' => array('{{status}}')
	                ))
        );

        $grid->updateColumn('percent',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEveragePercent'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );

        $grid->updateColumn('bal',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEverageMark'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );

        $grid->updateColumn('created', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            ))
        );

        $grid->updateColumn('stop', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            ))
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }
	
	public function updateName($name, $translation='') {  
		
		if($this->_currentLang == 'eng' && $translation != '') {
			$name = $translation;		
			 exit($translation);
		}	
		
        return $name;
    }
	
	public function updateTranslation($translation) {  if($translation) exit($translation);
		
		/* if($this->_currentLang == 'eng' && $translation != '') {
			$name = $translation;		
			 
		}	*/
		
        return $translation;
    }	
}