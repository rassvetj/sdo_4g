<? echo _('Курс');?>;<? echo _('Количество заявок') . "\r\n";?>
<?foreach ($this->data as $key => $value):?>
<? echo $key?>;<? echo $value . "\r\n";?>
<?endforeach;?>