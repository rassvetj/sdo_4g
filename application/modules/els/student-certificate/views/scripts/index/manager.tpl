<?php if (!$this->gridAjaxRequest):?>
	<?php echo $this->content_grid?>	
	<?php echo $this->content_form?>	
	<?php echo $this->content_form_q?>	
<?php else : ?>
	<?php echo $this->content_grid?>
<?php endif;?>