<?php
class Message_SendController extends HM_Controller_Action_Activity
{
    public function indexAction()
    {
        $defaultNS   = new Zend_Session_Namespace('default');
        $subject     = $this->_getParam('subject', false);
        $subjectId   = (int) $this->_getParam('subject_id', 0);
        $postMassIds = $this->_getParam('postMassIds_grid_contacts',
            $this->_getParam('postMassIds_grid',
            $this->_getParam('MID',
            $this->_getParam('postMassIds_grid'.$subjectId,''))));

        // со страницы заявок и прошедших обучения пришли не ИД пользователей, а ИД соотв элементов
        $postMassIds = $this->getUserIDs(explode(',', trim($postMassIds)));



        // сохраняем ссылку с которой пришли если пришли не из этого экшена
        if ( !strstr($_SERVER['HTTP_REFERER'],'message/send') ) {
            $defaultNS->message_referer_page = $_SERVER['HTTP_REFERER'];
        }

        $form = new HM_Form_Message();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $message = $form->getValue('message');

                $messenger = $this->getService('Messenger');

                $postMassIds = $form->getValue('users');
                if (strlen($postMassIds)) {
                    $ids = explode(',', $postMassIds);
                    if (count($ids)) {
                        $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));

                        foreach($users as $user) {

                            $userMessage = $message;
                            $userMessage = str_replace('[LOGIN]', $user->Login, $userMessage);

                            if ((strpos($userMessage, '[NEW_PASSWORD]') !== false) && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)) {
                                $data = $user->getData();
                                $password = $this->getService('User')->getRandomString();
                                $data['Password'] = new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($password) . ")");
                                $userMessage = str_replace('[NEW_PASSWORD]', $password, $userMessage);
                                $this->getService('User')->update($data);
                            }

                            $messenger->setOptions(
                                HM_Messenger::TEMPLATE_PRIVATE,
                                array('text' => $userMessage, 'subject' => _('Личное сообщение')),
                                $subject,
                                $subjectId
                            );

                            try {
                                $messenger->send($this->getService('User')->getCurrentUserId(), $user->MID);
								if(
									$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
									||
									$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
								){
									$this->getService('Workload')->getTimeWelcomeMessage($this->getService('User')->getCurrentUserId(), $subjectId); //--возвращает время просрочки, но также и фиксирует факт отправки приветственного письма
								}
                            } catch (Exception $e) {

                            }
                        }
                    }
                }

                $this->_flashMessenger->addMessage( ( count($ids) == 1)? _('Сообщение отправлено') : _('Сообщения отправлены'))  ;
                //$this->_redirector->gotoSimple('index', 'contact', 'message', array('subject' => $subject, 'subject_id' => $subjectId));
                $this->_redirector->gotoUrl($defaultNS->message_referer_page);
            }
        } else {

            if (!strlen($postMassIds)) {

                $this->_flashMessenger->addMessage(_('Пользователи не выбраны'));
                //$this->_redirector->gotoSimple('index', 'contact', 'message');
                $this->_redirector->gotoUrl($defaultNS->message_referer_page);
            }
            $form->setDefault('users', $postMassIds);
            $form->setDefault('subject', $subject);
            $form->setDefault('subject_id', $subjectId);
        }

        $users = array();
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $users = $this->getService('User')->fetchAll(
                    $this->getService('User')->quoteInto('MID IN (?)', $ids)
                );
            }
        }

        $this->view->users = $users;
        $this->view->form = $form;

    }

    /**
     * По HTTP_REFERER определяем откуда пришли, и если это страница заявок или прошедших обучение,
     * по элементам в массиве $postMassIds получаем ИД соответствующих пользователей
     * @param array $postMassIds
     * @return string:
     */
    private function getUserIDs($postMassIds)
    {
        $arResult = array();

        if ( !$postMassIds ) return '';

        // если явно передается MID - это пипл
        if ( $this->_hasParam('MID') ) return implode(',', $postMassIds);

        if ( strstr($_SERVER['HTTP_REFERER'],'order/list') ) { // заявки на обучение
            $result = $this->getService('Claimant')->fetchAll('SID IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->MID;
                }
            }
        } elseif ( strstr($_SERVER['HTTP_REFERER'],'assign/graduated') ) { // прошедшие обучение
            $result = $this->getService('Graduated')->fetchAll('SID IN (' . implode(',', $postMassIds) . ')');
            if ( count($result)>0 ) {
                foreach ($result as $rItem) {
                    $arResult[] = $rItem->MID;
                }
            }
        } else { // если пришли с другой страницы - возвращаем что было - это пиплы
            return implode(',', $postMassIds);
        }
        return ( $arResult )? implode(',', $arResult): '';
    }

    public function instantSendAction(){

        $users = $this->_getParam('users', array());
        $subject = $this->_getParam('subject', false);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $addresatModeAjax = trim($subject)=="";
        $form = new HM_Form_InstantSend();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $message = $form->getValue('message');

                /*@var $messenger HM_Messanger */
                $messenger = $this->getService('Messenger');
                $messenger->setTemplate(HM_Messenger::TEMPLATE_PRIVATE);
                $messenger->assign(array('text' => $message, 'subject' => _('Личное сообщение')));
                $messenger->setRoom($subject, $subjectId);
                $postMassIds = $form->getValue('users');

                if (!empty($users)) {
                    if (count($users)) {
                        foreach($users as $id) {
                            $messenger->send($this->getService('User')->getCurrentUserId(), $id);
                        }
                    }
                }

                $this->_flashMessenger->addMessage(_('Сообщение отправлено'));
                $this->_redirector->gotoSimple('index', 'view', 'message', array('subject' => $subject, 'subject_id' => $subjectId));
            } else {

                if (empty($users)) {

                    $this->_flashMessenger->addMessage(_('Пользователи не выбраны'));
                    $this->_redirector->gotoSimple('index', 'contact', 'message');
                }
                $form->setDefault('subject', $subject);
                $form->setDefault('subject_id', $subjectId);
//#16837
                if($addresatModeAjax)
                {
                    $users = $form->getValue('users');
                    $users2init = array();
                    $users = $this->getService('User')->fetchAll('mid in ('.implode(',', $users).')');
                    foreach($users as $u){
                        $fio =  "{$u->LastName} {$u->FirstName} {$u->Patronymic}";
                        $users2init[$u->MID] = trim($fio)?$fio:$u->Login;
            }
                    $form->setDefault('users', $users2init); 
                }
//                  
            }
        }else{
//#16837
            if(!$addresatModeAjax)
            {
            $multiElement = $form->getElement('users');
            if ($subjectId <= 0) {
                $collection = array($this->getService('Activity')->getActivityUsers());
            } else {
                    $collection = array($this->getService('Subject')->getAssignedUsers($subjectId),
                                        $this->getService('Subject')->getAssignedTeachers($subjectId));
            }
                $ret = array();
            foreach ($collection as $subcollection)
            {
                if (count($subcollection)>0)
                {
                    foreach($subcollection as $value){
                        if($value->MID != $this->getService('User')->getCurrentUserId()) {
                            $ret[$value->MID] = $value->getName();
                        }
                    }
                }
            }
//[che 20.05.2014 #16837]
                $ret2 = array();
                foreach($ret as $i=>$r)
                    $ret2[$i] = trim($r)."[SPLIT]{$i}";
                sort($ret2);
                $ret = array();
                foreach($ret2 as $r)
                {
                    $split = explode('[SPLIT]', $r);
                    $ret[$split[1]] = $split[0];
                }
//////////////////////////
                $multiElement->setOptions(array('multiOptions' => $ret));
        }
        }

        $this->view->form = $form;





    }
}
