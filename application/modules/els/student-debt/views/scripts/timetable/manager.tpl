<?php if($this->gridAjaxRequest):?>
	<?=$this->grid?>
<?php else:?>
	<div>	
		<p>Рекомендуемый размер файла - не более 5 000 строк</p>
		<br />
	</div>
	<?=$this->form?>
	<br />
	<p style="font-size:15px; font-weight:bold;"><?=_('Текущие данные')?>:</p>
	<br />
	<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
	<?=$this->grid?>
<?php endif;?>