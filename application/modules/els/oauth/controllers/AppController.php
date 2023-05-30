<?php
class Oauth_AppController extends HM_Controller_Action_Crud
{
    public function init()
    {
        parent::init();

        $this->_setForm(new HM_Form_App());
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'app', 'oauth');
    }

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT => _('Приложение успешно добавлено'),
            self::ACTION_UPDATE => _('Приложение успешно обновлено'),
            self::ACTION_DELETE => _('Приложение успешно удалено'),
            self::ACTION_DELETE_BY => _('Приложения успешно удалены')
        );
    }

    public function indexAction()
    {
        $select = $this->getService('OauthApp')->getSelect();

        $select->from(array('oa' => 'oauth_apps'), array('app_id', 'title', 'description'));

        $grid = $this->getGrid(
            $select,
            array(
                'app_id' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                    'decorator' => $this->view->cardLink($this->view->url(array('module' => 'oauth', 'controller' => 'app', 'action' => 'card', 'gridmod' => null, 'app_id' => ''), null, true) . '{{app_id}}') . '{{title}}'
                ),
                'description' => array(
                    'title' => _('Описание')
                )
            ),
            array(
                'title' => null
            )
        );

        $grid->addAction(array(
            'module' => 'oauth',
            'controller' => 'app',
            'action' => 'edit'
        ),
            array('app_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'oauth',
            'controller' => 'app',
            'action' => 'delete'
        ),
            array('app_id'),
            $this->view->icon('delete')
        );

        $this->view->grid = $grid->deploy();
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
    }

    public function create(Zend_Form $form)
    {
        $this->getService('OauthApp')->insert(
            array(
                'title' => $form->getValue('title'),
                'description' => $form->getValue('description'),
                'callback_url' => $form->getValue('callback_url'),
            )
        );

    }

    public function setDefaults(Zend_Form $form) {
        $appId = (int) $this->_getParam('app_id', 0);
        $app = $this->getOne($this->getService('OauthApp')->find($appId));
        if ($app) {
            $form->setDefaults($app->getValues());
        }
    }

    public function update(Zend_Form $form)
    {
        $this->getService('OauthApp')->insert(
            array(
                'app_id' => $form->getValue('app_id'),
                'title' => $form->getValue('title'),
                'description' => $form->getValue('description'),
                'callback_url' => $form->getValue('callback_url'),
            )
        );

    }

    public function delete($id)
    {
        return $this->getService('OauthApp')->delete($id);
    }

    public function cardAction()
    {
        $appId = (int) $this->_getParam('app_id', 0);
        $app = $this->getOne($this->getService('OauthApp')->find($appId));
        $this->view->app = $app;
    }



}