<? echo _('Период');?>;<? echo _('Количество заявок') . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
<? echo $value?>;<? echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>