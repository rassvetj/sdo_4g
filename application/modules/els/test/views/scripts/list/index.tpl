<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:test:list:new')):?>
        <?php
            echo $this->Actions(
                'tests',
                array(
                    array(
                        'title' => _('Создать тест'),
                        'url' => $this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'test'))
                    )
                )
            )
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
