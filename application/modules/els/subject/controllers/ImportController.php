<?php
class Subject_ImportController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_Subject_Import_Manager';

    public function indexAction()
    {
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
        $this->getService('Unmanaged')->setHeader(_('Импортировать записи из CSV'));
        $this->_importService = $this->getService('SubjectCsv');
    }

    public function processAction()
    {
        $source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $importManager = new HM_Subject_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
        }

        $importManager->import();

        if ($importManager->getUpdatesCount() || $importManager->getInsertsCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d записи(ей) и обновлены %d записи(ей)'), $importManager->getInsertsCount(), $importManager->getUpdatesCount()));
        }
		
		$dublIDs = $this->getService('Subject')->getMultipleIDSubjects();
		if(count($dublIDs)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
												'message' => _('Обнаружены задвоения сессий по внешнимему ID: '.implode(', ',$dublIDs))));
		}
        
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('base' => HM_Subject_SubjectModel::BASETYPE_SESSION));
        
    }
	
	public function getExampleFileAction()
	{
		$data = array(
			array(
				0  => '12134',
				1  => '567',
				2  => '890',
				3  => 'Тестовая сессия',
				4  => 'Кафедра',
				5  => '123-456-g',
				6  => 'Приём зачётов',
				7  => 5,
				8  => 3,
				9  => 4,
				10 => 5,
				11 => 6,
				12 => 7,
				13 => 8,
				14 => 9,
				15 => '02.05.2020',
				16 => '20.02.2022',
				17 => 1,
				18 => '10.05.2020',
				19 => '123',
				20 => '20',
				21 => '125',
				22 => 2,
				23 => '77',
				24 => '77',
				25 => '77',
				26 => 'Факультет',
				27 => 1,
				28 => 'Название модуля',
				29 => '01.02.2022',			
				30 => '20.02.2022',	
			),
		);
		
		
		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();		
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=sessions.csv');
		echo "\xEF\xBB\xBF";
		echo implode(';', HM_Subject_Csv_CsvAdapter::getColumnNameList());
		echo "\r\n";
		foreach($data as $item){
			echo implode(';', $item);
		}		
	}
	
	
}