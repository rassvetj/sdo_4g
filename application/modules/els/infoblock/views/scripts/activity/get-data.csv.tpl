<? echo _('Дата');?>;<? echo $this->legend . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
	<? echo $value?>;<? echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>