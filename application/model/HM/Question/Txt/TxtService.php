<?php
class HM_Question_Txt_TxtService extends HM_Service_Import_Abstract
{
    public function fetchAll($filename = null, $where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }
        return $this->getMapper()->fetchAll($filename, $where, $order, $count, $offset);
    }

    public function getForm()
    {
        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions(
            array(
                'Label' => _('Файл данных (txt)'),
                'Destination' => Zend_Registry::get('config')->path->upload->temp,
                'Validators' => array(
                    array('Count', false, 1),
                    array('Extension', false, 'txt')
                ),
                'file_size_limit' => 0,
                'file_types' => '*.txt',
                'file_upload_limit' => 1,
                'Required' => true
            )
        );
        $form->getElement('cancelUrl')->setValue(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
        return $form;
    }
}