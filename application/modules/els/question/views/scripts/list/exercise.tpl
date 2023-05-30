<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:question:list:new')):?>
        <?php echo $this->Actions(
            'question-exercise',
            array(
                array(
                    'title' => _('Создать вопрос'),
                    'url' => $this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'question', 'type'=>'9'))
                )
            )
        )
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
