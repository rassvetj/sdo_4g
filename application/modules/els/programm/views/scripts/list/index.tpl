<?php 

if (!$this->gridAjaxRequest) {
    echo $this->actions('programm');
	?><span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span><?php
}

?>
<?php echo $this->grid?>