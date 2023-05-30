<?php

class Task_QuestionController extends HM_Controller_Action_Crud
{

    private $_taskId = 0;
    private $_subjectId = 0;
    private $_task = null;

    public function init()
    {
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        $this->_taskId = (int) $this->_getParam('task_id', 0);
        $this->_task = $this->getOne($this->getService('Task')->find($this->_taskId));

        if (!$this->getService('TestAbstract')->isEditable($this->_task->subject_id, $this->_subjectId, $this->_task->location)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете добавлять варианты в глобальные задания')));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('task_id' => $this->_taskId, 'subject_id' => $this->_subjectId));
        }

        $this->_setForm(new HM_Form_Question());
        parent::init();
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Вариант успешно создан'),
            self::ACTION_UPDATE => _('Вариант успешно обновлён'),
            self::ACTION_DELETE => _('Вариант успешно удалён'),
            self::ACTION_DELETE_BY => _('Варианты успешно удалены')
        );
    }

    public function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('task', 'list', 'question', array('task_id' => $this->_getParam('task_id', 0), 'subject_id' => $this->_getParam('subject_id', 0)));
    }

    public function setDefaults($form)
    {
        $kod = $this->_getParam('kod', '');
        $question = $this->getOne($this->getService('Question')->find($kod));

        if ($question) {
            $form->setDefaults($question->getValues());
        }
    }

    private function _saveFiles($question, Zend_Form $form)
    {

//[che 09.06.2014 #16965]
        $populatedFiles = $this->getService('Question')->getPopulatedFiles($question->kod);

        if (!$this->_getParam('saveAsRevision')) {
            // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
            $deletedFiles = $form->files->updatePopulated($populatedFiles);
            if(count($deletedFiles))
            {
                $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
                $this->getService('QuestionFiles')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
            }
        }
//
        if ($form->files->isUploaded())
        {   if ($form->files->receive() && $form->files->isReceived())
            {   $files = $form->files->getFileName();
                if(count($files) > 1) {
                    foreach($files as $file)
                    {   $fileInfo = pathinfo($file);
                        $file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
                        $this->getService('QuestionFiles')->insert(array('file_id' => $file->file_id, 'kod' => $question->kod));
                    }
                }
                else
                {   $fileInfo = pathinfo($files);
                    $file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
                    $this->getService('QuestionFiles')->insert(array('file_id' => $file->file_id, 'kod' => $question->kod));
                }
            }
        }
		
		# EN
		# lang = 1 - пока условно 1 - это EN.  Позже связать с модлеью или списком из БД.
		if ($form->files_en->isUploaded())
        {   if ($form->files_en->receive() && $form->files_en->isReceived())
            {   $files = $form->files_en->getFileName();
                if(count($files) > 1) {
                    foreach($files as $file)
                    {   $fileInfo = pathinfo($file);
                        $file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
                        $this->getService('QuestionFiles')->insert(array('file_id' => $file->file_id, 'kod' => $question->kod, 'lang' => 1));
                    }
                }
                else
                {   $fileInfo = pathinfo($files);
                    $file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
                    $this->getService('QuestionFiles')->insert(array('file_id' => $file->file_id, 'kod' => $question->kod, 'lang' => 1));
                }
            }
        }
		
		
		
		
    }

    public function create($form)
    {
        $question = $this->getService('Question')->insert(
            array(
                'qtype' => HM_Question_QuestionModel::TYPE_FREE,
                'qdata' => $form->getValue('qdata', ''),
                'qdata_translation' => $form->getValue('qdata_translation', ''),
                'qtema' => $form->getValue('qtema', ''),
                'qtema_translation' => $form->getValue('qtema_translation', ''),
                'qmoder' => 1,
                'balmax' => $form->getValue('balmax', 1),
                'balmin' => $form->getValue('balmin', 0),
                'url' => $form->getValue('url', ''),
                'last' => time(),
                'timetoanswer' => $form->getValue('timetoanswer', 0)
            )
        );

        if ($question && $this->_task) {
            $this->_task->addQuestionsIds(array($question->kod));
            $this->getService('Task')->update($this->_task->getValues('test_id', 'data'));
        }

        if ($question) {
            $this->_saveFiles($question, $form);
        }
    }

    public function update($form)
    {
        $question = $this->getService('Question')->update(
            array(
                'kod' => $form->getValue('kod', ''),
                'qtype' => HM_Question_QuestionModel::TYPE_FREE,
                'qdata' => $form->getValue('qdata', ''),
				'qdata_translation' => $form->getValue('qdata_translation', ''),
                'qtema' => $form->getValue('qtema', ''),
				'qtema_translation' => $form->getValue('qtema_translation', ''),
                'qmoder' => 1,
                'balmax' => $form->getValue('balmax', 1),
                'balmin' => $form->getValue('balmin', 0),
                'url' => $form->getValue('url', ''),
                'last' => time(),
                'timetoanswer' => $form->getValue('timetoanswer', 0)
            )
        );

        if ($question) {
            $this->_saveFiles($question, $form);
        }
    }
    
    public function deleteAction()
    {
        $id = $this->_getParam('kod', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }
    

    public function delete($id)
    {
        $this->getService('Question')->delete($id);
        $this->getService('Interview')->onDeleteQuestion($id);
        if ($this->_taskId) {
            $task = $this->getService('Task')->getOne($this->getService('Task')->find($this->_taskId));
            $data = $task->getValues();
            $data['questions']--;
            $this->getService('Task')->update($data);
        }
    }
	
	public function deleteByAction()
    {		
		$postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                if ($this->_task) {					
					$data = $this->_task->getValues('test_id', 'data');							
				}				
				foreach($ids as $id) {
					if ($id && $this->_task) {						
						$this->_task->removeQuestionsIds(array($id)); 												
					}
                }             
				$data['data'] = $this->_task->data;				
				$this->getService('Task')->update($data);						
            }
        }		
		parent::deleteByAction();
        
    }
}