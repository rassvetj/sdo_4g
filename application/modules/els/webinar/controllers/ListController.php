<?php

class Webinar_ListController extends HM_Controller_Action_Crud
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;


    const ERROR_COULD_NOT_CREATE_WEBINAR = 'could_not_create_webinar';
    const ERROR_COULD_NOT_CREATE_FILE    = 'could_not_create_file';
    const ERROR_MORE_THAN_ONE_PPT        = 'more_than_one_ppt';
    const ERROR_COULD_NOT_CONNECT        = 'could_not_connect';

    public function init()
    {
        $this->_setForm(new HM_Form_Webinar());
        parent::init();

        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('upload-file', 'json')->initContext('json');

        /*if($this->_request->getActionName() == 'edit'){
            $form = $this->_form;

            $form->removeElement('files');

        }*/


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

    public function _redirectToIndex(){

        $this->_redirector->gotoSimple('index', null, null, array($this->idParamName => $this->id));

    }

    public function indexAction()
    {

        $subjectId = $this->_getParam('subject_id', 0);

        // пока придется прямо тут апдейтить
        // $this->getService('Ppt2Swf')->requestUpdate();


        $select = $this->getService('Webinar')->getSelect();

        // Не забыдь поменять потом files на другую таблицу, какую там нааутсортят
        $select->from(array('w' => 'webinars'), array('webinar_id', 'name'))
               ->joinLeft(array('wf' => 'webinar_files'), 'wf.webinar_id = w.webinar_id', array())
               ->joinLeft(array('f' => 'files'), 'wf.file_id = f.file_id', array('file_names' => new Zend_Db_Expr('GROUP_CONCAT(f.name)')))
               ->joinLeft(array('p' => 'ppt2swf'), 'w.webinar_id = p.webinar_id', array('status', 'process', 'w.create_date'))
               ->where('w.subject_id = ?', $subjectId)
               ->group(array('w.webinar_id', 'w.name', 'w.create_date', 'p.status', 'p.process'))
               ->order(array('webinar_id DESC'));


         $grid = $this->getGrid(
            $select,
            array(
                'webinar_id' => array('hidden' => true),
                'process' => array('hidden' => true),
                'name' => array('title' => _('Название')),
            	'file_names' => array('title' => _('Список файлов')),
                'status' => array('title' => _('Статус конвертации')),
                'create_date' => array('title' => _('Дата создания'))
            ),
            array(
                'name' => null,
                'create_date' => array(
                    'render' => 'date',
                    array(
                        'transform' => 'dateChanger'
                    )
                )
            )
        );


        $grid->updateColumn('status', array(
            'callback' => array(
                'function' => array($this,
                    'updateStatus'),
                'params' => array(
                    '{{status}}', '{{process}}')))
        );


        $grid->updateColumn('file_names', array(
            'callback' => array(
                'function' => array($this,
                    'updateFiles'),
                'params' => array(
                    '{{file_names}}')))
        );

        $grid->updateColumn('name', array(
            'callback' => array(
                'function' => array($this,
                    				'updateName'
                              ),
                'params' => array(
                    '{{name}}','{{webinar_id}}', $subjectId)))
        );

        $grid->updateColumn('create_date',
            array(
                'format' => 'DateTime'
            )
        );


        $grid->addAction(
            array('module' => 'webinar', 'controller' => 'list', 'action' => 'edit'),
            array('webinar_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'webinar', 'controller' => 'list', 'action' => 'delete'),
            array('webinar_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('module' => 'webinar', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы действительно хотите удалить материалы вебинара?')
        );




        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();


    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Материалы вебинара успешно созданы'),
            self::ACTION_UPDATE    => _('Материалы вебинара успешно обновлены'),
            self::ACTION_DELETE    => _('Материалы вебинара успешно удалены'),
            self::ACTION_DELETE_BY => _('Материалы вебинара успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE_WEBINAR => _('Невозможно создать материалы вебинара'),
            self::ERROR_COULD_NOT_CREATE_FILE    => _('Некоторые файлы не были загружены'),
            self::ERROR_MORE_THAN_ONE_PPT        => _('Загружено более одной презентации'),
            self::ERROR_COULD_NOT_CONNECT        => _('Соединение с сервером невозможно. Попробуйте позже')
        );

    }

    public function create(Zend_Form $form)
    {

        $subjectId = $this->_getParam('subject_id', 0);
        $this->paramId = $subjectId;

        $date = new Zend_Date();

        $imgArray = HM_Webinar_Files_FilesModel::getImgExtensions();

        $webinar = $this->getService('Webinar')->insert(
            array(
                'name'        => $form->getValue('name'),
                'subject_id'  => $subjectId,
                'create_date' => $date->toString('yyyy-MM-dd H:m')
            )
        );

        if(!$webinar){
            return self::ERROR_COULD_NOT_CREATE_WEBINAR;
        }

        if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){

            $files = $form->files->getFileName();
            if (is_string($files)) {
                $files = array($files);
            }
            //$adapter = $form->files->getTransferAdapter();

            //$dest = realpath(APPLICATION_PATH . '/../public/upload/files/');


            // Чтобы не грузили несколько ppt или
            // другие файлы месте с ppt
            $fileCount = 0;
            $boolPpt = false;
            foreach($files as $file){
                $fileName = basename(iconv('UTF-8', 'CP1251', $file));
                $temp = explode('.', $fileName);
                $ext = $temp[count($temp) - 1];

                if($ext == 'ppt'){
                    $boolPpt = true;
                }
                $fileCount++;
            }

            if($fileCount > 1 && $boolPpt === true){
                return self::ERROR_MORE_THAN_ONE_PPT;
            }

            foreach($files as $file)
            {
                //if ($adapter->receive($file['name']))
                //{
                    $fileName = basename(iconv('UTF-8', 'CP1251', $file));
                    $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);

                    $temp = explode('.', $fileName);
                    $ext = $temp[count($temp) - 1];

                    $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                    // Здесь делаем специфические операции для файлов разных типов
                    if(in_array($ext, $imgArray)){
                        // Изменение расрешения у изображений
                        $img = PhpThumb_Factory::create($filePath);
                        $img->resize(HM_Webinar_WebinarModel::MATERIAL_WIDTH, HM_Webinar_WebinarModel::MATERIAL_HEIGHT);
                        $img->save($filePath);
                    }elseif($ext == 'ppt'){
                        // Отправка ppt на конвертацию
                        // параметры
                        $arraySetup = array('Composition' => 1);

                        $res = $this->getService('Ppt2Swf')->sendRequest(realpath($filePath), $webinar->webinar_id, $arraySetup);
                        if($res === false){
                            return self::ERROR_COULD_NOT_CONNECT;
                        }elseif($res !== true){
                            return HM_Ppt2swf_Errors::getMessage((string)$res);
                        }
                    }

                    $this->getService('Files')->update(
                        array(
                            'file_id' => $fileData->file_id,
                            'path'	  => realpath($filePath),
                            'file_size'    => filesize($filePath)
                        )
                    );

                    // ppt добавлять в список файлов вебинара не нужно
                    if($ext != 'ppt'){
                        $this->getService('WebinarFiles')->insert(
                            array(
                                'webinar_id' => $webinar->webinar_id,
                                'file_id'    => $fileData->file_id
                            )
                        );
                    }


                //}else{
                //    $this->getService('Files')->delete($fileData->file_id); // WTF ???
                //}
            }

        }




    }


    public function updateStatus($status, $process)
    {
        if($status == "" || $status == HM_Ppt2swf_Ppt2swfModel::STATUS_READY){
            return _('Готово');
        }
        if($process != 0){
            return _('В процессе').'(' . $process . '%)';
        }
        return _('Ожидание');
    }


    public function updateFiles($files, $separator = ', ')
    {

        $files = explode(',', $files);

        natsort($files);


        return implode($separator, $files);


    }


    public function deleteAction()
    {
        $subjectId = $this->_getParam('subject_id', 0);
        $webinarId = $this->_getParam('webinar_id', 0);

        $this->delete($webinarId);

        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    /**
      *удаление youtube ролика из файлов вебинара
      */
    public function deleteYoutubeClipAction(){
        
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        $item=$this->getRequest()->getPost();
        $webinarId = $item['webinar_id'];
        if (strpos($webinarId, 'webinar_') === false) {
            $lesson = $this->getOne($this->getService('Lesson')->find($webinarId));
            if ($lesson) {
                $webinarId = $lesson->getModuleId();
            }
            else {
                $webinarId = 0;
            }
        } else {
            $webinarId = intval(str_replace('webinar_', '', $webinarId));
        }

        $files = $this->getService('WebinarFiles')->getFiles($webinarId);
        foreach ($files as $file) {
            if ($file->file_id == $item['file_id']) {
                $this->getService('Files')->deleteBy(array('file_id = ?' => $file->file_id));
                break;
            }
        }


    }

    public function deletefileAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
    
        //$fileName = $this->_getParam('filename', '');
        $filename = $_POST['filename'];
        $webinarId = $this->_getParam('webinar_id', 0);
        if (strpos($webinarId, 'webinar_') === false) {
            $lesson = $this->getOne($this->getService('Lesson')->find($webinarId));
            if ($lesson) {
                $webinarId = $lesson->getModuleId();
            }
            else {
                $webinarId = 0;
            }
        } else {
            $webinarId = intval(str_replace('webinar_', '', $webinarId));
        }
        
        $deleted = false;
        
        $files = $this->getService('WebinarFiles')->getFiles($webinarId);
        foreach ($files as $file) {
            if (basename($file->path) == basename($filename)) {
                $this->getService('Files')->deleteBy(array('file_id = ?' => $file->file_id));
                @unlink($file->path);
                $deleted = true;
                break;
            }
        }
        Zend_Registry::get('log_system')->log("Delete File (webinar={$webinarId}): {$_POST['filename']} - " . ($deleted ? "TRUE" : "FALSE"), Zend_Log::INFO);
        exit(Zend_Json::encode(array("error" => $deleted ? 0 : 1, "msg" => $deleted ? "" : "файл не найден")));
    }

    public function delete($id)
    {
        $this->getService('Webinar')->delete($id);
    }


    public function update(Zend_Form $form)
    {
        $subjectId = $this->_getParam('subject_id', 0);
        $this->paramId = $subjectId;
        $webinar = $this->getService('Webinar')->update(
            array(
                'webinar_id' => $form->getValue('webinar_id'),
                'name'       => $form->getValue('name'),
            )
        );
        
        if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){

            $files = $form->files->getFileName();
            if (is_string($files)) {
                $files = array($files);
            }
            //$adapter = $form->files->getTransferAdapter();

            //$dest = realpath(APPLICATION_PATH . '/../public/upload/files/');


            // Чтобы не грузили несколько ppt или
            // другие файлы месте с ppt
            $fileCount = 0;
            $boolPpt = false;
            foreach($files as $file){
                $fileName = basename(iconv('UTF-8', 'CP1251', $file));
                $temp = explode('.', $fileName);
                $ext = $temp[count($temp) - 1];

                if($ext == 'ppt'){
                    $boolPpt = true;
                }
                $fileCount++;
            }

            if($fileCount > 1 && $boolPpt === true){
                return self::ERROR_MORE_THAN_ONE_PPT;
            }

            foreach($files as $file)
            {
                //if ($adapter->receive($file['name']))
                //{
                $fileName = basename(iconv('UTF-8', 'CP1251', $file));
                $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);

                $temp = explode('.', $fileName);
                $ext = $temp[count($temp) - 1];

                $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                // Здесь делаем специфические операции для файлов разных типов
                if(in_array($ext, $imgArray)){
                    // Изменение расрешения у изображений
                    $img = PhpThumb_Factory::create($filePath);
                    $img->resize(HM_Webinar_WebinarModel::MATERIAL_WIDTH, HM_Webinar_WebinarModel::MATERIAL_HEIGHT);
                    $img->save($filePath);
                }elseif($ext == 'ppt'){
                    // Отправка ppt на конвертацию
                    // параметры
                    $arraySetup = array('Composition' => 1);

                    $res = $this->getService('Ppt2Swf')->sendRequest(realpath($filePath), $webinar->webinar_id, $arraySetup);
                    if($res === false){
                        return self::ERROR_COULD_NOT_CONNECT;
                    }elseif($res !== true){
                        return HM_Ppt2swf_Errors::getMessage((string)$res);
                    }
                }

                $this->getService('Files')->update(
                    array(
                        'file_id' => $fileData->file_id,
                        'path'	  => realpath($filePath),
                        'file_size'    => filesize($filePath)
                    )
                );

                // ppt добавлять в список файлов вебинара не нужно
                if($ext != 'ppt'){
                    $this->getService('WebinarFiles')->insert(
                        array(
                            'webinar_id' => $webinar->webinar_id,
                            'file_id'    => $fileData->file_id
                        )
                    );
                }
            }

        }
    }

    public function setDefaults(Zend_Form $form)
    {

        $webinarId = ( int ) $this->_request->getParam('webinar_id', 0);

        $webinar = $this->getService('Webinar')->getOne($this->getService('Webinar')->find($webinarId));
        if ($webinar)
        {
            $form->populate($webinar->getValues());
            //$form->setDefaults($subject->getValues());
        }
    }


    public function updateName($name, $webinarId, $subjectId){


        return '<a href="' . $this->view->url(array('action' => 'preview', 'module' => 'webinar', 'controller' => 'index', 'subject_id' => $subjectId, 'webinar_id' => $webinarId))
                .  '">' . $name . '</a>';
    }
        /**
         *добавление youtube ролика из файлов вебинара. запрос от webinar.swf. method POST
         *передаются флешкой parameters: webinar_id, name, path - префикс youtube_+ссылка
         */
    public function addYoutubeClipAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        $item=$this->getRequest()->getPost();
        $webinarId = $item['webinar_id'];
        if(strpos($webinarId, 'webinar_') === 0){
            $webinarId = (int) str_replace('webinar_', '', $webinarId);
        } else {
            $lesson = $this->getOne($this->getService('Lesson')->find($webinarId));
            if ($lesson) {
                $webinarId = $lesson->getModuleId();
            }
        }
        $fileData=$this->getService('Files')->addClip($item['path'], $item['name']);
          $this->getService('WebinarFiles')->insert(
            array(
                'webinar_id' => $webinarId,
                'file_id'    => $fileData->file_id
            )
        );
    }
    public function uploadFileAction()
    {
        try {

            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

            $form = new HM_Form_UploadFile();

            $imgArray = HM_Webinar_Files_FilesModel::getImgExtensions();

            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()) {

                    $ret = array('error' => 0, 'msg' => _('Файл успешно загружен.'));

                    $files = $form->files->getFileName();
                    if (is_string($files)) {
                        $files = array($files);
                    }

                    $webinarId = $form->getValue('webinar_id');
                    if(strpos($webinarId, 'webinar_') === 0){
                        $webinarId = (int) str_replace('webinar_', '', $webinarId);
                    } else {
                        $lesson = $this->getOne($this->getService('Lesson')->find($webinarId));
                        if ($lesson) {
                            $webinarId = $lesson->getModuleId();
                        }
                    }

                    $webinarId = (int) $webinarId;

                    foreach($files as $file) {
                        $fileName = basename($file);
                        $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);

                        $temp = explode('.', $fileName);
                        $ext = $temp[count($temp) - 1];

                        $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                        // Здесь делаем специфические операции для файлов разных типов
                        if(in_array($ext, $imgArray)) {
                            // Изменение расрешения у изображений
                            $img = PhpThumb_Factory::create($filePath);
                            $img->resize(HM_Webinar_WebinarModel::MATERIAL_WIDTH, HM_Webinar_WebinarModel::MATERIAL_HEIGHT);
                            $img->save($filePath);
                        }elseif($ext == 'ppt'){
                            // Отправка ppt на конвертацию
                            // параметры
                            $arraySetup = array('Composition' => 1);

                            $res = $this->getService('Ppt2Swf')->sendRequest(realpath($filePath), $webinarId, $arraySetup);
                            if($res === false){
                                throw new HM_Exception(_('Нет соединения с сервером конвертации PPT -> SWF'));
                            } elseif($res !== true){
                                throw new HM_Exception(HM_Ppt2swf_Errors::getMessage((string)$res));
                            }
                        }

                        $this->getService('Files')->update(
                            array(
                                'file_id' => $fileData->file_id,
                                'path'	  => realpath($filePath),
                                'file_size'    => filesize($filePath)
                            )
                        );

                        // ppt добавлять в список файлов вебинара не нужно
                        if($ext != 'ppt'){
                            $this->getService('WebinarFiles')->insert(
                                array(
                                    'webinar_id' => $webinarId,
                                    'file_id'    => $fileData->file_id
                                )
                            );
                        }
                    }
                } else {
                    throw new HM_Exception(_('Ошибка загрузки файла'));
                }
            } else {
                throw new HM_Exception(_('Файл не найден'));
            }
        } catch(Exception $e) {
            $ret = array('error' => 1, 'msg' => $e->getMessage());
        }

        $this->view->assign($ret);
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Webinar')->getDefaults();
        $defaults['name'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['name']) && $subjectId) {
            if ($webinar = $this->getService('Webinar')->insert($defaults)) {

				$this->getService('Subject')->update(array(
                    'last_updated' => $this->getService('Subject')->getDateTime(),
                    'subid' => $subjectId
                ));
                $result = $webinar->webinar_id;
            }
        }
        exit(Zend_Json::encode($result));
    }

}