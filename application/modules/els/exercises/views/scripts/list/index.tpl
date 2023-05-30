<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:exercises:list:new')):?>
        <?php echo $this->Actions(
            'exercises',
            array(
                array(
                    'title' => _('Создать упражнение'),
                    'url' => $this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'exercises'))
                )
            )
        )
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
