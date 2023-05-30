<?php
class Update_ListController extends HM_Controller_Action
{
    public function indexAction()
    {
        $select = $this->getService('Update')->getSelect();
        $select->from(
            array('u' => 'updates'),
            array(
                'update_id',
                'version',
                'description',
                'updated',
                'servers'
            )
        );

        $select->order('update_id DESC');

        $grid = $this->getGrid(
            $select,
            array(
                'update_id' => array('title' => _('Номер')),
                'version' => array('title' => _('Версия')),
                'description' => array('title' => _('Описание')),
                'updated' => array('title' => _('Дата установки'), 'callback' => array('function' => array($this, 'updateUpdated'), 'params' => array('{{servers}}'))),
                'servers' => array('title' => _('Сервера установки'), 'callback' => array('function' => array($this, 'updateServers'), 'params' => array('{{servers}}')))
            ),
            array(
                'update_id' => null,
                'version' => null,
                'description' => null
            )
        );

        $grid->addAction(array(
            'module' => 'update',
            'controller' => 'list',
            'action' => 'uninstall'
        ),
            array('update_id'),
            $this->view->icon('delete')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array()
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function updateActions($actions)
    {
        static $count = 0;
        $count++;
        if ($count > 1) {
            return '';
        }
        return $actions;
    }

    public function updateUpdated($servers)
    {
        if (strlen($servers)) {
            $servers = unserialize($servers);
            if (isset($servers[$this->getService('Update')->getServerAddr()])) {
                $date = new HM_Date($servers[$this->getService('Update')->getServerAddr()]);
                return $date->get(HM_Date::DATETIME);
            }
            return $servers[$this->getService('Update')->getServerAddr()];
        }
        return '-';
    }

    public function updateServers($servers)
    {
        if (strlen($servers)) {
            $servers = unserialize($servers);
            return join(', ', array_keys($servers));
        }
        return '';
    }

    public function installAction()
    {
        $form = new HM_Form_Update();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($form->file->isUploaded() && $form->file->receive() && $form->file->isReceived()) {
                    $filename = $form->file->getFileName();
                    try {
                        $this->getService('Update')->install($filename);
                        $this->_flashMessenger->addMessage(_('Обновление успешно установлено'));
                    } catch(HM_Exception $e) {
                        $this->_flashMessenger->addMessage(array(
                                                               'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                               'message' => $e->getMessage()
                                                           ));
                    }
                    $this->_redirector->gotoSimple('index', 'list', 'update', array());
                }
            }
        }

        $this->view->form = $form;
    }

    public function uninstallAction()
    {
        $updateId = (int) $this->_getParam('update_id', 0);

        if ($updateId) {
            try {
                $this->getService('Update')->uninstall($updateId);
                $this->_flashMessenger->addMessage(_('Обновление успешно удалено'));
            } catch(HM_Exception $e) {
                $this->_flashMessenger->addMessage(array(
                                                       'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                                       'message' => $e->getMessage()
                                                   ));
            }
        }
        $this->_redirector->gotoSimple('index', 'list', 'update', array());
    }
}