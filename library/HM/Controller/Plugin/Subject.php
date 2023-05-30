<?php
class HM_Controller_Plugin_Subject extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $error = false;
		$serviceContainer = Zend_Registry::get('serviceContainer');
        $subjectId = $request->getParam('subject_id', 0);
        
        if ($subjectId) {
            if ($serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                $collection = $serviceContainer->getService('Teacher')->fetchAll(
                    $serviceContainer->getService('Teacher')->quoteInto(
                        array('MID = ?', ' AND CID = ?'),
                        array($serviceContainer->getService('User')->getCurrentUserId(), $subjectId)
                    )
                );
                if (!count($collection)) {
                    $error = true;
                }
            } elseif ($serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {

                $actionName = $this->getRequest()->getActionName();
                
                if ($actionName !== "card" && $actionName !== 'description') { // карточку и описание могут видет и нестуденты - при подаче заявки
                    $collection = $serviceContainer->getService('Student')->fetchAll(
                        $serviceContainer->getService('Student')->quoteInto(
                            array('MID = ?', ' AND CID = ?'),
                            array($serviceContainer->getService('User')->getCurrentUserId(), $subjectId)
                        )
                    );
                    $collectionGraduated = $serviceContainer->getService('Graduated')->fetchAll(
                        $serviceContainer->getService('Graduated')->quoteInto(
                            array('MID = ?', ' AND CID = ?'),
                            array($serviceContainer->getService('User')->getCurrentUserId(), $subjectId)
                        )
                    );
                    if (!count($collection)) {
                        
                        $error = true;
                    
                        $collectionGraduated = $serviceContainer->getService('Graduated')->fetchAll(
                            $serviceContainer->getService('Graduated')->quoteInto(
                                array('MID = ?', ' AND CID = ?'),
                                array($serviceContainer->getService('User')->getCurrentUserId(), $subjectId)
                            )
                        );  
                        if (count($collectionGraduated)) {
                            $error = false;
                        } 
                    } 
                }            
            }
            
            if ($error) {
                $serviceContainer->getService('Log')->log(
                    $serviceContainer->getService('User')->getCurrentUserId(),
                    'Unauthorized access to subject pages',
                    'Fail',
                    Zend_Log::WARN,
                    get_class($this),
                    $subjectId
                );

                $flashMessengerCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('FlashMessenger');
                $redirectorCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('ConditionalRedirector');
                
                $flashMessenger = new $flashMessengerCls();
                $redirector = new $redirectorCls();

                $flashMessenger->addMessage(_('У вас нет права на просмотр этого курса'));
                $redirector->gotoUrl(Zend_Registry::get('baseUrl'));
            }
        }
    }
}
