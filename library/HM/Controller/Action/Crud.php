<?php

class HM_Controller_Action_Crud extends HM_Controller_Action
{
    const ACTION_INSERT    = 'insert';
    const ACTION_UPDATE    = 'update';
    const ACTION_DELETE    = 'delete';
    const ACTION_DELETE_BY = 'delete-by';

    // Errors
    const ERROR_NOT_FOUND      = 'not_found';
    const ERROR_COULD_NOT_CREATE = 'could_not_create';
    
    const EXPORT_FILENAME = 'Y-m-d_H-i-s'; // time format only!

    /* @var $_form HM_Form */
    protected $_form;

    protected function _setForm( $form)
    {
        $this->_form = $form;
    }

    protected function _getForm()
    {
        $this->_form->setServiceContainer(Zend_Registry::get('serviceContainer')); //todo: не юзать singletone
        return $this->_form;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Элемент успешно создан'),
            self::ACTION_UPDATE => _('Элемент успешно обновлён'),
            self::ACTION_DELETE => _('Элемент успешно удалён'),
            self::ACTION_DELETE_BY => _('Элементы успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Элемент не был создан'),
            self::ERROR_NOT_FOUND        => _('Элемент не найден')
        );


    }

    private function _getErrorMessage($error)
    {
        $messages = $this->_getErrorMessages();
        if (isset($messages[$error])) {
            return $messages[$error];
        }else{
            return $error;
        }

        return _('Сообщение для данного события не установлено');
    }



    protected function _getMessage($action)
    {
        $messages = $this->_getMessages();
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return _('Сообщение для данного события не установлено');
    }

    public function create(Zend_Form $form)
    {

    }

    public function update(Zend_Form $form)
    {

    }

    public function delete($id)
    {

    }

    public function setDefaults(Zend_Form $form)
    {

    }

    protected function _redirectToIndex()
    {

        $this->_redirector->gotoSimple('index');

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

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $params = $this->_getAllParams();
        foreach($params as $key => $value) {
            if (substr($key, -3) == '_id') {
                $this->_setParam('id', $value);
                break;
            }
            if ($key == 'subid') { // hack
                $this->_setParam('id', $value);
            }
        }
        $id = (int) $this->_getParam('id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->delete($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function unsetAction(&$actions, $unsetAction) 
    {
        $return = array();
        $unsetUrl = $this->view->url(array_merge($unsetAction, array('gridmod' => null, 'treeajax' => null, 'key' => null)));
        $urls = explode('<li>', $actions);
        foreach ($urls as $url) {
            if (!strpos($url, $unsetUrl)) $return[] = $url;
        }
        $actions = implode('<li>', $return);
    }

}