<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:criterion:list:new')):?>
    <?php echo $this->actions('criterion');?>
<?php endif;?>

<?php echo $this->grid;?>