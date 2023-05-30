<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:course:list:new')):?>
    <?php echo $this->Actions('course');?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>