<?php

class Resource_IndexController extends HM_Controller_Action_Resource
{
    const DEFAULT_VIDEO_WIDTH = 512;
    const DEFAULT_VIDEO_HEIGHT = 384;
    const MAX_VIDEO_WIDTH = 1280;
    const MAX_VIDEO_HEIGHT = 1024;

    private $_subjectId = 0;
    private $_courseId = 0;
    private $_key = 0;

    protected $_resource;

    public function init(){

        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_courseId = (int) $this->_getParam('course_id', 0);
        $this->_key = (int) $this->_getParam('key', 0);

        $this->view->key = $this->_key;
        $this->view->subjectId = $this->_subjectId;
        $this->view->courseId = $this->_courseId;

        if (!$this->isAjaxRequest()) {
            if ($this->_subjectId > 0) {
                $this->_initSubjectExtended();
            }

            if (!$this->_subjectId && ($this->_courseId > 0)) {
                $this->_initCourseExtended();
            }
        }

        if ($resourceId = $this->_getParam('resource_id', 0)) {
            if ($collection = $this->getService('Resource')->find($resourceId)) {
                $this->_resource = $collection->current();
            }
        }

        parent::init();
    }

    private function _initSubjectExtended()
    {
        $this->setService('Subject');

        $this->idParamName = 'subject_id';
        $this->idFieldName = 'subid';

        $this->_subject = $this->getOne($this->getService('Subject')->find($this->_subjectId));

        /*if($this->_subjectId && $this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE && $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
            $this->view->addInfoBlock('freeAccessToSubjectBlock', array('title' => _('Содержание'), 'subject' => $this->_subject));
            $this->view->setHeader($this->_subject->getName());
        }*/

        // hack для корректного отображения баяна и хлебных крошек
        if ($this->_courseId > 0) {
            $this->view->addContextNavigationModifier(
                new HM_Navigation_Modifier_Remove_SubPages('resource', 'cm::subject:page7_5')
            );
        } else {
            $this->view->addContextNavigationModifier(
                new HM_Navigation_Modifier_Remove_SubPages('resource', 'cm::subject:page7_1')
            );
        }
    }

    private function _initCourseExtended()
    {
        $this->setService('Course');

        $this->idParamName = 'course_id';
        $this->idFieldName = 'CID';
    }

    private function _redirectToIndex($resourceId)
    {
        if (($this->_subjectId > 0) && ($this->_courseId > 0)) {
            $this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId, 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId, 'key' => $this->_key));
        }elseif($this->_subjectId > 0){
            $this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId, 'subject_id' => $this->_subjectId, 'key' => $this->_key));
        }

        $this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId));
    }

    public function indexAction()
    {  
        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_subject->subid;
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $revisionId = (int) $this->_getParam('revision_id', 0);
        $num = (int) $this->_getParam('num', 0); // как-то ненадёжно, номера могут сбиться
        $lessonId   = (int) $this->_getParam('lesson_id', 0);

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
            $mark = $this->getOne($this->getService('LessonAssign')->fetchAllDependence('Lesson', array('SHEID = ?' => $lessonId, 'MID = ?' => $userId)));
            if ($mark && count($mark->lessons)){
                $lesson = $mark->lessons->current();
                if ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {
                $mark->V_STATUS = 100;
                $mark->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE;
                $this->getService('LessonAssign')->update($mark->getData());
            }
        }
        }

        $this->initLessonTabs();

        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

        if ($resource->type == HM_Resource_ResourceModel::TYPE_CARD) {
            $this->_redirector->gotoSimple('card', 'index', 'resource', array('resource_id' => $resourceId));
        }

        if ($revisionId) {
            $revision = $this->getService('ResourceRevision')->getOne($this->getService('ResourceRevision')->find($revisionId));
            $this->getService('Unmanaged')->setSubHeader(sprintf(_('Версия #%s от %s'), $num, $revision->dateTime($revision->updated)));
        }
//        $this->getService('Unmanaged')->setSubHeader($resource->title);

        $this->view->courseContent = true;
        $this->view->topContent = '';

        if ($this->_courseId > 0 && $this->_hasParam('key')) {
            $serverUrl = array(
                'module'     => 'course',
                'controller' => 'structure',
                'action'     => 'index',
                'subject_id' => $this->_subjectId,
                'course_id'  => $this->_courseId,
                'key'        => $this->_key
            );
            $serverUrl = $this->view->serverUrl($this->view->url($serverUrl, null, true));
            $this->view->topContent = $this->view->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$serverUrl.'"'));
        }

        if ($subjectId && $resource->type != HM_Resource_ResourceModel::TYPE_WEBINAR) {
            $this->view->topContent .= $this->view->headSwitcher(array(
                'module'     => 'resource',
                'controller' => 'index',
                'action'     => 'index',
                'switcher'   => 'index',
                'subject_id' => (int) $this->_getParam('subject_id', 0),
                'location'   => $resource->location
            ));
        }

        $this->view->resource = $resource;
        $this->view->resourceId = $resourceId;
        $this->view->revisionId = $revisionId;
        $this->view->subjectId = (int) $this->_getParam('subject_id', 0);
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if ($this->view->subjectId) {
			if($lng == 'eng' && $resource->title_translation != '')
				$this->view->setSubHeader($resource->title_translation);
			else
				$this->view->setSubHeader($resource->title);
        }

// @tocheck
//         if($subjectId && $this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE
//             && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)
//            //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT
//         ){
//             $this->view->deleteContextMenu('subject');
//         }

    }


    public function viewAction() {  
         /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');
				$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
        //$this->view->overlay         = false;
        $this->view->iframe_download = false;
        $this->view->type = $resource->type;
        // TODO: layout нужно дизаблить только если мы в этом методе вызываем die()
        //       если $resource->isViewable(), то layout нельзя вырубать, может быть
        //       стоит заменять его другим, но не вырубать!
        //       для HM_Resource_ResourceModel::TYPE_EXTERNAL и default НУЖЕН layout
        $this->_helper->getHelper('layout')->setLayout('naked');
        $this->view->disableExtendedFile();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $resourceId = (int) $this->_getParam('resource_id', 0);
        $revisionId = (int) $this->_getParam('revision_id', 0);
        if (!$resourceId && $this->_hasParam('db_id') && $this->_hasParam('test_id') && (false !== strstr($_SERVER['HTTP_REFERER'], $this->view->serverUrl('/test_vopros.php?')))) {
            /** @var HM_Resource_ResourceModel $resource */
            $resource = $this->getOne(
                $resourceService->fetchAll(
                    $this->quoteInto(
                        array('db_id = ?', ' AND test_id = ?'),
                        array($this->_getParam('db_id', 0), $this->_getParam('test_id', 0))
                    )
                )
            );

            if ($resource) {
                $resourceId = $resource->resource_id;
            } else {
                $resource = $this->getOne(
                    $this->getService('Resource')->fetchAll(
                        $this->quoteInto(
                            array('db_id = ?'),
                            array($this->_getParam('db_id', 0))
                        )
                    )
                );
                if ($resource) {
                    $resourceId = $resource->resource_id;
                }
            }

        } else {
            $resource = $resourceService->getResourceRevision($resourceId, $revisionId);
        }

        $this->view->type = $resource->type;
        // !!! TODO: Решение временное, в последствии данная процедура должна будет происходить при добавлении ресурса в базу !!!
        $resourceType = pathinfo($resource->filename, PATHINFO_EXTENSION);
        if(strtolower($resourceType) == 'flv'){ // Если ресурс FLV
            require_once 'FLV/flvinfo.php';
            $flvInfo = new Flvinfo();
            $meta = $flvInfo->getInfo(Zend_Registry::get('config')->path->upload->resource.'/'.$resource->resource_id, true);
            $resource->width = (int) $meta->video->width;
            $resource->height = (int) $meta->video->height;
        }

        // TODO: что делать если $resource = null
        //       пока возвращаю 404, но никаких видимых признаков этого не показываются
        if ($resource) {
            switch ($resource->type) {
                case HM_Resource_ResourceModel::TYPE_WEBINAR:
                case HM_Resource_ResourceModel::TYPE_URL:
                case HM_Resource_ResourceModel::TYPE_FILESET:
                //case HM_Resource_ResourceModel::TYPE_HTML:
                case HM_Resource_ResourceModel::TYPE_ACTIVITY:
                    $this->_helper->getHelper('layout')->disableLayout();
            }
            switch ($resource->type) {
                case HM_Resource_ResourceModel::TYPE_ACTIVITY:
                    if (count($collection = $this->getService('ActivityResource')->fetchAll(array('activity_id = ?' => $resource->activity_id, 'activity_type = ?' => $resource->activity_type)))) {
                        $activityResource = $collection->current();
                        $url = $activityResource->getUrl();
                        header("Location: {$url}");
                    }
                    die();
                case HM_Resource_ResourceModel::TYPE_WEBINAR:
                    $url = Zend_Registry::get('view')->baseUrl('/upload/webinar-records/' . $resource->resource_id . '/index.html') ;
                    header("Location: {$url}");
                    die();
                case HM_Resource_ResourceModel::TYPE_URL:
                    if (strpos($resource->url, 'http') !== 0) {
                        $resource->url = 'http://' . $resource->url;
                    }
                    header("Location: {$resource->url}");
                    die();
                case HM_Resource_ResourceModel::TYPE_FILESET:
                    $path = explode('public',Zend_Registry::get('config')->path->upload->public_resource);
                    $pathToFile = (isset($path[1])) ? $path[1] : '/upload/resources/';
                    if ($revisionId) {
                        $pathToFile .=  'revision/' . $revisionId . '/' . $resource->url;
                    } else {
                        $pathToFile .=  $resource->resource_id . '/' . $resource->url;
                    }
                    $protocol   = ($this->_request->isSecure())? 'https' : 'http';
                    $host       = $this->_request->getHttpHost();
                    $url        = $protocol . '://' .$host . $pathToFile;
                    header("Location: {$url}");
                    die();
                case HM_Resource_ResourceModel::TYPE_HTML:
                    // TODO: это фарш какой-то почему в $resource->content не может содержаться своего doctype?
//                     echo '<!DOCTYPE html><html><body>';
//                     echo $resource->content;
//                     echo '</body></html>';
//                     die();
  
// этот скрипт нужен в проекте УрФУ; в базовую ветку попал по ошибке
//                    $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mathML/ASCIIMathMLwFallback.js'));
                    Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('DocumentBlank');
					
                    //$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/common.css'));
                    $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/tinymce.css'));
			      
			if($lng == 'eng' && $resource->content_translation != ''){

			              if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $resource->content_translation, $matches)) {
                        $this->view->content = $matches[1];
                    } else {
                        $this->view->content = $resource->content_translation;
			}}
			else {
				
			              if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $resource->content, $matches)) {
                        $this->view->content = $matches[1];
                    } else {
                        $this->view->content = $resource->content;
			}
					}
					return true;
                case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                default:
                    if($resource->isViewable()){
                        switch(HM_Files_FilesModel::getFileType($resource->filename)){

                            case HM_Files_FilesModel::FILETYPE_TEXT:
                                if ($revisionId) {
                                    $fileName = Zend_Registry::get('config')->path->upload->resource.'/revision/' . $revisionId;
                                } else {
                                    $fileName = Zend_Registry::get('config')->path->upload->resource.'/'.$resource->resource_id;
                                }
                                $this->view->content = nl2br(file_get_contents($fileName));
                                break;

                            case HM_Files_FilesModel::FILETYPE_AUDIO:
                            case HM_Files_FilesModel::FILETYPE_VIDEO:
                                $this->view->content = call_user_func(array($this, 'fileTypeWrapper' . HM_Files_FilesModel::getFileType($resource->filename)), $resource, $revisionId);
                                break;

                            case HM_Files_FilesModel::FILETYPE_IMAGE:
                                $source = array(
                                    'module'      => 'resource',
                                    'controller'  => 'index',
                                    'action'      => 'data',
                                    'resource_id' => $resource->resource_id,
                                    'revision_id' => $revisionId,
                                );

                                $image = '<img';
                                $image.= ' src="' . $this->view->url($source, null, true) . '"';
                                $image.= ' title="' . $resource->title . '"';
                                $image.= ' alt="' . $resource->title . '"';
                                $image.= '/>';

                                $this->view->content = $image;
                                break;

                            case HM_Files_FilesModel::FILETYPE_FLASH:
                            case HM_Files_FilesModel::FILETYPE_PDF:
                            case HM_Files_FilesModel::FILETYPE_HTML:
                                $this->_redirector->gotoSimple('data', 'index', 'resource', array('resource_id' => $resourceId, 'revision_id' => $revisionId));
                                break;

                            default:
                                $this->view->content = $this->renderCard($resource);
                        }
                    }else{
                        $this->view->iframe_download = true;
                        $this->view->overlay = true;
                        $this->view->filelist =  $resource->getFilesList($revisionId);
                        $this->view->content =  $this->renderCard($resource);
                    }
                    // XXX: url к ресурсу для скачивания
                    $this->view->url     = $this->view->url( array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id, 'disposition' => 'attachment') );
                    break;
            }
        } else {
            $this->view->content = _('Файл не найден');
            $this->getResponse()->setHttpResponseCode(404);
        }
    }

    public function dataAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $resourceId = (int) $this->_getParam('resource_id', 0);
        $revisionId = (int) $this->_getParam('revision_id', 0);
        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
        $disposition = $this->_getParam('disposition', 'inline');

        if($resource){
            // XXX: this path must be valid UTF-8 string
            if ($revisionId) {
                $fileName = Zend_Registry::get('config')->path->upload->resource.'/revision/' . $revisionId;
            } else {
                $fileName = Zend_Registry::get('config')->path->upload->resource.'/'.$resource->resource_id;
            }
            // And this one convert manually
            $originalFileName = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $resource->filename);

            $sendSuccess = $this->_helper->SendFile(
                $fileName,
                HM_Files_FilesModel::getMimeType($resource->filename),
                array( // options
                    'disposition' => $disposition,
                    'filename'    => $originalFileName
                )
            );
            if($sendSuccess) die();
        }

        $this->getResponse()->setHttpResponseCode(404);
        die();
    }

    protected function renderCard($resource) {
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		if($lng == 'eng') {
			return $this->view->content = $this->view->card(
				$resource,
				array(
					'description_translation' => _('Краткое описание')
				),
				array(
					'title' => _('Карточка информационного ресурса')
				)
			);			
		}
		
        return $this->view->content =  $this->view->card(
            $resource,
            array(
				//'getLinkTitle()' => _('Название'),
                'description'    => _('Краткое описание'),
                //'description_translation'    => _('Перевод (en)'),
                //'getFilesList()' => _('Файлы')
                //'keywords'       => _('Ключевые слова'),
            ),
            array(
                'title' => _('Карточка информационного ресурса')
            )
        );
    }
    // Video
    protected function fileTypeWrapper5($resource, $revisionId = null)
    {
        $url = $this->view->url(array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id, 'revision_id' => $revisionId), null, true);
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mediaelement/mediaelement-and-player.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/mediaelement/mediaelementplayer.css'));
        $this->view->inlineScript()->appendScript("$('video').mediaelementplayer();");

        if(!isset($resource->height, $resource->width)){
            $resource->width = self::DEFAULT_VIDEO_WIDTH;
            $resource->height = self::DEFAULT_VIDEO_HEIGHT;
        }
        elseif($resource->width > self::MAX_VIDEO_WIDTH || $resource->height > self::MAX_VIDEO_HEIGHT){
            $resource->width = self::MAX_VIDEO_WIDTH;
            $resource->height = self::MAX_VIDEO_HEIGHT;
        }

        return '<video width="'.$resource->width
                .'" height="'.$resource->height
                .'" controls><source src="'.$this->view->escape($this->view->serverUrl($url))
                .'" type="'.$this->view->escape(HM_Files_FilesModel::getMimeType($resource->filename))
                .'"></video>';
    }
    //Audio
    protected function fileTypeWrapper4($resource, $revisionId = null)
    {
        $url = $this->view->url(array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id, 'revision_id' => $revisionId), null, true);
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mediaelement/mediaelement-and-player.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/mediaelement/mediaelementplayer.css'));
        $this->view->inlineScript()->appendScript("$('audio').mediaelementplayer();");
        return '<audio controls><source src="'.$this->view->escape($this->view->serverUrl($url)).'" type="'.$this->view->escape(HM_Files_FilesModel::getMimeType($resource->filename)).'"></audio>';
    }




    public function editAction()
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);

        $form = new HM_Form_Resource();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $data = $form->getSubForm('resourceStep1')->getNonClassifierValues();
                unset($data['tags']);
                if($data['type'] == null){
                    unset($data['type']);
                }

                $resource = $this->getService('Resource')->update($data);

                if ($tags = $form->getParam('tags', array())) {
                    $this->getService('Tag')->update($tags, $resource->resource_id, $this->getService('TagRef')->getResourceType() );
                }

                if ($resource && !$this->_getParam('subject_id', 0)) {
                    $this->getService('Resource')->linkClassifiers($resource->resource_id, $form->getSubForm('resourceStep1')->getClassifierValues());
                }

                $this->_flashMessenger->addMessage(_('Ресурс успешно изменён'));
                //$this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId));

                // если тип сменился с карточки на что-то другое
                if ($this->_resource && ($resource->type != HM_Resource_ResourceModel::TYPE_CARD) && ($this->_resource->type == HM_Resource_ResourceModel::TYPE_CARD)) {
                    $this->_redirector->gotoSimple('edit-content', 'index', 'resource', array('resource_id' => $resourceId));
                } else {
                    $this->_redirectToIndex($resourceId);
                }
            }
        } else {
            $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
            if ($resource) {
                $data = $resource->getValues();
                $data['related_resources'] = $this->getService('Resource')->setDefaultRelatedResources($data['related_resources']);
                $form->setDefaults($data);
            }
        }
        $this->view->form = $form;
    }

    public function editContentAction()
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->findDependence(array('DependentResource'), $resourceId));
        $this->view->subjectId = (int) $this->_getParam('subject_id', 0);

        if ($this->_resource && ($this->_resource->type == HM_Resource_ResourceModel::TYPE_CARD)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Невозможно редактировать содержимое данного ресурса. Измените его тип и повторите попытку.')
            ));
            $this->_redirector->gotoSimple('edit', 'index', 'resource', array('resource_id' => $resourceId));
        }

        $form = new HM_Form_Resource();
        $request = $this->getRequest();
        
        $populatedFiles  = $populateFromResources = array();
		
        if ($file = $form->getSubForm('resourceStep2')->getElement('file')) {
            $populateFromResources = count($resource->dependentResources) ? $resource->dependentResources : array($resource);
            foreach ($populateFromResources as $populateFromResource) { 
                if ($populateFromResource->parent_revision_id) continue; // нужно отсечь псевдо-ресурсы от неактуальных ревизий
                if (!empty($populateFromResource->filename) && file_exists($path = realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$populateFromResource->resource_id)) {
                    
                    // массив файлов для populate формы; может состоять из одного родного файла или нескольких файлов от псевдо-ресурсов;  
                    $populatedFiles[] = new HM_File_FileModel(array(
                        'id' => $populateFromResource->resource_id,
                        'displayName' => $populateFromResource->filename,
                        'path' => $path,
                        'url' => $this->view->url(array('module' => 'file', 'controller' => 'get', 'action' => 'resource', 'resource_id' => $populateFromResource->resource_id)),
                    ));
                }        
            }        
        }

        /* if ($file_translation = $form->getSubForm('resourceStep2')->getElement('file_translation')) {
            $populateFromResources = count($resource->dependentResources) ? $resource->dependentResources : array($resource);
            foreach ($populateFromResources as $populateFromResource) { 
                if ($populateFromResource->parent_revision_id) continue; // нужно отсечь псевдо-ресурсы от неактуальных ревизий
                if (!empty($populateFromResource->translation_filename) && file_exists($path = realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$populateFromResource->resource_id)) {
                    $populatedTranslateFiles[] = new HM_File_FileModel(array(
                        'id' => $populateFromResource->resource_id,
                        'displayName' => $populateFromResource->translation_filename,
                        'path' => $path,
                        'url' => $this->view->url(array('module' => 'file', 'controller' => 'get', 'action' => 'resource', 'resource_id' => $populateFromResource->resource_id)),
                    ));
                }        
            }        
        }		*/
		
                
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                if ($this->_getParam('saveAsRevision')) {
                    $this->getService('ResourceRevision')->insert($resourceId);
                } else {
                    // если были файлы в текущей ревизии не нужно удалять! только $deletedDependentResources
//                     $this->getService('Resource')->deleteBy(array(
//                         'parent_id = ?' => $resourceId,
//                         'parent_revision_id = ?' => 0,
//                     ));
                }

                $data = array('resource_id' => $form->getValue('resource_id'));
                if ($content = $form->getValue('content')) {
                    $data['content'] = $content;
                }
                if ($content_translation = $form->getValue('content_translation')) {
                    $data['content_translation'] = $content_translation;
                }				
                if ($url = $form->getValue('url')) {
                    $data['url'] = $url;
                }
                $resource = $this->getService('Resource')->update($data);

                $file = $form->getSubForm('resourceStep2')->getElement('file');
                // $file_translation = $form->getSubForm('resourceStep2')->getElement('file_translation');
				
				
                if ($resource && $file && ($file->isUploaded() || count($populatedFiles))) {

                    if ($file->isUploaded()) $file->receive();

                    if (!$this->_getParam('saveAsRevision')) {
                        // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
                        if (count($deletedDependentResources = $file->updatePopulated($populatedFiles))) {
                            foreach ($populatedFiles as $key => &$populatedFile) {
                                if (in_array($populatedFile->getId(), array_keys($deletedDependentResources))) {
                                    unset($populatedFiles[$key]);
                                }
                            }
                            // и удалить псевдо-ресурсы, привязанные к этим файлам
                            $this->getService('Resource')->deleteBy(array(
                                'parent_id = ?' => $resourceId,
                                'parent_revision_id = ?' => 0,
                                'resource_id IN (?)' => array_keys($deletedDependentResources),
                            ));
                        }
                    } else {
                        // в этом случае НЕ нужно удалять зависимые псевдо-ресурсы
                        // они снова понядобятся если ревизию восстановят
                        // их файлы НЕ будут появляться в форме редактирования, т.к. if ($populateFromResource->parent_revision_id) continue; (см.выше)

                        $unsetDependentResources = $file->updatePopulated($populatedFiles, false); // do not unlink   
                        foreach ($populatedFiles as $key => &$populatedFile) {
                            if (in_array($populatedFile->getId(), array_keys($unsetDependentResources))) {
                                unset($populatedFiles[$key]);
                            }
                        }
                    }

                    if ($file->isReceived() || count($populatedFiles)) {
                        
                        if ($file->isReceived()) $filename = $file->getFileName();
                        if ((count($filename) + count($populatedFiles)) > 1) {
                            $filename = $this->getService('Resource')->prepareMultipleFiles($resource, $file, $populatedFiles);
                            $count = $this->getService('Resource')->updateDependentResources($resource, $file, $this->_getParam('saveAsRevision') ? $populatedFiles : array());
                            $resource->volume = HM_Files_FilesModel::toByteString(filesize($filename));
                        } else {
                            if ($file->isReceived()) {
                                $resource->volume = $file->getFileSize();
                            } else {
                                $populatedFile = array_shift($populatedFiles);
                                $filename = $populatedFile->getPath();
                            }
                        }

                        if ($filename) {
                            $resource->filename = basename($filename);
                            $resource->filetype = HM_Files_FilesModel::getFileType($resource->filename);
    
                            $filter = new Zend_Filter_File_Rename(
                                array(
                                    'source' => $filename,
                                    'target' => realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id,
                                    'overwrite' => true
                                )
                            );
                            if ($filter->filter($filename)) {
                                $this->getService('Resource')->update($resource->getValues());
                            }
                        }
                    }
                }
				
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                /* if ($resource && $file_translation && ($file_translation->isUploaded() || count($populatedTranslateFiles))) {

                    if ($file_translation->isUploaded()) $file_translation->receive();

                    if (!$this->_getParam('saveAsRevision')) {
                        // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
                        if (count($deletedDependentResources = $file->updatePopulated($populatedTranslateFiles))) {
                            foreach ($populatedTranslateFiles as $key => &$populatedTranslateFile) {
                                if (in_array($populatedTranslateFile->getId(), array_keys($deletedDependentResources))) {
                                    unset($populatedTranslateFiles[$key]);
                                }
                            }
                            // и удалить псевдо-ресурсы, привязанные к этим файлам
                            $this->getService('Resource')->deleteBy(array(
                                'parent_id = ?' => $resourceId,
                                'parent_revision_id = ?' => 0,
                                'resource_id IN (?)' => array_keys($deletedDependentResources),
                            ));
                        }
                    } else {
                        $unsetDependentResources = $file->updatePopulated($populatedTranslateFiles, false); // do not unlink   
                        foreach ($populatedTranslateFiles as $key => &$populatedTranslateFile) {
                            if (in_array($populatedTranslateFile->getId(), array_keys($unsetDependentResources))) {
                                unset($populatedTranslateFiles[$key]);
                            }
                        }
                    }

                    if ($file_translation->isReceived() || count($populatedTranslateFiles)) {
                        
                        if ($file_translation->isReceived()) $translation_filename = $file_translation->getFileName();
						
                        if ((count($translation_filename) + count($populatedTranslateFiles)) > 1) {
                            $translation_filename = $this->getService('Resource')->prepareMultipleFiles($resource, $file_translation, $populatedTranslateFiles);
                            $count = $this->getService('Resource')->updateDependentResources($resource, $file_translation, $this->_getParam('saveAsRevision') ? $populatedTranslateFiles : array());
                            $resource->volume = HM_Files_FilesModel::toByteString(filesize($translation_filename));
                        } else {
                            if ($file_translation->isReceived()) {
                                $resource->volume = $file_translation->getFileSize();
                            } else {
                                $populatedTranslateFile = array_shift($populatedTranslateFiles);
                                $translation_filename = $populatedTranslateFile->getPath();
                            }
                        }

                        if ($translation_filename) {
                            $resource->translation_filename = basename($translation_filename);
                            $resource->filetype = HM_Files_FilesModel::getFileType($resource->translation_filename);
    
                            $filter = new Zend_Filter_File_Rename(
                                array(
                                    'source' => $translation_filename,
                                    'target' => realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id,
                                    'overwrite' => true
                                )
                            );
                            if ($filter->filter($translation_filename)) {
                                $this->getService('Resource')->update($resource->getValues());
                            }
                        }
                    }
                }		*/		
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++				

                $file = $form->getSubForm('resourceStep2')->getElement('filezip');
                if ($resource && $file && $file->isUploaded()) {
                    $file->receive();
                    $oldUmask = umask(0);
                    $resoursePath = realpath(Zend_Registry::get('config')->path->upload->public_resource);
                    $target = $resoursePath . '/' . $resource->resource_id . '/';

                    if ( !is_dir($target)) {
                        mkdir($target, 0755);
                    }

                    $filter = new Zend_Filter_Decompress( array('adapter' => 'Zip', 'options' => array('target' => $target)));
                    $filter -> filter($file->getFileName());

                    if ( file_exists( $resoursePath . '/zip/' . basename($file->getFileName()))) {
                        unlink( $resoursePath . '/zip/' . basename($file->getFileName()) );
                    }

                    umask($oldUmask);
                }
                $this->_flashMessenger->addMessage(_('Содержимое ресурса успешно изменено'));
                //$this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId));
                $this->_redirectToIndex($resourceId);

            }
        } else {
            if ($resource && $resource->type == HM_Resource_ResourceModel::TYPE_ACTIVITY) {
                $this->_flashMessenger->addMessage(_('Невозможно редактировать содержимое ресурсов на основе сервисов взаимодействия'));
                $this->_redirectToIndex($resourceId);
            }
            if ($resource && $resource->type != HM_Resource_ResourceModel::TYPE_FILESET) {
                $form->setDefaults(
                    $resource->getValues()
                );
            }
            if ($file = $form->getSubForm('resourceStep2')->getElement('file')) {
                $file->setValue($populatedFiles);
            }            
        }

        $this->view->form = $form;
    }

    public function cardAction()
    {
    /*    $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
        $this->view->disableExtendedFile();
      */
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
        $this->view->isAjaxRequest = $this->isAjaxRequest();
        $this->view->resource = $resource;
    }

    // этому методу место в listController, но там ненужные действия в init() => не работает как надо
    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Resource')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $defaults['description'] = '';
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($resource = $this->getService('Resource')->insert($defaults)) {

                if ($this->getService('SubjectResource')->insert(array('subject_id' => $subjectId, 'resource_id' => $resource->resource_id))) {
    				$this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                    $result = $resource->resource_id;

                    $section = $this->getService('Section')->getDefaultSection($subjectId);
                    $currentOrder = $this->getService('Section')->getCurrentOrder($section);
                    $this->getService('Resource')->createLesson($subjectId, $resource->resource_id, $section, ++$currentOrder);
                }
            }
        }
        exit(Zend_Json::encode($result));
    }

    public function resourcesListAction()
    {
        $res = array();
        $searchStr = $this->_getParam('tag');
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }

        if (strpos($searchStr, '#') !== false) {
        //if ($searchStr = (int)$searchStr) {
            $resourceId = (int)trim(str_replace('#','', $searchStr));
            $resources = $this->getService('Resource')->find($resourceId);
        } else {
            $resources = $this->getService('Resource')->fetchAll(array(
                'title LIKE ?' => '%' . trim($searchStr) . '%',
                'parent_id = ?' => 0,
            ));
        }

        foreach ($resources as $resource) {

            if ($resource->subject_id) continue;

            $o = new stdClass();
            $o->key = sprintf('#%s: %s', $resource->resource_id, $resource->title);
            $o->value = $resource->resource_id;
            $res[] = $o;
        }

        header('Content-type: application/json; charset=UTF-8');
        exit(Zend_Json::encode($res));
    }

    public function restoreAction()
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $revisionId = (int) $this->_getParam('revision_id', 0);

        if ($resourceId && $revisionId) {
            $this->getService('ResourceRevision')->insert($resourceId);
            if ($resource = $this->getService('ResourceRevision')->restore($revisionId)) {
                 $resourceId = $resource->resource_id;
                 $this->_flashMessenger->addMessage(_('Версия успешно восстановлена'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('В процессе восстановления версии произошли ошибки')
                ));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId));
    }
    
    public function deleteRevisionAction()
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $revisionId = (int) $this->_getParam('revision_id', 0);

        if ($resourceId && $revisionId) {
            if ($this->getService('ResourceRevision')->delete($revisionId)) {
                 $this->_flashMessenger->addMessage(_('Версия успешно удалена'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('В процессе удаление версии произошли ошибки')
                ));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'resource', array('resource_id' => $resourceId));
    }    
}