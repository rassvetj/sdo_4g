<?php

class Techsupport_ListController extends HM_Controller_Action_Crud
{
    protected $_techsupportService = null;

    protected $_supportRequestId  = 0;
    
    
    public function init()
    {
        $this->_supportRequestId = (int) $this->_getParam('support_request_id', 0);
        $this->_techsupportService = $this->getService('Techsupport');
        
        parent::init();
    }
    
    
    public function indexAction()
    {
        $gridId = 'grid';
    	$default = new Zend_Session_Namespace('default');

    	$notAll = !$this->_getParam('all', isset($default->grid['techsupport-list-index'][$gridId]['all']) ? $default->grid['techsupport-list-index'][$gridId]['all'] : null);
        
        $select = $this->_techsupportService->getIndexSelect();

        if ($notAll) {
            $where = $this->_techsupportService->quoteInto(
                array('status IN (?)'),
                array(
                    array(
                        HM_Techsupport_TechsupportModel::SATUS_NEW,
                        HM_Techsupport_TechsupportModel::SATUS_ACCEPTED,
                    )
                )
            );
            $select->where($where);
        }

        $userListViewLink = $this->view->url(
            array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',
                'gridmod' => null,
                'user_id' => ''
            ), null, true
        );
        
        $userEditCardLink = $this->view->url(
            array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'card',
                'gridmod' => null,
                'user_id' => ''
            ), null, true
        );
        
        $techsupportAjaxViewLink = $this->view->url(
            array(
                'module' => 'techsupport',
                'controller' => 'ajax',
                'action' => 'view',
                'gridmod' => null,
                'support_request_id' => ''
            ), null, true
        );
        
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false,true);
        
        $grid = $this->getGrid(
            $select,
            array(
                'support_request_id' => array('hidden' => true),
                'MID' => array('hidden' => true),
                'user_name' => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($userListViewLink . '{{MID}}')
                        . '<a href="' . $userEditCardLink . '{{MID}}' . '">' . '{{user_name}}</a>'            
                ),
                'roles' => array('title' => _('Роли')),
                'date_' => array(
                    'title' => _('Дата'),
                    'callback' => array('function' => array($this, 'updateDate_'), 'params' => array('{{date_}}'))
                ),
                'theme' => array(
                    'title' => _('Тема'),
                    'decorator' => $this->view->lightDialogLink($techsupportAjaxViewLink . '{{support_request_id}}', '{{theme}}')
                ),
                'status' => array(
                    'title' => _('Статус'),
                    'callback' => array('function' => array($this, 'updateStatus'), 'params' => array('{{status}}'))
                ),
            ),
            array(
                'user_name' => null,
                'roles' =>
                    array('values' => $roles,
                        'callback' => array(
                            'function' => array($this, 'roleFilter'),
                            'params' => array()
                    )
                ),
                'date_' => array('render' => 'DateSmart'),
                'theme' => null,
                'status' => array('values' => HM_Techsupport_TechsupportModel::getStatuses()),
            ),
            $gridId
        );

        $grid->setGridSwitcher(array(
            array('name' => 'all_active', 'title' => _('активные запросы'), 'params' => array('all' => 0), 'order' => 'user_name'),
            array('name' => 'all_requests', 'title' => _('все, включая обработанные'), 'params' => array('all' => 1), 'order' => 'user_name'),
        ));
        
        $grid->addAction(
            array('module' => 'techsupport', 'controller' => 'list', 'action' => 'send-message'),
            array('support_request_id'),
            _('Ответить')
        );

        $grid->addAction(
            array('module' => 'techsupport', 'controller' => 'list', 'action' => 'delete'),
            array('support_request_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('module' => 'techsupport', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );
        
        $grid->addMassAction(
            array('action' => 'set-status'),
            _('Назначить статус')
        );
        
        $grid->addSubMassActionSelect(
            array($this->view->url(array('action' => 'set-status'))),
            'status',
            HM_Techsupport_TechsupportModel::getStatuses()
        );
        

        $grid->updateColumn('roles',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRole'),
                    'params' => array('{{MID}}', $grid)
                )
            )
        );
        
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();
        
    }
    
    
    public function sendMessageAction() {
        
        $supportRequest = $this->_techsupportService->find($this->_supportRequestId)->current();
        $userService = $this->getService('User');
        
        $form = new HM_Form_Message();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $message = $form->getValue('message');
                $status  = $form->getValue('status');
                
                if ($this->_supportRequestId) {
                    
                    $data = array(
                        'support_request_id' => $this->_supportRequestId,
                        'status'             => $status,
                        
                    );
                    $result = $this->_techsupportService->update($data);
                    
                    $user = $userService->find($result->user_id)->current();
                    
                    $requestMessage = "\n" .
                        _('Описание проблемы:') . "\n" .
                        $result->problem_description . "\n" .
                        _('Ожидаемый результат:') . "\n" .
                        $result->wanted_result;
                    
                    $statuses = HM_Techsupport_TechsupportModel::getStatuses();
                    
                    $messageData = array(
                        'id'       => $result->support_request_id,
                        'title'    => $result->theme,
                        'request'  => $requestMessage,
                        'response' => $message,
                        'status'   => $statuses[$result->status],
                        'lfname'   => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
                    );
                    $this->sendMessage($messageData, $supportRequest->user_id, HM_Messenger::TEMPLATE_SUPPORT_MESSAGE);
                }

                $this->_flashMessenger->addMessage(_('Сообщение отправлено'));
                $this->_redirectToIndex();
            }
        } else {
            $form->setDefault('status', $supportRequest->status);
        }

        $this->view->form = $form;
    
    }   
        
    public function setStatusAction() {
        $status = (int) $this->_getParam('status', 0);
        
        $userService = $this->getService('User');
        
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $data = array(
                        'support_request_id' => $id,
                        'status'             => $status,
                    );
                    $result = $this->_techsupportService->update($data);
                    
                    if($result){
                        $user = $userService->find($result->user_id)->current();
                        $statuses = HM_Techsupport_TechsupportModel::getStatuses();

                        $messageData = array(
                            'id'     => $result->support_request_id,
                            'title'  => $result->theme,
                            'status' => $statuses[$result->status],
                            'lfname' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
                        );
                        $this->sendMessage($messageData, $result->user_id, HM_Messenger::TEMPLATE_SUPPORT_STATUS);
                    }
                }
            }
        }
        
        if($result){
            $this->_flashMessenger->addMessage(_('Статусы успешно назначены!'));
        }
        $this->_redirectToIndex();
    }
      
    public function sendMessage($messageData, $user_id, $template) {
        $messenger = $this->getService('Messenger');

        $messenger->setOptions(
            $template,
            $messageData
        );

        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user_id);
    }
    
    public function delete($id) {
        $this->_techsupportService->delete($id);
    }
    
    public function updateStatus($status) {
        $statuses = HM_Techsupport_TechsupportModel::getStatuses();
        return $statuses[$status];
    }
    
    public function updateDate_($date) {
        $date = date('d.m.Y', strtotime($date));
        return $date;
    }
    
    public function roleFilter($data){
        $value=$data['value'];
        $select=$data['select'];
        
        if (!empty($value)){
            $select->joinInner(array('rs'=>'roles_source'),$this->quoteInto('rs.MID = sr.user_id AND rs.role = ?', $value),array());
        }
        
    }
    
    
}