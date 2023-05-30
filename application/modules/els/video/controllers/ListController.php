<?php
class Video_ListController extends HM_Controller_Action
{
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

	public function getVideoAction()
	{
        //$this->_helper->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender();
        $html='<p>'._('Файл не найден').'</p>';
        $file_id = $this->_getParam('screencast',null);
        $filepath = $this->getService('FilesVideoblock')->getfilepath($file_id);
        $mediaelementPath=$this->view->serverUrl()."/js/tiny_mce/plugins/media/moxieplayer.swf";
        if (file_exists($filepath)){
            $filepath=$this->view->serverUrl().'/upload/files/'.basename($filepath);
            $html='<object width="320" height="240" type="application/x-shockwave-flash" data="'.$mediaelementPath.'">
                <param value="'.$mediaelementPath.'" name="src">
                <param value="url='.$filepath.'&amp;autoplay=1" name="flashvars">
                <param value="true" name="allowfullscreen">
                <param value="true" name="allowscriptaccess">

                </object>';
        }
        $this->view->html= $html;
    }

    public function indexAction(){
        $this->view->setHeader(_('Видеоролики'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl()."/css/content-modules/schedule_table.css");
        $select=$this->getService('FilesVideoblock')->getSelect();
        $select->from(array('v'=>'videoblock'), array())
            ->joinLeft(array('f' => 'files'),
                'v.file_id = f.file_id',array(
                    'file_id' => 'v.file_id',
                    'file_size' => 'f.file_size',
                    'name' => 'v.name'));
        $grid=$this->getGrid($select,
            array(
                'file_id' => array('hidden' => true),
                'name' => array('title' => _('Название')),
                'file_size' => array('title' => _('Размер')),
            ),
            array(
                'name' => null
            )
        );
        $grid->addAction(array(
                'module' => 'video',
                'controller' => 'list',
                'action' => 'edit'
            ),
            array('file_id'),
            $this->view->icon('edit')
        );
        $grid->addAction(array(
                'module' => 'video',
                'controller' => 'list',
                'action' => 'delete'
            ),
            array('file_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных видеороликов?'));
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function newAction(){
        $form=new HM_Form_Video();
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            if ($form->file->isUploaded() && $form->file->receive() && $form->file->isReceived()) {

                $ret = array('error' => 0, 'msg' => _('Файл успешно загружен.'));

                $file = $form->file->getFileName();
                $fileName = basename($file);
                $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);

                if($fileData){
                    $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                    $this->getService('Files')->update(
                        array(
                            'file_id' => $fileData->file_id,
                            'path'	  => realpath($filePath),
                            'file_size'    => filesize($filePath)
                        )
                    );
                    $this->getService('FilesVideoblock')->insert(array(
                            'file_id' => $fileData->file_id,
                            'name' => $this->_getParam('Title'))
                    );
                    $this->_flashMessenger->addMessage(_('Видеоролик успешно загружен'));
                }

            } else {
                $this->_flashMessenger->addMessage(_('Ошибка загрузки файла'));
            }
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
        }
        $this->view->form  = $form;
    }

    public function deleteAction(){
        $file_id = (int) $this->_getParam('file_id', 0);
        $file=$this->getService('FilesVideoblock')->getFilePath($file_id);
        if ($file_id!=0) {
            $res = $this->getService('FilesVideoblock')->delete($file_id);
            if($res > 0){
                $this->getService('Files')->delete($file_id);
                unlink($file);
            }
        }
        $this->_flashMessenger->addMessage(_('Видеоролик успешно удален'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
    }

    public function deleteByAction(){
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('FilesVideoblock');
        foreach ($ids as $value) {
            $file=$this->getService('FilesVideoblock')->getFilePath($value);
            $res = $service->delete(intval($value));
            if($res > 0){
                $this->getService('Files')->delete($value);
                unlink($file);
            }
        }
        $this->_flashMessenger->addMessage(_('Видеоролики успешно удалены'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
    }

    public function editAction(){
        $form = new HM_Form_Video();
        $file_id = (int) $this->_getParam('file_id', 0);
        $this->view->setHeader(_('Редактирование видеоролика'));
        $form->removeElement('file');
        $file = $this->getService('FilesVideoblock')->getOne($this->getService('FilesVideoblock')->find($file_id));
        $form->getElement('Title')->setValue($file->name);
        if($this->_request->isPost() && $form->isValid($this->_request->getPost())){
            $file->name = $this->_getParam('Title');
            $res = $this->getService('FilesVideoblock')->update($file->getValues());
            if ($res){
                $this->_flashMessenger->addMessage(_('Видеоролик успешно обновлен'));
                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
            }
        }
        $this->view->form=$form;
    }
}