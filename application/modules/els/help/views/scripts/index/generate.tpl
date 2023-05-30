<?php foreach($this->helps as $help):?>	

<div><a href="<?php echo $this->url(array('action' => 'delete', 'help_id' => $help->help_id))?>"><?php echo _('удалить')?></a></div>
	<?php if($help->title && $help->text):?>
		<a href="<?php echo $this->url(array('action' => $help->action, 'controller' => $help->controller, 'module' => $help->module))?>">
		<?php echo $help->title?>
		</a> 
		<?php if(!$help->moderated):?>
			<?php echo _('не отрецензировано')?>
		<?php endif;?>
		<br>
		<?php echo $help->text?>
	<?php else:?>
		<a href="<?php echo $this->url(array('action' => $help->action, 'controller' => $help->controller, 'module' => $help->module))?>">
			<?php echo _('Не заполнено');?>
		</a> 
	<?php endif;?>
	<br><br>
<?php endforeach;?>