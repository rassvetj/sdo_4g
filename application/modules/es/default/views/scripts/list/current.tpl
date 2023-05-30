<?php if (!$this->isGridAjaxRequest): ?>	
	<div class="_grid_gridswitcher">				
		<a href="<?=$this->url(array('module' => 'default', 'controller' => 'list', 'action' => 'index'));?>" ><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?=_('Стандартное отображение')?></div></a>		
		<div  class="ending _u_selected"><?=_('Все уведомления')?></div>
		<a href="<?=$this->url(array('module' => 'default', 'controller' => 'list', 'action' => 'deleted'));?>" ><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?=_('Удаленные уведомления')?></div></a>
	</div>
	<br />
	<br />
	<?=$this->grid;?>	
<?php else : ?>
	<?=$this->grid;?>	
<?php endif; ?>