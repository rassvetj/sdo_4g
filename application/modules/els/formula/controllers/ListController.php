<?php

class Formula_ListController extends HM_Controller_Action
{


    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function init()
    {
        parent::init();

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
        $subjectId = $_GET['cid'] = $cid = $this->_request->getParam('subject_id', 0);

       	$currentRole = $this->getService('User')->getCurrentUserRole();
        /*if(!$subjectId && $currentRole != HM_Role_RoleModelAbstract::ROLE_ADMIN){
	        $this->_flashMessenger->addMessage(_('Выберите курс'));
	        $this->_redirector->gotoSimple('index', 'list', 'subject'); 
        }*/
        
        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $s = &$_SESSION['s'];
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        include(APPLICATION_PATH.'/../public/unmanaged/formula.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));
        chdir($currentDir);

        $this->view->content = $content;

    }

    public function deleteAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $formula_id = (int) $this->_getParam('formula_id', 0);

        if ($formula_id) {
            $this->getService('Formula')->delete($formula_id);
        }

        $this->_flashMessenger->addMessage(_('Формула успешно удалена'));
        $this->_redirector->gotoSimple('index', 'list', 'formula', array('subject_id' => $subjectId));        

    }
}