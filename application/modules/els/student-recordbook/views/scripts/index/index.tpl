<a href="<?=$this->url(array('module' => 'student-recordbook', 'controller' => 'export', 'action' => 'pdf'), 'default', true);?>" target="_blank">Скачать</a>
<br />

<div style="width: 1224px; margin: 0 auto;">
	<?php echo $this->p_main;?>
	
	<div style="clear:both; padding: 10px;"> </div>
	
	<?php if(count($this->p_base) > 0) : ?>
		<?php foreach($this->p_base as $p) : ?>		
			<?=$p;?>
			<div style="clear:both; padding: 10px;"> </div>
		<?php endforeach; ?>		
	<?php endif; ?>	

	<?php echo $this->p_facult;?>

	<div style="clear:both; padding: 10px;"> </div>

	<?php echo $this->p_coursework;?>

	<div style="clear:both; padding: 0px;"> </div>

	<?php echo $this->p_practice;?>

	<div style="clear:both; padding: 10px;"> </div>

	<?php echo $this->p_scientific;?>

	<div style="clear:both; padding: 10px;"> </div>

	<?php echo $this->p_gos;?>

	<div style="clear:both; padding: 0px;"> </div>
	<div style="margin-top:-50px; display: inline-block;">
		<?php echo $this->p_grad_work;?>
	</div>	
	
</div>
<div style="clear:both;"></div>
<br>