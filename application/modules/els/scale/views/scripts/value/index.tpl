<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:scale:value:new')):?>
<?php echo $this->actions(
    'value',
    array(
        array(
            'url' => $this->url(array('action' => 'new', 'controller' => 'value', 'module' => 'scale')),
            'title' =>  _('Создать значение шкалы')
        ),
    )
);
?>
<?php endif;?>
<?php echo $this->grid;?>