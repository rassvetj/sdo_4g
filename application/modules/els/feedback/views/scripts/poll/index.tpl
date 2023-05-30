<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:feedback:poll:new')):?>
    <?php echo $this->Actions('polls', 
        array(
            array(
            	'title' => _('Создать опрос'), 
            	'url' => 
                    $this->url(
                        array(
                        	'action' => 'new'
                        )
                    )
             )
         )
     );?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>