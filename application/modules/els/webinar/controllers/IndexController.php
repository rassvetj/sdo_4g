<?php

class Webinar_IndexController extends HM_Controller_Action {

    public function init()
    {
        //$this->required_permission_level = Roles_Basic::PERMISSION_LEVEL_GUEST;
        //Zend_Registry::get('unmanaged_controller')->setView('DocumentBlank');
        return parent::init();
    }

    public function indexAction()
    {
        $returnUrl = $_SERVER['HTTP_REFERER'];
        $this->getResponse()->clearBody();
        $pointId = (int) $this->_getParam('lesson_id', 0);

        // проверка на возможность запуска вебинара с pointId
        $userId = $this->getService('User')->getCurrentUserId();

        if (!$this->getService('Webinar')->isUserAllowed($pointId, $userId)
            && !$this->getService('Webinar')->isTeacherAllowed($pointId, $userId)) {

            $this->_flashMessenger->addMessage(
                array(
                    'type' => HM_Notification_NotificationModel::TYPE_NOTICE,
                    'message' => _('Вам не назначено данное занятие')
                )
            );
            $this->_redirector->gotoUrl($returnUrl);

        }

        $this->view->pointId = $pointId;
        $this->view->userId = $userId;
        $this->view->content = '';
        $this->view->media = Zend_Registry::get('config')->webinar->media;
        $this->view->server = Zend_Registry::get('config')->webinar->server;

       // if (defined('WEBINAR_MEDIA')) $this->view->media = WEBINAR_MEDIA;
        if (defined('WEBINAR_SERVER')) $this->view->server = WEBINAR_SERVER;

        echo $this->view->render('index/index.tpl');

        exit;

    }


    public function previewAction()
    {
        $this->getResponse()->clearBody();
        $pointId = (int) $this->_getParam('webinar_id', 0);

        $subjectId = (int) $this->_getParam('subject_id', 0);

        // проверка на возможность запуска вебинара с pointId
        $userId = $this->getService('User')->getCurrentUserId();

        if (!$this->getService('Webinar')->isWebinarTeacherAllowed($pointId, $userId, $subjectId)) {
            throw new HM_Permission_Exception(_('Данный вебинар не принадлежит к списку Ваших курсов'));
        }

        $this->view->pointId = 'webinar_' . $pointId;
        $this->view->userId = $userId;
        $this->view->content = '';
        $this->view->media = Zend_Registry::get('config')->webinar->media;
        $this->view->server = Zend_Registry::get('config')->webinar->server;

       // if (defined('WEBINAR_MEDIA')) $this->view->media = WEBINAR_MEDIA;
        if (defined('WEBINAR_SERVER')) $this->view->server = WEBINAR_SERVER;

        echo $this->view->render('index/preview.tpl');

        exit;

    }













    public function activityAction()
    {
        $pointId = 0;

        $user = $this->getOne($this->getService('WebinarUser')->find($pointId, $this->getService('User')->getCurrentUserId()));
        if (!$user) {
            $this->getService('WebinarUser')->insert(
                array(
                    'pointId' => $pointId,
                    'userId' => $this->getService('User')->getCurrentUserId(),
                    'last' => Zend_Date::now()
                )
            );
        }

        $this->view->pointId = $pointId;
        $this->view->userId = $this->getService('User')->getCurrentUserId();
        $this->view->content = '';
        $this->view->media = Zend_Registry::get('config')->webinar->media;
        $this->view->server = Zend_Registry::get('config')->webinar->server;

        if (defined('WEBINAR_MEDIA')) $this->view->media = WEBINAR_MEDIA;
        if (defined('WEBINAR_SERVER')) $this->view->server = WEBINAR_SERVER;

    }

    public function playAction()
    {
        $this->getResponse()->clearBody();
        $pointId = (int) $this->_getParam('pointId', 0);
        if(!$pointId) {
            $pointId = (int) $this->_getParam('lesson_id', 0);    
        }
        $this->view->pointId = $pointId;
        $this->view->libraryItem = Webinar_Library_Service::getInstance()->get($pointId);

        $this->view->media = Zend_Registry::get('config')->webinar->media;
        $this->view->server = Zend_Registry::get('config')->webinar->server;

        if (defined('WEBINAR_MEDIA')) $this->view->media = WEBINAR_MEDIA;
        if (defined('WEBINAR_SERVER')) $this->view->server = WEBINAR_SERVER;

        echo $this->view->render('index/play.tpl');
        exit();

    }

    public function xmlAction()
    {
        $this->getResponse()->clearBody();
        $pointId = (int) $this->_getParam('pointId', 0);

        echo Webinar_Xml_Service::getInstance()->get($pointId);
        exit();
    }

    protected function _getDestFileName($sourceFileName, $pointId)
    {
        $destPath = APPLICATION_PATH . '/../public/unmanaged/temp/webinar/'.$pointId;
        return $destPath . '/' . basename($sourceFileName);
    }

    public function prepareAction()
    {
        $url = Zend_Registry::get('baseUrl').'lib.php';
        if ($this->_getParam('cms', false)) {
            $url = Zend_Registry::get('baseUrl').'cms/lib.php';
        }
        try {
            //$this->getResponse()->clearBody();
            $pointId = (int) $this->_getParam('pointId', 0);
            if(!$pointId) {
                $pointId = (int) $this->_getParam('lesson_id', 0);    
            }
            if ($pointId) {
            	if (!Webinar_Service::getInstance()->get($pointId)) {
            		throw new Zend_Exception(sprintf(_('Публикация вебинара невозможна. Отсутствует занятие вебинара.')));
            	}
                Library::mkDirIfNotExists(APPLICATION_PATH . '/../public/unmanaged/temp/webinar');
                $destPath = APPLICATION_PATH . '/../public/unmanaged/temp/webinar/'.$pointId;
                Library::mkDirIfNotExists($destPath);
                $zipFile = $destPath.'/'.($pointId).'.zip';

                $files = Webinar_Service::getInstance()->getRecordFiles($pointId);
                if (is_array($files) && count($files)) {

                    foreach($files as $file) {

                        $sourceFile = $file;
                        $destFile   = $this->_getDestFileName($sourceFile, $pointId);

                        if (false === Library::streamCopy($sourceFile, $destFile)) {
                            // ошибочка, заново пробуем
                            throw new Zend_Exception(sprintf(_('Ошибка копирования файла %s.'), $sourceFile));
                        }
                    }
                }

                // todo: генерируем xml и копируем плеер
                $files2copy = array('webinar.swf', 'index.html', 'expressInstall.swf', 'swfobject.js');
                foreach($files2copy as $file2copy) {
                    if (!copy(APPLICATION_PATH.'/../public/webinar/player/local/'.$file2copy, $destPath.'/'.$file2copy)) {
                        throw new Zend_Exception(sprintf(_('Ошибка копирования файла %s.'), $file2copy));
                    }
                }

                $xml = Webinar_Xml_Service::getInstance()->get($pointId, true);
                @file_put_contents($destPath.'/webinar.xml', $xml);

                if (!file_exists($destPath.'/webinar.xml')) {
                    throw new Zend_Exception(_('Файл webinar.xml не создан.'));
                }

                // todo: копируем файло материалов вебинара
                $webinarItems = Webinar_Files_Service::getInstance()->getItemList($pointId);
                if (count($webinarItems)) {
                    foreach($webinarItems as $item) {
                        if (strlen($item->path)) {
                            if (!copy($item->path, $destPath.'/'.basename($item->path))) {
                                throw new Zend_Exception(sprintf(_('Ошибка копирования файла %s в %s.'), $item->path, $destPath.'/'.basename($item->path)));
                            }
                        }
                    }
                }

                // пакуем
                $zip = new Zend_Filter_Compress(array(
                     'adapter' => 'zip',
                     'options' => array(
                         'archive' => $zipFile,
                         'target' => $destPath.'/'
                      ),
                ));

                if (!$zip->filter($destPath.'/')) {
                    // ошибочка, заново пробуем
                    throw new Zend_Exception(sprintf(_('Архив %s не создан.'), $zipFile));
                }

                if (file_exists($zipFile) && is_readable($zipFile)) {
                    // todo: копируем материал куда нужно и выставляем ссылки на него
                    $libraryItem = Webinar_Library_Service::getInstance()->get($pointId);

                    if ($libraryItem) {

                        $libraryPath = APPLICATION_PATH.'/../public/unmanaged/library/'.(int) $libraryItem->bid.'/';
                        Library::mkDirIfNotExists($libraryPath);

                        if (copy($zipFile, $libraryPath.'/webinar.zip')) {

	                        $xml = Webinar_Xml_Service::getInstance()->get($pointId);
			                @file_put_contents($libraryPath.'/webinar.xml', $xml);

			                if (!file_exists($libraryPath.'/webinar.xml')) {
			                    throw new Zend_Exception(_('Файл webinar.xml не создан.'));
			                }

                            @unlink($zipFile);
                            $libraryItem->filename = "/".(int) $libraryItem->bid.'/webinar.zip';
                            $libraryItem->location = Zend_Registry::get('baseUrl').'webinar/index/play/pointId/'.$pointId;

                            $leader = Webinar_Service::getInstance()->getLeader($pointId);
                            if ($leader) {
                                $libraryItem->author = $leader->getName();
                            }

                            //Webinar_Library_Service::getInstance()->update($libraryItem);
                            $libraryItem->save();

                        } else {
                            throw new Zend_Exception(sprintf(_('Ошибка копирования файла %s.'), $zipFile));
                        }
                    } else {
                        throw new Zend_Exception(_('Не найдены материалы вебинара'));
                    }
                    //$this->_helper->sendFile($zipFile, 'application/zip');
                    //exit();
                } else {
                    throw new Zend_Exception('Материалы вебинара не были опубликованы. Попробуйте ещё раз.');
                }

                //$this->_flashMessenger->addMessage(_('Материалы вебинара успешно опубликованы.'));
                Zend_Registry::get('unmanaged_controller')->setView('Document');
                Zend_Registry::get('unmanaged_controller')->setMessage(_('Материалы вебинара успешно опубликованы.'), JS_GO_URL, $url);
            }
        } catch(Zend_Exception $e) {
            @unlink($zipFile);
            Zend_Registry::get('unmanaged_controller')->setView('Document');
            Zend_Registry::get('unmanaged_controller')->setMessage($e->getMessage(), JS_GO_URL, $url);

        }

    }

}