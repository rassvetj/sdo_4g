<?php if (!$this->isAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:event:list:new')):?>
    <?php echo $this->actions('events')?>
<?php endif;?>
<?php echo $this->grid?>