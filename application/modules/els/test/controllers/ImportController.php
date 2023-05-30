<?php
class Test_ImportController extends HM_Controller_Action
{
    const MESSAGE_IMPORT_SUCCESS = 'Данные успешно загружены';
    const MESSAGE_IMPORT_TEST_SUCCESS = 'Данные успешно загружены';

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;
    protected $_subject;
    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $this->id = (int) $this->_getParam($this->idParamName, 0);
            if ($this->id) { // Делаем страницу расширенной
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
                $this->_subject = $subject;
            }
        }
    }
    
    
	public function indexAction()
	{
		$subjectId = (int) $this->_getParam('subject_id', 0);
		$form = new HM_Form_TestImport();
		$this->view->form = $form;

		$request = $this->getRequest();
		if ($request->isPost() && $valid = $form->isValid($request->getParams())) {
			if ($form->zipfile->isUploaded()) {
				if ($form->zipfile->receive() && $form->zipfile->isReceived()) {
					// Импортирование теста
					$this->form = '';
					$s = Zend_Registry::get('session_namespace_unmanaged')->s;
					$params = $form->getValues();
					if (is_array($params) && count($params)) {
						foreach($params as $key => $value) {
							$$key = $value;
						}
					}

					$paths = get_include_path();
					set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

					$GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

					$currentDir = getcwd();
					ob_start();

					chdir(APPLICATION_PATH.'/../public/unmanaged/teachers/');
					//$GLOBALS['wwf'] = APPLICATION_PATH.'/../public/unmanaged/';
					include(APPLICATION_PATH.'/../public/unmanaged/teachers/organization_exp.php');
					$content = ob_get_contents();
					ob_end_clean();
					set_include_path(implode(PATH_SEPARATOR, array($paths)));

					chdir($currentDir);

                    $type = HM_Notification_NotificationModel::TYPE_SUCCESS;
                    //global $strMsg;
                    if ((false === strstr($strMsg, _(self::MESSAGE_IMPORT_SUCCESS))) && (false === strstr($strMsg, _(self::MESSAGE_IMPORT_TEST_SUCCESS)))) {
                        $type = HM_Notification_NotificationModel::TYPE_ERROR;
                    }

                    if ( isset($GLOBALS['testAbstractId']) &&
                         $GLOBALS['testAbstractId'] &&
                         $this->_subject
                    ) {
                        $this->getService('TestAbstract')->createLesson($this->_subject->subid, $GLOBALS['testAbstractId']);
                    }

                    if ($GLOBALS['testAbstractId'] && isset($GLOBALS['resources']) && is_array($GLOBALS['resources']) && count($GLOBALS['resources'])) {
                        foreach($GLOBALS['resources'] as $resource) {
                            $resources = $this->getService('Resource')->fetchAll($this->quoteInto('db_id = ?', $resource['db_id']));
                            if (count($resources)) {
                                foreach($resources as $item) {
                                    $this->getService('Resource')->delete($item->resource_id);
                                }
                            }

                            $item = $this->getService('Resource')->insert(
                                array(
                                    'title' => $resource['title'],
                                    'type' => HM_Resource_ResourceModel::TYPE_EXTERNAL,
                                    'description' => '',
                                    'subject_id' => 0,
                                    'location' => 1,
                                    'status' => HM_Resource_ResourceModel::STATUS_UNPUBLISHED,
                                    'content' => '',
                                    'filename' => basename($resource['filename']),
                                    'db_id' => $resource['db_id'],
                                    'test_id' => (int) $GLOBALS['testAbstractId']
                                )
                            );

                            if ($item) {
                                $filename = realpath(APPLICATION_PATH.'/../public/unmanaged/COURSES/course1'.substr($resource['filename'], 1));
                                if (file_exists($filename) && is_readable($filename)) {
                                    $item->volume = filesize($filename);
                                    $item->filetype = HM_Files_FilesModel::getFileType($item->filename);

                                    $filter = new Zend_Filter_File_Rename(
                                        array(
                                            'source' => $filename,
                                            'target' => realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$item->resource_id,
                                            'overwrite' => true
                                        )
                                    );
                                    if ($filter->filter($filename)) {
                                        $this->getService('Resource')->update($item->getValues());
                                    }
                                }
                            }
                        }
                    }

                    $this->_flashMessenger->addMessage(array('message' => str_ireplace('<br>', "\n", $strMsg), 'type' => $type));
                    //global $page;
                    $this->_redirector->gotoUrl($page);

				}
			}
		} else {
			$form->setDefault('subject', $subjectId);
			if ($subjectId) {
				$form->setDefault('location', 0);
			}
		}
	}
}