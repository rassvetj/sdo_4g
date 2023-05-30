<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:question:list:new') && $this->isEditable):?>
        <?php// echo $this->addButton($this->url(array('action' => 'new-task', 'controller' => 'list', 'module' => 'question')), _('Добавить вариант'))?>
        <?php echo $this->actions(
            'question-list',
            array(
                array('url' => $this->url(array('action' => 'new', 'controller' => 'question', 'module' => 'task')), 'title' =>  _('Создать вариант')),
               // array('url' => $this->url(array('action' => 'index', 'controller' => 'import', 'module' => 'question', 'source' => 'txt')), 'title' =>  _('Импортировать вопросы из текстового файла'))
               array('url' => $this->url(array('action' => 'task', 'controller' => 'import', 'module' => 'question', 'source' => 'txt')), 'title' =>  _('Импортировать вопросы из текстового файла'))
            )
        );
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
