<?php 

if (!$this->news->show):?>
    <?php echo _('Содержимое скрыто администратором')?>
<?php else:?>
    <?php  
	
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		if($lng == "eng" && isset($this->news->translation))
			echo $this->news->translation;			
		else 		
			echo $this->news->message;	
		
	?>
	


    <?php if ($this->news->resource_id): ?>
        <iframe src="<?php echo $this->url(array(
        				'module' => 'resource',
        				'controller' => 'index',
        				'action' => 'view',
        				'resource_id' => $this->news->resource_id,
                  )); ?>" name="item" id="resource-iframe" frameborder="0">
            <?php echo _("Отсутствует поддержка iframe!"); ?>
        </iframe>
        <script type="text/javascript">
        var resource_id = <?php echo $this->news->resource_id;// @todo: а если несколько ресурсов с на основе сервисов вз-я..? ?>;
        </script>

    <?php endif;?>    
<?endif;?>