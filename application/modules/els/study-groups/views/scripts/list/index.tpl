<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:study-groups:list:new')):?>
<?php echo $this->actions('study-groupss')?>
<?php endif;?>
<?php if (!$this->gridAjaxRequest):?>
	<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<?php endif;?>
<?php echo $this->grid?>