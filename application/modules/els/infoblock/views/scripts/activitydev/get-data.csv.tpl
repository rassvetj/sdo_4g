<? echo $this->legendY;?>;<? echo $this->legendX . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
<? echo iconv('UTF-8', Zend_Registry::get('config')->charset, $value)?>;<? echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>