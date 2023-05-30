<? echo _('Название курса');?>;<? echo _('Записаны');?>;<? echo _('Учатся');?>;<? echo _('Завершили');?>;<? echo _('%') . PHP_EOL;?>
<?foreach ($this->data as $value):?>
    <? echo iconv('UTF-8', Zend_Registry::get('config')->charset, $value[0]);?>;<? echo $value[1]?>;<? echo $value[2]?>;<? echo $value[3]?>;<? echo $value[4]?>;<? echo PHP_EOL;?>
<?endforeach;?>