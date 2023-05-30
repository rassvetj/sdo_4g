<?php
class Kbase_SourceController extends HM_Controller_Action
{	
	private 	$_importService = null;
    protected 	$_importManagerClass = 'HM_Kbase_Source_Import_Manager';
	
    public function indexAction()
    {
        try {
			$this->view->setHeader(_('Электронные издания и ресурсы'));
			$config = Zend_Registry::get('config');
			$this->view->headLink()->appendStylesheet($config->url->base.'css/content-modules/kbase.css');
			
			$select = $this->getService('KbaseSource')->getSelect();
			$select->from('kbase_source', array(
				'source_id',
				'direction',
				'code',
				'years',
				'discipline',
				'e_publishing',
				'e_publishing_url',
				'e_educational',
				'e_educational_url',				
			));
					 									

			$grid = $this->getGrid($select,
				array(
					'source_id'			=> array('hidden' => true),
					'direction' 		=> array('title' => _('Направление подготовки') ),
					'code' 				=> array('title' => _('Шифр') ),
					'years' 				=> array('title' => _('Год начала обучения') ),
					'discipline' 		=> array('title' => _('Наименование дисциплины') ),
					'e_publishing' 		=> array('title' => _('Перечень электронных изданий указанных в рабочей программе дисциплины') ),
					
					
					'e_publishing_url' => array(
						'title' => _('Ссылки на электронные издания указанные в рабочей программе дисциплины'),
						'callback' => array(
							'function'=> array($this, 'updateLink'),
							'params'=> array('{{e_publishing_url}}')
						)
					),
					
					'e_educational' 	=> array('title' => _('Перечень электронных образовательных ресурсов указанных в рабочей программе дисциплины') ),
			
					
					'e_educational_url' => array(
						'title' => _('Ссылки на электронные образовательные ресурсы   указанные в рабочей программе дисциплины'),
						'callback' => array(
							'function'=> array($this, 'updateLink'),
							'params'=> array('{{e_educational_url}}')
						)
					),
				),
				array()
			);			
			$this->view->grid = $grid->deploy();
			$this->view->gridAjaxRequest = $this->isGridAjaxRequest();			
		} catch (Exception $e) {
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}		
	}
	
	public function importAction(){		
		$source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));
        
        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm();
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName($form->file->getFileName());
                        $this->_valid = true;						
                    }
                }
            } else {
                $this->view->form = $form;
            }
        }

        try {
            $class = $this->_importManagerClass;
            $this->_importManager = $importManager = new $class();
            if ($this->_valid) {				
                $importManager->init($this->_importService->fetchAll());
            }
        } catch(HM_Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
        $this->view->importManager = $importManager;
        $this->view->source = $source;		
	}
	
	public function csv()
    {
        try {			
			$this->view->setHeader(_('Электронные издания и ресурсы: импортировать из CSV'));
			$this->_importService = $this->getService('KbaseSourceCsv');
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}		
    }
	
	public function processAction()
    {        
		try {
			$source = $this->_getParam('source', false);

			if (!$source || !method_exists($this, $source)) {
				throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
			}

			call_user_func(array($this, $source));

			$importManager = new HM_Kbase_Source_Import_Manager();
		
			if ($importManager->restoreFromCache()) {			
				$importManager->init(array());
			} else {					
				$importManager->init($this->_importService->fetchAll());
			}

			if (!$importManager->getInsertCount()) {
				$this->_flashMessenger->addMessage(_('Новые записи не найдены'));
			}

			$importManager->import();

			if ($importManager->getInsertCount() ) {
				$this->_flashMessenger->addMessage(sprintf(_('Было добавлено %d записи(ей)'), $importManager->getInsertCount()));
			}
			
			$this->_redirector->gotoSimple('import', 'source', 'kbase', array('source' => 'csv'));
		} catch (Exception $e) {			
			$this->_flashMessenger->addMessage('Ошибка: '.$e->getMessage());
		}	
		$this->_redirector->gotoSimple('import', 'source', 'kbase', array('source' => 'csv'));
    }
	
	
	public function updateLink($url){
		if(empty($url)) { return ''; }
		$urls = explode('~~', $url);
		$urls = array_filter($urls);
		$count = count($urls);
		
		$result = ($count > 1) ? array('<p class="total">' . sprintf(_n('ссылки plural', '%s ссылки', $count), $count) . '</p>') : array();
		foreach($urls as $u){
			$result[] = '<p><a href="'.$u.'" target="_blank">'.$u.'</a></p>';			
		}		
		$result = implode('',$result);
		return $result;		
	}
}