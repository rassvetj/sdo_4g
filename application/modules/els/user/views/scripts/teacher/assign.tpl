<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:user:list:new')):?>
    <?php echo $this->Actions('users');?>
                
                
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>