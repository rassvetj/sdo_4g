<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:question:list:new') && $this->isEditable):?>
        <?php //echo $this->addButton($this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'question')), _('Создать вопрос'))?>
        <?php echo $this->actions(
            'question-list',
            array(
                array('url' => $this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'question')), 'title' =>  _('Создать вопрос')),
                array('url' => $this->url(array('action' => 'index', 'controller' => 'import', 'module' => 'question', 'source' => 'txt')), 'title' =>  _('Импортировать вопросы из текстового файла'))
            )
        );
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
