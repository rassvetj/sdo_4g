<?php
class HM_Form_Icon extends HM_Form {

    public function init() {
        $this->setAction($this->getView()->url());

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Загрузить иконку из файла'),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => null,
        )
        );

        $lessonId = $this->getParam('lesson_id', 0);

        if ($lessonId != 0) {
            /** @var HM_Lesson_LessonService $subj */
            $lessonService = $this->getService('Lesson');
            /** @var HM_Collection $lessonCollection */
            $lessonCollection = $lessonService->fetchAll($lessonService->quoteInto('SHEID = ?', $lessonId));
            /** @var HM_Lesson_LessonModel $lessonModel */
            $lessonModel = $lessonService->getOne($lessonCollection);
            $icon = $lessonModel->getUserIcon();
            //    ->getById($lessonId);
            //$icon = $subj->getUserIcon();
        }
        $this->addElement('serverFile', 'server_icon', array(
                'Label' => _('Выбрать иконку из файлов на сервере'),
                'Value' => $icon,
                'preview' => $icon,
            )
        );

        $this->addDisplayGroup(array(
            'icon',
            'server_icon',
        ),
            'lessonLessons',
            array('legend' => _('Иконка'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}