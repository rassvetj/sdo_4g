<?php
class Question_ExportController extends HM_Controller_Action
{
    public function txtAction()
    {
        $subjectId = (int) $this->_request->getParam('subject_id', 0);

        $testId = (int) $this->_request->getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $ids = $this->_getParam('postMassIds_'.$gridId, '');

        if (strlen($ids)) {
            $questionsIds = explode(',', $ids);
            if (count($questionsIds)) {
                $countProcessed = 0; $txt = '';
                $questions = $this->getService('Question')->fetchAll($this->quoteInto('kod IN (?)', $questionsIds));
                if (count($questions)) {

                    foreach($questions as $question) {
                        if ($question->couldBeExportedToTxt()) {
                            $countProcessed++;

                            $txt .= sprintf('%d. %s', $countProcessed, $question->exportToTxt())."\r\n";
                        } else {
                            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Экспортировать в текстовой формат возможно только вопросы с одиночным и множественным выбором')));
                            $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' => $subjectId, 'test_id' => $testId));
                        }
                    }
                    $oldEncoding = mb_internal_encoding();
                    mb_internal_encoding("Windows-1251");
                    $this->_helper->SendFile->sendData(
                         $txt,
                         'text/plain; charset='.Zend_Registry::get('config')->charset,
                         'questions.txt'
                     );
                    mb_internal_encoding($oldEncoding);
                    die();
                }
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не выбраны вопросы')));
            }
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не выбраны вопросы')));
        }

        $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' => $subjectId, 'test_id' => $testId));

    }
}