<?php if (!$this->gridAjaxRequest):?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:lesson:list:new')):?>
<?php echo $this->Actions('customer', array(array('title' => _('Добавить видеоролик'),
'url' => $this->url(array('action' => 'new'))
)));?>
<?php endif;?>
<?php endif;?>
<?php echo $this->grid?>