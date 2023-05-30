<?php

class Assign_GraduatedController extends HM_Controller_Action
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;
	protected $_currentLang = 'rus';

    public function init()
    {
        parent::init();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if (!$this->isAjaxRequest()) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    )
                );
            }
        }
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'fio_ASC');
        }
        $this->_request->setParam('masterOrdergrid', 'notempty DESC');

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $select = $this->getService('Graduated')->getSelect();

        $select->from(
                    array('g' => 'graduated'),
                    array(
                        'g.SID',
                        'g.MID',
                        'g.CID',
                        'g.status',
                        'notempty' => "CASE WHEN (p.LastName IS NULL AND p.FirstName IS NULL AND  p.Patronymic IS NULL) OR (p.LastName = '' AND p.FirstName = '' AND p.Patronymic = '') THEN 0 ELSE 1 END",
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        's.name',
                        's.scale_id',
                        'g.begin',
                        'g.end',
                    	'g.certificate_id',
                        #'m.mark',
						'mark' => new Zend_Db_Expr('
							CASE WHEN (m.mark_current IS NOT NULL AND m.mark_landmark IS NOT NULL AND (convert(float, m.mark_current) + convert(float, m.mark_landmark)) > convert(float, m.mark)  ) 
							THEN	convert(float, m.mark_current) + convert(float, m.mark_landmark)
							ELSE m.mark END
							'),
                    )
                )
                ->joinInner(array('p' => 'People'), 'g.MID = p.MID', array())
                ->joinInner(array('s' => 'subjects'), 'g.CID = s.subid', array())
                ->joinLeft(array('m' => 'courses_marks'), '(m.cid = g.CID AND m.mid = g.MID)', array());

        // Область ответственности
        // Хитрая логика тут имеет место быть

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_DEAN)){
            $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
            if($options['unlimited_subjects'] != 1){
                $select->joinLeft(array('d2' => 'deans'), 'd2.subject_id = s.subid', array())
                       ->where('(d2.MID = ? OR d2.MID IS NULL)', $this->getService('User')->getCurrentUserId());
            }
            if($options['unlimited_classifiers'] != 1){

                $select->joinLeft(array('d' => 'structure_of_organ'),
                    'd.mid = p.MID',
                    array()
                );

                $select->joinLeft(
                    array('cl' => 'classifiers_links'),
                    '(cl.type = '.HM_Classifier_Link_LinkModel::TYPE_PEOPLE.' AND item_id = p.MID) OR (cl.type = '.HM_Classifier_Link_LinkModel::TYPE_STRUCTURE.' AND item_id = d.soid)',
                    array()
                );
            }
            $userId = $this->getService('User')->getCurrentUserId();
            if($options['unlimited_classifiers'] != 1 && $options['unlimited_subjects'] != 1){
                $responsibilities = $this->getService('DeanResponsibility')->getResponsibilities($userId);
                $area = $responsibilities->getList('classifier_id', 'classifier_id');
                $subjectResp = $this->getService('Dean')->getAssignedSubjectsResponsibilities($userId);
                $subj = $subjectResp->getList('subject_id', 'MID');
                //$select->where('(cl.classifier_id IN (?) OR d2.subject_id IN (?))', array($area, array_keys($subj)));

                $select->where(
                    $this->quoteInto(
                        array('(cl.classifier_id IN (?)', ' OR d2.subject_id IN (?))'),
                        array(count($area) ? $area : array(0), count($subj) ? array_keys($subj) : array(0))
                    )
                );

            }elseif($options['unlimited_classifiers'] == 1 && $options['unlimited_subjects'] != 1){
                $subjectResp = $this->getService('Dean')->getAssignedSubjectsResponsibilities($userId);
                $subj = $subjectResp->getList('subject_id', 'MID');
                $select->where('d2.subject_id IN (?)', array(array_keys($subj)));
            }elseif($options['unlimited_classifiers'] != 1 && $options['unlimited_subjects'] == 1){
                $responsibilities = $this->getService('DeanResponsibility')->getResponsibilities($userId);
                $area = $responsibilities->getList('classifier_id', 'classifier_id');
                $select->where('cl.classifier_id IN (?)', array($area));
            }
        }



        if ($subjectId) {
            $select->where('g.CID = ?', $subjectId);
			
			# если тьютор		             
			$subjectService = $this->getService('Subject');	
			$userService = $this->getService('User');		
			if ($this->getService('Acl')->inheritsRole($userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
				$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $subjectId);
				if($studentIDs){
					if(is_array($studentIDs)){
						if(count($studentIDs)){
							$select->where($subjectService->quoteInto('g.MID IN (?)', $studentIDs));
						} else {
							# нет доступных студентов.
							$select->where('1=0');
						}
					}
				}		
			}
        }

        $grid = $this->getGrid(
            $select,
            array(
                'SID'            => array('hidden' => true),
                'MID'            => array('hidden' => true),
                'CID'            => array('hidden' => true),
                'status'         => array('hidden' => true),
                'notempty'       => array('hidden' => true),
                'scale_id'       => array('hidden' => true),
                'fio'            => array('title' => _('ФИО'), 'decorator' => 
                    $this->view->cardLink(
                            $this->view->url(array(
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'view',
                                'user_id' => ''
                            ), null, true).'{{MID}}',_('Карточка пользователя')).
                            '<a href="'.$this->view->url(array(
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'view',
                                'user_id' => ''), null, true) . '{{MID}}'.'">'.'{{fio}}</a>'),
                'begin'          => array('title' => _('Дата начала обучения')),
                'end'            => array('title' => _('Дата окончания обучения')),
                'employer'       => array('title' => _('Место работы')),
                'name'           => ($subjectId ? array('hidden' => true) : array('title' => _('Курс'))),
                'certificate_id' => array('title' => _('Номер сертификата')),
                'mark'           => array('title' => _('Итоговая оценка')),
                #'mark_sum'       => array('title' => _('Итоговая оценка'))
            ),
            array(
                'fio'      => null,
                'begin'    => array('render' => 'date'),
                'end'      => array('render' => 'date'),
                'employer' => null,
                'name'     => null/*,
                'mark'     => null*/
            )
        );


        $grid->updateColumn('begin', array('format' => array('date',
                                                             array('date_format' => HM_Locale_Format::getDateFormat()))));

        $grid->updateColumn('end', array('format' => array('date',
                                                           array('date_format' => HM_Locale_Format::getDateFormat()))));

        $grid->updateColumn('certificate_id', array('callback' => array('function' => array($this,'updateCertificateNumber'),
        																'params' => array('{{certificate_id}}'))));
        $grid->updateColumn('mark', array('callback' => array('function' => array($this,'updateMark'),
        													  'params'   => array('{{mark}}','{{scale_id}}'))));
        
        $grid->updateColumn('fio', array(
								'callback' => array(
									'function' => array($this, 'updateFio'),
									'params' => array('{{fio}}', '{{MID}}'))
								)
							);

        /* if (!$subjectId) {
            $grid->updateColumn('name', array(
            'callback' => array(
            'function' => array(
                $this,
                'updateSubjectName'),
            'params' => array(
                '{{name}}', '{{CID}}'))));

        }
          */
        $grid->addAction(array('module' => 'message',
            				   'controller' => 'send',
            				   'action' => 'index'),
                         array('MID'),
                         _('Отправить сообщение'));

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_DEAN)){

            $grid->addAction(array(
                'module' => 'assign',
                'controller' => 'graduated',
                'action' => 'login-as'
            ),
                array('MID'),
                _('Войти от имени пользователя'),
                _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
            );

            $grid->addMassAction(
                $this->view->url(array('module' => 'assign', 'controller' => 'graduated', 'action' => 'order')),
                _('Печать приказа об окончании обучения')
            );
            $grid->addMassAction(
                $this->view->url(array('module' => 'assign', 'controller' => 'graduated', 'action' => 'certificates')),
                _('Печать сертификатов')
            );
            $grid->addMassAction(
                $this->view->url(array('module' => 'assign', 'controller' => 'graduated', 'action' => 'delete')),
                _('Удалить с сессии'),
                _('Вы уверены?')
            );
        }

        $grid->addMassAction(array('module' => 'message',
        						   'controller' => 'send',
        						   'action' => 'index'),
                             _('Отправить сообщение'));
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateFio($fio, $userId)
    {
		if($this->_currentLang == 'eng')
			$fio = $this->translit($fio);		
			
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }

    public function updateSubjectName($name, $subjectId)
    {
        $name = trim($name);
        if (!strlen($name)) {
            $name = sprintf(_('Учебный курс #%d'), $subjectId);
        }
        return $name;
    }

    public function orderAction()
    {
        $ids = $this->_request->getParam('postMassIds_grid');
        $ids = explode(',', $ids);
        if (count($ids)) {
            $this->_helper->getHelper('layout')->disableLayout();
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
            $this->getHelper('viewRenderer')->setNoRender();

            $graduated = $this->getService('Graduated')->findDependence(array('User', 'Subject'), $ids);

            $word = new HM_Word();

            $word->appendHtml($this->getService('Option')->getOption('template_order_header'));
            $word->appendHtml($this->getService('Option')->getOption('template_order_text'));

            if (count($graduated)) {
                $data = array();
                foreach($graduated as $item) {
                    $data[] = array($item->getUser()->getName(), $item->getSubject()->name);
                }

                $word->appendTable(array(_('ФИО'), _('Курс')), $data);
            }

            $word->appendHtml($this->getService('Option')->getOption('template_order_footer'));
            $word->send();

        } else {
            $this->_flashMessenger->addMessage(_('Не выбраны прошедшие обучение'));
            $this->_redirector->gotoSimple('index', 'graduated', 'assign');
        }

    }

    /**
     * Печать сертификатов для окончивших обучение
     */
    public function certificatesAction()
    {
        $ids = $this->_request->getParam('postMassIds_grid');
        $ids = explode(',', $ids);
        if (count($ids)) {

            $graduated = $this->getService('Graduated')->find($ids);

            if (count($graduated)) {

                $pdf = new Zend_Pdf();
                $oldEncoding = mb_internal_encoding();
		        mb_internal_encoding("Windows-1251");

                foreach($graduated as $item) {
                    $cert_path = Zend_Registry::get('config')->path->upload->cetrificates . "{$item->certificate_id}.pdf";
                    if ( file_exists($cert_path) ) {
                        $user_cert = Zend_Pdf::load($cert_path);
                        $pages = $user_cert->pages;
                        // добавляем все страницы
                        foreach ($pages as $page) {
                            $pdf->pages[] = clone $page;
                        }
                    }
                }

                if ( count($pdf->pages) ) {

                    $this->_helper->getHelper('layout')->disableLayout();
                    Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
                    $this->getHelper('viewRenderer')->setNoRender();

                    $this->getResponse()
                         ->setHeader('Content-Type', 'application/x-pdf',true)
                         ->setHeader('Content-Disposition','filename="certificates_'.date("Y-m-d_H-i-s").'.pdf"',true)
                         ->appendBody($pdf->render());
                } else {
                    $this->_flashMessenger->addMessage(_('Не найдены сертификаты выбранных пользователей'));
                    $this->_redirector->gotoSimple('index', 'graduated', 'assign');
                }
                mb_internal_encoding($oldEncoding);
            } else {
                $this->_flashMessenger->addMessage(_('Не найдены прошедшие обучение'));
                $this->_redirector->gotoSimple('index', 'graduated', 'assign');
            }


        } else {
            $this->_flashMessenger->addMessage(_('Не выбраны прошедшие обучение'));
            $this->_redirector->gotoSimple('index', 'graduated', 'assign');
        }

    }
    
    public function deleteAction(){
        
        $ids = $this->_request->getParam('postMassIds_grid');
        $ids = explode(',', $ids);
        foreach($ids as $id){
            $this->getService('Graduated')->delete($id);
        }
        $this->_redirector->gotoSimple('index', 'graduated', 'assign', array($this->idParamName => $this->id));
    }
	
	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	} 	
	
}