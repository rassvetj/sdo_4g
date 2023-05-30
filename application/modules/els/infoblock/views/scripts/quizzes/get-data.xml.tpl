<pie>
<?foreach ($this->data as $key => $value):?>
	<slice title="<? echo iconv(Zend_Registry::get('config')->charset, 'UTF-8', $key);?>"><? echo $value?></slice>
<?endforeach;?>
</pie>